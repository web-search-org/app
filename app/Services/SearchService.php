<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    /*
    public function search(string $query, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        if (empty(trim($query))) {
            return Page::paginate($perPage);
        }
        
        $searchTerms = $this->parseQuery($query);
        
        return Page::query()
            ->with('url')
            ->where(function ($q) use ($searchTerms) {
                // Fulltext for main terms
                if (!empty($searchTerms['must'])) {
                    $q->whereFullText(
                        ['title', 'content', 'meta_description'],
                        implode(' ', $searchTerms['must'])
                    );
                }
                
                // Title boost
                foreach ($searchTerms['must'] as $term) {
                    $q->orWhere('title', 'LIKE', "%{$term}%");
                }
            })
            ->orderByRaw($this->getRankingSql())
            ->paginate($perPage, ['*'], 'page', $page);
    }
    */
    
    private function parseQuery(string $query): array
    {
        // Support operators: word, -exclude, "exact phrase"
        preg_match_all('/(-?)"?([^"]+)"?/', $query, $matches);
        
        $terms = [
            'must' => [],
            'exclude' => [],
        ];
        
        foreach ($matches[2] as $i => $term) {
            $term = trim($term);
            if (empty($term)) continue;
            
            if (str_starts_with($matches[1][$i], '-')) {
                $terms['exclude'][] = $term;
            } else {
                $terms['must'][] = $term;
            }
        }
        
        return $terms;
    }
    
    private function getRankingSql(): string
    {
        return <<<SQL
            CASE 
                WHEN title LIKE ? THEN 100
                WHEN title LIKE ? THEN 50
                WHEN meta_description LIKE ? THEN 25
                ELSE 1
            END DESC,
            word_count DESC,
            created_at DESC
        SQL;
    }

    public function search(string $query, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        $query = trim($query);

        // Empty query -> empty paginator
        if ($query === '') {
            return new LengthAwarePaginator(
                [], 0, $perPage, $page, [
                    'path' => request()->url(),
                    'query' => request()->query(),
                ]
            );
        }

        $cacheKey = 'search:' . md5($query . '|' . $page . '|' . $perPage);

        $data = Cache::remember($cacheKey, 300, function () use ($query, $page, $perPage) {
            $qb = Page::with('url')
                ->whereFullText(['title', 'content', 'meta_description'], $query)
                ->orWhere('title', 'LIKE', "%{$query}%");

            $total = (clone $qb)->count();

            $items = $qb
                ->orderByRaw("MATCH(title, content, meta_description) AGAINST(? IN NATURAL LANGUAGE MODE) DESC", [$query])
                ->orderByDesc('created_at')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get()
                ->map(function (Page $pageModel) use ($query) {
                    $textSource = $pageModel->meta_description ?: $pageModel->content ?? '';
                    return [
                        'id' => $pageModel->id,
                        'title' => $pageModel->title ?? 'Untitled',
                        'url' => $pageModel->url?->url,
                        'description' => $this->getSnippet($textSource, $query),
                        'domain' => $pageModel->url?->domain,
                        'created_at' => $pageModel->created_at?->toDateTimeString(),
                        'word_count' => $pageModel->word_count,
                    ];
                })
                ->toArray();

            return ['items' => $items, 'total' => $total];
        });

        return new LengthAwarePaginator(
            $data['items'],
            $data['total'],
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    private function getSnippet(string $text, string $query): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $pos = stripos($text, $query);

        if ($pos !== false) {
            $start = max(0, $pos - 75);
            $snippet = substr($text, $start, 200);
            if ($start > 0) {
                $snippet = '...' . $snippet;
            }
            return trim($snippet) . '...';
        }

        return trim(substr($text, 0, 200)) . '...';
    }
}