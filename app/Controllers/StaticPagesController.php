<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Core\CronRunner;
use App\Models\Property;
use App\Models\User;

class StaticPagesController extends Controller
{
    public function newDevelopments(Request $request, Response $response): void
    {
        $developments = Database::query(
            "SELECT p.*, c.name AS category_name, s.name AS state_name, ci.name AS city_name, n.name AS neighbourhood_name,
                    (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY is_cover DESC LIMIT 1) AS cover_image
             FROM properties p
             JOIN property_categories c ON c.id = p.category_id
             JOIN states s ON s.id = p.state_id
             JOIN cities ci ON ci.id = p.city_id
             LEFT JOIN neighbourhoods n ON n.id = p.neighbourhood_id
             WHERE c.slug = 'new-developments' OR p.property_condition = 'under_construction'
             ORDER BY p.created_at DESC"
        );

        $html = $this->render('pages/new_developments', [
            'title' => 'Signature Off-Plan & New Real Estate Developments | Afriserva Real Estate',
            'developments' => $developments
        ]);

        $response->html($html);
    }

    public function investments(Request $request, Response $response): void
    {
        $investmentProperties = Property::all(['sort' => 'views'], 6);

        $html = $this->render('pages/investments', [
            'title' => 'Institutional Real Estate Investments & High-Yield Assets | Afriserva Real Estate',
            'properties' => $investmentProperties
        ]);

        $response->html($html);
    }

    public function propertyManagement(Request $request, Response $response): void
    {
        $html = $this->render('pages/property_management', [
            'title' => 'Comprehensive Property Management & Landlord Services | Afriserva Real Estate'
        ]);

        $response->html($html);
    }

    public function agentsDirectory(Request $request, Response $response): void
    {
        $agents = User::getAgents(20);

        $html = $this->render('pages/agents', [
            'title' => 'Verified Property Advisors & Licensed Brokers | Afriserva Real Estate',
            'agents' => $agents
        ]);

        $response->html($html);
    }

    public function agenciesDirectory(Request $request, Response $response): void
    {
        $agencies = User::getAgencies(20);

        $html = $this->render('pages/agencies', [
            'title' => 'Corporate Real Estate Agencies & Brokerages | Afriserva Real Estate',
            'agencies' => $agencies
        ]);

        $response->html($html);
    }

    public function switchCurrency(Request $request, Response $response): void
    {
        $currency = strtoupper($request->query('currency', 'NGN'));
        $unit = strtoupper($request->query('unit', 'SQM'));

        $allowedCurrencies = ['NGN', 'USD', 'GBP', 'EUR', 'AED', 'GHS'];
        $allowedUnits = ['SQM', 'SQFT', 'PLOTS', 'ACRES', 'HECTARES'];

        if (in_array($currency, $allowedCurrencies, true)) {
            Session::set('user_currency', $currency);
        }

        if (in_array($unit, $allowedUnits, true)) {
            Session::set('measurement_unit', $unit);
        }

        $redirect = $_SERVER['HTTP_REFERER'] ?? url('/');
        $this->redirect($response, $redirect);
    }

    public function marketInsights(Request $request, Response $response): void
    {
        $category = $request->query('category');
        $search = $request->query('q');

        $query = "SELECT * FROM market_articles WHERE is_published = 1";
        $params = [];

        if ($category) {
            $query .= " AND category = :category";
            $params['category'] = $category;
        }

        if ($search) {
            $query .= " AND (title LIKE :search OR excerpt LIKE :search OR content LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $query .= " ORDER BY published_at DESC";
        $articles = Database::query($query, $params);

        // Fetch distinct categories
        $categories = Database::query("SELECT DISTINCT category FROM market_articles WHERE is_published = 1 ORDER BY category ASC");

        $html = $this->render('pages/market_insights', [
            'title' => 'Market Intelligence & Real Estate Advisory Guides | Afriserva Real Estate',
            'articles' => $articles,
            'categories' => array_column($categories, 'category'),
            'selectedCategory' => $category,
            'searchQuery' => $search
        ]);

        $response->html($html);
    }

    public function marketArticle(Request $request, Response $response, array $params): void
    {
        $slug = $params['slug'] ?? '';
        $articles = Database::query("SELECT * FROM market_articles WHERE slug = :slug AND is_published = 1 LIMIT 1", ['slug' => $slug]);

        if (empty($articles)) {
            // Check fallback blog_posts table
            $blogPost = Database::query(
                "SELECT bp.*, bc.name AS category, u.full_name AS author_name, 'Legal Counsel & Real Estate Advisor' AS author_role, '5 min read' AS read_time, bp.cover_image_url AS image_url
                 FROM blog_posts bp
                 JOIN blog_categories bc ON bc.id = bp.category_id
                 JOIN users u ON u.id = bp.author_id
                 WHERE bp.slug = :slug AND bp.status = 'published' LIMIT 1",
                ['slug' => $slug]
            );

            if (empty($blogPost)) {
                flash('error', 'The requested market intelligence guide was not found or has been archived.');
                $this->redirect($response, url('/market-insights'));
                return;
            }

            $article = $blogPost[0];
        } else {
            $article = $articles[0];
        }

        // Fetch related articles
        $related = Database::query(
            "SELECT * FROM market_articles WHERE id != :id AND is_published = 1 ORDER BY published_at DESC LIMIT 3",
            ['id' => $article['id']]
        );

        $html = $this->render('pages/market_article', [
            'title' => e($article['title']) . ' | Afriserva Market Intelligence',
            'article' => $article,
            'related' => $related
        ]);

        $response->html($html);
    }

    public function runCron(Request $request, Response $response): void
    {
        // Require either active Admin role or valid cron secret key
        $adminRole = Session::get('role');
        $cronToken = $request->query('token');
        $expectedToken = getenv('CRON_TOKEN') ?: 'afriserva_cron_secret_key_2026';
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

        $isAuthorized = ($adminRole === 'admin') || 
                         ($cronToken === $expectedToken) || 
                         in_array($clientIp, ['127.0.0.1', '::1'], true);

        if (!$isAuthorized) {
            $this->json($response, ['status' => 'error', 'message' => 'Unauthorized cron execution attempt.'], 403);
            return;
        }

        $task = $request->query('task');
        $results = CronRunner::runAll($task);

        $this->json($response, [
            'status' => 'success',
            'timestamp' => date('Y-m-d H:i:s'),
            'task_executed' => $task ?: 'all',
            'results' => $results
        ]);
    }
}

