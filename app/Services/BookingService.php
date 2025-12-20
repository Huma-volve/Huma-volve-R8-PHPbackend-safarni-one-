<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Interfaces\Repositories\BookingRepositoryInterface;
use App\Interfaces\Repositories\FlightRepositoryInterface;
use App\Interfaces\Repositories\SeatRepositoryInterface;
use App\Models\BookingDetail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected BookingRepositoryInterface $bookingRepository,
        protected FlightRepositoryInterface $flightRepository,
        protected SeatRepositoryInterface $seatRepository
    ) {}

    /**
     * Get user bookings.
     */
    public function getUserBookings(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->bookingRepository->getByUser($userId, $perPage);
    }

    /**
     * Get booking by ID.
     */
    public function getBookingById(int $id): ?object
    {
        return $this->bookingRepository->findWithRelations($id);
    }

    /**
     * Create booking summary (pre-checkout).
     */
    public function createSummary(array $data): array
    {
        $flight = $this->flightRepository->findWithRelations($data['flight_id']);

        if (!$flight) {
            return ['success' => false, 'message' => 'Flight not found'];
        }

        // Calculate pricing
        $basePricePerPerson = $flight->base_price_egp;
        $taxPercentage = $flight->tax_percentage;
        $passengerCount = count($data['passengers']);

        // Calculate seat modifiers
        $seatModifiers = 0;
        if (isset($data['seat_ids'])) {
            foreach ($data['seat_ids'] as $seatId) {
                // Cast UUID to string
                $seat = $this->seatRepository->find((string) $seatId);
                if ($seat) {
                    $seatModifiers += $seat->price_modifier_egp;
                }
            }
        }

        $subtotal = ($basePricePerPerson * $passengerCount) + $seatModifiers;
        $taxAmount = (int) ($subtotal * ($taxPercentage / 100));
        $totalPrice = $subtotal + $taxAmount;

        // Generate booking token
        $bookingToken = bin2hex(random_bytes(32));

        // Store summary in cache for checkout
        $summary = [
            'flight_id' => $flight->id,
            'flight_number' => $flight->flight_number,
            'origin' => $flight->originAirport->code,
            'destination' => $flight->destinationAirport->code,
            'departure_time' => $flight->departure_time->toISOString(),
            'passengers' => $data['passengers'],
            'seat_ids' => $data['seat_ids'] ?? [],
            'pricing' => [
                'base_price_per_person' => $basePricePerPerson,
                'passenger_count' => $passengerCount,
                'seat_modifiers' => $seatModifiers,
                'subtotal' => $subtotal,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total_price' => $totalPrice,
                'formatted' => [
                    'subtotal' => number_format($subtotal / 100, 2) . ' EGP',
                    'tax_amount' => number_format($taxAmount / 100, 2) . ' EGP',
                    'total_price' => number_format($totalPrice / 100, 2) . ' EGP',
                ],
            ],
            'booking_token' => $bookingToken,
            'expires_at' => now()->addMinutes(30)->toISOString(),
        ];

        cache()->put("booking_summary_{$bookingToken}", $summary, now()->addMinutes(30));

        return ['success' => true, 'summary' => $summary];
    }

    /**
     * Process checkout and create booking.
     */
    public function checkout(string $bookingToken, int $userId): array
    {
        $summary = cache()->get("booking_summary_{$bookingToken}");

        if (!$summary) {
            return ['success' => false, 'message' => 'Booking session expired'];
        }

        try {
            return DB::transaction(function () use ($summary, $userId, $bookingToken) {
                // Create booking
                $booking = $this->bookingRepository->create([
                    'user_id' => $userId,
                    'category' => 'flights',
                    'item_id' => 0,
                    'total_price' => $summary['pricing']['total_price'],
                    'payment_status' => PaymentStatus::PENDING->value,
                    'status' => BookingStatus::PENDING->value,
                ]);

                // Create booking detail
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'meta' => [
                        'flight_id' => $summary['flight_id'],
                        'flight_number' => $summary['flight_number'],
                        'origin' => $summary['origin'],
                        'destination' => $summary['destination'],
                        'departure_time' => $summary['departure_time'],
                        'seat_ids' => $summary['seat_ids'],
                        'pricing' => $summary['pricing'],
                    ],
                ]);

                // Book seats - Cast UUID to string
                foreach ($summary['seat_ids'] as $seatId) {
                    $this->seatRepository->bookSeat((string) $seatId);
                }

                // Clear cache
                cache()->forget("booking_summary_{$bookingToken}");

                return [
                    'success' => true,
                    'booking' => $this->bookingRepository->findWithRelations($booking->id),
                ];
            });
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()];
        }
    }

    /**
     * Confirm booking after payment.
     */
    public function confirmBooking(int $bookingId): bool
    {
        $this->bookingRepository->updatePaymentStatus($bookingId, PaymentStatus::SUCCEEDED->value);

        return $this->bookingRepository->updateStatus($bookingId, BookingStatus::CONFIRMED->value);
    }

    /**
     * Cancel booking.
     */
    public function cancelBooking(int $bookingId): bool
    {
        $booking = $this->bookingRepository->findWithRelations($bookingId);

        if (!$booking) {
            return false;
        }

        // Release seats if any
        if ($booking->detail && isset($booking->detail->meta['seat_ids'])) {
            foreach ($booking->detail->meta['seat_ids'] as $seatId) {
                // Cast UUID to string
                $seat = $this->seatRepository->find((string) $seatId);
                if ($seat) {
                    $seat->update(['is_available' => true]);
                }
            }
        }

        return $this->bookingRepository->updateStatus($bookingId, BookingStatus::CANCELLED->value);
    }
}