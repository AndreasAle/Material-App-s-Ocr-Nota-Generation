<?php

return [

    'enabled' => env('RECEIPT_AI_ENABLED', false),

    // Single notes start immediately on shared hosting; use queue with a persistent worker.
    'single_dispatch' => env('RECEIPT_SCAN_DISPATCH', 'sync'),

    'model' => env('RECEIPT_AI_MODEL', 'gpt-4.1-mini'),

    'max_image_side' => (int) env('RECEIPT_AI_MAX_IMAGE_SIDE', 1800),

    // Preserve handwritten digits by default; compression is an explicit trade-off.
    'compress_images' => (bool) env('RECEIPT_AI_COMPRESS_IMAGES', false),

    'timeout' => (int) env('RECEIPT_AI_TIMEOUT', 120),

    'debug' => (bool) env('RECEIPT_AI_DEBUG', false),

];
