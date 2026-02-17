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
        $query = $request->input('q', '');
        $results = $this->searchService->search($query);
        
        return view('search.show', [
            'query' => $query,
            'results' => $results,
            'count' => $results->count(),
        ]);
    }
}