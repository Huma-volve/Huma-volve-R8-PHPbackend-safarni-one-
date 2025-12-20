<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Airline\StoreAirlineRequest;
use App\Http\Requests\Airline\UpdateAirlineRequest;
use App\Http\Resources\AirlineResource;
use App\Services\AirlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AirlineController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AirlineService $airlineService
    ) {}

    /**
     * Display a listing of airlines.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if ($request->has('search')) {
            $airlines = $this->airlineService->searchAirlines($request->input('search'));
            return AirlineResource::collection($airlines);
        }

        if ($request->boolean('active_only')) {
            $airlines = $this->airlineService->getActiveAirlines();
            return AirlineResource::collection($airlines);
        }

        $airlines = $this->airlineService->getPaginatedAirlines(
            $request->input('per_page', 15)
        );

        return AirlineResource::collection($airlines);
    }

    /**
     * Store a newly created airline.
     */
    public function store(StoreAirlineRequest $request): JsonResponse
    {
        $airline = $this->airlineService->createAirline($request->validated());

        return $this->createdResponse(
            new AirlineResource($airline),
            'Airline created successfully'
        );
    }

    /**
     * Display the specified airline.
     */
    public function show(int $id): JsonResponse
    {
        $airline = $this->airlineService->getAirlineById($id);

        return $this->successResponse(
            new AirlineResource($airline)
        );
    }

    /**
     * Update the specified airline.
     */
    public function update(UpdateAirlineRequest $request, int $id): JsonResponse
    {
        $this->airlineService->updateAirline($id, $request->validated());
        $airline = $this->airlineService->getAirlineById($id);

        return $this->successResponse(
            new AirlineResource($airline),
            'Airline updated successfully'
        );
    }

    /**
     * Remove the specified airline.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->airlineService->deleteAirline($id);

        return $this->successResponse(
            null,
            'Airline deleted successfully'
        );
    }

    /**
     * Find airline by IATA code.
     */
    public function findByCode(string $code): JsonResponse
    {
        $airline = $this->airlineService->getAirlineByCode($code);

        if (!$airline) {
            return $this->notFoundResponse('Airline not found');
        }

        return $this->successResponse(
            new AirlineResource($airline)
        );
    }
}