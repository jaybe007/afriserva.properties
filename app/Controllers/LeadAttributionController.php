<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Property;

class LeadAttributionController extends Controller
{
    public function trackWhatsAppClick(Request $request, Response $response, array $params): void
    {
        $propertyId = (int)($params['id'] ?? 0);
        $prop = Property::findById($propertyId);

        if (!$prop) {
            $this->redirect($response, url('properties'));
            return;
        }

        $userId = Auth::id();
        $ip = $request->ip();

        // Fetch Agent's WhatsApp number
        $agent = Database::fetch(
            "SELECT u.phone, ap.whatsapp_number 
             FROM users u 
             LEFT JOIN agent_profiles ap ON ap.user_id = u.id 
             WHERE u.id = ?",
            [(int)$prop['user_id']]
        );

        $rawPhone = $agent['whatsapp_number'] ?? $agent['phone'] ?? '2348030000001';
        $phoneDigits = preg_replace('/[^0-9]/', '', $rawPhone);

        // Record lead attribution in database to avoid platform leakage
        Database::execute(
            "INSERT INTO enquiries (property_id, user_id, agent_id, name, email, phone, message, enquiry_type, status, created_at)
             VALUES (?, ?, ?, 'WhatsApp Visitor', 'unspecified@lead.afriserva.com', ?, ?, 'pricing_inquiry', 'contacted', NOW())",
            [
                $propertyId,
                $userId,
                (int)$prop['user_id'],
                $phoneDigits,
                "Attributed WhatsApp lead generated from IP {$ip} for property Ref: {$prop['reference_code']}"
            ]
        );

        $text = urlencode("Hello, I am contacting you from Afriserva Real Estate (afriserva.com) regarding your listing: {$prop['title']} (Listing Ref: {$prop['reference_code']}). Is this property still available for inspection?");
        $whatsappUrl = "https://wa.me/{$phoneDigits}?text={$text}";

        $this->redirect($response, $whatsappUrl);
    }
}
