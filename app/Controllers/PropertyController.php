<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Models\Property;
use App\Models\Location;
use App\Models\SearchAlert;
use App\Core\Database;
use App\Core\Session;

class PropertyController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $filters = [
            'keyword' => $request->query('q') ?: $request->query('keyword'),
            'transaction_type' => $request->query('type'),
            'category_id' => $request->query('category'),
            'property_type_id' => $request->query('property_type_id'),
            'state_id' => $request->query('state'),
            'city_id' => $request->query('city'),
            'neighbourhood_id' => $request->query('neighbourhood'),
            'bedrooms' => $request->query('beds'),
            'bathrooms' => $request->query('baths'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'furnishing_status' => $request->query('furnishing'),
            'property_condition' => $request->query('condition'),
            'title_document' => $request->query('title_doc') ?: $request->query('title_document'),
            'luxury' => $request->query('luxury'),
            'featured' => $request->query('featured'),
            'verified' => $request->query('verified'),
            'sort' => $request->query('sort', 'newest')
        ];

        $page = max(1, (int)$request->query('page', 1));
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $properties = Property::all($filters, $limit, $offset);
        $totalCount = Property::count($filters);
        $totalPages = ceil($totalCount / $limit);

        if ($request->isAjax()) {
            $this->json($response, [
                'properties' => $properties,
                'total' => $totalCount,
                'page' => $page,
                'totalPages' => $totalPages
            ]);
            return;
        }

        $states = Location::getStates();
        $categories = Database::query("SELECT * FROM property_categories ORDER BY display_order ASC");
        $propertyTypes = Database::query("SELECT * FROM property_types ORDER BY name ASC");

        $html = $this->render('properties/index', [
            'title' => 'Search Properties for Sale & Rent in Nigeria | Afriserva Real Estate',
            'properties' => $properties,
            'totalCount' => $totalCount,
            'page' => $page,
            'totalPages' => $totalPages,
            'filters' => $filters,
            'states' => $states,
            'categories' => $categories,
            'propertyTypes' => $propertyTypes
        ]);

        $response->html($html);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $property = Property::findBySlug($slug);

        if (!$property) {
            $response->status(404);
            $response->html($this->render('errors/404', ['title' => 'Property Not Found']), 404);
            return;
        }

        Property::incrementViews((int)$property['id']);

        // Track recently viewed properties in session
        $viewed = \App\Core\Session::get('recently_viewed_properties', []);
        if (!in_array((int)$property['id'], $viewed, true)) {
            array_unshift($viewed, (int)$property['id']);
            $viewed = array_slice($viewed, 0, 6);
            \App\Core\Session::set('recently_viewed_properties', $viewed);
        }

        $viewedIds = array_filter($viewed, fn($id) => $id !== (int)$property['id']);
        $recentlyViewed = Property::getRecentlyViewed($viewedIds);

        // Similar properties in same category or state
        $similar = Property::getSimilar((int)$property['id'], (int)$property['category_id'], (int)$property['state_id'], 3);

        $html = $this->render('properties/show', [
            'title' => e($property['title']) . ' | Afriserva Real Estate',
            'metaDescription' => substr(strip_tags($property['description']), 0, 160),
            'ogImage' => !empty($property['images'][0]['image_url']) ? $property['images'][0]['image_url'] : null,
            'property' => $property,
            'similar' => $similar,
            'recentlyViewed' => $recentlyViewed
        ]);

        $response->html($html);
    }

    public function reportListing(Request $request, Response $response): void
    {
        $propertyId = (int)($request->param('id') ?? $request->input('property_id') ?? $request->input('id') ?? 0);
        $reason = trim($request->input('reason') ?? '');
        $details = trim($request->input('details') ?? '');
        $userId = Auth::id();

        if (empty($reason) || empty($details) || $propertyId <= 0) {
            flash('danger', 'Please provide a valid reason and descriptive details for reporting.');
            $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('properties'));
            return;
        }

        Database::execute(
            "INSERT INTO listing_reports (property_id, user_id, reason, details, status, created_at)
             VALUES (?, ?, ?, ?, 'pending', NOW())",
            [$propertyId, $userId, $reason, $details]
        );

        if ($request->isAjax()) {
            $this->json($response, ['status' => 'success', 'message' => 'Thank you. Your report has been submitted to the compliance team.']);
            return;
        }

        flash('success', 'Thank you. Your report has been submitted to our regulatory compliance officers for investigation.');
        $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('properties'));
    }

    public function map(Request $request, Response $response): void
    {
        $states = Location::getStates();
        $categories = Database::query("SELECT * FROM property_categories ORDER BY display_order ASC");

        $html = $this->render('properties/map', [
            'title' => 'Interactive Real Estate Map Discovery | Afriserva Real Estate',
            'states' => $states,
            'categories' => $categories
        ]);

        $response->html($html);
    }

    public function apiCoordinates(Request $request, Response $response): void
    {
        $filters = [
            'keyword' => $request->query('q'),
            'transaction_type' => $request->query('type'),
            'category_id' => $request->query('category'),
            'state_id' => $request->query('state'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price')
        ];

        $coordinates = Property::getMapCoordinates($filters);
        $this->json($response, ['status' => 'success', 'data' => $coordinates]);
    }

    public function toggleFavorite(Request $request, Response $response): void
    {
        if (!Auth::check()) {
            if ($request->isAjax()) {
                $this->json($response, ['status' => 'error', 'message' => 'Please sign in to save properties to your shortlist.'], 401);
                return;
            }
            flash('warning', 'Please sign in to save properties to your wishlist.');
            $this->redirect($response, url('login'));
            return;
        }

        $propertyId = (int)($request->param('id') ?? $request->input('id') ?? $request->input('property_id') ?? 0);
        $userId = (int)Auth::id();

        if ($propertyId <= 0) {
            if ($request->isAjax()) {
                $this->json($response, ['status' => 'error', 'message' => 'Invalid property ID.'], 400);
                return;
            }
            $this->redirect($response, url('properties'));
            return;
        }

        $existing = Database::fetch("SELECT id FROM saved_properties WHERE user_id = ? AND property_id = ?", [$userId, $propertyId]);

        if ($existing) {
            Database::execute("DELETE FROM saved_properties WHERE user_id = ? AND property_id = ?", [$userId, $propertyId]);
            Database::execute("UPDATE properties SET favourite_count = GREATEST(0, favourite_count - 1) WHERE id = ?", [$propertyId]);
            $saved = false;
            $msg = 'Property removed from your shortlist.';
        } else {
            Database::execute("INSERT INTO saved_properties (user_id, property_id) VALUES (?, ?)", [$userId, $propertyId]);
            Database::execute("UPDATE properties SET favourite_count = favourite_count + 1 WHERE id = ?", [$propertyId]);
            $saved = true;
            $msg = 'Property saved to your favorites wishlist!';
        }

        if ($request->isAjax()) {
            $this->json($response, ['status' => 'success', 'saved' => $saved, 'message' => $msg]);
            return;
        }

        flash('success', $msg);
        $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('properties'));
    }

    public function saveSearchAlert(Request $request, Response $response): void
    {
        $userId = (int) Session::get('user_id');
        if ($userId <= 0) {
            flash('error', 'You must be signed in to save search alerts.');
            $this->redirect($response, url('/login'));
            return;
        }

        $name = trim($request->input('alert_name', 'My Saved Search'));
        $frequency = $request->input('frequency', 'daily');
        $criteria = [
            'purpose' => $request->input('purpose'),
            'type' => $request->input('type'),
            'category' => $request->input('category'),
            'state_id' => $request->input('state_id'),
            'city_id' => $request->input('city_id'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'beds' => $request->input('beds'),
            'keyword' => $request->input('keyword')
        ];
        // Filter out null/empty values
        $criteria = array_filter($criteria, fn($v) => $v !== null && $v !== '');

        $alertId = SearchAlert::create($userId, $name ?: 'Property Search Alert', $criteria, $frequency);

        if ($request->isAjax()) {
            $this->json($response, [
                'status' => 'success',
                'alert_id' => $alertId,
                'message' => 'Search alert successfully created! You will receive notifications when matching properties are listed.'
            ]);
            return;
        }

        flash('success', 'Search alert created! You will be notified of new matching listings.');
        $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/properties'));
    }

    public function deleteSearchAlert(Request $request, Response $response, array $params): void
    {
        $userId = (int) Session::get('user_id');
        $alertId = (int) ($params['id'] ?? 0);

        if ($userId > 0 && $alertId > 0) {
            SearchAlert::delete($alertId, $userId);
            flash('success', 'Search alert deleted.');
        }

        $this->redirect($response, $_SERVER['HTTP_REFERER'] ?? url('/dashboard'));
    }
}


