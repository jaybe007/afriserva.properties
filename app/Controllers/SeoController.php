<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\CacheService;

class SeoController extends Controller
{
    /**
     * Generate dynamic, search-engine-friendly XML sitemap
     */
    public function sitemapXml(Request $request, Response $response): void
    {
        // Cache XML for 1 hour to maximize query performance
        $xml = CacheService::remember('seo_sitemap_xml', 3600, function () {
            $baseUrl = rtrim(url('/'), '/');
            $doc = new \DOMDocument('1.0', 'UTF-8');
            $doc->formatOutput = true;

            $urlset = $doc->createElement('urlset');
            $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $urlset->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
            $doc->appendChild($urlset);

            // 1. Static Core Landing Pages
            $staticPages = [
                ['path' => '', 'priority' => '1.0', 'changefreq' => 'daily'],
                ['path' => '/properties', 'priority' => '0.9', 'changefreq' => 'daily'],
                ['path' => '/properties?type=sale', 'priority' => '0.9', 'changefreq' => 'daily'],
                ['path' => '/properties?type=rent', 'priority' => '0.9', 'changefreq' => 'daily'],
                ['path' => '/properties?type=short_let', 'priority' => '0.8', 'changefreq' => 'daily'],
                ['path' => '/properties?type=land', 'priority' => '0.8', 'changefreq' => 'daily'],
                ['path' => '/properties?type=commercial', 'priority' => '0.8', 'changefreq' => 'daily'],
                ['path' => '/properties/map', 'priority' => '0.8', 'changefreq' => 'weekly'],
                ['path' => '/new-developments', 'priority' => '0.8', 'changefreq' => 'weekly'],
                ['path' => '/investments', 'priority' => '0.8', 'changefreq' => 'weekly'],
                ['path' => '/market-insights', 'priority' => '0.8', 'changefreq' => 'daily'],
                ['path' => '/agents', 'priority' => '0.7', 'changefreq' => 'weekly'],
                ['path' => '/agencies', 'priority' => '0.7', 'changefreq' => 'weekly'],
                ['path' => '/property-management', 'priority' => '0.6', 'changefreq' => 'monthly']
            ];

            foreach ($staticPages as $page) {
                $url = $doc->createElement('url');
                $loc = $doc->createElement('loc', htmlspecialchars($baseUrl . $page['path']));
                $changefreq = $doc->createElement('changefreq', $page['changefreq']);
                $priority = $doc->createElement('priority', $page['priority']);

                $url->appendChild($loc);
                $url->appendChild($changefreq);
                $url->appendChild($priority);
                $urlset->appendChild($url);
            }

            // 2. Published Properties
            $properties = Database::query(
                "SELECT p.slug, p.updated_at, p.title,
                        (SELECT image_url FROM property_images pi WHERE pi.property_id = p.id AND pi.is_cover = 1 LIMIT 1) AS cover_image
                 FROM properties p
                 WHERE p.status = 'published'
                 ORDER BY p.updated_at DESC
                 LIMIT 500"
            );

            foreach ($properties as $prop) {
                $url = $doc->createElement('url');
                $loc = $doc->createElement('loc', htmlspecialchars($baseUrl . '/properties/' . $prop['slug']));
                $lastmod = $doc->createElement('lastmod', date('Y-m-d', strtotime($prop['updated_at'])));
                $changefreq = $doc->createElement('changefreq', 'weekly');
                $priority = $doc->createElement('priority', '0.8');

                $url->appendChild($loc);
                $url->appendChild($lastmod);
                $url->appendChild($changefreq);
                $url->appendChild($priority);

                if (!empty($prop['cover_image'])) {
                    $image = $doc->createElement('image:image');
                    $imgLoc = $doc->createElement('image:loc', htmlspecialchars($prop['cover_image']));
                    $imgTitle = $doc->createElement('image:title', htmlspecialchars($prop['title']));
                    $image->appendChild($imgLoc);
                    $image->appendChild($imgTitle);
                    $url->appendChild($image);
                }

                $urlset->appendChild($url);
            }

            // 3. Market Articles & Regulatory Guides
            $articles = Database::query(
                "SELECT slug, published_at FROM market_articles WHERE is_published = 1 ORDER BY published_at DESC LIMIT 100"
            );

            foreach ($articles as $art) {
                $url = $doc->createElement('url');
                $loc = $doc->createElement('loc', htmlspecialchars($baseUrl . '/market-insights/' . $art['slug']));
                $lastmod = $doc->createElement('lastmod', date('Y-m-d', strtotime($art['published_at'])));
                $changefreq = $doc->createElement('changefreq', 'monthly');
                $priority = $doc->createElement('priority', '0.7');

                $url->appendChild($loc);
                $url->appendChild($lastmod);
                $url->appendChild($changefreq);
                $url->appendChild($priority);
                $urlset->appendChild($url);
            }

            $xmlContent = $doc->saveXML();
            file_put_contents(__DIR__ . '/../../public/sitemap.xml', $xmlContent, LOCK_EX);
            return $xmlContent;
        });

        header('Content-Type: application/xml; charset=utf-8');
        echo $xml;
        exit;
    }

    public static function generateXml(): string
    {
        $instance = new self();
        $req = new Request();
        $res = new Response();
        // Clear cache and regenerate
        CacheService::delete('seo_sitemap_xml');
        ob_start();
        $instance->sitemapXml($req, $res);
        return ob_get_clean() ?: '';
    }
}
