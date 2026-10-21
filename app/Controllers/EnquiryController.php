<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Core\NotificationService;
use App\Models\Enquiry;
use App\Models\ViewingAppointment;
use App\Models\Conversation;
use App\Models\Message;

class EnquiryController extends Controller
{
    public function submitEnquiry(Request $request, Response $response): void
    {
        $input = $request->all();
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        // 1. Rate Limiting Check
        if (Enquiry::isRateLimited($input['email'] ?? '', $clientIp)) {
            if ($request->isAjax()) {
                $this->json($response, [
                    'status' => 'error',
                    'message' => 'Too many enquiries submitted recently. Please wait a few minutes before trying again.'
                ], 429);
                return;
            }
            flash('warning', 'You have submitted multiple enquiries recently. Please wait a few minutes.');
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/'));
            return;
        }

        $errors = $this->validate($input, [
            'property_id' => 'required',
            'agent_id' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required'
        ]);

        if (!empty($errors)) {
            if ($request->isAjax()) {
                $this->json($response, ['status' => 'error', 'errors' => $errors], 422);
                return;
            }
            flash('danger', 'Please correct the highlighted form errors.');
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/'));
            return;
        }

        $userId = Auth::id();
        $input['user_id'] = $userId;
        $enquiryId = Enquiry::create($input);

        // Fetch property title for notifications
        $property = Database::fetch("SELECT title, slug FROM properties WHERE id = ?", [(int)$input['property_id']]);
        $propTitle = $property['title'] ?? 'Selected Property';

        // Notify Agent / Landlord via in-app alert
        $agentId = (int)$input['agent_id'];
        NotificationService::send(
            $agentId,
            "New Property Lead: " . e($input['name']),
            "Received an enquiry regarding '{$propTitle}'. Message: " . substr(strip_tags($input['message']), 0, 100) . '...',
            'enquiry',
            url('dashboard/enquiries')
        );

        // If client is logged in, initiate a conversation thread for in-app messaging history
        if ($userId && $userId !== $agentId) {
            $convId = Conversation::findOrCreate($userId, $agentId, (int)$input['property_id']);
            Message::create($convId, $userId, $input['message']);
        }

        if ($request->isAjax()) {
            $this->json($response, [
                'status' => 'success',
                'enquiry_id' => $enquiryId,
                'message' => 'Your enquiry has been delivered directly to the assigned property advisor.'
            ]);
            return;
        }

        flash('success', 'Thank you! Your enquiry has been forwarded directly to the property agent.');
        $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/'));
    }

    public function unlockContact(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $agentId = Auth::id();

        if (Enquiry::unlockContact($id, $agentId)) {
            flash('success', 'Client contact details unlocked successfully.');
        } else {
            flash('danger', 'Unable to unlock contact details or unauthorized.');
        }

        $this->redirect($response, url('dashboard/enquiries'));
    }

    public function bookViewing(Request $request, Response $response): void
    {
        $input = $request->all();

        if (!Auth::check()) {
            if ($request->isAjax()) {
                $this->json($response, ['status' => 'error', 'message' => 'Please sign in or register to schedule an inspection.'], 401);
                return;
            }
            flash('warning', 'Please sign in or register to book a property inspection.');
            $this->redirect($response, url('login'));
            return;
        }

        $errors = $this->validate($input, [
            'property_id' => 'required',
            'agent_id' => 'required',
            'appointment_date' => 'required',
            'appointment_time' => 'required'
        ]);

        if (!empty($errors)) {
            if ($request->isAjax()) {
                $this->json($response, ['status' => 'error', 'errors' => $errors], 422);
                return;
            }
            flash('danger', 'Please choose a valid inspection date and preferred time.');
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/'));
            return;
        }

        $userId = Auth::id();
        $input['user_id'] = $userId;
        $appointmentId = ViewingAppointment::create($input);

        // Notify Agent of appointment request
        $property = Database::fetch("SELECT title FROM properties WHERE id = ?", [(int)$input['property_id']]);
        $propTitle = $property['title'] ?? 'Listing';

        NotificationService::send(
            (int)$input['agent_id'],
            "Viewing Request Scheduled",
            "A prospective client requested an inspection for '{$propTitle}' on {$input['appointment_date']} at {$input['appointment_time']}.",
            'viewing',
            url('dashboard/viewings')
        );

        if ($request->isAjax()) {
            $this->json($response, [
                'status' => 'success',
                'appointment_id' => $appointmentId,
                'message' => 'Your property viewing appointment has been requested. The advisor will confirm shortly.'
            ]);
            return;
        }

        flash('success', 'Your inspection request has been submitted. The agent will confirm your appointment shortly.');
        $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/'));
    }

    public function updateViewingStatus(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status', 'confirmed');
        $newDate = $request->input('appointment_date');
        $newTime = $request->input('appointment_time');

        $appt = Database::fetch("SELECT * FROM viewing_appointments WHERE id = ?", [$id]);
        if (!$appt) {
            flash('danger', 'Appointment record not found.');
            $this->redirect($response, url('dashboard/viewings'));
            return;
        }

        if ($status === 'rescheduled' && (!empty($newDate) && !empty($newTime))) {
            Database::execute(
                "UPDATE viewing_appointments 
                 SET status = 'rescheduled', appointment_date = ?, appointment_time = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$newDate, $newTime, $id]
            );
            NotificationService::send(
                (int)$appt['user_id'],
                "Viewing Rescheduled",
                "Your property viewing has been rescheduled to {$newDate} at {$newTime}.",
                'viewing',
                url('dashboard/viewings')
            );
            flash('info', "Inspection appointment rescheduled to {$newDate} {$newTime}.");
        } else {
            ViewingAppointment::updateStatus($id, $status);
            NotificationService::send(
                (int)$appt['user_id'],
                "Viewing Status: " . ucfirst($status),
                "Your property viewing appointment status has been updated to {$status}.",
                'viewing',
                url('dashboard/viewings')
            );
            flash('success', "Viewing appointment status updated to {$status}.");
        }

        $this->redirect($response, url('dashboard/viewings'));
    }
}
