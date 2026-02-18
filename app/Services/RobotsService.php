<?php

namespace App\Services;

use App\Models\Domain;
use GuzzleHttp\Client;
use Spatie\Robots\RobotsTxt;

class RobotsService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 5,
            'verify' => false,
        ]);
    }

    public function canCrawl(string $url): bool
    {
        $parsed = parse_url($url);
        $domain = $parsed['host'];
        
        $domainModel = Domain::firstOrCreate(
            ['domain' => $domain],
            ['domain' => $domain]
        );
        
        // Fetch robots.txt if not cached
        if (!$domainModel->robots_content || 
            $domainModel->robots_fetched_at?->diffInDays(now()) > 7) {
            $this->fetchRobots($domainModel);
        }
        
        if (!$domainModel->robots_content) {
            return true; // No robots.txt = allowed
        }
        
        $robots = RobotsTxt::create($domainModel->robots_content);
        return $robots->allows($url, 'WebSearchBot');
    }

    private function fetchRobots(Domain $domain): void
    {
        try {
            $url = "https://{$domain->domain}/robots.txt";
            $response = $this->client->get($url);
            
            $domain->update([
                'robots_content' => (string) $response->getBody(),
                'robots_fetched_at' => now(),
            ]);
        } catch (\Exception $e) {
            $domain->update([
                'robots_fetched_at' => now(),
            ]);
        }
    }
    
    public function getCrawlDelay(string $domain): ?int
    {
        $domainModel = Domain::where('domain', $domain)->first();
        
        if (!$domainModel?->robots_content) {
            return null;
        }
        
        // Parse crawl-delay from robots.txt
        if (preg_match('/crawl-delay:\s*(\d+)/i', 
            $domainModel->robots_content, $matches)) {
            return (int) $matches[1] * 1000; // Convert to ms
        }
        
        return null;
    }
}