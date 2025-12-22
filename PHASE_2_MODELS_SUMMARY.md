# Phase 2: Models & Relationships - Complete ✅

## Created Models Overview

### 1. **Car** Model

**File:** `app/Models/Car.php`

**Key Features:**

-   ✅ Soft deletes support
-   ✅ JSON casting for `features` array
-   ✅ Enum casting for `availability`
-   ✅ Comprehensive filtering scopes
-   ✅ Tiered pricing calculation
-   ✅ Availability checking for date ranges
-   ✅ Auto rating updates

**Relationships:**

-   `hasMany` → CarImage
-   `hasMany` → CarPricingTier
-   `hasMany` → CarBooking
-   `hasMany` → Review
-   `hasMany` → Favorite

**Scopes:**

```php
->available()
->byLocation($location)
->byBrand($brand)
->bySeats($seats)
->byType($type)
->priceRange($min, $max)
->withFeature($feature)
```

**Methods:**

```php
calculatePrice($hours)           // Calculate price with tiers
isAvailableForPeriod($from, $to) // Check availability
updateRating()                    // Recalculate rating from reviews
```

---

### 2. **CarImage** Model

**File:** `app/Models/CarImage.php`

**Purpose:** Multiple images per car

**Relationships:**

-   `belongsTo` → Car

**Scopes:**

```php
->active()
->byType($type)  // primary, gallery, interior, exterior
->ordered()
```

---

### 3. **CarExtra** Model

**File:** `app/Models/CarExtra.php`

**Purpose:** Rental extras (GPS, child seat, insurance)

**Relationships:**

-   `hasMany` → BookingExtra

**Scopes:**

```php
->available()
->byPricingType($type)  // per_rental, per_day
```

**Methods:**

```php
calculatePrice($days, $hours)  // Calculate based on pricing type
```

---

### 4. **CarPricingTier** Model

**File:** `app/Models/CarPricingTier.php`

**Purpose:** Tiered hourly pricing

**Relationships:**

-   `belongsTo` → Car

**Auto Accessors:**

-   `description` → "Hours 1-30" or "Hours 31+"
-   `price_per_hour_in_egp` → Convert piasters to EGP

---

### 5. **CarBooking** Model

**File:** `app/Models/CarBooking.php`

**Purpose:** Car rental booking details

**Relationships:**

-   `belongsTo` → Booking (main booking)
-   `belongsTo` → Car
-   `hasMany` → BookingExtra

**Accessors:**

-   `base_price_in_egp`
-   `extras_price_in_egp`
-   `total_price_in_egp`
-   `duration_in_days`

**Methods:**

```php
calculateTotalHours()    // Auto-calculate from datetime diff
calculatePricing()       // Calculate all pricing components
```

---

### 6. **BookingExtra** Model

**File:** `app/Models/BookingExtra.php`

**Purpose:** Pivot table for booking extras

**Relationships:**

-   `belongsTo` → CarBooking
-   `belongsTo` → CarExtra

---

### 7. **Review** Model

**File:** `app/Models/Review.php`

**Purpose:** Car reviews and ratings

**Relationships:**

-   `belongsTo` → User
-   `belongsTo` → Car

**Scopes:**

```php
->approved()
->forCar($carId)
->verified()  // From actual bookings
```

---

### 8. **Favorite** Model

**File:** `app/Models/Favorite.php`

**Purpose:** User favorites

**Relationships:**

-   `belongsTo` → User
-   `belongsTo` → Car

**Scopes:**

```php
->forCar($carId)
->byUser($userId)
```

---

## Relationship Diagram

```
User
 ├─ hasMany → Booking
 ├─ hasMany → Review
 └─ hasMany → Favorite

Car
 ├─ hasMany → CarImage
 ├─ hasMany → CarPricingTier
 ├─ hasMany → CarBooking
 ├─ hasMany → Review
 └─ hasMany → Favorite

Booking
 └─ hasOne → CarBooking
     └─ hasMany → BookingExtra
         └─ belongsTo → CarExtra
```

---

## Usage Examples

### Search Cars with Filters

```php
$cars = Car::available()
    ->byLocation('Cairo')
    ->bySeats(4)
    ->byType('sedan')
    ->priceRange(1000, 5000)
    ->withFeature('GPS')
    ->with(['images', 'reviews', 'pricingTiers'])
    ->paginate(20);
```

### Calculate Tiered Pricing

```php
$car = Car::find(1);
$totalPrice = $car->calculatePrice(45); // 45 hours

// With tiers:
// Hours 1-30:  30 × $20 = $600
// Hours 31-40: 10 × $10 = $100
// Hours 41-45:  5 × $5  = $25
// Total: $725
```

### Check Availability

```php
$car = Car::find(1);
$isAvailable = $car->isAvailableForPeriod(
    '2025-12-25 10:00:00',
    '2025-12-27 18:00:00'
);
```

### Create Booking with Extras

```php
$carBooking = CarBooking::create([
    'booking_id' => $booking->id,
    'car_id' => $car->id,
    'pickup_datetime' => '2025-12-25 10:00:00',
    'dropoff_datetime' => '2025-12-27 18:00:00',
    'total_hours' => 56,
    'pickup_location' => 'Cairo Airport',
    'dropoff_location' => 'Cairo Airport',
    'driver_age' => 30,
    'driver_license' => 'ABC123456',
]);

// Add extras
$gps = CarExtra::where('name', 'GPS')->first();
BookingExtra::create([
    'car_booking_id' => $carBooking->id,
    'car_extra_id' => $gps->id,
    'quantity' => 1,
    'price' => $gps->calculatePrice(3), // 3 days
]);

// Calculate total pricing
$carBooking->calculatePricing();
```

### Get Car with All Relations

```php
$car = Car::with([
    'images' => fn($q) => $q->active()->ordered(),
    'pricingTiers',
    'reviews' => fn($q) => $q->approved()->latest()->limit(5),
])->find($id);

// Access data
$primaryImage = $car->image;
$galleryImages = $car->images;
$avgRating = $car->rating;
$reviewCount = $car->reviews->count();
$features = $car->features; // ['AC', 'GPS', 'Bluetooth']
```

---

## Next Steps (Phase 3)

✅ Phase 1 Complete: Database Structure  
✅ Phase 2 Complete: Models & Relationships

🔄 **Phase 3: API Controllers & Routes**

-   CarController (CRUD, search, filters)
-   CarBookingController (booking flow)
-   CarExtraController (list extras)
-   CarComparisonController (compare cars)
-   ReviewController (car reviews)

📋 **Upcoming:**

-   Phase 4: Request Validation
-   Phase 5: Role-Based Access (Middleware & Policies)
-   Phase 6: Seeders & Testing

---

**Status:** ✅ Phase 2 Complete - Ready for Phase 3  
**Models Created:** 8 models  
**Total Lines:** ~600 lines of clean, documented code
