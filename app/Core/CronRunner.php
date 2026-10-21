<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Lease;
use App\Models\SearchAlert;
use PDO;
use Throwable;

class CronRunner
{
    /**
     * Run all active scheduled tasks or a specific task by name
     */
    public static function runAll(?string $taskName = null): array
    {
        $db = Database::getInstance();
        $results = [];

        $query = "SELECT * FROM scheduled_tasks WHERE is_active = 1";
        $params = [];
        if ($taskName !== null) {
            $query .= " AND task_name = :task_name";
            $params['task_name'] = $taskName;
        }

        $tasks = Database::query($query, $params);

        foreach ($tasks as $task) {
            $name = $task['task_name'];
            $startTime = microtime(true);

            // Mark as running
            $stmt = $db->prepare("UPDATE scheduled_tasks SET status = 'running' WHERE id = :id");
            $stmt->execute(['id' => $task['id']]);

            try {
                $output = self::executeTask($name);
                $duration = round(microtime(true) - $startTime, 3);
                $outputSummary = json_encode($output, JSON_UNESCAPED_SLASHES);

                $stmt = $db->prepare(
                    "UPDATE scheduled_tasks 
                     SET status = 'success', last_run_at = NOW(), last_output = :output 
                     WHERE id = :id"
                );
                $stmt->execute([
                    'output' => "Executed in {$duration}s: {$outputSummary}",
                    'id' => $task['id']
                ]);

                $results[$name] = [
                    'status' => 'success',
                    'duration' => "{$duration}s",
                    'details' => $output
                ];
            } catch (Throwable $e) {
                $duration = round(microtime(true) - $startTime, 3);
                $stmt = $db->prepare(
                    "UPDATE scheduled_tasks 
                     SET status = 'failed', last_run_at = NOW(), last_output = :output 
                     WHERE id = :id"
                );
                $stmt->execute([
                    'output' => "Failed after {$duration}s: " . $e->getMessage(),
                    'id' => $task['id']
                ]);

                $results[$name] = [
                    'status' => 'failed',
                    'duration' => "{$duration}s",
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Internal task dispatcher
     */
    protected static function executeTask(string $taskName): array
    {
        $db = Database::getInstance();

        switch ($taskName) {
            case 'check_expiring_leases':
                $expiring = Lease::getExpiringLeases(60);
                $notified = 0;
                foreach ($expiring as $lease) {
                    // Send renewal reminder notification to landlord
                    $notifStmt = $db->prepare(
                        "INSERT INTO in_app_notifications (user_id, title, message, type, link_url, is_read, created_at)
                         VALUES (:user_id, :title, :message, 'lease_renewal', :link_url, 0, NOW())"
                    );
                    $notifStmt->execute([
                        'user_id' => $lease['landlord_id'],
                        'title' => "Lease Expiring in {$lease['days_remaining']} Days",
                        'message' => "Lease for {$lease['property_title']} (Tenant: {$lease['tenant_name']}) expires on {$lease['end_date']}. Consider initiating renewal terms.",
                        'link_url' => url('/dashboard/leases/' . $lease['id'] . '/statement')
                    ]);
                    $notified++;
                }
                return ['expiring_leases_found' => count($expiring), 'reminders_sent' => $notified];

            case 'reconcile_pending_payments':
                // Auto-expire stale pending payments older than 24 hours
                $stmt = $db->prepare(
                    "UPDATE payments 
                     SET status = 'failed' 
                     WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)"
                );
                $stmt->execute();
                $reconciled = $stmt->rowCount();
                return ['stale_payments_expired' => $reconciled];

            case 'process_search_alerts':
                return SearchAlert::dispatchAlerts();

            case 'purge_expired_sessions':
                // Clear sessions inactive for > 30 days
                $stmt = $db->prepare(
                    "DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );
                $stmt->execute();
                $purged = $stmt->rowCount();
                return ['expired_sessions_purged' => $purged];

            default:
                return ['message' => "Generic task '{$taskName}' completed."];
        }
    }
}
