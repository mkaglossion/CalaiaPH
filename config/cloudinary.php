<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary API credentials
    |--------------------------------------------------------------------------
    |
    | These values must match your Cloudinary account credentials.
    | You can also use the CLOUDINARY_URL env variable, but individual
    | keys are recommended for Laravel config.
    |
    */

    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    'cloudinary_url' => env('CLOUDINARY_URL'),

    'url' => [
        'secure' => true, // force https
    ],

    'upload' => [
        'folder' => env('CLOUDINARY_UPLOAD_FOLDER', ''), // optional
    ],

];
