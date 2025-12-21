<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the behavior of the OTP (One-Time Password)
    | system used for email verification and password reset.
    |
    */

    // Length of the OTP code (4 digits as per requirements)
    'length' => (int) env('OTP_LENGTH', 4),

    // Number of minutes before an OTP expires (10 minutes as per requirements)
    'expiry_minutes' => (int) env('OTP_EXPIRY_MINUTES', 10),

    // Number of seconds to wait before allowing OTP resend (rate limiting)
    'resend_throttle_seconds' => (int) env('OTP_RESEND_THROTTLE_SECONDS', 60),
];
