<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\Repositories\AirportRepositoryInterface;
use App\Interfaces\Repositories\AirlineRepositoryInterface;
use App\Interfaces\Repositories\FlightRepositoryInterface;
use App\Interfaces\Repositories\SeatRepositoryInterface;
use App\Interfaces\Repositories\BookingRepositoryInterface;
use App\Interfaces\Repositories\PassengerRepositoryInterface;
use App\Repositories\AirportRepository;
use App\Repositories\AirlineRepository;
use App\Repositories\FlightRepository;
use App\Repositories\SeatRepository;
use App\Repositories\BookingRepository;
use App\Repositories\PassengerRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     *
     * @var array<class-string, class-string>
     */
    protected array $repositories = [
        AirportRepositoryInterface::class => AirportRepository::class,
        AirlineRepositoryInterface::class => AirlineRepository::class,
        FlightRepositoryInterface::class => FlightRepository::class,
        SeatRepositoryInterface::class => SeatRepository::class,
        BookingRepositoryInterface::class => BookingRepository::class,
        PassengerRepositoryInterface::class => PassengerRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            if (interface_exists($interface) && class_exists($implementation)) {
                $this->app->bind($interface, $implementation);
            }
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}