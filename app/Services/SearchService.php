<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Collection;

class SearchService
{
    public function search(string $query, int $perPage = 10): Collection
    {
        if (empty(trim($query))) {
            return collect();
        }
        
        // Using MySQL/MariaDB FULLTEXT search
        $results = Page::with('url')
            ->whereFullText(['title', 'content', 'meta_description'], $query)
            ->orWhere('title', 'LIKE', "%{$query}%")
            ->limit($perPage)
            ->get();
        
        return $results->map(function ($page) use ($query) {
            return [
                'title' => $page->title ?? 'Untitled',
                'url' => $page->url->url,
                'description' => $this->getSnippet(
                    $page->meta_description ?? $page->content, 
                    $query
                ),
                'domain' => $page->url->domain,
            ];
        });
    }

    private function getSnippet(string $text, string $query): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Try to find query in text
        $pos = stripos($text, $query);
        
        if ($pos !== false) {
            $start = max(0, $pos - 75);
            $snippet = substr($text, $start, 200);
            
            if ($start > 0) {
                $snippet = '...' . $snippet;
            }
            
            return $snippet . '...';
        }
        
        // Fallback to first 200 chars
        return substr($text, 0, 200) . '...';
    }
}