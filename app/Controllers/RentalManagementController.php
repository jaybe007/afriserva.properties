<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Core\NotificationService;
use App\Models\RentalApplication;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;

class RentalManagementController extends Controller
{
    /**
     * Public Rental Application Form.
     */
    public function applyForm(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $property = Property::findBySlug($slug);

        if (!$property || !in_array($property['purpose'], ['rent', 'short_let'], true)) {
            flash('danger', 'This property is not currently accepting tenancy applications.');
            $this->redirect($response, url('properties'));
            return;
        }

        $html = $this->render('properties/apply', [
            'title' => 'Apply for Tenancy: ' . e($property['title']) . ' | Afriserva Real Estate',
            'property' => $property,
            'user' => Auth::user()
        ], 'layouts/main');

        $response->html($html);
    }

    /**
     * Submit Tenancy Application.
     */
    public function submitApplication(Request $request, Response $response, array $params): void
    {
        if (!Auth::check()) {
            flash('warning', 'Please sign in or register to submit a rental application.');
            $this->redirect($response, url('login'));
            return;
        }

        $slug = $params['slug'] ?? '';
        $property = Property::findBySlug($slug);
        if (!$property) {
            flash('danger', 'Property listing not found.');
            $this->redirect($response, url('properties'));
            return;
        }

        $input = $request->all();
        $errors = $this->validate($input, [
            'annual_income' => 'required',
            'employer_name' => 'required',
            'guarantor_name' => 'required',
            'guarantor_phone' => 'required',
            'guarantor_email' => 'required|email',
            'guarantor_relationship' => 'required'
        ]);

        if (!empty($errors)) {
            flash('danger', 'Please fill out all required tenancy application fields.');
            $this->redirect($response, url("properties/{$slug}/apply"));
            return;
        }

        $data = [
            'property_id' => $property['id'],
            'applicant_id' => Auth::id(),
            'landlord_id' => $property['user_id'],
            'annual_income' => (float)$input['annual_income'],
            'employer_name' => $input['employer_name'],
            'employment_status' => $input['employment_status'] ?? 'employed',
            'guarantor_name' => $input['guarantor_name'],
            'guarantor_phone' => $input['guarantor_phone'],
            'guarantor_email' => $input['guarantor_email'],
            'guarantor_relationship' => $input['guarantor_relationship'],
            'notes' => $input['notes'] ?? ''
        ];

        $appId = RentalApplication::create($data);

        // Notify Landlord
        NotificationService::send(
            (int)$property['user_id'],
            "New Rental Application Submitted",
            "Applicant " . Auth::user()['full_name'] . " applied for '{$property['title']}'.",
            'lease',
            url('dashboard/rental-applications')
        );

        flash('success', 'Your tenancy application has been lodged successfully for landlord review.');
        $this->redirect($response, url('dashboard/rental-applications'));
    }

    /**
     * View Applications (Landlords and Applicants).
     */
    public function applications(Request $request, Response $response): void
    {
        $user = Auth::user();
        $userId = Auth::id();

        if (in_array($user['role'], ['landlord', 'agent', 'agency', 'property_manager', 'super_admin'], true)) {
            $applications = RentalApplication::getForLandlord($userId);
            $viewType = 'landlord';
        } else {
            $applications = RentalApplication::getForApplicant($userId);
            $viewType = 'applicant';
        }

        $html = $this->render('dashboard/rental_applications/index', [
            'title' => 'Tenancy Applications | Afriserva Real Estate',
            'applications' => $applications,
            'viewType' => $viewType,
            'user' => $user
        ], 'layouts/dashboard');

        $response->html($html);
    }

    /**
     * Review Tenancy Application Status.
     */
    public function reviewApplication(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('status', 'screening');
        $notes = $request->input('notes', '');

        RentalApplication::updateStatus($id, $status, $notes);

        flash('success', "Rental application transitioned to status: {$status}");
        $this->redirect($response, url('dashboard/rental-applications'));
    }

