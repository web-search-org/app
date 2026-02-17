<?php

namespace App\Console\Commands;

use App\Models\Url;
use App\Models\Page;
use Illuminate\Console\Command;

class HealthCheckCommand extends Command
{
    protected $signature = 'search:health';
    
    public function handle(): int
    {
        $stats = [
            'Total URLs' => Url::count(),
            'Pending' => Url::where('status', 'pending')->count(),
            'Crawled' => Url::where('status', 'crawled')->count(),
            'Failed' => Url::where('status', 'failed')->count(),
            'Pages without content' => Page::where('content', '')->count(),
            'Avg page size' => Page::avg('word_count') . ' words',
            'Unique domains' => Url::distinct('domain')->count(),
        ];
        
        foreach ($stats as $label => $value) {
            $this->line("<info>{$label}:</info> {$value}");
        }
        
        // Alert thresholds
        $failedRate = Url::where('status', 'failed')->count() / max(Url::count(), 1);
        if ($failedRate > 0.1) {
            $this->warn("High failure rate: " . round($failedRate * 100, 1) . "%");
        }
        
        return Command::SUCCESS;
    }
}