<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Property;
use App\Models\Enquiry;
use App\Models\ViewingAppointment;
use App\Models\Subscription;
use App\Models\Location;

class DashboardController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $user = Auth::user();
        $userId = (int)$user['id'];

        $isProfessional = Auth::hasRole(['agent', 'agency_admin', 'landlord', 'developer', 'seller', 'property_manager', 'super_admin']);

        if ($isProfessional) {
            $listingsCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE user_id = ? AND deleted_at IS NULL", [$userId]);
            $enquiriesCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM enquiries WHERE agent_id = ?", [$userId]);
            $viewingsCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM viewing_appointments WHERE agent_id = ? AND status = 'pending'", [$userId]);
            $totalViews = (int)Database::fetchColumn("SELECT COALESCE(SUM(view_count), 0) FROM properties WHERE user_id = ? AND deleted_at IS NULL", [$userId]);

            $recentEnquiries = Enquiry::getByAgent($userId, 5);
            $upcomingAppointments = ViewingAppointment::getByAgent($userId);
            $recentProperties = Property::all(['user_id' => $userId], 5);
            $subscription = Subscription::getUserSubscription($userId);

            $html = $this->render('dashboard/index', [
                'title' => 'Executive Agent & Landlord Portal | Afriserva Real Estate',
                'user' => $user,
                'isProfessional' => true,
                'stats' => [
                    'listings' => $listingsCount,
                    'enquiries' => $enquiriesCount,
                    'pending_viewings' => $viewingsCount,
                    'total_views' => $totalViews
                ],
                'recentEnquiries' => $recentEnquiries,
                'upcomingAppointments' => $upcomingAppointments,
                'recentProperties' => $recentProperties,
                'subscription' => $subscription
            ], 'layouts/dashboard');
        } else {
            // Client/Buyer & Tenant Portal
            $appointments = ViewingAppointment::getByUser($userId);
            $savedCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM saved_properties WHERE user_id = ?", [$userId]);
            $leases = \App\Models\Lease::getForTenant($userId);
            $applications = \App\Models\RentalApplication::getForApplicant($userId);
            $maintenanceRequests = \App\Models\MaintenanceRequest::getForTenant($userId);
            $enquiriesCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM enquiries WHERE user_id = ? OR email = ?", [$userId, $user['email'] ?? '']);

            $isTenant = Auth::hasRole('tenant');
            $html = $this->render('dashboard/client', [
                'title' => ($isTenant ? 'Tenant Living Portal' : 'Property Buyer & Investor Portal') . ' | Afriserva Real Estate',
                'user' => $user,
                'isProfessional' => false,
                'isTenant' => $isTenant,
                'appointments' => $appointments,
                'savedCount' => $savedCount,
                'leases' => $leases,
                'applications' => $applications,
                'maintenanceRequests' => $maintenanceRequests,
                'enquiriesCount' => $enquiriesCount
            ], 'layouts/dashboard');
        }

        $response->html($html);
    }

    public function myListings(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $properties = Property::all(['user_id' => $userId], 100);

        $html = $this->render('dashboard/listings', [
            'title' => 'My Portfolio & Properties | Afriserva Real Estate',
            'properties' => $properties
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function createListingForm(Request $request, Response $response): void
    {
        if (!Auth::hasRole(['agent', 'agency_admin', 'landlord', 'developer', 'seller', 'property_manager', 'super_admin'])) {
            flash('warning', 'Listing creation requires a verified Seller, Landlord, Agent, Developer, or Property Manager account.');
            $this->redirect($response, url('dashboard'));
            return;
        }

        $states = Location::getStates();
        $categories = Database::query("SELECT * FROM property_categories ORDER BY display_order ASC");
        $propertyTypes = Database::query("SELECT * FROM property_types ORDER BY name ASC");
        $amenities = Database::query("SELECT * FROM amenities ORDER BY group_name ASC, name ASC");

        $html = $this->render('dashboard/create_listing', [
            'title' => 'List a Prime Property | Afriserva Real Estate',
            'states' => $states,
            'categories' => $categories,
            'propertyTypes' => $propertyTypes,
            'amenities' => $amenities
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function storeListing(Request $request, Response $response): void
    {
        $input = $request->all();
        $userId = Auth::id();

        $errors = $this->validate($input, [
            'title' => 'required|min:10',
            'category_id' => 'required',
            'property_type_id' => 'required',
            'transaction_type' => 'required',
            'price' => 'required|numeric',
            'state_id' => 'required',
            'city_id' => 'required',
            'address' => 'required',
            'description' => 'required|min:30'
        ]);

        if (!empty($errors)) {
            flash('danger', 'Please verify and correct all highlighted listing fields.');
            $states = Location::getStates();
            $categories = Database::query("SELECT * FROM property_categories ORDER BY display_order ASC");
            $propertyTypes = Database::query("SELECT * FROM property_types ORDER BY name ASC");
            $amenities = Database::query("SELECT * FROM amenities ORDER BY group_name ASC, name ASC");

            $html = $this->render('dashboard/create_listing', [
                'title' => 'List a Prime Property',
                'errors' => $errors,
                'input' => $input,
                'states' => $states,
                'categories' => $categories,
                'propertyTypes' => $propertyTypes,
                'amenities' => $amenities
            ], 'layouts/dashboard');

            $response->html($html, 422);
            return;
        }

        // Fraud detection: check for duplicates
        $duplicates = Property::detectDuplicates($input['title'], $input['address'], (float)$input['price'], (int)$input['state_id']);

        // Generate clean unique reference code & slug
        $refCode = 'AFR-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title']), '-'));
        $slug = $baseSlug . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
        $uuid = bin2hex(random_bytes(16));
        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split($uuid, 4));

        $submitAction = $input['submit_action'] ?? 'publish';
        $status = ($submitAction === 'draft') ? 'draft' : 'published';

        $sql = "INSERT INTO properties (
            uuid, reference_code, user_id, category_id, property_type_id, title, slug, description,
            transaction_type, price, price_suffix, currency, country_id, state_id, city_id,
            neighbourhood_id, address, is_address_hidden, latitude, longitude, bedrooms, bathrooms, toilets,
            parking_spaces, building_size_sqm, land_size_sqm, furnishing_status, property_condition,
            title_document, status, verification_status, created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, 'NGN', 1, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, 'unverified', NOW()
        )";

        Database::execute($sql, [
            $uuid,
            $refCode,
            $userId,
            (int)$input['category_id'],
            (int)$input['property_type_id'],
            trim($input['title']),
            $slug,
            trim($input['description']),
            $input['transaction_type'],
            (float)$input['price'],
            trim($input['price_suffix'] ?? ''),
            (int)$input['state_id'],
            (int)$input['city_id'],
            !empty($input['neighbourhood_id']) ? (int)$input['neighbourhood_id'] : null,
            trim($input['address']),
            !empty($input['is_address_hidden']) ? 1 : 0,
            !empty($input['latitude']) ? (float)$input['latitude'] : 6.4549,
            !empty($input['longitude']) ? (float)$input['longitude'] : 3.4246,
            (int)($input['bedrooms'] ?? 0),
            (int)($input['bathrooms'] ?? 0),
            (int)($input['toilets'] ?? 0),
            (int)($input['parking_spaces'] ?? 0),
            !empty($input['building_size_sqm']) ? (float)$input['building_size_sqm'] : null,
            !empty($input['land_size_sqm']) ? (float)$input['land_size_sqm'] : null,
            $input['furnishing_status'] ?? 'unfurnished',
            $input['property_condition'] ?? 'brand_new',
            trim($input['title_document'] ?? 'Certificate of Occupancy'),
            $status
        ]);

        $propId = (int)Database::lastInsertId();

        // Attach Primary Cover Image (uploaded file or URL)
        $imageUrl = '';
        if (!empty($_FILES['cover_image_file']['tmp_name'])) {
            $uploaded = \App\Helpers\UploadHelper::uploadImage($_FILES['cover_image_file']);
            if ($uploaded) {
                $imageUrl = $uploaded;
            }
        }
        if (empty($imageUrl)) {
            $imageUrl = trim($input['image_url'] ?? '');
        }
        if (empty($imageUrl)) {
            $imageUrl = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1600&q=80';
        }
        Database::execute(
            "INSERT INTO property_images (property_id, image_url, thumbnail_url, caption, is_cover, display_order)
             VALUES (?, ?, ?, ?, 1, 1)",
            [$propId, $imageUrl, $imageUrl, trim($input['title'])]
        );

        // Attach Additional Gallery Images (if uploaded)
        if (!empty($_FILES['gallery_images']['name']) && is_array($_FILES['gallery_images']['name'])) {
            $galleryUrls = \App\Helpers\UploadHelper::uploadMultipleImages($_FILES['gallery_images']);
            $order = 2;
            foreach ($galleryUrls as $gUrl) {
                Database::execute(
                    "INSERT INTO property_images (property_id, image_url, thumbnail_url, caption, is_cover, display_order)
                     VALUES (?, ?, ?, ?, 0, ?)",
                    [$propId, $gUrl, $gUrl, trim($input['title']), $order++]
                );
            }
        }

        // Attach Amenities
        if (!empty($input['amenities']) && is_array($input['amenities'])) {
            $stmtAm = Database::connect()->prepare("INSERT IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?, ?)");
            foreach ($input['amenities'] as $amId) {
                $stmtAm->execute([$propId, (int)$amId]);
            }
        }

        \App\Helpers\AuditLogger::log('property_created', 'property', $propId, null, [
            'status' => $status,
            'title' => $input['title'],
            'price' => $input['price']
        ]);

        if (!empty($duplicates)) {
            flash('warning', 'Property indexed, but flagged potential duplicate listings in the jurisdiction.');
        } else {
            flash('success', 'Your listing has been created and indexed successfully!');
        }

        $this->redirect($response, url('dashboard/listings'));
    }

    public function editListingForm(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();

        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized access to listing.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        $states = Location::getStates();
        $categories = Database::query("SELECT * FROM property_categories ORDER BY display_order ASC");
        $propertyTypes = Database::query("SELECT * FROM property_types ORDER BY name ASC");
        $amenities = Database::query("SELECT * FROM amenities ORDER BY group_name ASC, name ASC");

        $duplicateWarnings = Property::detectDuplicates($property['title'], $property['address'], (float)$property['price'], (int)$property['state_id'], $id);
        $images = Database::query("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_cover DESC, display_order ASC", [$id]);
        $videos = Database::query("SELECT * FROM property_videos WHERE property_id = ?", [$id]);

        $html = $this->render('dashboard/edit_listing', [
            'title' => 'Edit Property | ' . $property['reference_code'],
            'property' => $property,
            'states' => $states,
            'categories' => $categories,
            'propertyTypes' => $propertyTypes,
            'amenities' => $amenities,
            'duplicateWarnings' => $duplicateWarnings,
            'images' => $images,
            'videos' => $videos
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function updateListing(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();

        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized access to listing.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        $input = $request->all();

        $sql = "UPDATE properties SET
            title = ?, category_id = ?, property_type_id = ?, description = ?, transaction_type = ?, price = ?, price_suffix = ?,
            state_id = ?, address = ?, is_address_hidden = ?, bedrooms = ?, bathrooms = ?,
            toilets = ?, parking_spaces = ?, furnishing_status = ?, property_condition = ?,
            title_document = ?, status = ?, updated_at = NOW()
            WHERE id = ?";

        Database::execute($sql, [
            trim($input['title']),
            (int)($input['category_id'] ?? 1),
            (int)($input['property_type_id'] ?? 1),
            trim($input['description']),
            $input['transaction_type'],
            (float)$input['price'],
            trim($input['price_suffix'] ?? ''),
            (int)$input['state_id'],
            trim($input['address']),
            !empty($input['is_address_hidden']) ? 1 : 0,
            (int)($input['bedrooms'] ?? 0),
            (int)($input['bathrooms'] ?? 0),
            (int)($input['toilets'] ?? 0),
            (int)($input['parking_spaces'] ?? 0),
            $input['furnishing_status'] ?? 'unfurnished',
            $input['property_condition'] ?? 'brand_new',
            trim($input['title_document'] ?? 'Certificate of Occupancy'),
            $input['status'] ?? 'published',
            $id
        ]);

        \App\Helpers\AuditLogger::log('property_updated', 'property', $id, null, ['title' => $input['title'], 'status' => $input['status']]);

        flash('success', "Property '{$input['title']}' updated successfully.");
        $this->redirect($response, url('dashboard/listings'));
    }

    public function addPropertyImage(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();
        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized access.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        $imageUrl = '';
        if (!empty($_FILES['image_file']['tmp_name'])) {
            $uploaded = \App\Helpers\UploadHelper::uploadImage($_FILES['image_file']);
            if ($uploaded) {
                $imageUrl = $uploaded;
            }
        }
        if (empty($imageUrl)) {
            $imageUrl = trim($request->input('image_url', ''));
        }
        $caption = trim($request->input('caption', ''));

        if (!empty($imageUrl)) {
            Database::execute(
                "INSERT INTO property_images (property_id, image_url, thumbnail_url, caption, is_cover, display_order)
                 VALUES (?, ?, ?, ?, 0, 99)",
                [$id, $imageUrl, $imageUrl, $caption]
            );
            \App\Helpers\AuditLogger::log('property_image_added', 'property', $id, null, ['image_url' => $imageUrl]);
            flash('success', 'Photograph added to property gallery.');
        } else {
            flash('warning', 'Please provide an image file or a valid photo URL.');
        }

        $this->redirect($response, url('dashboard/listings/edit/' . $id));
    }

    public function deletePropertyImage(Request $request, Response $response, array $params): void
    {
        $propId = (int)($params['id'] ?? 0);
        $imageId = (int)($params['imageId'] ?? 0);
        $userId = Auth::id();
        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$propId]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized access.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        Database::execute("DELETE FROM property_images WHERE id = ? AND property_id = ?", [$imageId, $propId]);
        \App\Helpers\AuditLogger::log('property_image_deleted', 'property', $propId, null, ['image_id' => $imageId]);

        flash('info', 'Image removed from gallery.');
        $this->redirect($response, url('dashboard/listings/edit/' . $propId));
    }

    public function setCoverImage(Request $request, Response $response, array $params): void
    {
        $propId = (int)($params['id'] ?? 0);
        $imageId = (int)($params['imageId'] ?? 0);
        $userId = Auth::id();
        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$propId]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized access.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        Database::execute("UPDATE property_images SET is_cover = 0 WHERE property_id = ?", [$propId]);
        Database::execute("UPDATE property_images SET is_cover = 1 WHERE id = ? AND property_id = ?", [$imageId, $propId]);
        \App\Helpers\AuditLogger::log('property_cover_image_set', 'property', $propId, null, ['cover_image_id' => $imageId]);

        flash('success', 'Primary cover photo updated.');
        $this->redirect($response, url('dashboard/listings/edit/' . $propId));
    }

    public function savePropertyVideo(Request $request, Response $response, array $params): void
    {
        $propId = (int)($params['id'] ?? 0);
        $userId = Auth::id();
        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$propId]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized access.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        $videoUrl = trim($request->input('video_url', ''));
        $videoType = $request->input('video_type', 'youtube');

        if (!empty($videoUrl)) {
            Database::execute(
                "INSERT INTO property_videos (property_id, video_url, video_type) VALUES (?, ?, ?)",
                [$propId, $videoUrl, $videoType]
            );
            \App\Helpers\AuditLogger::log('property_video_added', 'property', $propId, null, ['video_url' => $videoUrl]);
            flash('success', 'Video tour attached successfully.');
        }

        $this->redirect($response, url('dashboard/listings/edit/' . $propId));
    }

    public function updateViewingStatus(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $status = trim($request->input('status', 'confirmed'));
        $userId = Auth::id();

        $app = Database::fetch("SELECT * FROM viewing_appointments WHERE id = ?", [$id]);
        if (!$app || ((int)$app['agent_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized tour appointment modification.');
            $this->redirect($response, url('dashboard/viewings'));
            return;
        }

        Database::execute("UPDATE viewing_appointments SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);
        \App\Helpers\AuditLogger::log('viewing_status_updated', 'viewing_appointment', $id, ['from' => $app['status']], ['to' => $status]);

        flash('success', "Inspection tour status updated to " . strtoupper($status));
        $this->redirect($response, url('dashboard/viewings'));
    }

    public function verification(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $documents = Database::query(
            "SELECT * FROM identity_verification_documents WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );

        $html = $this->render('dashboard/verification', [
            'title' => 'Professional Identity & Agency Verification | Afriserva Real Estate',
            'documents' => $documents
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function uploadVerificationDocument(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $input = $request->all();

        $docType = $input['document_type'] ?? 'lasrera_license';
        $docNumber = trim($input['document_number'] ?? '');
        $filePath = '';
        $fileName = '';

        if (!empty($_FILES['verification_file']) && $_FILES['verification_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['verification_file'];
            $maxBytes = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $maxBytes) {
                flash('danger', 'Uploaded file exceeds the maximum size of 10MB.');
                $this->redirect($response, url('dashboard/verification'));
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowedExts)) {
                flash('danger', 'Invalid file type. Only PDF, JPG, and PNG documents are accepted.');
                $this->redirect($response, url('dashboard/verification'));
                return;
            }

            $baseDir = dirname(__DIR__, 2);
            $targetDir = $baseDir . '/storage/documents';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $safeFileName = 'id_doc_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            $destPath = $targetDir . '/' . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $filePath = 'storage/documents/' . $safeFileName;
                $fileName = basename($file['name']);
            } else {
                flash('danger', 'Failed to save uploaded document file to secure storage.');
                $this->redirect($response, url('dashboard/verification'));
                return;
            }
        } elseif (!empty($input['file_path'])) {
            // Programmatic/test upload
            $filePath = trim($input['file_path']);
            $fileName = trim($input['file_name'] ?? basename($filePath));
        } else {
            flash('danger', 'Please select a document file to upload.');
            $this->redirect($response, url('dashboard/verification'));
            return;
        }

        Database::execute(
            "INSERT INTO identity_verification_documents (user_id, document_type, document_number, file_path, file_name, verification_status, created_at)
             VALUES (?, ?, ?, ?, ?, 'pending', NOW())",
            [$userId, $docType, $docNumber, $filePath, $fileName]
        );

        $docId = (int)Database::lastInsertId();

        \App\Helpers\AuditLogger::log('identity_document_submitted', 'identity_verification_document', $docId, null, [
            'type' => $docType,
            'number' => $docNumber,
            'file_name' => $fileName
        ]);

        flash('success', 'Compliance credential submitted successfully for legal due diligence review.');
        $this->redirect($response, url('dashboard/verification'));
    }

    public function downloadVerificationDocument(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();

        $doc = Database::fetch("SELECT * FROM identity_verification_documents WHERE id = ?", [$id]);
        if (!$doc) {
            $response->status(404)->html("<h3>Verification credential not found.</h3>", 404);
            return;
        }

        $isOwner = ((int)$doc['user_id'] === (int)$userId);
        $isAdmin = (function_exists('has_role') && (has_role('super_admin') || has_role('moderator')));

        if (!$isOwner && !$isAdmin) {
            $response->status(403)->html("<h3>Unauthorized: You do not have permission to view this legal credential.</h3>", 403);
            return;
        }

        $baseDir = dirname(__DIR__, 2);
        $filePath = $doc['file_path'];
        $fullPath = str_starts_with($filePath, '/') || preg_match('/^[A-Za-z]:/', $filePath)
            ? $filePath
            : $baseDir . '/' . ltrim($filePath, '/');

        if (!file_exists($fullPath)) {
            $response->status(404)->html("<h3>Physical document file not found on secure storage.</h3>", 404);
            return;
        }

        $fileName = basename($doc['file_name'] ?: 'Credential.pdf');
        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        header("Content-Type: {$mime}");
        header("Content-Disposition: inline; filename=\"{$fileName}\"");
        header("Content-Length: " . filesize($fullPath));
        header("Cache-Control: private, no-transform, no-store, must-revalidate");
        header("X-Content-Type-Options: nosniff");
        readfile($fullPath);
        exit;
    }

    public function updateQuickStatus(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();
        $status = trim($request->input('status', 'published'));

        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized listing modification.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        Property::updateStatus($id, $status);
        \App\Helpers\AuditLogger::log('property_status_quick_update', 'property', $id, ['from' => $property['status']], ['to' => $status]);

        flash('success', "Listing status updated to " . strtoupper($status));
        $this->redirect($response, url('dashboard/listings'));
    }

    public function deleteListing(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userId = Auth::id();

        $property = Database::fetch("SELECT * FROM properties WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$property || ((int)$property['user_id'] !== (int)$userId && !has_role('super_admin'))) {
            flash('danger', 'Unauthorized listing deletion.');
            $this->redirect($response, url('dashboard/listings'));
            return;
        }

        Database::execute("UPDATE properties SET deleted_at = NOW(), status = 'archived' WHERE id = ?", [$id]);
        \App\Helpers\AuditLogger::log('property_archived', 'property', $id);

        flash('info', 'Listing archived from your active portfolio.');
        $this->redirect($response, url('dashboard/listings'));
    }

    public function team(Request $request, Response $response): void
    {
        if (!Auth::hasRole(['agency_admin', 'super_admin'])) {
            flash('warning', 'Agency team management is restricted to corporate broker firms.');
            $this->redirect($response, url('dashboard'));
            return;
        }

        $userId = Auth::id();

        // Find agency associated with current user
        $agencyId = (int)Database::fetchColumn(
            "SELECT agency_id FROM agent_profiles WHERE user_id = ? 
             UNION 
             SELECT agency_id FROM agency_members WHERE user_id = ? 
             LIMIT 1",
            [$userId, $userId]
        );

        if (!$agencyId) {
            // Default to prestige prime for testing
            $agencyId = 1;
        }

        $teamMembers = Database::query(
            "SELECT am.*, u.full_name, u.email, u.phone, u.avatar_url
             FROM agency_members am
             JOIN users u ON u.id = am.user_id
             WHERE am.agency_id = ?
             ORDER BY am.created_at ASC",
            [$agencyId]
        );

        $html = $this->render('dashboard/team', [
            'title' => 'Agency Team Management | Afriserva Real Estate',
            'teamMembers' => $teamMembers,
            'agencyId' => $agencyId
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function inviteTeamMember(Request $request, Response $response): void
    {
        $input = $request->all();
        $email = trim(strtolower($input['email'] ?? ''));
        $fullName = trim($input['full_name'] ?? '');
        $roleInAgency = $input['role_in_agency'] ?? 'associate_agent';

        if (empty($email) || empty($fullName)) {
            flash('danger', 'Agent name and email are required.');
            $this->redirect($response, url('dashboard/team'));
            return;
        }

        // Find or create user
        $existing = \App\Models\User::findByEmail($email);
        if (!$existing) {
            $tempPassword = bin2hex(random_bytes(6)) . 'A1!';
            $userId = \App\Models\User::create([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $input['phone'] ?? '',
                'password' => $tempPassword
            ], 'agent');
        } else {
            $userId = (int)$existing['id'];
        }

        Database::execute(
            "INSERT INTO agency_members (agency_id, user_id, role_in_agency, status, created_at)
             VALUES (1, ?, ?, 'active', NOW())
             ON DUPLICATE KEY UPDATE role_in_agency = VALUES(role_in_agency)",
            [$userId, $roleInAgency]
        );

        \App\Helpers\AuditLogger::log('agency_member_invited', 'agency', 1, null, ['user_id' => $userId, 'role' => $roleInAgency]);

        flash('success', "Agent '{$fullName}' has been added to your agency team.");
        $this->redirect($response, url('dashboard/team'));
    }

    public function removeTeamMember(Request $request, Response $response, array $params): void
    {
        $memberId = (int)($params['id'] ?? 0);
        Database::execute("DELETE FROM agency_members WHERE user_id = ?", [$memberId]);
        \App\Helpers\AuditLogger::log('agency_member_removed', 'agency', 1, null, ['user_id' => $memberId]);

        flash('info', 'Agent removed from agency roster.');
        $this->redirect($response, url('dashboard/team'));
    }

    public function invoices(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $invoices = Database::query(
            "SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        );

        $html = $this->render('dashboard/invoices', [
            'title' => 'Invoices & Billing History | Afriserva Real Estate',
            'invoices' => $invoices
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function exportListings(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $listings = Database::query(
            "SELECT p.reference_code, p.title, p.transaction_type, p.price, p.currency, p.status, p.verification_status, p.view_count, p.created_at
             FROM properties p
             WHERE p.user_id = ? AND p.deleted_at IS NULL
             ORDER BY p.created_at DESC",
            [$userId]
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=listings_export_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Reference Code', 'Title', 'Transaction Type', 'Price', 'Currency', 'Status', 'Verification', 'Total Views', 'Date Created']);

        foreach ($listings as $l) {
            fputcsv($output, [
                $l['reference_code'],
                $l['title'],
                strtoupper($l['transaction_type']),
                $l['price'],
                $l['currency'],
                strtoupper($l['status']),
                strtoupper($l['verification_status']),
                $l['view_count'],
                $l['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportLeads(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $leads = Database::query(
            "SELECT e.id, e.sender_name, e.sender_email, e.sender_phone, e.status, e.message, p.title AS property_title, e.created_at
             FROM enquiries e
             LEFT JOIN properties p ON p.id = e.property_id
             WHERE e.agent_id = ?
             ORDER BY e.created_at DESC",
            [$userId]
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=leads_export_' . date('Ymd_His') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Lead ID', 'Client Name', 'Email', 'Phone', 'Stage Status', 'Property Inquired', 'Message', 'Date Received']);

        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['id'],
                $lead['sender_name'],
                $lead['sender_email'],
                $lead['sender_phone'],
                strtoupper($lead['status']),
                $lead['property_title'] ?? 'Direct Agent Lead',
                $lead['message'],
                $lead['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    public function enquiries(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $enquiries = Enquiry::getByAgent($userId, 50);
        $counts = Enquiry::getCountsByStatus($userId);
        $analytics = Enquiry::getResponseAnalytics($userId);

        $html = $this->render('dashboard/enquiries', [
            'title' => 'Client Leads & Enquiries | Afriserva Real Estate',
            'enquiries' => $enquiries,
            'counts' => $counts,
            'analytics' => $analytics
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function updateEnquiryStatus(Request $request, Response $response): void
    {
        $enquiryId = (int)$request->param('id');
        $status = trim($request->input('status') ?? '');
        $userId = Auth::id();

        $enquiry = Enquiry::getById($enquiryId);
        if (!$enquiry || ((int)$enquiry['agent_id'] !== (int)$userId && !has_role('super_admin'))) {
            if ($request->isAjax()) {
                $this->json($response, ['status' => 'error', 'message' => 'Unauthorized lead access.'], 403);
                return;
            }
            flash('danger', 'Unauthorized lead modification.');
            $this->redirect($response, url('dashboard/enquiries'));
            return;
        }

        Enquiry::updateStatus($enquiryId, $status);

        if ($request->isAjax()) {
            $this->json($response, ['status' => 'success', 'message' => 'Lead status updated to ' . ucfirst($status)]);
            return;
        }

        flash('success', 'Lead status successfully updated to ' . ucfirst($status) . '.');
        $this->redirect($response, url('dashboard/enquiries'));
    }

    public function viewings(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $appointments = ViewingAppointment::getByAgent($userId);

        $html = $this->render('dashboard/viewings', [
            'title' => 'Scheduled Viewings & Inspections | Afriserva Real Estate',
            'appointments' => $appointments
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function subscription(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $plans = Subscription::getPlans();
        $currentSub = Subscription::getUserSubscription($userId);

        $html = $this->render('dashboard/subscription', [
            'title' => 'Professional Subscriptions & Monetization Plans | Afriserva Real Estate',
            'plans' => $plans,
            'currentSub' => $currentSub
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function leases(Request $request, Response $response): void
    {
        $userId = Auth::id();

        $leases = Database::query(
            "SELECT l.*, p.title AS property_title, p.slug AS property_slug, p.address,
                    t.full_name AS tenant_name, t.email AS tenant_email, t.phone AS tenant_phone,
                    ll.full_name AS landlord_name, ll.phone AS landlord_phone
             FROM leases l
             JOIN properties p ON p.id = l.property_id
             JOIN users t ON t.id = l.tenant_id
             JOIN users ll ON ll.id = l.landlord_id
             WHERE l.landlord_id = ? OR l.tenant_id = ?
             ORDER BY l.created_at DESC",
            [$userId, $userId]
        );

        $rentPayments = Database::query(
            "SELECT rp.*, p.title AS property_title
             FROM rent_payments rp
             JOIN leases l ON l.id = rp.lease_id
             JOIN properties p ON p.id = l.property_id
             WHERE l.landlord_id = ? OR l.tenant_id = ?
             ORDER BY rp.due_date DESC",
            [$userId, $userId]
        );

        $maintenanceRequests = Database::query(
            "SELECT mr.*, p.title AS property_title
             FROM maintenance_requests mr
             JOIN properties p ON p.id = mr.property_id
             WHERE mr.landlord_id = ? OR mr.tenant_id = ?
             ORDER BY mr.created_at DESC",
            [$userId, $userId]
        );

        $html = $this->render('dashboard/leases', [
            'title' => 'Tenancy Agreements, Rent Schedules & Maintenance | Afriserva Real Estate',
            'leases' => $leases,
            'rentPayments' => $rentPayments,
            'maintenanceRequests' => $maintenanceRequests
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function savedProperties(Request $request, Response $response): void
    {
        $userId = Auth::id();

        $saved = Database::query(
            "SELECT p.*, s.name as state_name, c.name as city_name, 
                    pi.image_url as primary_image
             FROM saved_properties sp
             JOIN properties p ON p.id = sp.property_id
             LEFT JOIN states s ON s.id = p.state_id
             LEFT JOIN cities c ON c.id = p.city_id
             LEFT JOIN property_images pi ON pi.property_id = p.id AND pi.is_cover = 1
             WHERE sp.user_id = ?
             ORDER BY sp.created_at DESC",
            [$userId]
        );

        $html = $this->render('dashboard/saved', [
            'title' => 'My Saved Properties & Investment Shortlist',
            'savedProperties' => $saved
        ], 'layouts/dashboard');

        $response->html($html);
    }

    public function profile(Request $request, Response $response): void
    {
        $userId = Auth::id();
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);

        if ($request->isPost()) {
            $input = $request->all();
            $fullName = trim($input['full_name'] ?? '');
            $phone = trim($input['phone'] ?? '');
            $bio = trim($input['bio'] ?? '');
            $avatarUrl = trim($input['avatar_url'] ?? '');

            if (empty($fullName)) {
                flash('danger', 'Full name is required.');
                $this->redirect($response, url('dashboard/profile'));
                return;
            }

            // Optional Password Change
            if (!empty($input['new_password'])) {
                $currentPassword = $input['current_password'] ?? '';
                if (!password_verify($currentPassword, $user['password_hash'])) {
                    flash('danger', 'Your current password is incorrect. Profile not updated.');
                    $this->redirect($response, url('dashboard/profile'));
                    return;
                }
                if (strlen($input['new_password']) < 8) {
                    flash('danger', 'New password must contain at least 8 characters.');
                    $this->redirect($response, url('dashboard/profile'));
                    return;
                }

                $newHash = password_hash($input['new_password'], PASSWORD_ARGON2ID);
                Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $userId]);
                \App\Helpers\AuditLogger::log('password_change', 'user', $userId);
            }

            // Notification preferences
            $notifyEmail = !empty($input['notify_email']) ? 1 : 0;
            $notifySms = !empty($input['notify_sms']) ? 1 : 0;
            $notifyMarketing = !empty($input['notify_marketing']) ? 1 : 0;

            // Privacy settings
            $isPhonePublic = !empty($input['is_phone_public']) ? 1 : 0;
            $isEmailPublic = !empty($input['is_email_public']) ? 1 : 0;

            // 2FA toggle
            $twoFactorEnabled = !empty($input['two_factor_enabled']) ? 1 : 0;

            Database::execute(
                "UPDATE users SET 
                    full_name = ?, phone = ?, bio = ?, avatar_url = ?,
                    notify_email = ?, notify_sms = ?, notify_marketing = ?,
                    is_phone_public = ?, is_email_public = ?, two_factor_enabled = ?,
                    updated_at = NOW() 
                 WHERE id = ?",
                [
                    $fullName, $phone, $bio, $avatarUrl ?: null,
                    $notifyEmail, $notifySms, $notifyMarketing,
                    $isPhonePublic, $isEmailPublic, $twoFactorEnabled,
                    $userId
                ]
            );

            // Refresh session cache
            $_SESSION['user']['full_name'] = $fullName;
            if ($avatarUrl) $_SESSION['user']['avatar_url'] = $avatarUrl;

            \App\Helpers\AuditLogger::log('profile_update', 'user', $userId, null, [
                'name' => $fullName, 
                'phone' => $phone,
                'two_factor' => $twoFactorEnabled
            ]);

            flash('success', 'Profile, notification preferences, and security parameters updated successfully.');
            $this->redirect($response, url('dashboard/profile'));
            return;
        }

        $activeSessions = Auth::getSessions($userId);
        $recentAuditLogs = Database::query(
            "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 8",
            [$userId]
        );

        $html = $this->render('dashboard/profile', [
            'title' => 'Account Settings & Security Profile | Afriserva Real Estate',
            'user' => $user,
            'activeSessions' => $activeSessions,
            'recentAuditLogs' => $recentAuditLogs
        ], 'layouts/dashboard');

        $response->html($html);
    }
}
