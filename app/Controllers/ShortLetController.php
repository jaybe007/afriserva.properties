<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\ShortLetReservation;
use App\Models\Property;
use Exception;

class ShortLetController extends Controller
{
    public function book(Request $request, Response $response): void
    {
        if (!Auth::check()) {
            flash('warning', 'Please sign in or create an account to book your executive stay.');
            $this->redirect($response, url('login'));
            return;
        }

        $input = $request->all();
        $errors = $this->validate($input, [
            'property_id' => 'required',
            'check_in_date' => 'required',
            'check_out_date' => 'required',
            'guest_name' => 'required',
            'guest_email' => 'required|email',
            'guest_phone' => 'required'
        ]);

        if (!empty($errors)) {
            flash('danger', 'Please provide valid check-in and check-out dates.');
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('properties?type=short_let'));
            return;
        }

        if (strtotime($input['check_in_date']) < strtotime(date('Y-m-d'))) {
            flash('danger', 'Check-in date cannot be in the past.');
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('properties?type=short_let'));
            return;
        }

        if (strtotime($input['check_out_date']) <= strtotime($input['check_in_date'])) {
            flash('danger', 'Check-out date must be at least one day after check-in.');
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('properties?type=short_let'));
            return;
        }

        $prop = Property::findById((int)$input['property_id']);
        if (!$prop) {
            flash('danger', 'Property listing not found.');
            $this->redirect($response, url('properties'));
            return;
        }

        $input['user_id'] = Auth::id();
        $input['nightly_rate'] = $prop['price'];

        try {
            $reservationId = ShortLetReservation::create($input);
            flash('success', "Your executive stay at {$prop['title']} has been reserved successfully! The host will contact you for check-in access.");
            $this->redirect($response, url('dashboard'));
        } catch (Exception $e) {
            flash('danger', $e->getMessage());
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('properties/' . $prop['slug']));
        }
    }
}
