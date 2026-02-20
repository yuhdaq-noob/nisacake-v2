<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Otentikasi
    |--------------------------------------------------------------------------
    |
    | Pengaturan default guard dan password broker untuk otentikasi.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards Otentikasi
    |--------------------------------------------------------------------------
    |
    | Definisi guard (mis. session, token) yang digunakan aplikasi.
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Pengguna
    |--------------------------------------------------------------------------
    |
    | Sumber data pengguna (Eloquent atau database) yang digunakan untuk
    | memuat data user saat otentikasi.
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    |
    | Konfigurasi tabel token, masa berlaku, dan throttle untuk reset password.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout Konfirmasi Password
    |--------------------------------------------------------------------------
    |
    | Waktu (detik) sebelum permintaan konfirmasi password kedaluwarsa.
    | Default: 3 jam (10800 detik).
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
