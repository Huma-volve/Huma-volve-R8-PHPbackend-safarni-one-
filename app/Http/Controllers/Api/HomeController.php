<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Home\HomeDataRequest;
use App\Services\HomeService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for home page API endpoints.
 */
class HomeController extends BaseApiController
{
    /**
     * Get home page data.
     */
    public function index(HomeDataRequest $request, HomeService $service): JsonResponse
    {
        return $this->successResponse(
            data: $service->getHomePayload(),
            message: 'Home page data retrieved successfully'
        );
    }
}