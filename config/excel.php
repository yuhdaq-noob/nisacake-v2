<?php

use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

return [
    'exports' => [

        /*
        |--------------------------------------------------------------------------
        | Ukuran Chunk
        |--------------------------------------------------------------------------
        |
        | Saat menggunakan FromQuery, query akan diproses per-chunk. Atur ukuran
        | chunk di sini.
        |
        */
        'chunk_size' => 1000,

        /* Pre-calculate formulas selama ekspor */
        'pre_calculate_formulas' => false,

        /*
        |--------------------------------------------------------------------------
        | Perbandingan null ketat
        |--------------------------------------------------------------------------
        |
        | Jika aktif, sel kosong ('') akan dianggap sebagai nilai kosong pada sheet.
        */
        'strict_null_comparison' => false,

        /* Pengaturan CSV untuk ekspor (delimiter, enclosure, line ending) */
        'csv' => [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => false,
            'include_separator_line' => false,
            'excel_compatibility' => false,
            'output_encoding' => '',
            'test_auto_detect' => true,
        ],

        /* Properti worksheet (title, creator, subject, dsb.) */
        'properties' => [
            'creator' => '',
            'lastModifiedBy' => '',
            'title' => '',
            'description' => '',
            'subject' => '',
            'keywords' => '',
            'category' => '',
            'manager' => '',
            'company' => '',
        ],
    ],

    'imports' => [

        /*
        |--------------------------------------------------------------------------
        | Mode Baca Saja (imports)
        |--------------------------------------------------------------------------
        |
        | Jika aktif, impor hanya membaca data tanpa memproses style.
        |
        */
        'read_only' => true,

        /* Abaikan baris kosong saat impor jika diperlukan */
        'ignore_empty' => false,

        /* Formatter baris heading (none|slug|custom) */
        'heading_row' => [
            'formatter' => 'slug',
        ],

        /* Pengaturan CSV untuk impor (delimiter, enclosure, encoding) */
        'csv' => [
            'delimiter' => null,
            'enclosure' => '"',
            'escape_character' => '\\',
            'contiguous' => false,
            'input_encoding' => Csv::GUESS_ENCODING,
        ],

        /*
        |--------------------------------------------------------------------------
        | Worksheet properties
        |--------------------------------------------------------------------------
        |
        | Configure e.g. default title, creator, subject,...
        |
        */
        'properties' => [
            'creator' => '',
            'lastModifiedBy' => '',
            'title' => '',
            'description' => '',
            'subject' => '',
            'keywords' => '',
            'category' => '',
            'manager' => '',
            'company' => '',
        ],

        /* Middleware sel: jalankan middleware saat membaca nilai sel */
        'cells' => [
            'middleware' => [
                // \Maatwebsite\Excel\Middleware\TrimCellValue::class,
                // \Maatwebsite\Excel\Middleware\ConvertEmptyCellValuesToNull::class,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Detektor Ekstensi
    |--------------------------------------------------------------------------
    |
    | Tentukan jenis reader/writer yang dipilih berdasarkan ekstensi file.
    |
    */
    'extension_detector' => [
        'xlsx' => Excel::XLSX,
        'xlsm' => Excel::XLSX,
        'xltx' => Excel::XLSX,
        'xltm' => Excel::XLSX,
        'xls' => Excel::XLS,
        'xlt' => Excel::XLS,
        'ods' => Excel::ODS,
        'ots' => Excel::ODS,
        'slk' => Excel::SLK,
        'xml' => Excel::XML,
        'gnumeric' => Excel::GNUMERIC,
        'htm' => Excel::HTML,
        'html' => Excel::HTML,
        'csv' => Excel::CSV,
        'tsv' => Excel::TSV,

        /*
        |--------------------------------------------------------------------------
        | PDF Extension
        |--------------------------------------------------------------------------
        |
        | Configure here which Pdf driver should be used by default.
        | Available options: Excel::MPDF | Excel::TCPDF | Excel::DOMPDF
        |
        */
        'pdf' => Excel::DOMPDF,
    ],

    /*
    |--------------------------------------------------------------------------
    | Value Binder
    |--------------------------------------------------------------------------
    |
    | Hooks untuk menentukan bagaimana nilai ditulis ke sel (formatter default).
    |
    */
    'value_binder' => [
        'default' => Maatwebsite\Excel\DefaultValueBinder::class,
    ],

    'cache' => [
        /*
        |--------------------------------------------------------------------------
        | Default cell caching driver
        |--------------------------------------------------------------------------
        |
        | By default PhpSpreadsheet keeps all cell values in memory, however when
        | dealing with large files, this might result into memory issues. If you
        | want to mitigate that, you can configure a cell caching driver here.
        | When using the illuminate driver, it will store each value in the
        | cache store. This can slow down the process, because it needs to
        | store each value. You can use the "batch" store if you want to
        | only persist to the store when the memory limit is reached.
        |
        | Drivers: memory|illuminate|batch
        |
        */
        'driver' => 'memory',

        /*
        |--------------------------------------------------------------------------
        | Batch memory caching
        |--------------------------------------------------------------------------
        |
        | When dealing with the "batch" caching driver, it will only
        | persist to the store when the memory limit is reached.
        | Here you can tweak the memory limit to your liking.
        |
        */
        'batch' => [
            'memory_limit' => 60000,
        ],

        /*
        |--------------------------------------------------------------------------
        | Illuminate cache
        |--------------------------------------------------------------------------
        |
        | When using the "illuminate" caching driver, it will automatically use
        | your default cache store. However if you prefer to have the cell
        | cache on a separate store, you can configure the store name here.
        | You can use any store defined in your cache config. When leaving
        | at "null" it will use the default store.
        |
        */
        'illuminate' => [
            'store' => null,
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Time-to-live (TTL)
        |--------------------------------------------------------------------------
        |
        | The TTL of items written to cache. If you want to keep the items cached
        | indefinitely, set this to null.  Otherwise, set a number of seconds,
        | a \DateInterval, or a callable.
        |
        | Allowable types: callable|\DateInterval|int|null
        |
         */
        'default_ttl' => 10800,
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Handler
    |--------------------------------------------------------------------------
    |
    | By default the import is wrapped in a transaction. This is useful
    | for when an import may fail and you want to retry it. With the
    | transactions, the previous import gets rolled-back.
    |
    | You can disable the transaction handler by setting this to null.
    | Or you can choose a custom made transaction handler here.
    |
    | Supported handlers: null|db
    |
    */
    'transactions' => [
        'handler' => 'db',
        'cache' => [
            'driver' => 'memory',
        ],
        'local_path' => storage_path('framework/cache/laravel-excel'),

        /*
        |--------------------------------------------------------------------------
        | Local Temporary Path Permissions
        |--------------------------------------------------------------------------
        |
        | Permissions is an array with the permission flags for the directory (dir)
        | and the create file (file).
        | If omitted the default permissions of the filesystem will be used.
        |
        */
        'local_permissions' => [
            // 'dir'  => 0755,
            // 'file' => 0644,
        ],

        /*
        |--------------------------------------------------------------------------
        | Remote Temporary Disk
        |--------------------------------------------------------------------------
        |
        | When dealing with a multi server setup with queues in which you
        | cannot rely on having a shared local temporary path, you might
        | want to store the temporary file on a shared disk. During the
        | queue executing, we'll retrieve the temporary file from that
        | location instead. When left to null, it will always use
        | the local path. This setting only has effect when using
        | in conjunction with queued imports and exports.
        |
        */
        'remote_disk' => null,
        'remote_prefix' => null,

        /*
        |--------------------------------------------------------------------------
        | Force Resync
        |--------------------------------------------------------------------------
        |
        | When dealing with a multi server setup as above, it's possible
        | for the clean up that occurs after entire queue has been run to only
        | cleanup the server that the last AfterImportJob runs on. The rest of the server
        | would still have the local temporary file stored on it. In this case your
        | local storage limits can be exceeded and future imports won't be processed.
        | To mitigate this you can set this config value to be true, so that after every
        | queued chunk is processed the local temporary file is deleted on the server that
        | processed it.
        |
        */
        'force_resync_remote' => null,
    ],
];
