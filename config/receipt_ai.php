<?php

return [

    'enabled' => env('RECEIPT_AI_ENABLED', false),

    'model' => env('RECEIPT_AI_MODEL', 'gpt-4.1-mini'),

    'max_image_side' => (int) env('RECEIPT_AI_MAX_IMAGE_SIDE', 1800),

    'timeout' => (int) env('RECEIPT_AI_TIMEOUT', 120),

    'debug' => (bool) env('RECEIPT_AI_DEBUG', false),

];