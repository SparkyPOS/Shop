<?php

return [
    // Base URL of the external app to sync products to, e.g. https://other-app.test
    'base_url' => env('SYNC_BASE_URL', ''),
    // Shared token with SparkyPOS ProductSyncController
    'token' => env('SYNC_TOKEN', ''),
    // Optional: map Shop unit_type_id => POS unit_group id
    'unit_group_map' => [
        // example: 7 => 1,
    ],
];
