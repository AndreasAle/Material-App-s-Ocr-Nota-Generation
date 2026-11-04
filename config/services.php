<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_vision' => [
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),
        'use_real' => env('GOOGLE_VISION_USE_REAL', false),
    ],

'paddle_ocr' => [
    'python_path' => env('PADDLE_OCR_PYTHON_PATH'),
    'script_path' => env('PADDLE_OCR_SCRIPT'),
    'lang' => env('PADDLE_LANG', 'id'),
    'use_gpu' => filter_var(env('PADDLE_USE_GPU', false), FILTER_VALIDATE_BOOL),
    'enable_mkldnn' => filter_var(env('PADDLE_ENABLE_MKLDNN', true), FILTER_VALIDATE_BOOL),
    'det_limit_side_len' => (int) env('PADDLE_DET_LIMIT_SIDE_LEN', 1216),
    'ocr_version' => env('PADDLE_OCR_VERSION', 'PP-OCRv4'),
],

    'tesseract' => [
        'path' => env('TESSERACT_PATH'),
        'lang' => env('TESSERACT_LANG', 'ind+eng'),
        'psm' => (int) env('TESSERACT_PSM', 6),
        'oem' => (int) env('TESSERACT_OEM', 1),
    ],

    'ocr_space' => [
    'api_key' => env('OCR_SPACE_API_KEY'),
    'endpoint' => env('OCR_SPACE_ENDPOINT', 'https://api.ocr.space/parse/image'),
    'language' => env('OCR_SPACE_LANGUAGE', 'eng'),
    'engine' => (int) env('OCR_SPACE_ENGINE', 2),
    'overlay' => filter_var(env('OCR_SPACE_OVERLAY', false), FILTER_VALIDATE_BOOL),
],

    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
    ],

];