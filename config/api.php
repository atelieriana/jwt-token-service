<?php
return [
    /*
    |--------------------------------------------------------------------------
    | JWT Private Key
    |--------------------------------------------------------------------------
    |
    | Digunakan untuk menentukan lokasi JWT Private Key yang digunakan saat
    | melakukan enkripsi token
    |
    */
    'jwt_private_key' => env('JWT_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Access Token Duration
    |--------------------------------------------------------------------------
    |
    | Value ini akan digunakan untuk menentukan berapa lama durasi yang diberikan
    | pada token yang baru dilakukan generate, default-nya adalah 5 menit
    |
    */
    'access_token_duration' => env('ACCESS_TOKEN_DURATION', 5),

    /*
    |--------------------------------------------------------------------------
    | Token Duration
    |--------------------------------------------------------------------------
    |
    | Value ini akan digunakan untuk menentukan berapa lama durasi yang diberikan
    | pada token yang baru dilakukan generate, default-nya adalah 5 menit
    |
    */
    'token_refresh_duration' => env('TOKEN_REFRESH_DURATION', 5),

    /*
    |--------------------------------------------------------------------------
    | Refresh Token Duration
    |--------------------------------------------------------------------------
    |
    | Value ini akan digunakan untuk menentukan berapa hari refresh token akan
    | dapat digunakan untuk melakukan generate token yang baru
    |
    */
    'refresh_token_duration' => env('REFRESH_TOKEN_DURATION', 30),
];
