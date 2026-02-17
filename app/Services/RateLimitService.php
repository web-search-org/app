<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    private const DEFAULT_DELAY_MS = 1000;
    
    public function waitForDomain(string $domain): void
    {
        $key = "crawl_last:{$domain}";
        $delay = $this->getDomainDelay($domain);
        
        $lastCrawl = Cache::get($key, 0);
        $elapsed = (microtime(true) - $lastCrawl) * 1000;
        
        if ($elapsed < $delay) {
            usleep(($delay - $elapsed) * 1000);
        }
        
        Cache::put($key, microtime(true), 3600);
    }
    
    private function getDomainDelay(string $domain): int
    {
        // Check robots.txt delay first
        $robotsDelay = app(RobotsService::class)->getCrawlDelay($domain);
        
        return $robotsDelay ?? self::DEFAULT_DELAY_MS;
    }
}