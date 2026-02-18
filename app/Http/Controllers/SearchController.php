<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {}

    public function index()
    {
        return view('search.index');
    }

    public function show(Request $request)
    {
        $query = (string) $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('per_page', 10)));

        $results = $this->searchService->search($query, $page, $perPage);

        return view('search.show', [
            'query' => $query,
            'results' => $results, // LengthAwarePaginator
            'count' => $results->total(),
        ]);
    }
}