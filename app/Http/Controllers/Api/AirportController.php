<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Airport\StoreAirportRequest;
use App\Http\Requests\Airport\UpdateAirportRequest;
use App\Http\Resources\AirportResource;
use App\Services\AirportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AirportController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected AirportService $airportService
    ) {}

    /**
     * Display a listing of airports.
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        if ($request->has('search')) {
            $airports = $this->airportService->searchAirports($request->input('search'));
            return AirportResource::collection($airports);
        }

        $airports = $this->airportService->getPaginatedAirports(
            $request->input('per_page', 15)
        );

        return AirportResource::collection($airports);
    }

    /**
     * Store a newly created airport.
     */
    public function store(StoreAirportRequest $request): JsonResponse
    {
        $airport = $this->airportService->createAirport($request->validated());

        return $this->createdResponse(
            new AirportResource($airport),
            'Airport created successfully'
        );
    }

    /**
     * Display the specified airport.
     */
    public function show(int $id): JsonResponse
    {
        $airport = $this->airportService->getAirportById($id);

        return $this->successResponse(
            new AirportResource($airport)
        );
    }

    /**
     * Update the specified airport.
     */
    public function update(UpdateAirportRequest $request, int $id): JsonResponse
    {
        $this->airportService->updateAirport($id, $request->validated());
        $airport = $this->airportService->getAirportById($id);

        return $this->successResponse(
            new AirportResource($airport),
            'Airport updated successfully'
        );
    }

    /**
     * Remove the specified airport.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->airportService->deleteAirport($id);

        return $this->successResponse(
            null,
            'Airport deleted successfully'
        );
    }

    /**
     * Find airport by IATA code.
     */
    public function findByCode(string $code): JsonResponse
    {
        $airport = $this->airportService->getAirportByCode($code);

        if (!$airport) {
            return $this->notFoundResponse('Airport not found');
        }

        return $this->successResponse(
            new AirportResource($airport)
        );
    }
}