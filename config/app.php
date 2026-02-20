<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nama Aplikasi
    |--------------------------------------------------------------------------
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Environment Aplikasi
    |--------------------------------------------------------------------------
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Mode Debug Aplikasi
    |--------------------------------------------------------------------------
    |
    | Jika aplikasi berada dalam mode debug, pesan error rinci dengan stack
    | trace akan ditampilkan pada setiap kesalahan. Jika dimatikan, halaman
    | error sederhana akan ditampilkan.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL Aplikasi
    |--------------------------------------------------------------------------
    |
    | URL ini digunakan oleh console untuk menghasilkan URL yang benar saat
    | menggunakan Artisan. Tetapkan ini ke root aplikasi sehingga tersedia
    | dalam perintah Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Zona Waktu Aplikasi
    |--------------------------------------------------------------------------
    |
    | Tentukan zona waktu default aplikasi Anda, yang akan digunakan oleh
    | fungsi tanggal/waktu PHP. Secara default di-set ke "UTC".
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Locale Aplikasi
    |--------------------------------------------------------------------------
    |
    | Locale aplikasi menentukan locale default yang akan digunakan oleh
    | metode terjemahan/lokalisasi Laravel. Pilih locale yang sesuai.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Kunci Enkripsi
    |--------------------------------------------------------------------------
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
