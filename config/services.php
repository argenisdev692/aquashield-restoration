<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
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

    'tavily' => [
        'api_key'      => env('TAVILY_API_KEY'),
        'search_url'   => env('TAVILY_SEARCH_URL', 'https://api.tavily.com/search'),
        'search_depth' => env('TAVILY_SEARCH_DEPTH', 'advanced'),
        'max_results'  => (int) env('TAVILY_MAX_RESULTS', 5),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'api_url' => env('ANTHROPIC_API_URL', 'https://api.anthropic.com/v1/messages'),
        'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
    ],

    'ai' => [
        'default_provider' => env('AI_DEFAULT_PROVIDER', 'anthropic'),
        'default_model'    => env('AI_DEFAULT_MODEL', 'claude-sonnet-4-6'),
    ],

    'replicate' => [
        'api_token'              => env('REPLICATE_API_TOKEN'),
        'base_url'               => env('REPLICATE_BASE_URL', 'https://api.replicate.com/v1'),
        'image_model'            => env('REPLICATE_IMAGE_MODEL', 'black-forest-labs/flux-1.1-pro'),
        'image_aspect_ratio'     => env('REPLICATE_IMAGE_ASPECT_RATIO', '16:9'),
        'image_output_format'    => env('REPLICATE_IMAGE_OUTPUT_FORMAT', 'webp'),
        'image_output_quality'   => (int)  env('REPLICATE_IMAGE_OUTPUT_QUALITY', 85),
        'image_safety_tolerance' => (int)  env('REPLICATE_IMAGE_SAFETY_TOLERANCE', 2),
        'image_prompt_upsampling'=> (bool) env('REPLICATE_IMAGE_PROMPT_UPSAMPLING', true),
        'wait_seconds'           => (int)  env('REPLICATE_WAIT_SECONDS', 25),
        'storage_disk'           => env('REPLICATE_STORAGE_DISK', 'r2'),
        'storage_directory'      => env('REPLICATE_STORAGE_DIRECTORY', 'ai/campaigns'),
        'storage_directory_posts'=> env('REPLICATE_STORAGE_DIRECTORY_POSTS', 'ai/posts'),
    ],

];
