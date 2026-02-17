<?php

namespace App\Console\Commands;

use App\Models\Url;
use App\Jobs\CrawlUrlJob;
use Illuminate\Console\Command;

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