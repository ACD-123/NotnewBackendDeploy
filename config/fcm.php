<?php

return [
    'project_id' => env('FCM_PROJECT_ID'),
    'private_key_id' => env('FCM_PRIVATE_KEY_ID'),
    'private_key' => env('FCM_PRIVATE_KEY'),
    'client_email' => env('FCM_CLIENT_EMAIL'),
    'client_id' => env('FCM_CLIENT_ID'),
    'auth_uri' => env('FCM_AUTH_URI'),
    'token_uri' => env('FCM_TOKEN_URI'),
    'auth_provider_x509_cert_url' => env('FCM_AUTH_PROVIDER_X509_CERT_URL'),
    'client_x509_cert_url' => env('FCM_CLIENT_X509_CERT_URL'),
    'client_secret' => env('FCM_CLIENT_SECRET', ''), 
];
