<?php

return [
    'rate_limits' => [
        // requests per minute
        'driver_location' => env('TRACKING_RATE_LIMIT_LOCATION', 60),
        'driver_jobs' => env('TRACKING_RATE_LIMIT_JOBS', 30),
        'driver_status' => env('TRACKING_RATE_LIMIT_STATUS', 30),
    ],
];
