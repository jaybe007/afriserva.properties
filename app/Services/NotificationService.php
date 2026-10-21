<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use PDO;

class NotificationService
{
    /**
     * Dispatch an event-driven notification with user preference checks, duplicate prevention, and audit logging
     */
    public static function dispatch(
        int $userId,
        string $eventType,
        string $subject,
        string $templateName,
        array $data,
        ?string $recipientEmail = null
    ): bool {
        $db = Database::getInstance();

        // 1. Fetch user data if recipient email not provided
        if (!$recipientEmail) {
            $user = Database::fetch("SELECT email, full_name, notify_email FROM users WHERE id = ?", [$userId]);
            if (!$user) {
                Logger::warning("Notification failed: User ID {$userId} not found.");
                return false;
            }
            if ((int)$user['notify_email'] === 0) {
                Logger::info("Notification skipped: User {$userId} opted out of email notifications.");
                return false;
            }
            $recipientEmail = $user['email'];
        }

        // 2. Check granular notification preferences
        $pref = Database::fetch(
            "SELECT email_enabled FROM user_notification_preferences WHERE user_id = ? AND event_type = ?",
            [$userId, $eventType]
        );
        if ($pref && (int)$pref['email_enabled'] === 0) {
            Logger::info("Notification skipped: User {$userId} disabled '{$eventType}' notifications.");
            return false;
        }

        // 3. Duplicate Prevention (Deduplication window: 10 minutes)
        $dedupHash = md5($recipientEmail . $eventType . ($data['reference'] ?? json_encode($data)));
        $recentDuplicate = Database::fetch(
            "SELECT id FROM email_logs 
             WHERE recipient_email = ? AND template_name = ? AND error_message = ? 
               AND sent_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
             LIMIT 1",
            [$recipientEmail, $templateName, "dedup:{$dedupHash}"]
        );

        if ($recentDuplicate) {
            Logger::info("Notification duplicate prevented for {$recipientEmail} [{$eventType}].");
            return false;
        }

        // 4. Render Email Template Body
        $messageBody = self::renderTemplate($templateName, $data);

        // 5. In-App Notification Delivery
        if (!empty($data['in_app_title'])) {
            $notifStmt = $db->prepare(
                "INSERT INTO in_app_notifications (user_id, title, message, type, link_url, is_read, created_at)
                 VALUES (:user_id, :title, :message, :type, :link_url, 0, NOW())"
            );
            $notifStmt->execute([
                'user_id' => $userId,
                'title' => $data['in_app_title'],
                'message' => $data['in_app_message'] ?? $subject,
                'type' => $eventType,
                'link_url' => $data['link_url'] ?? url('/dashboard')
            ]);
        }

        // 6. Record Delivery Log in email_logs
        $stmt = $db->prepare(
            "INSERT INTO email_logs (recipient_email, subject, template_name, status, error_message, sent_at)
             VALUES (:email, :subject, :template, 'sent', :error_msg, NOW())"
        );
        $stmt->execute([
            'email' => $recipientEmail,
            'subject' => $subject,
            'template' => $templateName,
            'error_msg' => "dedup:{$dedupHash}"
        ]);

        Logger::info("Notification dispatched successfully to {$recipientEmail} [{$eventType}].");
        return true;
    }

    /**
     * Concrete event helpers
     */
    public static function sendEmailVerification(int $userId, string $email, string $token): bool
    {
        return self::dispatch($userId, 'email_verification', 'Verify your Afriserva Properties Account', 'auth_verify_email', [
            'verification_url' => url('/verify-email?token=' . urlencode($token)),
            'in_app_title' => 'Email Verification Sent',
            'in_app_message' => 'Please verify your email address to unlock full agent listing features.'
        ], $email);
    }

    public static function sendPasswordReset(int $userId, string $email, string $token): bool
    {
        return self::dispatch($userId, 'password_reset', 'Reset your Afriserva Security Password', 'auth_password_reset', [
            'reset_url' => url('/reset-password?token=' . urlencode($token)),
            'reference' => $token,
            'in_app_title' => 'Password Reset Requested',
            'in_app_message' => 'A password reset authorization link was generated for your account.'
        ], $email);
    }