    /**
     * Screen Tenant Workflow.
     */
    public function screenTenant(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = $request->input('screening_status', 'passed');
        $notes = $request->input('screening_notes', 'Verified employer and guarantor references.');

        RentalApplication::updateScreening($id, $status, $notes);

        flash('success', "Tenant screening recorded with status: {$status}");
        $this->redirect($response, url('dashboard/rental-applications'));
    }

    /**
     * Leases and Rent Schedules.
     */
    public function leases(Request $request, Response $response): void
    {
        $user = Auth::user();
        $userId = Auth::id();

        if (in_array($user['role'], ['landlord', 'agent', 'agency', 'property_manager', 'super_admin'], true)) {
            $leases = Lease::getForLandlord($userId);
            $arrearsSummary = Lease::getArrearsSummary($userId);
            $expiringLeases = Lease::getExpiringLeases($userId, 60);
            $properties = Database::query("SELECT id, title, price FROM properties WHERE user_id = ? AND purpose IN ('rent', 'short_let')", [$userId]);
            $tenants = Database::query("SELECT id, full_name, email FROM users WHERE role IN ('tenant', 'buyer', 'client')");
            $isLandlord = true;
        } else {
            $leases = Lease::getForTenant($userId);
            $arrearsSummary = ['overdue_count' => 0, 'total_arrears' => 0.00];
            $expiringLeases = [];
            $properties = [];
            $tenants = [];
            $isLandlord = false;
        }

        $html = $this->render('dashboard/leases/index', [
            'title' => 'Lease Management & Rent Ledgers | Afriserva Real Estate',
            'leases' => $leases,
            'arrearsSummary' => $arrearsSummary,
            'expiringLeases' => $expiringLeases,
            'properties' => $properties,
            'tenants' => $tenants,
            'isLandlord' => $isLandlord,
            'user' => $user
        ], 'layouts/dashboard');

        $response->html($html);
    }

    /**
     * Create New Tenancy Agreement Record.
     */
    public function createLease(Request $request, Response $response): void
    {
        $input = $request->all();
        $errors = $this->validate($input, [
            'property_id' => 'required',
            'tenant_id' => 'required',
            'lease_start_date' => 'required',
            'lease_end_date' => 'required',
            'rent_amount' => 'required'
        ]);

        if (!empty($errors)) {
            flash('danger', 'Please complete all required tenancy terms.');
            $this->redirect($response, url('dashboard/leases'));
            return;
        }

        $input['landlord_id'] = Auth::id();
        $leaseId = Lease::create($input);

        // Record initial rent schedule item
        Lease::recordPayment(
            $leaseId,
            (float)$input['rent_amount'],
            $input['lease_start_date'],
            $input['lease_start_date'],
            'bank_transfer',
            'paid'
        );

        NotificationService::send(
            (int)$input['tenant_id'],
            "New Tenancy Agreement Registered",
            "Your lease starting {$input['lease_start_date']} has been opened on Afriserva.",
            'lease',
            url('dashboard/leases')
        );

        flash('success', 'Tenancy ledger successfully created.');
        $this->redirect($response, url('dashboard/leases'));
    }

    /**
     * Record Rent Payment Entry.
     */
    public function recordRentPayment(Request $request, Response $response, array $params): void
    {
        $leaseId = (int)($params['id'] ?? 0);
        $amount = (float)$request->input('amount', 0);
        $dueDate = $request->input('due_date', date('Y-m-d'));
        $paidDate = $request->input('paid_date', date('Y-m-d'));
        $method = $request->input('payment_method', 'bank_transfer');
        $status = $request->input('status', 'paid');

        Lease::recordPayment($leaseId, $amount, $dueDate, $paidDate, $method, $status);

        flash('success', "Rent payment record of " . format_money($amount) . " logged to ledger.");
        $this->redirect($response, url('dashboard/leases'));
    }

