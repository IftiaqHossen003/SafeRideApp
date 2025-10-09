<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-Create SOS on Route Anomaly
    |--------------------------------------------------------------------------
    |
    | When enabled, the system will automatically create an SOS alert when
    | a route anomaly (deviation or stoppage) is detected during a trip.
    |
    | Default: false (disabled for safety - manual SOS preferred)
    |
    */
    'auto_create_sos_on_anomaly' => env('SAFERIDE_AUTO_SOS_ON_ANOMALY', false),

    /*
    |--------------------------------------------------------------------------
    | Route Deviation Threshold (in kilometers)
    |--------------------------------------------------------------------------
    |
    | Maximum allowed distance from the straight-line path between origin
    | and destination before triggering a deviation alert.
    |
    */
    'deviation_threshold_km' => env('SAFERIDE_DEVIATION_THRESHOLD_KM', 0.5),

    /*
    |--------------------------------------------------------------------------
    | Stoppage Detection Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for detecting when a trip has stopped moving.
    |
    */
    'stoppage_distance_threshold_m' => env('SAFERIDE_STOPPAGE_DISTANCE_M', 20),
    'stoppage_time_threshold_minutes' => env('SAFERIDE_STOPPAGE_TIME_MINUTES', 10),
];
