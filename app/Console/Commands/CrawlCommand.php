<?php

namespace App\Console\Commands;

use App\Models\Url;
use App\Jobs\CrawlUrlJob;
use Illuminate\Console\Command;
use App\Services\RobotsService;

class CrawlCommand extends Command
{
    protected $signature = 'crawl:start {url?}';
    protected $description = 'Start crawling URLs';

    public function handle(): void
    {
        if ($url = $this->argument('url')) {
            // Add single URL
            $domain = parse_url($url, PHP_URL_HOST);
            
            $urlModel = Url::firstOrCreate(
                ['url' => $url],
                ['domain' => $domain, 'status' => 'pending']
            );
            
            $this->info("Added: {$url}");
            CrawlUrlJob::dispatch($urlModel);
        } else {
            // Process pending URLs
            $urls = Url::where('status', 'pending')->limit(10)->get();
            $urls = Url::where('status', 'pending')
                ->orderBy('priority', 'desc')
                ->orderBy('created_at')
                ->limit(100)
                ->get();
    
            foreach ($urls as $url) {
                // Check robots.txt before dispatching
                if (!app(RobotsService::class)->canCrawl($url->url)) {
                    $url->update([
                        'status' => 'skipped',
                        'robots_allowed' => false,
                    ]);
                    continue;
                }
                
                CrawlUrlJob::dispatch($url)
                    ->onQueue("crawl-{$url->priority}");
            }
            
            if ($urls->isEmpty()) {
                $this->warn('No pending URLs to crawl.');
                return;
            }
            
            foreach ($urls as $url) {
                $this->info("Crawling: {$url->url}");
                CrawlUrlJob::dispatch($url);
            }
        }
        
        $this->info('Crawl jobs dispatched!');
    }
}