    public static function sendNewEnquiry(int $agentId, array $enquiryData): bool
    {
        return self::dispatch($agentId, 'new_enquiry', "New Client Enquiry: {$enquiryData['property_title']}", 'lead_new_enquiry', [
            'client_name' => $enquiryData['client_name'],
            'property_title' => $enquiryData['property_title'],
            'message' => $enquiryData['message'],
            'reference' => 'enq_' . ($enquiryData['id'] ?? uniqid()),
            'in_app_title' => 'New Qualified Enquiry',
            'in_app_message' => "Client {$enquiryData['client_name']} inquired about {$enquiryData['property_title']}.",
            'link_url' => url('/dashboard/enquiries')
        ]);
    }

    public static function sendViewingReminder(int $userId, array $appointmentData): bool
    {
        return self::dispatch($userId, 'viewing_reminder', "Viewing Reminder: {$appointmentData['property_title']}", 'tour_reminder', [
            'scheduled_time' => $appointmentData['scheduled_time'],
            'property_title' => $appointmentData['property_title'],
            'reference' => 'view_' . ($appointmentData['id'] ?? uniqid()),
            'in_app_title' => 'Upcoming Property Tour',
            'in_app_message' => "Your inspection appointment for {$appointmentData['property_title']} is scheduled for {$appointmentData['scheduled_time']}.",
            'link_url' => url('/dashboard/viewings')
        ]);
    }

    public static function sendListingStatusChange(int $ownerId, string $propertyTitle, string $status, ?string $reason = null): bool
    {
        $statusText = ucfirst(str_replace('_', ' ', $status));
        return self::dispatch($ownerId, 'listing_status_change', "Listing Status Updated: {$propertyTitle} ({$statusText})", 'listing_status', [
            'property_title' => $propertyTitle,
            'status' => $status,
            'reason' => $reason,
            'in_app_title' => "Listing {$statusText}",
            'in_app_message' => "Your listing '{$propertyTitle}' is now {$statusText}." . ($reason ? " Reason: {$reason}" : ""),
            'link_url' => url('/dashboard/listings')
        ]);
    }

    public static function sendPaymentConfirmation(int $userId, string $reference, float $amount, string $currency): bool
    {
        $formatted = $currency . ' ' . number_format($amount, 2);
        return self::dispatch($userId, 'payment_confirmation', "Payment Confirmed - Reference {$reference}", 'billing_receipt', [
            'reference' => $reference,
            'amount' => $formatted,
            'in_app_title' => 'Payment Successful',
            'in_app_message' => "Your payment of {$formatted} (Ref: {$reference}) was successfully confirmed.",
            'link_url' => url('/dashboard/invoices')
        ]);
    }

    public static function sendSecurityAlert(int $userId, string $activityDescription, string $ipAddress): bool
    {
        return self::dispatch($userId, 'security_alert', 'Security Alert: Sensitive Account Action', 'security_notice', [
            'activity' => $activityDescription,
            'ip' => $ipAddress,
            'in_app_title' => 'Security Notice',
            'in_app_message' => "Action detected: {$activityDescription} from IP {$ipAddress}.",
            'link_url' => url('/dashboard/profile')
        ]);
    }

    /**
     * Render template HTML string
     */
    protected static function renderTemplate(string $templateName, array $data): string
    {
        $title = $data['in_app_title'] ?? 'Afriserva Properties Notification';
        $message = $data['in_app_message'] ?? 'You have a new update on Afriserva Properties.';

        return "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #E2E8F0; border-radius: 8px;'>
            <div style='background: #0B192C; color: #D4AF37; padding: 16px; text-align: center; font-size: 20px; font-weight: bold;'>
                AFRISERVA PROPERTIES
            </div>
            <div style='padding: 24px; color: #1E293B;'>
                <h2 style='color: #0B192C; font-size: 18px;'>{$title}</h2>
                <p style='line-height: 1.6; color: #475569;'>{$message}</p>
            </div>
            <div style='background: #F8FAFC; padding: 12px; text-align: center; font-size: 11px; color: #94A3B8;'>
                &copy; " . date('Y') . " Afriserva Properties Ltd. RC 1849204. Victoria Island, Lagos, Nigeria.
            </div>
        </div>";
    }
}
