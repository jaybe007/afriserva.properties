<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Property;
use App\Models\User;
use App\Models\Location;
use App\Core\Database;

class HomeController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $featured = Property::getFeatured(6);
        $newlyListed = Property::getNewlyListed(6);
        $luxury = Property::getLuxury(4);
        $shortLets = Property::all(['transaction_type' => 'short_let'], 4);
        $affordable = Property::getAffordable(4);
        $recentlyReduced = Property::getRecentlyReduced(4);
        $popularLocations = Location::getPopularLocations();
        $topAgents = User::getAgents(4);
        $topAgencies = User::getAgencies(3);

        $marketArticles = Database::query(
            "SELECT * FROM market_articles WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3"
        );

        $reviews = Database::query(
            "SELECT r.*, u.full_name, u.avatar_url, 
                    p.title AS property_title, p.slug AS property_slug,
                    ag.full_name AS reviewed_agent_name
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             LEFT JOIN properties p ON (r.reviewable_type = 'property' AND p.id = r.reviewable_id)
             LEFT JOIN users ag ON (r.reviewable_type = 'agent' AND ag.id = r.reviewable_id)
             WHERE r.is_approved = 1
             ORDER BY r.created_at DESC
             LIMIT 3"
        );

        $categories = Database::query(
            "SELECT pc.*, COUNT(p.id) AS property_count 
             FROM property_categories pc
             LEFT JOIN properties p ON p.category_id = pc.id AND p.status = 'published'
             GROUP BY pc.id
             ORDER BY pc.display_order ASC"
        );

        $states = Location::getStates();

        $stats = [
            'properties_listed' => (int)Database::fetchColumn("SELECT COUNT(*) FROM properties WHERE status = 'published' AND deleted_at IS NULL"),
            'verified_agents' => (int)Database::fetchColumn("SELECT COUNT(*) FROM agent_profiles WHERE is_verified = 1"),
            'prime_locations' => (int)Database::fetchColumn("SELECT COUNT(*) FROM neighbourhoods"),
            'active_states' => (int)Database::fetchColumn("SELECT COUNT(DISTINCT state_id) FROM properties WHERE status = 'published'"),
            'total_inquiries' => (int)Database::fetchColumn("SELECT COUNT(*) FROM enquiries")
        ];

        $topBanner = \App\Models\BannerAd::getActive('homepage_top');
        if ($topBanner) {
            \App\Models\BannerAd::recordImpression((int)$topBanner['id']);
        }

        $html = $this->render('home/index', [
            'title' => 'Afriserva Real Estate | Nigeria’s Premier Property Marketplace & Asset Ecosystem',
            'featured' => $featured,
            'newlyListed' => $newlyListed,
            'luxury' => $luxury,
            'shortLets' => $shortLets,
            'affordable' => $affordable,
            'recentlyReduced' => $recentlyReduced,
            'popularLocations' => $popularLocations,
            'topAgents' => $topAgents,
            'topAgencies' => $topAgencies,
            'marketArticles' => $marketArticles,
            'reviews' => $reviews,
            'categories' => $categories,
            'states' => $states,
            'stats' => $stats,
            'topBanner' => $topBanner
        ]);

        $response->html($html);
    }

    public function trackBannerClick(Request $request, Response $response, array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $banner = Database::fetch("SELECT * FROM banner_advertisements WHERE id = ?", [$id]);

        if ($banner) {
            \App\Models\BannerAd::recordClick($id);
            $this->redirect($response, $banner['target_url']);
            return;
        }

        $this->redirect($response, url('/'));
    }

    public function subscribeNewsletter(Request $request, Response $response): void
    {
        $email = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL);
        $name = trim($request->input('name') ?? '');

        if (!$email) {
            if ($request->isAjax()) {
                $this->json($response, ['status' => 'error', 'message' => 'Please provide a valid corporate or personal email address.'], 422);
                return;
            }
            flash('danger', 'Please provide a valid email address.');
            $this->redirect($response, url('/'));
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        Database::execute(
            "INSERT INTO newsletter_subscribers (email, full_name, ip_address, created_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE status = 'active', full_name = COALESCE(VALUES(full_name), full_name)",
            [$email, $name ?: null, $ip]
        );

        if ($request->isAjax()) {
            $this->json($response, ['status' => 'success', 'message' => 'Thank you for subscribing to Afriserva Private Real Estate Briefings.']);
            return;
        }

        flash('success', 'Thank you! You are now subscribed to Afriserva institutional market insights.');
        $this->redirect($response, url('/'));
    }
}

