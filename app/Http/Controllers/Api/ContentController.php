<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchContentRequest;
use Illuminate\Http\Request;
use App\Services\ContentService;
use App\Http\Requests\StoreContentRequest;

class ContentController extends Controller
{
    public function __construct(protected ContentService $service)
    {
    }

    public function index(Request $request, $locale)
    {
        $data = $this->service->getContentForDelivery($locale, $request->tag, $request->group);

        return response()->json($data)
            ->setPublic()
            ->setMaxAge(3600)
            ->header('Vary', 'Accept-Encoding, Authorization');
    }

    public function store(StoreContentRequest $request)
    {
        $content = $this->service->storeContent($request->validated());
        return response()->json($content, 201);
    }

    public function show($id)
    {
        $content = $this->service->getContentDetail((int) $id);
        return response()->json($content);
    }

    public function search(SearchContentRequest $request)
    {
        $filters = $request->validated();

        $results = $this->service->searchContent($filters);

        if ($results->isEmpty()) {
            return response()->json(['message' => 'No results'], 200);
        }

        return response()->json($results);
    }
}
