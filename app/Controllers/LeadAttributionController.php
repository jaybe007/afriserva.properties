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

        // Anti-Bypass Security: Never route directly to seller/agent private number.
        // Direct WhatsApp inquiries are routed strictly through the official Afriserva Transaction Desk Concierge.
        $officialLine = (string)Database::fetchColumn(
            "SELECT value FROM system_settings WHERE `key` = 'official_whatsapp' OR `key` = 'contact_whatsapp' LIMIT 1"
        );
        if (empty($officialLine)) {
            $officialLine = '2348002374737'; // Afriserva Official Concierge Desk
        }
        $phoneDigits = preg_replace('/[^0-9]/', '', $officialLine);

        // Record lead attribution in database to avoid platform leakage
        Database::execute(
            "INSERT INTO enquiries (property_id, user_id, agent_id, name, email, phone, message, enquiry_type, status, created_at)
             VALUES (?, ?, ?, 'Afriserva Concierge Lead', 'concierge@afriserva.com', ?, ?, 'pricing_inquiry', 'new', NOW())",
            [
                $propertyId,
                $userId,
                (int)$prop['user_id'],
                $phoneDigits,
                "Attributed concierge lead generated from IP {$ip} for property Ref: {$prop['reference_code']}. Handled by Afriserva Transaction Desk."
            ]
        );

        $text = urlencode("Hello Afriserva Transaction Desk, I am interested in property listing: {$prop['title']} (Listing Ref: {$prop['reference_code']}) on afriserva.com. Please provide verification details and assign an inspection advisor.");
        $whatsappUrl = "https://wa.me/{$phoneDigits}?text={$text}";

        $this->redirect($response, $whatsappUrl);
    }
}
