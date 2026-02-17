<?php

namespace App\Jobs;

use App\Models\Url;
use App\Services\CrawlerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrawlUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Url $url
    ) {}

    public function handle(CrawlerService $crawler): void
    {
        $crawler->crawl($this->url);
        
        // Small delay to be polite
        sleep(1);
    }
}