    /**
     * Maintenance Requests Hub.
     */
    public function maintenance(Request $request, Response $response): void
    {
        $user = Auth::user();
        $userId = Auth::id();

        if (in_array($user['role'], ['landlord', 'agent', 'agency', 'property_manager', 'super_admin'], true)) {
            $requests = MaintenanceRequest::getForLandlord($userId);
            $isLandlord = true;
        } else {
            $requests = MaintenanceRequest::getForTenant($userId);
            $isLandlord = false;
        }

        // Fetch user properties or leases
        $leases = Lease::getForTenant($userId);

        $html = $this->render('dashboard/maintenance/index', [
            'title' => 'Property Maintenance & Repairs | Afriserva Real Estate',
            'requests' => $requests,
            'leases' => $leases,
            'isLandlord' => $isLandlord,
            'user' => $user
        ], 'layouts/dashboard');

        $response->html($html);
    }

    /**
     * Submit Maintenance Ticket.
     */
    public function submitMaintenance(Request $request, Response $response): void
    {
        $input = $request->all();
        $errors = $this->validate($input, [
            'property_id' => 'required',
            'title' => 'required',
            'description' => 'required'
        ]);

        if (!empty($errors)) {
            flash('danger', 'Please provide a title and detailed description of the maintenance issue.');
            $this->redirect($response, url('dashboard/maintenance'));
            return;
        }

        // Get landlord ID from property
        $prop = Database::fetch("SELECT user_id, title FROM properties WHERE id = ?", [(int)$input['property_id']]);
        if (!$prop) {
            flash('danger', 'Property not found.');
            $this->redirect($response, url('dashboard/maintenance'));
            return;
        }

        $input['tenant_id'] = Auth::id();
        $input['landlord_id'] = (int)$prop['user_id'];
        MaintenanceRequest::create($input);

        NotificationService::send(
            (int)$prop['user_id'],
            "New Maintenance Request: " . e($input['title']),
            "Priority: " . strtoupper($input['priority'] ?? 'medium') . " for '{$prop['title']}'",
            'maintenance',
            url('dashboard/maintenance')
        );

        flash('success', 'Maintenance request dispatched to property manager and landlord.');
        $this->redirect($response, url('dashboard/maintenance'));
    }

    /**
     * Assign Contractor & Expenses.
     */
    public function assignMaintenance(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $data = [
            'assigned_contractor_name' => $request->input('assigned_contractor_name'),
            'assigned_contractor_phone' => $request->input('assigned_contractor_phone'),
            'cost' => $request->input('cost'),
            'management_fee' => $request->input('management_fee'),
            'scheduled_date' => $request->input('scheduled_date'),
            'status' => $request->input('status', 'in_progress')
        ];

        MaintenanceRequest::updateAssignment($id, $data);

        flash('success', 'Contractor assigned and maintenance schedule updated.');
        $this->redirect($response, url('dashboard/maintenance'));
    }

    /**
     * Resolve Maintenance.
     */
    public function resolveMaintenance(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        MaintenanceRequest::resolve($id);

        flash('success', 'Maintenance job marked as resolved.');
        $this->redirect($response, url('dashboard/maintenance'));
    }

    /**
     * Downloadable / Printable Landlord Statement.
     */
    public function statement(Request $request, Response $response, array $params): void
    {
        $leaseId = (int)($params['id'] ?? 0);
        $lease = Lease::getById($leaseId);

        if (!$lease) {
            flash('danger', 'Lease record not found.');
            $this->redirect($response, url('dashboard/leases'));
            return;
        }

        $payments = Lease::getPayments($leaseId);

        $html = $this->render('dashboard/leases/statement', [
            'title' => 'Statement of Account - Lease #' . $leaseId . ' | Afriserva Real Estate',
            'lease' => $lease,
            'payments' => $payments,
            'user' => Auth::user()
        ], 'layouts/print');

        $response->html($html);
    }
}
