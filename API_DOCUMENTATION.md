# 🚗 Car Rental API Documentation

## Base URL

```
http://localhost:8000/api
```

---

## 📋 Table of Contents

1. [Cars API](#cars-api)
2. [Car Comparison API](#car-comparison-api)
3. [Car Extras API](#car-extras-api)
4. [Booking API](#booking-api)
5. [Reviews API](#reviews-api)
6. [Authentication](#authentication)

---

## 🚙 Cars API

### 1. List & Search Cars

**GET** `/cars`

**Query Parameters:**

-   `location` (string) - Filter by location (e.g., "Cairo")
-   `brand` (string) - Filter by brand (e.g., "Toyota")
-   `type` (string) - Filter by type (sedan, suv, luxury)
-   `seats` (integer) - Minimum seats required
-   `min_price` (integer) - Minimum price in piasters
-   `max_price` (integer) - Maximum price in piasters
-   `feature` (string) - Filter by feature (e.g., "GPS")
-   `available_only` (boolean) - Show only available cars (default: true)
-   `pickup_datetime` (datetime) - Check availability from this time
-   `dropoff_datetime` (datetime) - Check availability until this time
-   `sort_by` (string) - Sort by: price, rating, created_at
-   `sort_order` (string) - asc or desc
-   `per_page` (integer) - Results per page (default: 20)

**Example Request:**

```http
GET /api/cars?location=Cairo&brand=Toyota&seats=4&min_price=1000&max_price=5000&sort_by=price&sort_order=asc
```

**Response:**

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "brand": "Toyota",
        "model": "Camry",
        "year": 2024,
        "type": "sedan",
        "seats": 5,
        "location": "Cairo",
        "price_per_hour": 2000,
        "image": "https://example.com/car.jpg",
        "description": "Comfortable sedan...",
        "features": ["AC", "GPS", "Bluetooth"],
        "availability": "available",
        "rating": 4.5,
        "images": [...]
      }
    ],
    "total": 15,
    "per_page": 20
  }
}
```

---

### 2. Get Car Details

**GET** `/cars/{id}`

**Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "brand": "Toyota",
    "model": "Camry",
    "images": [...],
    "pricing_tiers": [
      {
        "id": 1,
        "from_hours": 1,
        "to_hours": 30,
        "price_per_hour": 2000,
        "description": "Hours 1-30"
      }
    ],
    "reviews": [...]
  }
}
```

---

### 3. Calculate Price

**POST** `/cars/{id}/calculate-price`

**Request Body:**

```json
{
    "hours": 45
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "car_id": 1,
        "hours": 45,
        "total_price": 72500,
        "total_price_egp": 725,
        "price_breakdown": [
            {
                "tier": "Hours 1-30",
                "hours": 30,
                "price_per_hour": 2000,
                "price_per_hour_egp": 20,
                "subtotal": 60000,
                "subtotal_egp": 600
            },
            {
                "tier": "Hours 31-40",
                "hours": 10,
                "price_per_hour": 1000,
                "price_per_hour_egp": 10,
                "subtotal": 10000,
                "subtotal_egp": 100
            },
            {
                "tier": "Hours 41+",
                "hours": 5,
                "price_per_hour": 500,
                "price_per_hour_egp": 5,
                "subtotal": 2500,
                "subtotal_egp": 25
            }
        ]
    }
}
```

---

### 4. Check Availability

**POST** `/cars/{id}/check-availability`

**Request Body:**

```json
{
    "pickup_datetime": "2025-12-25 10:00:00",
    "dropoff_datetime": "2025-12-27 18:00:00"
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "car_id": 1,
        "is_available": true,
        "pickup_datetime": "2025-12-25 10:00:00",
        "dropoff_datetime": "2025-12-27 18:00:00"
    }
}
```

---

### 5. Get Brands

**GET** `/cars/brands`

**Response:**

```json
{
    "success": true,
    "data": ["Toyota", "BMW", "Mercedes", "Honda"]
}
```

---

### 6. Get Locations

**GET** `/cars/locations`

**Response:**

```json
{
    "success": true,
    "data": ["Cairo", "Alexandria", "Giza", "Sharm El Sheikh"]
}
```

---

## 🔄 Car Comparison API

### Compare Cars

**POST** `/cars/compare`

**Request Body:**

```json
{
    "car_ids": [1, 2, 3]
}
```

**Response:**

```json
{
  "success": true,
  "data": {
    "cars": [
      {
        "id": 1,
        "brand": "Toyota",
        "model": "Camry",
        "price_per_hour": 2000,
        "price_per_hour_egp": 20,
        "features": ["AC", "GPS"],
        "has_tiered_pricing": true,
        "pricing_tiers": [...]
      },
      {
        "id": 2,
        "brand": "BMW",
        "model": "X5",
        "price_per_hour": 5000,
        "price_per_hour_egp": 50,
        "features": ["AC", "GPS", "Leather Seats"],
        "has_tiered_pricing": false
      }
    ],
    "comparison_count": 2
  }
}
```

---

## 🎁 Car Extras API

### 1. List Extras

**GET** `/car-extras`

**Query Parameters:**

-   `pricing_type` (string) - Filter by: per_rental, per_day

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "GPS Navigation",
            "pricing_type": "per_rental",
            "price": 5000,
            "price_egp": 50,
            "is_available": true
        },
        {
            "id": 2,
            "name": "Child Seat",
            "pricing_type": "per_day",
            "price": 1000,
            "price_egp": 10,
            "is_available": true
        }
    ]
}
```

---

### 2. Calculate Extra Price

**POST** `/car-extras/{id}/calculate-price`

**Request Body:**

```json
{
    "days": 3
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "extra_id": 2,
        "extra_name": "Child Seat",
        "pricing_type": "per_day",
        "days": 3,
        "unit_price": 1000,
        "unit_price_egp": 10,
        "total_price": 3000,
        "total_price_egp": 30
    }
}
```

---

## 📅 Booking API

### 1. Create Booking

**POST** `/bookings/cars` 🔒 (Requires Authentication)

**Request Body:**

```json
{
    "car_id": 1,
    "pickup_datetime": "2025-12-25 10:00:00",
    "dropoff_datetime": "2025-12-27 18:00:00",
    "pickup_location": "Cairo Airport",
    "dropoff_location": "Cairo Airport",
    "driver_age": 30,
    "driver_license": "ABC123456",
    "extras": [
        {
            "id": 1,
            "quantity": 1
        },
        {
            "id": 2,
            "quantity": 2
        }
    ]
}
```

**Response:**

```json
{
    "success": true,
    "message": "Booking created successfully",
    "data": {
        "booking": {
            "id": 1,
            "user_id": 1,
            "category": "car",
            "total_price": 75000,
            "payment_status": "pending",
            "status": "pending",
            "car_booking": {
                "id": 1,
                "total_hours": 56,
                "base_price": 70000,
                "extras_price": 5000,
                "total_price": 75000,
                "status": "pending"
            }
        },
        "next_step": "payment"
    }
}
```

---

### 2. Get My Bookings

**GET** `/bookings/cars` 🔒 (Requires Authentication)

**Query Parameters:**

-   `status` (string) - Filter by status: pending, confirmed, active, completed, cancelled

**Response:**

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "status": "confirmed",
        "total_price": 75000,
        "car_booking": {
          "pickup_datetime": "2025-12-25 10:00:00",
          "dropoff_datetime": "2025-12-27 18:00:00",
          "car": {...}
        }
      }
    ]
  }
}
```

