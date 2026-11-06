<?php

return [

    'enabled' => env('RECEIPT_AI_ENABLED', false),

    // Single notes start immediately on shared hosting; use queue with a persistent worker.
    'single_dispatch' => env('RECEIPT_SCAN_DISPATCH', 'sync'),

    'model' => env('RECEIPT_AI_MODEL', 'gpt-4.1-mini'),

    'max_image_side' => (int) env('RECEIPT_AI_MAX_IMAGE_SIDE', 1800),

    'timeout' => (int) env('RECEIPT_AI_TIMEOUT', 120),

    'debug' => (bool) env('RECEIPT_AI_DEBUG', false),

];
