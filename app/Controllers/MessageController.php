<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Core\NotificationService;
use App\Models\Conversation;
use App\Models\Message;

class MessageController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $conversations = Conversation::getForUser($userId);
        $unreadCount = Message::getUnreadCount($userId);

        $html = $this->render('dashboard/messages/index', [
            'title' => 'Messages & Inquiries | Afriserva Dashboard',
            'conversations' => $conversations,
            'unreadCount' => $unreadCount,
            'user' => Auth::user()
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function thread(Request $request, Response $response, array $params): void
    {
        $userId = Auth::id();
        $conversationId = (int)($params['id'] ?? 0);

        $conversation = Conversation::getById($conversationId, $userId);
        if (!$conversation) {
            flash('danger', 'Conversation thread not found or access denied.');
            $this->redirect($response, url('dashboard/messages'));
            return;
        }

        // Mark unread messages in this thread as read
        Message::markAsRead($conversationId, $userId);

        $messages = Message::getByConversation($conversationId);
        $conversations = Conversation::getForUser($userId);

        $html = $this->render('dashboard/messages/thread', [
            'title' => 'Message Thread | Afriserva Dashboard',
            'activeConversation' => $conversation,
            'messages' => $messages,
            'conversations' => $conversations,
            'user' => Auth::user()
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function send(Request $request, Response $response, array $params): void
    {
        $userId = Auth::id();
        $conversationId = (int)($params['id'] ?? 0);
        $messageText = trim($request->input('message', ''));

        if (empty($messageText)) {
            flash('warning', 'Cannot send an empty message.');
            $this->redirect($response, url("dashboard/messages/{$conversationId}"));
            return;
        }

        $conversation = Conversation::getById($conversationId, $userId);
        if (!$conversation) {
            flash('danger', 'Unauthorized or invalid conversation thread.');
            $this->redirect($response, url('dashboard/messages'));
            return;
        }

        // Determine recipient
        $recipientId = ($conversation['user_one_id'] == $userId) ? (int)$conversation['user_two_id'] : (int)$conversation['user_one_id'];

        Message::create($conversationId, $userId, $messageText);

        // Notify recipient
        $sender = Auth::user();
        NotificationService::send(
            $recipientId,
            "New message from " . e($sender['full_name']),
            substr(strip_tags($messageText), 0, 90) . '...',
            'message',
            url("dashboard/messages/{$conversationId}")
        );

        flash('success', 'Message sent successfully.');
        $this->redirect($response, url("dashboard/messages/{$conversationId}"));
    }

    public function notifications(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $notifications = NotificationService::getForUser($userId, 50);
        $unreadCount = NotificationService::getUnreadCount($userId);

        $html = $this->render('dashboard/notifications/index', [
            'title' => 'Notifications | Afriserva Dashboard',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'user' => Auth::user()
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function markNotificationRead(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        NotificationService::markAsRead($id, Auth::id());
        $this->redirect($response, url('dashboard/notifications'));
    }

    public function markAllNotificationsRead(Request $request, Response $response): void
    {
        NotificationService::markAllAsRead(Auth::id());
        flash('success', 'All notifications marked as read.');
        $this->redirect($response, url('dashboard/notifications'));
    }

    /**
     * Connect buyer directly with Afriserva Administration Desk.
     */
    public function contactAdminDirect(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $adminId = (int)(Database::fetchColumn(
            "SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE r.name = 'super_admin' ORDER BY u.id ASC LIMIT 1"
        ) ?: 1);

        $convId = Conversation::findOrCreate($userId, $adminId, null);

        flash('info', 'Connected directly to the Afriserva Administration & Transaction Desk. Type your message below to speak with an official advisor.');
        $this->redirect($response, url("dashboard/messages/{$convId}"));
    }

    /**
     * Connect buyer directly with Afriserva Admin Desk regarding a specific property (before or after viewing).
     */
    public function contactAdminForProperty(Request $request, Response $response, array $params): void
    {
        $userId = Auth::id();
        $propertyId = (int)($params['id'] ?? 0);
        $property = Database::fetch("SELECT id, title, slug, reference_code FROM properties WHERE id = ?", [$propertyId]);

        if (!$property) {
            flash('warning', 'Property listing not found.');
            $this->redirect($response, url('properties'));
            return;
        }

        $adminId = (int)(Database::fetchColumn(
            "SELECT u.id FROM users u JOIN user_roles ur ON ur.user_id = u.id JOIN roles r ON r.id = ur.role_id WHERE r.name = 'super_admin' ORDER BY u.id ASC LIMIT 1"
        ) ?: 1);

        $convId = Conversation::findOrCreate($userId, $adminId, $propertyId);

        // Pre-seed an initial context message if thread is brand new
        $messageCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM messages WHERE conversation_id = ?", [$convId]);
        if ($messageCount === 0) {
            $contextText = "Hello Afriserva Admin Desk, I am reaching out regarding '{$property['title']}' (Listing Ref: {$property['reference_code']}). I would like assistance with title due diligence, viewing coordination, or transaction terms.";
            Message::create($convId, $userId, $contextText);
        }

        flash('success', "Connected directly to Afriserva Administration Desk for '{$property['title']}'. An official transaction officer is ready to assist you.");
        $this->redirect($response, url("dashboard/messages/{$convId}"));
    }
}