---

### 3. Get Booking Details

**GET** `/bookings/cars/{id}` 🔒 (Requires Authentication)

---

### 4. Cancel Booking

**POST** `/bookings/cars/{id}/cancel` 🔒 (Requires Authentication)

**Response:**

```json
{
    "success": true,
    "message": "Booking cancelled successfully"
}
```

---

### 5. Confirm Booking (Admin Only)

**POST** `/admin/bookings/cars/{id}/confirm` 🔒🔑 (Requires Admin)

**Response:**

```json
{
    "success": true,
    "message": "Booking confirmed successfully"
}
```

---

## ⭐ Reviews API

### 1. Get Car Reviews

**GET** `/cars/{carId}/reviews`

**Response:**

```json
{
    "success": true,
    "data": {
        "car": {
            "id": 1,
            "brand": "Toyota",
            "model": "Camry",
            "rating": 4.5
        },
        "reviews": {
            "data": [
                {
                    "id": 1,
                    "title": "Great car!",
                    "comment": "Very comfortable and clean",
                    "rating": 5,
                    "user": {
                        "id": 1,
                        "name": "John Doe"
                    },
                    "created_at": "2025-12-20T10:00:00"
                }
            ]
        }
    }
}
```

---

### 2. Create Review

**POST** `/cars/{carId}/reviews` 🔒 (Requires Authentication)

**Request Body:**

```json
{
    "title": "Great experience!",
    "comment": "The car was in excellent condition and very comfortable.",
    "rating": 5,
    "photos": ["url1", "url2"]
}
```

**Response:**

```json
{
  "success": true,
  "message": "Review submitted successfully and pending approval",
  "data": {...}
}
```

---

### 3. Update Review

**PUT** `/reviews/{id}` 🔒 (Requires Authentication)

---

### 4. Delete Review

**DELETE** `/reviews/{id}` 🔒 (Requires Authentication)

---

## 🔐 Authentication

All protected endpoints require authentication using Laravel Sanctum.

**Headers:**

```
Authorization: Bearer {your_token}
Accept: application/json
```

---

## 🎯 Role-Based Access

### Guest (No Authentication)

-   ✅ Browse cars
-   ✅ Search & filter
-   ✅ View car details
-   ✅ Compare cars
-   ✅ Calculate prices
-   ✅ Check availability
-   ✅ View reviews

### User (Authenticated)

-   ✅ All Guest permissions
-   ✅ Create bookings
-   ✅ View my bookings
-   ✅ Cancel bookings
-   ✅ Write reviews
-   ✅ Update/delete own reviews

### Admin

-   ✅ All User permissions
-   ✅ Confirm bookings
-   ✅ Manage cars (TODO)
-   ✅ Manage extras (TODO)
-   ✅ Approve reviews (TODO)

---

## 📊 Response Format

### Success Response

```json
{
  "success": true,
  "data": {...}
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "errors": {...} // Validation errors
}
```

---

## 🚀 Quick Start Examples

### Search Available Cars in Cairo

```bash
curl "http://localhost:8000/api/cars?location=Cairo&available_only=true"
```

### Book a Car

```bash
curl -X POST http://localhost:8000/api/bookings/cars \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "car_id": 1,
    "pickup_datetime": "2025-12-25 10:00:00",
    "dropoff_datetime": "2025-12-27 18:00:00",
    "pickup_location": "Cairo Airport",
    "dropoff_location": "Cairo Airport",
    "driver_age": 30,
    "driver_license": "ABC123456"
  }'
```

---

**API Version:** 1.0  
**Last Updated:** 2025-12-22
