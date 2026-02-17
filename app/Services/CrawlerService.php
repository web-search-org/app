<?php

namespace App\Services;

use App\Models\Url;
use App\Models\Page;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class CrawlerService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'WebSearchBot/1.0'
            ]
        ]);
    }

    public function crawl(Url $url): bool
    {
        try {
            $response = $this->client->get($url->url);
            $html = (string) $response->getBody();
            
            $crawler = new Crawler($html, $url->url);
            
            // Extract data
            $title = $this->extractTitle($crawler);
            $description = $this->extractDescription($crawler);
            $content = $this->extractContent($crawler);
            
            // Save page
            Page::updateOrCreate(
                ['url_id' => $url->id],
                [
                    'title' => $title,
                    'meta_description' => $description,
                    'content' => $content,
                ]
            );
            
            // Extract and queue new URLs
            $this->extractLinks($crawler, $url);
            
            // Update URL status
            $url->update([
                'status' => 'crawled',
                'last_crawled_at' => now(),
                'crawl_count' => $url->crawl_count + 1,
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Crawl failed for {$url->url}: " . $e->getMessage());
            
            $url->update(['status' => 'failed']);
            
            return false;
        }
    }

    private function extractTitle(Crawler $crawler): string
    {
        try {
            return $crawler->filter('title')->text();
        } catch (\Exception $e) {
            return 'Untitled';
        }
    }

    private function extractDescription(Crawler $crawler): ?string
    {
        try {
            return $crawler->filter('meta[name="description"]')
                ->attr('content');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractContent(Crawler $crawler): string
    {
        try {
            // Remove script and style tags
            $crawler->filter('script, style, nav, footer, header')
                ->each(fn($node) => $node->getNode(0)?->parentNode
                    ->removeChild($node->getNode(0)));
            
            // Get body text
            $text = $crawler->filter('body')->text();
            
            // Clean up whitespace
            $text = preg_replace('/\s+/', ' ', $text);
            
            return trim(substr($text, 0, 50000)); // Limit content size
            
        } catch (\Exception $e) {
            return '';
        }
    }

    private function extractLinks(Crawler $crawler, Url $parentUrl): void
    {
        try {
            $crawler->filter('a')->each(function ($node) use ($parentUrl) {
                try {
                    $href = $node->attr('href');
                    if (!$href) return;
                    
                    // Convert relative URLs to absolute
                    $absoluteUrl = $this->makeAbsoluteUrl(
                        $href, 
                        $parentUrl->url
                    );
                    
                    if (!$absoluteUrl || !$this->isValidUrl($absoluteUrl)) {
                        return;
                    }
                    
                    // Only crawl same domain (optional)
                    $domain = parse_url($absoluteUrl, PHP_URL_HOST);
                    
                    // Add to queue if not exists
                    Url::firstOrCreate(
                        ['url' => $absoluteUrl],
                        [
                            'domain' => $domain,
                            'status' => 'pending',
                        ]
                    );
                } catch (\Exception $e) {
                    // Skip invalid links
                }
            });
        } catch (\Exception $e) {
            // No links found
        }
    }

    private function makeAbsoluteUrl(string $href, string $baseUrl): ?string
    {
        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return $href;
        }
        
        $base = parse_url($baseUrl);
        
        if ($href[0] === '/') {
            return $base['scheme'] . '://' . $base['host'] . $href;
        }
        
        $path = $base['path'] ?? '/';
        $directory = dirname($path);
        
        return $base['scheme'] . '://' . $base['host'] . 
               $directory . '/' . $href;
    }

    private function isValidUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $parsed = parse_url($url);
        
        // Only HTTP/HTTPS
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return false;
        }
        
        // Skip common file extensions
        $path = $parsed['path'] ?? '';
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'exe'];
        
        foreach ($extensions as $ext) {
            if (str_ends_with(strtolower($path), ".$ext")) {
                return false;
            }
        }
        
        return true;
    }
}