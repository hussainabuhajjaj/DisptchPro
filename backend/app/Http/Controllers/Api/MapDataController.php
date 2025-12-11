<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Map\MapDataCacheService;
use Illuminate\Http\JsonResponse;

class MapDataController extends Controller
{
    public function __construct(
        protected MapDataCacheService $cacheService
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->cacheService->get());
    }
}
