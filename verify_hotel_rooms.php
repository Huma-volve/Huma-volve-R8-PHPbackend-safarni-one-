<?php

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Hotel;
use App\Http\Controllers\Api\Hotel\RoomController;
use Illuminate\Support\Facades\Auth;

// Mock Auth
$user = User::first();
if ($user)
    Auth::login($user);

$hotel = Hotel::where('name', 'Grand Plaza Hotel')->first();
if (!$hotel) {
    echo "Grand Plaza Hotel not found. Please seed data.\n";
    exit;
}

$controller = app(RoomController::class);

echo "--- Testing Room Availability for {$hotel->name} ---\n";
$request = Request::create("/hotels/{$hotel->id}/rooms", 'GET');
$response = $controller->index($request, $hotel->id);
$data = $response->getData()->data;

echo "Found " . count($data) . " rooms.\n";

foreach ($data as $room) {
    echo "Room: {$room->name}\n";
    echo "Price: {$room->price_per_night} EGP\n";
    echo "Image: " . ($room->main_image ?? 'No Image') . "\n";
    echo "-------------------\n";
}
