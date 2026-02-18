<?php

namespace App\Console\Commands;

use App\Models\Url;
use Illuminate\Console\Command;

class ScheduleCrawlCommand extends Command
{
    protected $signature = 'crawl:schedule 
                            {--fresh : Re-crawl already crawled URLs}
                            {--priority= : Minimum priority to consider}';
    
    public function handle(): int
    {
        $query = Url::query();
        
        if ($this->option('fresh')) {
            // Re-crawl old pages
            $query->where(function ($q) {
                $q->where('last_crawled_at', '<', now()->subDays(7))
                  ->orWhereNull('last_crawled_at');
            });
        } else {
            $query->where('status', 'pending');
        }
        
        if ($priority = $this->option('priority')) {
            $query->where('priority', '>=', $priority);
        }
        
        $count = $query->update([
            'status' => 'pending',
            'next_crawl_at' => now(),
        ]);
        
        $this->info("Scheduled {$count} URLs for crawling.");
        
        return Command::SUCCESS;
    }
}