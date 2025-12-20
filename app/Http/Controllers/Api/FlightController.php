<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Flight\CompareFlightsRequest;
use App\Http\Requests\Flight\SearchFlightRequest;
use App\Http\Requests\Flight\StoreFlightRequest;
use App\Http\Requests\Flight\UpdateFlightRequest;
use App\Http\Resources\FlightComparisonResource;
use App\Http\Resources\FlightResource;
use App\Services\FlightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FlightController extends BaseApiController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected FlightService $flightService
    ) {}

    /**
     * Search flights.
     */
    public function index(SearchFlightRequest $request): AnonymousResourceCollection
    {
        $flights = $this->flightService->searchFlights(
            $request->input('origin'),
            $request->input('destination'),
            $request->input('date'),
            $request->except(['origin', 'destination', 'date'])
        );

        return FlightResource::collection($flights);
    }

    /**
     * Store a newly created flight.
     */
    public function store(StoreFlightRequest $request): JsonResponse
    {
        $flight = $this->flightService->createFlight($request->validated());

        return $this->createdResponse(
            new FlightResource($flight->load(['airline', 'originAirport', 'destinationAirport'])),
            'Flight created successfully'
        );
    }

    /**
     * Display the specified flight.
     */
    public function show(string $id): JsonResponse
    {
        $flight = $this->flightService->getFlightById($id);

        if (!$flight) {
            return $this->notFoundResponse('Flight not found');
        }

        return $this->successResponse(
            new FlightResource($flight)
        );
    }

    /**
     * Update the specified flight.
     */
    public function update(UpdateFlightRequest $request, string $id): JsonResponse
    {
        $flight = $this->flightService->getFlightById($id);

        if (!$flight) {
            return $this->notFoundResponse('Flight not found');
        }

        $this->flightService->updateFlight($id, $request->validated());
        $updatedFlight = $this->flightService->getFlightById($id);

        return $this->successResponse(
            new FlightResource($updatedFlight),
            'Flight updated successfully'
        );
    }

    /**
     * Remove the specified flight.
     */
    public function destroy(string $id): JsonResponse
    {
        $flight = $this->flightService->getFlightById($id);

        if (!$flight) {
            return $this->notFoundResponse('Flight not found');
        }

        $this->flightService->deleteFlight($id);

        return $this->successResponse(
            null,
            'Flight deleted successfully'
        );
    }

    /**
     * Compare multiple flights.
     */
    public function compare(CompareFlightsRequest $request): JsonResponse
    {
        $flights = $this->flightService->compareFlights($request->input('flight_ids'));

        return $this->successResponse([
            'comparison' => FlightComparisonResource::collection($flights),
            'count' => $flights->count(),
        ]);
    }
}