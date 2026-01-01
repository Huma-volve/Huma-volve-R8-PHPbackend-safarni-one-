<?php

use App\Http\Controllers\Api\Hotel\HotelHomepageController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock Auth
$user = User::first();
if ($user)
    Auth::login($user);

$controller = app(HotelHomepageController::class);

echo "--- Testing Recommendations ---\n";
$response = $controller->recommendations();
$data = $response->getData()->data;
echo "Count: " . count($data) . "\n";
echo "First Hotel: " . ($data[0]->name ?? 'None') . " (Rating: " . ($data[0]->rating ?? 0) . ")\n";

echo "\n--- Testing Nearby ---\n";
// Mock Request
$request = Request::create('/hotels/nearby', 'GET');
$response = $controller->nearby($request);
$data = $response->getData()->data;
echo "Count: " . count($data) . "\n";

echo "\n--- Testing Search (City: Cairo) ---\n";
$request = Request::create('/hotels/search', 'GET', ['city' => 'Cairo']);
$response = $controller->search($request);
$data = $response->getData()->data;
echo "Count: " . count($data) . "\n";
if (count($data) > 0) {
    echo "Found: " . $data[0]->name . " in " . $data[0]->city . "\n";
}

echo "\n--- Testing Search (Guests: 2) ---\n";
$request = Request::create('/hotels/search', 'GET', ['guests' => 2]);
$response = $controller->search($request);
$data = $response->getData()->data;
echo "Count: " . count($data) . "\n";

echo "\n--- Testing Search (Guests: 5 - Should be 0) ---\n";
$request = Request::create('/hotels/search', 'GET', ['guests' => 5]);
$response = $controller->search($request);
$data = $response->getData()->data;
echo "Count: " . count($data) . "\n";
