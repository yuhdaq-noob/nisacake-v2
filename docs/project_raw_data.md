# Project Raw Data — NISA Cake Management System

Dokumentasi teknis komprehensif untuk proyek NISA Cake Management System. Berisi data, konfigurasi, skema database, formula perhitungan, routes, dan spesifikasi yang berasal langsung dari kode sumber. Dokumen ini adalah bahan mentah (raw data) untuk mengisi konten skripsi.

---

## 1. Informasi Dasar Proyek

| Detail         | Nilai                                            |
| -------------- | ------------------------------------------------ |
| Nama Proyek    | NISA Cake Management System                      |
| Tipe           | Web Application (SPA + Backend API)              |
| Bahasa Dasar   | PHP 8.2+, JavaScript/Vite                        |
| Framework      | Laravel 12.0, Eloquent ORM                       |
| Database       | MySQL (migrasi dengan Laravel)                   |
| Stack Frontend | HTML5, CSS3, JavaScript, Tailwind CSS v4.0, Vite |

| Stack Backend | PHP, Laravel, RESTful API, Sanctum (Auth) |
| Service Eksternal | Telegram Bot API |
| Build Tools | npm, Vite, Composer |
| Lokasi Repo | Sesuai dengan konfigurasi web server (misal: `c:\xampp\htdocs\nisacake` atau `C:\laragon\www\nisacake`) |
| Lisensi | MIT |

---

## 2. Setup & Deployment

### 2.1 Perintah Setup Awal

Dari `composer.json` key `scripts.setup`:

```
bash
composer install
@php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate --force
npm install
npm run build
```

Catatan: `scripts.setup` tidak menjalankan seeder. Jika membutuhkan data awal/dummy, jalankan manual:

```
bash
php artisan db:seed
```

### 2.2 Perintah Development

```
bash
composer run dev
```

Menjalankan secara bersamaan: Laravel server, queue listener, log monitoring (pail), dan Vite dev server.

### 2.3 Perintah Testing

```
bash
composer run test
```

Menjalankan PHPUnit tests (konfigurasi: `phpunit.xml`).

### 2.4 Konfigurasi Service Eksternal

File: `config/services.php`

- **Telegram**:
    - Token dari env var `TELEGRAM_BOT_TOKEN`
    - Chat ID dari env var `TELEGRAM_CHAT_ID`

---

## 3. Struktur Proyek (Folder Tree)

```
nisacake/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── SendOrderReminders.php
│   ├── Enums/
│   │   ├── OrderStatus.php (PRE_ORDER|COMPLETED|CANCELLED)
│   │   └── StockLogType.php (IN|OUT)
│   ├── Exceptions/
│   │   ├── InsufficientStockException.php
│   │   └── MaterialNotFoundException.php
│   ├── Exports/
│   │   └── LaporanExport.php (Excel export)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php (login, register, logout)
│   │   │   ├── OrderController.php (create order, pre-order, execute)
│   │   │   ├── ProductController.php (CRUD produk)
│   │   │   ├── MaterialController.php (CRUD material, update harga)
│   │   │   ├── MaterialPriceLogController.php (riwayat harga)
│   │   │   ├── StockController.php (tambah stok)
│   │   │   ├── InventoryController.php (dashboard gudang)
│   │   │   ├── ReportController.php (laporan omzet, export)
│   │   │   ├── OverheadSettingController.php (konfigurasi overhead)
│   │   │   ├── TelegramController.php (test, health check)
│   │   │   └── LoginController.php (web login)
│   │   ├── Requests/ (Form Request validation)
│   │   │   ├── StoreOrderRequest.php
│   │   │   ├── StoreProductRequest.php
│   │   │   ├── StoreStockRequest.php
│   │   │   ├── UpdateMaterialPriceRequest.php
│   │   │   ├── UpdateProductRequest.php
│   │   │   └── ReduceStockRequest.php
│   │   └── Resources/ (API Resource/JSON)
│   │       ├── MaterialResource.php
│   │       ├── MaterialPriceLogResource.php
│   │       ├── ProductResource.php
│   │       ├── OrderResource.php
│   │       ├── OrderItemResource.php
│   │       ├── StockLogResource.php
│   │       ├── OverheadSettingResource.php
│   │       └── MaterialForProductResource.php
│   ├── Jobs/
│   │   └── SendTelegramReminderJob.php (Queue job dengan retry)
│   ├── Models/
│   │   ├── Material.php
│   │   ├── MaterialPriceLog.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── StockLog.php
│   │   ├── OverheadSetting.php
│   │   ├── NotificationLog.php
│   │   └── User.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── Services/
│   │   ├── OverheadService.php (perhitungan overhead)
│   │   ├── OrderService.php (logika order, HPP)
│   │   ├── MaterialService.php (konversi unit)
│   │   ├── StockService.php (add/reduce stok)
│   │   ├── TelegramService.php (API Telegram wrapper)
│   │   └── Contracts/
│   │       └── TelegramServiceContract.php
│   └── View/
│       └── Components/ (Blade components)
│           ├── Alert.php
│           └── Navbar.php
├── bootstrap/
│   ├── app.php
│   ├── providers.php
│   └── cache/
├── config/
│   ├── app.php, auth.php, cache.php, database.php
│   ├── dompdf.php, excel.php, filesystems.php
│   ├── logging.php, mail.php, queue.php, sanctum.php
│   ├── services.php (Telegram config), session.php
│   └── ... (Laravel default configs)
├── database/
│   ├── factories/
│   │   ├── MaterialFactory.php
│   │   ├── OrderFactory.php
│   │   ├── OrderItemFactory.php
│   │   ├── ProductFactory.php
│   │   └── UserFactory.php
│   ├── migrations/ (15 files)
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_02_02_142805_create_materials_table.php
│   │   ├── 2026_02_02_143801_create_products_table.php
│   │   ├── 2026_02_02_144137_create_product_materials_table.php (BOM pivot)
│   │   ├── 2026_02_02_151609_create_orders_table.php
│   │   ├── 2026_02_02_151655_create_order_items_table.php
│   │   ├── 2026_02_04_062829_create_personal_access_tokens_table.php
│   │   ├── 2026_02_05_061958_create_stock_logs_table.php
│   │   ├── 2026_02_12_000003_create_material_price_logs_table.php
│   │   ├── 2026_02_16_070121_create_overhead_settings_table.php
│   │   ├── 2026_02_18_120000_add_scheduled_at_to_orders_table.php
│   │   ├── 2026_02_18_120100_add_is_notified_to_orders_table.php
│   │   ├── 2026_02_19_000000_create_notification_logs_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   └── 0001_01_01_000002_create_jobs_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php (master seeder)
│   │   ├── OwnerSeeder.php (user default)
│   │   ├── MasterDataSeeder.php (27 materials, 21 products, 178 BOMs)
│   │   ├── OverheadSettingSeeder.php (9 setting keys)
│   │   ├── StockSeeder.php (initial stok)
│   │   └── OrderSeeder.php (200 dummy orders)
│   └── schema/
├── public/
│   ├── index.php (entry point)
│   ├── robots.txt
│   ├── build/ (Vite assets)
│   └── images/
├── resources/
│   ├── css/ (Vite + Tailwind CSS v4.0)
│   │   ├── app.css (Main stylesheet dengan custom design system)
│   │   ├── login-tailwind.css (Login page specific styles)
│   │   └── ... (other styles)
│   ├── js/ (Vite, SPA components)
│   │   ├── api.js, app.js, bootstrap.js
│   │   ├── kasir.js, gudang.js, laporan.js
│   │   ├── login.js, overhead.js
│   │   ├── notifications.js (Toast & Confirm dialog system)
│   │   ├── ui.js (UI components)
│   │   └── utils.js
│   └── views/ (Blade)

├── routes/
│   ├── web.php (web routes + session auth)
│   ├── api.php (API routes + Sanctum auth)
│   └── console.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── TestCase.php
│   ├── Unit/
│   │   ├── ExampleTest.php
│   │   ├── MaterialServiceTest.php
│   │   ├── OverheadServiceTest.php
│   │   ├── StockServiceTest.php
│   │   └── TelegramServiceTest.php
│   └── Feature/ (currently empty)
├── vendor/
├── .env.example
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
└── README.md
```

---

## 4. Spesifikasi Fitur Lengkap

### 4.1 Manajemen Autentikasi

- **User Login**: Form login dengan session (web) atau token Sanctum (API)
- **User Registration**: Endpoint API (tidak dipakai di UI saat ini)
- **User Logout**: Menghapus session/token
- **Default User**: Dibuat via OwnerSeeder dengan kredensial default

### 4.2 Manajemen Produk (Kue)

- **List Produk**: Menampilkan semua produk dengan harga jual, HPP kalkulasi, margin
- **Tambah Produk**: Buat produk baru dengan nama, harga jual, deskripsi
- **Edit Produk**: Update nama, harga, deskripsi, override overhead per produk
- **Hapus Produk**: Soft delete produk
- **BOM (Bill of Materials)**: Relasi produk ke material dengan `quantity_needed` per material

### 4.3 Manajemen Bahan Baku (Inventory)

- **List Bahan**: Menampilkan semua bahan, stok, harga, min stok level
- **Tambah Bahan**: Buat bahan baru dengan nama, unit (gram/ml/pcs), harga per unit kecil
- **Edit Bahan**: Update harga bahan (dengan auto-konversi base unit dan pencatatan history)
- **Update Harga Bahan**: Dengan pencatatan log perubahan ke `material_price_logs` (termasuk old/new price, user, timestamp)
- **Unit Konversi**: Otomatis konversi gram ke kg, ml ke liter, pcs ke Pack sesuai `MaterialService::convertUnitPricing()`

### 4.4 Pembelian & Penerimaan Stok

- **Tambah Stok Manual**: Input qty bahan yang diterima dengan deskripsi (pembelian/koreksi)
- **Pencatatan**: Otomatis membuat entry `stock_logs` tipe `in`
- **Update Stok**: Tambah `current_stock` material dan log timestamp

### 4.5 Pemesanan (Order Management)

- **Order Langsung (Pembayaran Segera)**:
    - Validasi stok material tersedia
    - Kurangi stok langsung
    - Set status `COMPLETED`
    - Hitung HPP real-time dari BOM + overhead
    - Catat `total_price` dan `total_hpp` di order header
- **Pre-Order (Pembayaran Terjadwal)**:
    - Validasi produk ada (tidak perlu nilai stok)
    - Set status `PRE_ORDER`
    - Set field `scheduled_at` (waktu pelunasan)
    - Tidak mengurangi stok
    - Saat pelunasan: jalankan `executePreOrder()` - validasi stok - kurangi stok - ubah status ke `COMPLETED`
- **Cancel Order**: Endpoint API tersedia untuk membatalkan order berstatus `PRE_ORDER` (tanpa rollback stok karena stok belum dikurangi)
- **List & Detail Order**: Lihat semua orders atau detail order + items

### 4.6 Laporan & Analytics

- **Laporan Omzet**: Group by tanggal/periode, hitung total penjualan, total HPP, margin
- **Laporan per Produk**: Hitung qty terjual, omzet per produk, HPP, kontribusi margin
- **Export Excel/PDF**: Generate file laporan dalam format Excel atau PDF (via `Exports/LaporanExport.php` dan DOMPDF)
- **Dashboard Kasir**: Summary hari ini (omzet, transaksi, HPP)
- **Dashboard Gudang**: Summary stok (current stok vs min level, alert bahan kurang)

### 4.7 Pengaturan Overhead (Konfigurasi)

- **List Overhead Settings**: Tampilkan 9 konfigurasi (gas, listrik, tenaga kerja, depreciation, baking time, mixer time, safety margin)
- **Edit Setting**: Update nilai parameter overhead di DB
- **Kalkulasi Overhead**: Otomatis dihitung perubahan overhead saat ada perubahan setting

### 4.8 Notifikasi & Integrasi Eksternal

- **Telegram Integration**: Kirim notifikasi reminder ke Telegram Bot
- **Queue Job**: `SendTelegramReminderJob` dengan retry/backoff exponential (max 5 tries)
- **Notification Logging**: Catat setiap pengiriman (payload, response, status, timestamp) ke `notification_logs`
- **Health Check**: Endpoint test Telegram connection

### 4.9 Manajemen Stok & Validasi

- **Stock Validation**: Saat order, validasi bahwa material ada dan stok cukup (dengan lock pessimistic)
- **Stock Movement Log**: Setiap perubahan stok dicatat dengan type (`in`/`out`), qty, deskripsi, timestamp
- **Min Stock Alert**: Flag jika `current_stock` turun di bawah `min_stock_level`

---

## 5. Entity Relationship Diagram (Tekstual)

```
users
└── 1:N relationship dengan material_price_logs (siapa yang update harga)

materials
├── N:M dengan products (via product_materials pivot)
│   └── product_materials.quantity_needed
├── 1:N dengan stock_logs (movement history)
└── 1:N dengan material_price_logs (price change history)

products
├── N:M dengan materials (via product_materials)
│   └── quantity_needed = qty bahan per unit produk
└── 1:N dengan order_items (dalam order yang dibuat)

orders
├── 1:N dengan order_items (line items dalam order)
└── 1:N dengan notification_logs (reminder Telegram)

order_items
├── Relasi ke orders (order_id FK)
└── Relasi ke products (product_id FK)

stock_logs
└── Relasi ke materials (material_id FK)

material_price_logs
├── Relasi ke materials (material_id FK)
└── Relasi ke users (user_id FK, nullable)

overhead_settings
└── No relations (standalone config)

notification_logs
└── Relasi ke orders (order_id FK, nullable)
```

---

## 6. Routes & API Mapping

### 6.1 Web Routes (Session-Based Auth)

File: `routes/web.php`

| Method | Route                    | Controller                     | Fungsi              | Auth |
| ------ | ------------------------ | ------------------------------ | ------------------- | ---- |
| GET    | `/login`                 | LoginController@index          | Tampil form login   | No   |
| POST   | `/login`                 | LoginController@authenticate   | Proses login        | No   |
| POST   | `/logout`                | LoginController@logout         | Logout              | Yes  |
| GET    | `/`                      | Redirect ke `/kasir`           | Home redirect       | Yes  |
| GET    | `/kasir`                 | View render                    | Dashboard kasir     | Yes  |
| GET    | `/gudang`                | InventoryController@index      | Dashboard gudang    | Yes  |
| GET    | `/admin/telegram/test`   | TelegramController@test        | Test Telegram       | Yes  |
| GET    | `/admin/telegram/health` | TelegramController@health      | Health check        | Yes  |
| GET    | `/laporan`               | View render                    | Halaman laporan     | Yes  |
| POST   | `/materials/reduce`      | MaterialController@reduceStock | Kurangi stok manual | Yes  |
| POST   | `/stocks/add`            | StockController@store          | Tambah stok         | Yes  |
| GET    | `/laporan/export`        | ReportController@export        | Export Excel/PDF    | Yes  |

### 6.2 API Routes (Sanctum Token-Based Auth)

File: `routes/api.php`

| Method        | Endpoint                           | Controller                         | Fungsi                         |
| ------------- | ---------------------------------- | ---------------------------------- | ------------------------------ |
| **Auth**:     |                                    |                                    |                                |
| POST          | `/login`                           | AuthController@login               | API login (token)              |
| POST          | `/register`                        | AuthController@register            | Register (unused in UI)        |
| POST          | `/logout`                          | AuthController@logout              | Logout token                   |
| GET           | `/user`                            | inline                             | Get current user (unused)      |
| **Order**:    |                                    |                                    |                                |
| POST          | `/buat-pesanan`                    | OrderController@store              | Create order direct            |
| POST          | `/jadwal-pesanan`                  | OrderController@preOrder           | Create pre-order               |
| GET           | `/jadwal-pesanan`                  | OrderController@getScheduledOrders | List pre-orders                |
| POST          | `/orders/{order}/execute-preorder` | OrderController@executePreOrder    | Execute pre-order              |
| GET           | `/orders`                          | OrderController@index              | List orders (unused in UI)     |
| GET           | `/orders/{order}`                  | OrderController@show               | Detail order (unused in UI)    |
| PATCH         | `/orders/{order}/complete`         | OrderController@complete           | Mark complete                  |
| PATCH         | `/orders/{order}/cancel`           | OrderController@cancel             | Cancel pre-order               |
| **Product**:  |                                    |                                    |                                |
| GET           | `/products`                        | ProductController@index            | List products                  |
| POST          | `/products`                        | ProductController@store            | Create product                 |
| GET           | `/products/{product}`              | ProductController@show             | Detail (unused in UI)          |
| PATCH         | `/products/{product}`              | ProductController@update           | Update product                 |
| **Material**: |                                    |                                    |                                |
| GET           | `/materials`                       | MaterialController@index           | List materials                 |
| PATCH         | `/materials/{material}/price`      | MaterialController@updatePrice     | Update harga bahan             |
| POST          | `/materials/reduce`                | MaterialController@reduceStock     | Reduce manually (unused in UI) |
| GET           | `/materials/price-history`         | MaterialPriceLogController@index   | List price history             |
| **Stock**:    |                                    |                                    |                                |
| POST          | `/stocks/add`                      | StockController@store              | Add stock                      |
| GET           | `/stocks/history`                  | StockController@index              | Stock logs history             |
| **Report**:   |                                    |                                    |                                |
| GET           | `/reports`                         | ReportController@index             | Get reports data               |
| **Overhead**: |                                    |                                    |                                |
| GET           | `/overhead-settings`               | OverheadSettingController@index    | List overhead config           |

### 6.3 Console Schedule (Cron)

File: `routes/console.php`

- Scheduler menjalankan command `orders:send-reminders` setiap hari pukul `18:10` timezone `Asia/Jakarta`.
- Command mengambil order yang `scheduled_at` besok, `status != completed`, dan `is_notified = false`, lalu dispatch `SendTelegramReminderJob`.

---

## 7. Database Schema (Ringkasan Lengkap)

### 7.1 Tabel: materials

**Penyimpanan master bahan baku dengan harga dan stok**

| Kolom               | Tipe          | Nullable | Default        | Keterangan                                   |
| ------------------- | ------------- | -------- | -------------- | -------------------------------------------- |
| id                  | bigint        | No       | auto_increment | Primary key                                  |
| name                | string        | No       | -              | Nama bahan (misal: Tepung Terigu)            |
| unit                | string        | No       | -              | Unit kecil (gram, ml, butir, pcs)            |
| price_per_unit      | decimal(10,2) | No       | -              | Harga per unit kecil (Rp)                    |
| base_unit           | string        | No       | gram           | Konversi base (kg, liter, Pack, dll)         |
| price_per_base_unit | decimal(10,2) | Yes      | -              | Harga per base unit (Rp) - dihitung otomatis |
| current_stock       | integer       | No       | 0              | Stok saat ini (dalam unit kecil)             |
| min_stock_level     | integer       | No       | 0              | Alert jika stok di bawah ini                 |
| created_at          | timestamp     | -        | -              | -                                            |
| updated_at          | timestamp     | -        | -              | -                                            |
| deleted_at          | timestamp     | Yes      | -              | Soft delete                                  |

**Catatan Penting**: Kolom `base_unit` dan `price_per_base_unit` dihitung secara otomatis saat data dibuat/diupdate melalui `MaterialService::convertUnitPricing()`.

**Relasi**:

- `1:N` dengan `stock_logs` (riwayat perubahan stok)
- `1:N` dengan `material_price_logs` (riwayat harga)
- `N:M` dengan `products` (via `product_materials` pivot)

**Cast di Model**: `price_per_unit`, `price_per_base_unit` - decimal:2; `current_stock`, `min_stock_level` - integer

---

### 7.2 Tabel: products

**Master produk kue dengan harga jual dan konfigurasi overhead**

| Kolom                  | Tipe          | Nullable | Default        | Keterangan                                      |
| ---------------------- | ------------- | -------- | -------------- | ----------------------------------------------- |
| id                     | bigint        | No       | auto_increment | Primary key                                     |
| name                   | string        | No       | -              | Nama produk (misal: Kue Tart Bolu 14)           |
| selling_price          | integer       | No       | -              | Harga jual ke pelanggan (Rp)                    |
| production_cost        | integer       | No       | 0              | Reserve field untuk info                        |
| overhead_cost_per_unit | decimal(10,2) | No       | 0              | Override overhead per produk (0=gunakan global) |
| description            | string(1000)  | Yes      | -              | Deskripsi produk                                |
| created_at             | timestamp     | -        | -              | -                                               |
| updated_at             | timestamp     | -        | -              | -                                               |
| deleted_at             | timestamp     | Yes      | -              | Soft delete                                     |

**Relasi**:

- `N:M` dengan `materials` (via `product_materials` pivot, menyimpan `quantity_needed`)
- `1:N` dengan `order_items` (setiap penjualan produk)

**Cast di Model**: `selling_price`, `production_cost`, `overhead_cost_per_unit` - decimal:2

---

### 7.3 Tabel: product_materials (Pivot / Bill of Materials)

**Menghubungkan produk ke bahan dengan kuantitas yang dibutuhkan**

| Kolom           | Tipe      | Nullable | Keterangan                                     |
| --------------- | --------- | -------- | ---------------------------------------------- |
| id              | bigint    | -        | Primary key                                    |
| product_id      | bigint FK | No       | Ref ke `products.id` (cascade delete)          |
| material_id     | bigint FK | No       | Ref ke `materials.id` (cascade delete)         |
| quantity_needed | integer   | No       | Qty bahan per 1 unit produk (dalam unit bahan) |
| created_at      | timestamp | -        | -                                              |
| updated_at      | timestamp | -        | -                                              |

---

### 7.4 Tabel: orders

**Header pesanan dengan total harga dan HPP**

| Kolom         | Tipe (DB Aktual) | Nullable | Default        | Keterangan                                                |
| ------------- | ---------------- | -------- | -------------- | --------------------------------------------------------- |
| id            | bigint           | -        | auto_increment | Primary key                                               |
| customer_name | string           | No       | -              | Nama pembeli                                              |
| order_date    | date             | No       | -              | Tanggal pemesanan (di-cast ke datetime pada model)        |
| status        | string           | No       | pending        | Status order (pre_order, completed, cancelled)            |
| total_price   | integer          | No       | 0              | Total harga jual bruto (Rp)                               |
| total_hpp     | integer          | No       | 0              | Total harga pokok penjualan (Rp)                          |
| scheduled_at  | datetime         | Yes      | -              | Waktu pelunasan pre-order (nullable untuk order langsung) |
| is_notified   | boolean          | No       | false          | Apakah sudah dikirim notifikasi Telegram                  |
| created_at    | timestamp        | -        | -              | -                                                         |
| updated_at    | timestamp        | -        | -              | -                                                         |

**Cast di Model**: `status` - OrderStatus enum; `order_date`, `scheduled_at` - datetime; `total_price`, `total_hpp` - decimal:2; `is_notified` - boolean

**Relasi**: `1:N` dengan `order_items`, `1:N` dengan `notification_logs`

---

### 7.5 Tabel: order_items

**Line items dalam pesanan (detail per produk)**

| Kolom          | Tipe          | Nullable | Keterangan                                                |
| -------------- | ------------- | -------- | --------------------------------------------------------- |
| id             | bigint        | -        | Primary key                                               |
| order_id       | bigint FK     | No       | Ref ke `orders.id` (cascade delete)                       |
| product_id     | bigint FK     | No       | Ref ke `products.id`                                      |
| quantity       | integer       | No       | Qty produk yang dibeli                                    |
| price_per_unit | decimal(10,2) | No       | Harga jual per unit saat order dibuat (Rp)                |
| hpp_per_unit   | decimal(10,2) | No       | HPP per unit saat order dibuat (Rp) - historical snapshot |
| created_at     | timestamp     | -        | -                                                         |
| updated_at     | timestamp     | -        | -                                                         |

**Cast di Model**: `quantity` - integer; `price_per_unit`, `hpp_per_unit` - decimal:2

---

### 7.6 Tabel: stock_logs

**Riwayat perubahan stok bahan**

| Kolom       | Tipe      | Nullable | Keterangan                                           |
| ----------- | --------- | -------- | ---------------------------------------------------- |
| id          | bigint    | -        | Primary key                                          |
| material_id | bigint FK | No       | Ref ke `materials.id` (cascade delete)               |
| type        | enum      | No       | Tipe DB: 'in', 'out', 'adjustment'                   |
| amount      | integer   | No       | Jumlah perubahan (dalam unit material)               |
| description | string    | Yes      | Keterangan (misal: "Belanja", "Produksi Order #123") |
| created_at  | timestamp | -        | -                                                    |
| updated_at  | timestamp | -        | -                                                    |

**Cast di Model**: `type` - StockLogType enum; `amount` - integer

**Catatan Implementasi**:

- Enum aplikasi `StockLogType` saat ini hanya mendefinisikan `in` dan `out`.
- Nilai `adjustment` tersedia di skema DB, tetapi belum dipakai oleh service/controller saat ini.

---

### 7.7 Tabel: material_price_logs

**Riwayat perubahan harga bahan baku**

| Kolom                   | Tipe          | Nullable | Keterangan                                |
| ----------------------- | ------------- | -------- | ----------------------------------------- |
| id                      | bigint        | -        | Primary key                               |
| material_id             | bigint FK     | No       | Ref ke `materials.id` (cascade delete)    |
| user_id                 | bigint FK     | Yes      | Ref ke `users.id` (siapa yang ubah harga) |
| old_price_per_unit      | decimal(10,2) | Yes      | Harga lama per unit kecil (Rp)            |
| new_price_per_unit      | decimal(10,2) | Yes      | Harga baru per unit kecil (Rp)            |
| old_price_per_base_unit | decimal(10,2) | Yes      | Harga lama per base unit (Rp)             |
| new_price_per_base_unit | decimal(10,2) | Yes      | Harga baru per base unit (Rp)             |
| base_unit               | string        | Yes      | Base unit saat perubahan (kg/liter/Pack)  |
| created_at              | timestamp     | -        | -                                         |
| updated_at              | timestamp     | -        | -                                         |

---

### 7.8 Tabel: overhead_settings

**Konfigurasi parameter perhitungan overhead (dapat diubah di runtime)**

| Kolom      | Tipe          | Keterangan                                                 |
| ---------- | ------------- | ---------------------------------------------------------- |
| id         | bigint        | Primary key                                                |
| key        | string unique | Identifier (gas_price_per_tube, gas_capacity_minutes, dll) |
| name       | string        | Label readable (Harga Gas per Tabung, dll)                 |
| value      | decimal(15,2) | Nilai parameter (Rp/unit atau skalar)                      |
| unit       | string        | Satuan (Rp/tabung, menit, Rp/kWh, %, dll)                  |
| created_at | timestamp     | -                                                          |
| updated_at | timestamp     | -                                                          |

**Default Keys (dari OverheadSettingSeeder)**:

| Key                      | Nilai Default | Satuan    | Keterangan                 |
| ------------------------ | ------------- | --------- | -------------------------- |
| gas_price_per_tube       | 22000         | Rp/tabung | Harga Gas per Tabung       |
| gas_capacity_minutes     | 620           | menit     | Kapasitas Gas (menit)      |
| electricity_rate_kwh     | 605           | Rp/kWh    | Tarif Listrik per kWh      |
| mixer_power_kw           | 0.16          | kW        | Daya Mixer (kW)            |
| labor_rate_per_hour      | 10000         | Rp/jam    | Tarif Tenaga Kerja per Jam |
| depreciation_per_batch   | 800           | Rp/batch  | Biaya Penyusutan per Batch |
| baking_minutes_per_batch | 50            | menit     | Durasi Panggang per Batch  |
| mixer_minutes_per_batch  | 12            | menit     | Durasi Mixer per Batch     |
| safety_margin_percent    | 5             | %         | Safety Margin (%)          |

---

### 7.9 Tabel: notification_logs

**Log pengiriman notifikasi (khusunya Telegram reminder)**

| Kolom         | Tipe      | Nullable | Keterangan                                |
| ------------- | --------- | -------- | ----------------------------------------- |
| id            | bigint    | -        | Primary key                               |
| channel       | string    | No       | Channel (misal: 'telegram')               |
| order_id      | bigint FK | Yes      | Ref ke `orders.id` (set null jika delete) |
| payload       | json      | Yes      | Pesan yang dikirim (isi, format)          |
| response      | json      | Yes      | Respons dari API eksternal                |
| attempts      | integer   | No       | Jumlah percobaan pengiriman               |
| status        | string    | No       | Status: queued, sent, failed, processing  |
| error_message | text      | Yes      | Pesan error jika gagal                    |
| sent_at       | timestamp | Yes      | Timestamp sukses terkirim                 |
| created_at    | timestamp | -        | -                                         |
| updated_at    | timestamp | -        | -                                         |

---

### 7.10 Tabel: users

Standard Laravel users table dengan fields: id, name, email, password, email_verified_at, remember_token, created_at, updated_at.

---

## 8. Enumerasi (Enum)

### 8.1 OrderStatus Enum (`app/Enums/OrderStatus.php`)

| Value     | Case      | Label      | Keterangan                                                   |
| --------- | --------- | ---------- | ------------------------------------------------------------ |
| pre_order | PRE_ORDER | Pre-Order  | Pesanan terjadwal, belum kurangi stok, pembayaran belakangan |
| completed | COMPLETED | Selesai    | Pesanan selesai (stok sudah dikurangi)                       |
| cancelled | CANCELLED | Dibatalkan | Pesanan dibatalkan (saat ini digunakan untuk pre-order)      |

**Method**: `label()` mengembalikan teks readable untuk ditampilkan di UI.

---

### 8.2 StockLogType Enum (`app/Enums/StockLogType.php`)

| Value | Case | Label  | Keterangan                        |
| ----- | ---- | ------ | --------------------------------- |
| in    | IN   | Masuk  | Stok masuk (pembelian/penerimaan) |
| out   | OUT  | Keluar | Stok keluar (produksi/order)      |

---

## 9. Exception Classes

### 9.1 InsufficientStockException (`app/Exceptions/InsufficientStockException.php`)

**Dilempar ketika**: Stok material tidak mencukupi saat membuat order langsung

**Parameter konstruktor**:

- `$material_name`: Nama bahan yang kurang stok
- `$current_stock`: Stok saat ini
- `$needed`: Qty yang dibutuhkan

---

### 9.2 MaterialNotFoundException (`app/Exceptions/MaterialNotFoundException.php`)

**Dilempar ketika**: Material atau produk tidak ditemukan saat validasi

---

## 10. Master Data dari Seeder

### 10.1 Material Master Data (27 items)

Dari `database/seeders/MasterDataSeeder.php`:

| ID  | Nama             | Unit  | Harga/Unit (Rp) | Min Stok | Base Unit | Harga/Base (Rp) | Kategori         |
| --- | ---------------- | ----- | --------------- | -------- | --------- | --------------- | ---------------- |
| 1   | Tepung Terigu    | gram  | 12.0            | 2000     | kg        | 12000           | Bahan Utama      |
| 2   | Telur Ayam       | butir | 1800.0          | 60       | Pack      | 180000          | Bahan Utama      |
| 3   | SP (Emulsifier)  | gram  | 80.0            | 200      | kg        | 80000           | Bahan Pengembang |
| 4   | Minyak Goreng    | gram  | 17.0            | 1000     | kg        | 17000           | Oils             |
| 5   | Gula Pasir       | gram  | 17.0            | 2000     | kg        | 17000           | Sugar            |
| 6   | Pewarna Pandan   | gram  | 110.0           | 100      | kg        | 110000          | Pewarna          |
| 7   | Coklat Bubuk     | gram  | 150.0           | 500      | kg        | 150000          | Coklat           |
| 8   | Coklat Batang    | gram  | 60.0            | 1200     | kg        | 60000           | Coklat           |
| 9   | Butter Cream     | gram  | 28.0            | 1000     | kg        | 28000           | Topping          |
| 10  | Mentega/Margarin | gram  | 30.0            | 500      | kg        | 30000           | Lemak            |
| 11  | Tepung Maizena   | gram  | 21.0            | 400      | kg        | 21000           | Bahan            |
| 12  | Susu Bubuk       | gram  | 32.0            | 300      | kg        | 32000           | Susu             |
| 13  | Selai            | gram  | 24.0            | 400      | kg        | 24000           | Topping          |
| 14  | Pisang           | gram  | 10.0            | 1500     | kg        | 10000           | Buah             |
| 15  | Santan Cair      | gram  | 83.0            | 500      | liter     | 83000           | Cair             |
| 16  | Garam            | gram  | 7.0             | 200      | kg        | 7000            | Bumbu            |
| 17  | Air Lemon        | gram  | 25.0            | 100      | liter     | 25000           | Asam             |
| 18  | Parutan Kelapa   | gram  | 18.0            | 100      | kg        | 18000           | Kelapa           |
| 19  | Mika Kue 14      | pcs   | 3000.0          | 5        | Pack      | 300000          | Kemasan          |
| 20  | Mika Kue 16      | pcs   | 3200.0          | 5        | Pack      | 320000          | Kemasan          |
| 21  | Mika Kue 18      | pcs   | 3500.0          | 5        | Pack      | 350000          | Kemasan          |
| 22  | Mika Kue 20      | pcs   | 5000.0          | 5        | Pack      | 500000          | Kemasan          |
| 23  | Mika Kue 22      | pcs   | 6500.0          | 5        | Pack      | 650000          | Kemasan          |
| 24  | Mika Kue 24      | pcs   | 8000.0          | 5        | Pack      | 800000          | Kemasan          |
| 25  | Kardus Kue 16    | pcs   | 15000.0         | 5        | Pack      | 1500000         | Kardus           |
| 26  | Kardus Kue 24    | pcs   | 2500.0          | 5        | Pack      | 250000          | Kardus           |
| 27  | Plastik          | pcs   | 18.0            | 50       | Pack      | 1800            | Plastik          |

---

### 10.2 Product Master Data (21 items)

| ID  | Nama                       | Harga Jual (Rp) | Kategori |
| --- | -------------------------- | --------------- | -------- |
| 1   | Kue Tart Bolu 14           | 50000           | Tart     |
| 2   | Kue Tart Bolu 16           | 60000           | Tart     |
| 3   | Kue Tart Bolu 18           | 70000           | Tart     |
| 4   | Kue Tart Bolu 20           | 80000           | Tart     |
| 5   | Kue Tart Bolu 22           | 90000           | Tart     |
| 6   | Kue Tart Bolu 24           | 100000          | Tart     |
| 7   | Kue Tart Brownies 14       | 60000           | Brownies |
| 8   | Kue Tart Brownies 16       | 75000           | Brownies |
| 9   | Kue Tart Brownies 18       | 100000          | Brownies |
| 10  | Kue Tart Brownies 20       | 120000          | Brownies |
| 11  | Kue Tart Brownies 22       | 135000          | Brownies |
| 12  | Kue Tart Brownies 24       | 155000          | Brownies |
| 13  | Bolu Gulung (12 biji)      | 35000           | Bolu     |
| 14  | Bolu Pisang 18             | 55000           | Bolu     |
| 15  | Brownis Kukus 24 (30 biji) | 105000          | Brownies |
| 16  | Brownis Panggang (15 biji) | 75000           | Brownies |
| 17  | Bolen pisang (40 biji)     | 100000          | Bolen    |
| 18  | Bolu jadul (24 biji)       | 65000           | Bolu     |
| 19  | Shifon cake (16 biji)      | 56000           | Chiffon  |
| 20  | Bolu Kukus biasa (24 biji) | 60000           | Bolu     |
| 21  | Putu ayu (25 biji)         | 75000           | Putu     |

**Catatan**: Total 178 entries `product_materials` di seeder mendefinisikan BOM lengkap setiap produk.

---

## 11. Perhitungan & Formula

### 11.1 Perhitungan Overhead Per Unit

**Sumber**: `app/Services/OverheadService.php` - method `calculateOverheadPerUnit()`

Overhead adalah biaya produksi tidak langsung (gas, listrik, tenaga kerja, penyusutan) per unit/batch.

**Asumsi**: 1 unit produk = 1 batch pemanggang

**Rumus**:

```
Biaya Gas per Batch = (Gas Price per Tube / Gas Capacity Minutes) x Baking Minutes per Batch

Biaya Listrik per Batch (Mixer) = (Mixer Power kW x Electricity Rate kWh / 60) x Mixer Minutes per Batch

Biaya Tenaga Kerja per Batch = (Labor Rate per Hour / 60) x Baking Minutes per Batch

Base Cost per Batch = Biaya Gas + Biaya Listrik + Biaya Tenaga Kerja + Depreciation per Batch

Overhead per Unit (Final) = Base Cost per Batch x (1 + Safety Margin Percent / 100)
```

**Perhitungan Numerik (menggunakan default seeder)**:

```
Input Parameters:
  gas_price_per_tube = 22000 (Rp/tabung)
  gas_capacity_minutes = 620 (menit/tabung)
  electricity_rate_kwh = 605 (Rp/kWh)
  mixer_power_kw = 0.16 (kW)
  labor_rate_per_hour = 10000 (Rp/jam)
  depreciation_per_batch = 800 (Rp/batch)
  baking_minutes_per_batch = 50 (menit)
  mixer_minutes_per_batch = 12 (menit)
  safety_margin_percent = 5 (%)

Step 1 - Biaya Gas per Menit:
  gasPerMinute = 22000 / 620 = 35.48 Rp/menit

Step 2 - Biaya Gas per Batch:
  gasCostPerBatch = 35.48 x 50 = 1774.19 Rp

Step 3 - Biaya Listrik per Menit:
  electricityPerMinute = (0.16 x 605) / 60 = 1.613 Rp/menit

Step 4 - Biaya Listrik per Batch:
  electricityCostPerBatch = 1.613 x 12 = 19.36 Rp

Step 5 - Biaya Tenaga Kerja per Menit:
  laborPerMinute = 10000 / 60 = 166.67 Rp/menit

Step 6 - Biaya Tenaga Kerja per Batch:
  laborCostPerBatch = 166.67 x 50 = 8333.33 Rp

Step 7 - Biaya Dasar per Batch:
  basePerBatch = 1774.19 + 19.36 + 8333.33 + 800 = 10926.88 Rp

Step 8 - Overhead Final (dengan Safety Margin 5%):
  overheadPerUnit = 10926.88 x 1.05 = 11473.23 Rp/unit
```

**Kesimpulan**: Setiap 1 unit produk menambah biaya overhead minimal 11473.23 Rp (sebelum faktor lain).

---

### 11.2 Perhitungan HPP (Harga Pokok Penjualan)

**Sumber**: `app/Services/OrderService.php` - method `calculateRealTimeHPP()`

HPP (Harga Pokok Penjualan) dihitung saat order dibuat, menggunakan harga material saat itu dan konfigurasi overhead.

---

#### 11.2.1 Formula HPP Lengkap (Menggunakan Variabel Database)

Berikut adalah formula HPP lengkap dengan semua komponen menggunakan nama variabel yang sesuai dengan database:

```
HPP_total = Sum dari (HPP_per_item x quantity) untuk setiap item

Dimana untuk setiap item:

HPP_per_item = (Material_Cost_per_Unit + Overhead_Cost_per_Unit) x quantity

Material_Cost_per_Unit = Sum dari (price_per_unit_material x quantity_needed) untuk setiap material dalam BOM

Overhead_Cost_per_Unit =
    JIKA product.overhead_cost_per_unit > 0:
        MAKA product.overhead_cost_per_unit
    JIKA TIDAK:
        MAKA OverheadService.calculateOverheadPerUnit()

Jadi:

HPP_total = Sum[ ( Sum[ price_per_unit(material_i) x quantity_needed(material_i) ]
                    + Overhead_Cost_per_Unit
                  ) x quantity(item_j) ]

Untuk seluruh order:

Order.total_hpp = Sum dari HPP_per_item untuk semua order_items
Order.total_price = Sum dari (selling_price(product) x quantity) untuk semua order_items
```

---

#### 11.2.2 Formula HPP Lengkap (Menggunakan Variabel Matematika)

```
Notasi:
- H = Total HPP (Harga Pokok Penjualan total untuk seluruh pesanan)
- n  = Jumlah item dalam pesanan
- mi = Jumlah material dalam BOM produk ke-i
- Qi = Kuantitas produk ke-i yang dipesan
- Pij = Harga per unit material ke-j dari produk ke-i
- Qij = Jumlah kebutuhan material ke-j untuk 1 unit produk ke-i (dari BOM)
- Oi = Overhead cost per unit produk ke-i
- Sj = Harga jual produk ke-i

Formula per item (untuk item ke-i):
  Biaya Material per Unit (BMi) = Sum(j=1 to mi) [ Pij x Qij ]
  HPP per Unit (Hi) = BMi + Oi
  Total HPP untuk item i (Hi_total) = Hi x Qi

Formula Total HPP:
  H = Sum(i=1 to n) [ Hi_total ]

Formula Total Pendapatan:
  R = Sum(i=1 to n) [ Sj x Qi ]

Formula Margin:
  Margin = R - H
  Margin % = ((R - H) / R) x 100%
```

---

#### 11.2.3 Penjelasan Komponen / Variabel

| Variabel Database                            | Variabel Matematika | Tipe          | Keterangan                                                          |
| -------------------------------------------- | ------------------- | ------------- | ------------------------------------------------------------------- |
| `order_items.quantity`                       | Qi                  | Integer       | Kuantitas produk yang dipesan dalam 1 line item                     |
| `order_items.price_per_unit`                 | Sj                  | Decimal       | Harga jual per unit produk (saat order dibuat)                      |
| `order_items.hpp_per_unit`                   | Ci                  | Decimal       | HPP per unit produk (historical snapshot)                           |
| `product_materials.quantity_needed`          | Qij                 | Integer/Float | Jumlah material ke-j yang dibutuhkan untuk 1 unit produk (dari BOM) |
| `materials.price_per_unit`                   | Pij                 | Decimal       | Harga per unit material ke-j                                        |
| `materials.price_per_base_unit`              | -                   | Decimal       | Harga per base unit (fallback jika price_per_unit tidak ada)        |
| `products.overhead_cost_per_unit`            | Oi                  | Decimal       | Override overhead per produk (jika 0, gunakan overhead global)      |
| `orders.total_price`                         | R                   | Decimal       | Total pendapatan/penjualan bruto                                    |
| `orders.total_hpp`                           | C                   | Decimal       | Total Harga Pokok Penjualan                                         |
| `OverheadService.calculateOverheadPerUnit()` | Og                  | Decimal       | Overhead cost per unit global (dihitung dari konfigurasi)           |

---

#### 11.2.4 Komponen Overhead (Oi atau Og)

Overhead cost per unit dihitung dari formula berikut:

```
Og = (Biaya_Gas + Biaya_Listrik + Biaya_Tenaga_Kerja + Depreciation) x (1 + Safety_Margin/100)

Dimana:
- Biaya_Gas = (gas_price_per_tube / gas_capacity_minutes) x baking_minutes_per_batch
- Biaya_Listrik = (mixer_power_kw x electricity_rate_kwh / 60) x mixer_minutes_per_batch
- Biaya_Tenaga_Kerja = (labor_rate_per_hour / 60) x baking_minutes_per_batch
- Depreciation = depreciation_per_batch
- Safety_Margin = safety_margin_percent
```

(Lihat section 11.1 untuk detail lengkap perhitungan overhead)

---

#### 11.2.5 Algoritma Perhitungan (Pseudocode)

```
FUNCTION calculateRealTimeHPP(orderItems):
    total_hpp = 0

    FOR EACH orderItem IN orderItems:
        product = orderItem.product
        quantity = orderItem.quantity

        // Hitung biaya material dari BOM
        material_cost = 0
        bom_items = product.materials // dari product_materials pivot

        FOR EACH bom IN bom_items:
            material = bom.material
            qty_needed = bom.quantity_needed

            // Gunakan price_per_unit, fallback ke price_per_base_unit
            IF material.price_per_unit IS NOT NULL:
                price = material.price_per_unit
            ELSE:
                price = material.price_per_base_unit

            material_cost += price * qty_needed

        // Hitung overhead per unit
        IF product.overhead_cost_per_unit > 0:
            overhead_per_unit = product.overhead_cost_per_unit
        ELSE:
            overhead_per_unit = OverheadService.calculateOverheadPerUnit()

        // HPP per unit produk
        hpp_per_unit = material_cost + overhead_per_unit

        // Total HPP untuk item ini
        item_hpp = hpp_per_unit * quantity
        total_hpp += item_hpp

    RETURN total_hpp
```

---

#### 11.2.6 Contoh Perhitungan Lengkap

**Contoh: Produk ID=1 (Kue Tart Bolu 14) - 2 unit**

```
Data Produk:
  - selling_price = 50000 Rp/unit
  - overhead_cost_per_unit = 0 (gunakan overhead global)

Data BOM (dari product_materials):
  Material #1 (Tepung Terigu): qty_needed = 120 gram, price = 12.0 Rp/gram
  Material #2 (Telur Ayam): qty_needed = 2 butir, price = 1800.0 Rp/butir
  Material #3 (SP): qty_needed = 2 gram, price = 80.0 Rp/gram
  Material #4 (Minyak Goreng): qty_needed = 60 gram, price = 17.0 Rp/gram
  Material #5 (Gula Pasir): qty_needed = 90 gram, price = 17.0 Rp/gram
  Material #6 (Pewarna Pandan): qty_needed = 2 gram, price = 110.0 Rp/gram
  Material #9 (Butter Cream): qty_needed = 250 gram, price = 28.0 Rp/gram
  Material #19 (Mika Kue 14): qty_needed = 1 pcs, price = 3000.0 Rp/pcs

Overhead Global (dari section 11.1):
  Og = 11473.23 Rp/unit

Perhitungan:
  Step 1 - Biaya Material per Unit:
    BMi = (120 x 12) + (2 x 1800) + (2 x 80) + (60 x 17) + (90 x 17) + (2 x 110) + (250 x 28) + (1 x 3000)
        = 1440 + 3600 + 160 + 1020 + 1530 + 220 + 7000 + 3000
        = 17970 Rp

  Step 2 - Overhead per Unit:
    Oi = Og = 11473.23 Rp (karena product.overhead_cost_per_unit = 0)

  Step 3 - HPP per Unit:
    Ci = BMi + Oi = 17970 + 11473.23 = 29443.23 Rp

  Step 4 - Total HPP untuk 2 unit:
    C = Ci x Qi = 29443.23 x 2 = 58886.46 Rp

  Step 5 - Total Pendapatan:
    R = Sj x Qi = 50000 x 2 = 100000 Rp

  Step 6 - Margin:
    Margin = R - C = 100000 - 58886.46 = 41113.54 Rp
    Margin % = (41113.54 / 100000) x 100 = 41.11%

Hasil:
  Order.total_hpp = 58886.46 Rp
  Order.total_price = 100000 Rp
  Margin = 41113.54 Rp (41.11%)
```

---

### 11.3 Konversi Unit Bahan

**Sumber**: `app/Services/MaterialService.php` - method `convertUnitPricing()`

Konversi otomatis dari unit kecil ke base unit untuk normalisasi harga.

| Unit Kecil      | Base Unit     | Konversi | Contoh                        |
| --------------- | ------------- | -------- | ----------------------------- |
| gram (g)        | kilogram (kg) | x 1000   | 12 Rp/gram -> 12000 Rp/kg     |
| milliliter (ml) | liter (L)     | x 1000   | 25 Rp/ml -> 25000 Rp/L        |
| pcs             | Pack          | x 100    | 1800 Rp/pcs -> 180000 Rp/Pack |
| (lainnya)       | (sama)        | x 1      | Keep as-is                    |

---

## 12. Service Classes & Business Logic

### 12.1 OrderService (`app/Services/OrderService.php`)

#### createOrder($data)

Membuat order langsung dengan kurangkan stok otomatis (status = COMPLETED).

**Langkah**:

1. Hitung total kebutuhan material dari `$data['items']`
2. Lock material di DB (pessimistic lock)
3. Validasi stok cukup - InsufficientStockException jika kurang
4. Hitung order items (price, HPP) via `calculateOrderItemsData()`
5. Buat order + order_items
6. Kurangi stok (deduct) dan catat di stock_logs

#### createPreOrder($data)

Membuat pre-order tanpa kurangi stok. Status = PRE_ORDER, set `scheduled_at`.

#### executePreOrder(Order $preOrder)

Konversi pre-order dari PRE_ORDER ke COMPLETED:

1. Recalculate kebutuhan material dari order items
2. Validasi stok + lock
3. Kurangi stok
4. Update status -> COMPLETED, set order_date = now()

#### Catatan cancel order

- Alur cancel ada di `OrderController::cancel()` (bukan di `OrderService`).
- Saat ini cancel hanya diizinkan untuk status `PRE_ORDER`.
- Tidak ada rollback stok karena stok memang belum terpotong pada fase pre-order.

#### calculateRealTimeHPP(Product $product, int $quantity)

Hitung HPP real-time sesuai BOM dan harga material saat ini.

#### calculateTotalNeeds(array $items)

Kalkulasi total kebutuhan material dari array items (aggregate qty per material).

---

### 12.2 OverheadService (`app/Services/OverheadService.php`)

#### calculateOverheadPerUnit()

Kalkulasi overhead global berdasarkan `overhead_settings`. Lihat rumus di bagian 11.1.

**Usage**: Dipanggil oleh `OrderService::calculateRealTimeHPP()` jika product tidak punya override `overhead_cost_per_unit`.

---

### 12.3 StockService (`app/Services/StockService.php`)

#### addStock($data)

Tambah stok dengan catat log. Input: `material_id`, `amount`, `description`

#### reduceStock($data)

Kurangi stok dengan validasi dan lock. Input: `material_id`, `amount`, `description`. Exception: InsufficientStockException jika stok kurang

---

### 12.4 TelegramService (`app/Services/TelegramService.php`)

#### sendMessage(string $message)

Kirim pesan ke Telegram Bot API (endpoint `/sendMessage`). Parse Mode: HTML. Timeout: 10 detik. Return: Boolean success/fail

---

### 12.5 MaterialService (`app/Services/MaterialService.php`)

#### convertUnitPricing(string $unit, float $pricePerUnit)

Konversi harga unit kecil -> base unit. Return array `['base_unit' => ..., 'price_per_base_unit' => ...]`

---

## 13. Job Queue, Command, & Scheduling

### 13.1 Console Command (`app/Console/Commands/SendOrderReminders.php`)

Command signature:

```
bash
php artisan orders:send-reminders
```

Opsi tambahan:

```
bash
php artisan orders:send-reminders --dry-run
```

Fungsi utama:

- Mengambil order `scheduled_at` besok
- Filter: `status != completed` dan `is_notified = false`
- Menyusun pesan Telegram per order
- Dispatch `SendTelegramReminderJob` ke queue

`routes/console.php` menjadwalkan command ini harian pukul `18:10` (`Asia/Jakarta`).

### 13.2 SendTelegramReminderJob (`app/Jobs/SendTelegramReminderJob.php`)

**Trigger**: Saat command `orders:send-reminders` melakukan dispatch ke queue.

**Parameter**: `$orderId`, `$payload` (isi pesan)

**Retry Policy**:

- Max tries: 5
- Backoff (exponential): 60s, 300s, 900s, 3600s

**Process**:

1. Buat entry `notification_logs` dengan status=processing
2. Kirim pesan via `TelegramService::sendMessage()`
3. Update log status -> sent (success) atau failed (error)
4. Update order.is_notified = true jika sukses
5. Jika gagal semua, log final failure entry

---

## 14. Data Validation & Error Handling

### 14.1 Exception Hierarchy

```
Exception
├── InsufficientStockException
│   └── Thrown: OrderService::validateStockAvailability()
│   └── Message: "{material} insufficient stock. Current: {current}, Needed: {needed}"
│
└── MaterialNotFoundException
    └── Thrown: OrderService::validateProductsExist()
    └── Message: "Material/Product {id} not found"
```

### 14.2 Validasi di Controller/Request Layer

Setiap endpoint API menggunakan `FormRequest` validation untuk:

- Type casting (integer, string, datetime)
- Required/optional field check
- Custom rules (misal: product exists, material valid, qty > 0)

### 14.3 PHPUnit Tests

**Lokasi**: `tests/Unit/`, `tests/Feature/`

Status saat ini:

- `tests/Unit/ExampleTest.php`
- `tests/Unit/MaterialServiceTest.php`
- `tests/Unit/OverheadServiceTest.php`
- `tests/Unit/StockServiceTest.php`
- `tests/Unit/TelegramServiceTest.php`
- `tests/Feature/` masih kosong

**Konfigurasi**: `phpunit.xml`

**Menjalankan tests**:

```
bash
composer run test
```

Atau untuk test spesifik:

```
bash
php artisan test tests/Unit/ExampleTest.php
```

### 14.4 Test Data (Dummy Orders)

**OrderSeeder** membuat 200 dummy orders dengan perhitungan HPP otomatis yang sesuai runtime logic.

**Cara menjalankan**:

```
bash
php artisan db:seed --class=OrderSeeder
```

**Atau semua seeder**:

```
bash
php artisan migrate --seed
```

---

## 15. Dependencies & Packages

### 15.1 Production Dependencies (dari `composer.json`)

| Package                 | Versi   | Fungsi                                       |
| ----------------------- | ------- | -------------------------------------------- |
| laravel/framework       | ^12.0   | Framework utama                              |
| laravel/sanctum         | ^4.0    | API authentication (token-based)             |
| laravel/tinker          | ^2.10.1 | Interactive shell (artisan tinker)           |
| barryvdh/laravel-dompdf | ^3.1    | PDF generation (DOMPDF wrapper)              |
| maatwebsite/excel       | ^3.1    | Excel import/export (PhpSpreadsheet wrapper) |

### 15.2 Development Dependencies

| Package              | Versi   | Fungsi                         |
| -------------------- | ------- | ------------------------------ |
| tailwindcss          | ^4.0.0  | CSS framework (utility-first)  |
| @tailwindcss/vite    | ^4.0.0  | Tailwind Vite plugin           |
| vite                 | ^7.0.7  | Build tool & dev server        |
| laravel-vite-plugin  | ^2.0.0  | Laravel Vite integration       |
| axios                | ^1.11.0 | HTTP client                    |
| concurrently         | ^9.0.1  | Run multiple commands          |
| fakerphp/faker       | ^1.23   | Fake data generation (seeders) |
| laravel/pail         | ^1.2.2  | Real-time log monitoring       |
| laravel/pint         | ^1.24   | Code style fixer               |
| laravel/sail         | ^1.41   | Docker dev environment         |
| mockery/mockery      | ^1.6    | Mocking library (tests)        |
| nunomaduro/collision | ^8.6    | Error handler                  |
| phpunit/phpunit      | ^11.5.3 | Testing framework              |

---

## 16. Catatan Penting untuk Skripsi

### 16.1 Asumsi Desain Sistem

1. **1 Unit Produk = 1 Batch**
    - Setiap produk dianggap sebagai 1 batch di oven
    - Overhead dihitung per batch, bukan per hari atau per kategori

2. **Harga Material Real-Time**
    - HPP dihitung saat order dibuat menggunakan harga material terkini
    - Setiap perubahan harga bahan dicatat di `material_price_logs` untuk audit trail

3. **Pre-order vs Direct Order**
    - Pre-order: Validasi produk ada, jangan kurangi stok saat pembuatan (tunda hingga pembayaran)
    - Direct order: Validasi stok cukup, kurangi stok segera

4. **Unit Conversion**
    - Sistem mendukung multiple unit (gram, ml, pcs) dengan konversi otomatis ke base unit
    - Harga dihitung dalam unit kecil (price_per_unit) saat order, bukan base unit

5. **Notifikasi Asynchronous**
    - Pengiriman notifikasi Telegram melalui queue job dengan retry otomatis
    - Tidak memblok proses order creation

### 16.2 Rekomendasi untuk Penelitian/Skripsi

1. **Analisis HPP dan Margin**
    - Ekstrak 200 dummy orders via `OrderSeeder`
    - Hitung margin per produk dan total periode
    - Analisis dampak perubahan overhead terhadap margin
    - Simulasikan perubahan harga bahan menggunakan `UpdatePrice` endpoint

2. **Stress Test Concurrency**
    - Test race condition stok dengan multiple simultaneous orders
    - Validasi bahwa `lockForUpdate()` mencegah oversell

3. **Export & Reporting**
    - Test export Excel laporan omzet bulanan
    - Analisis performa query laporan dengan 1000+ orders

4. **Telegram Integration**
    - Test retry mechanism dengan simulate Telegram API timeout
    - Monitor notification_logs untuk tracking delivery

5. **Database Performance**
    - Analisis query pada `order_items` JOIN `order` JOIN `product` JOIN `product_materials` JOIN `materials`
    - Index optimization pada high-traffic fields (`order_date`, `status`, `material_id`)

### 16.3 File Kunci untuk Referensi Skripsi

| Purpose            | File                                         | Catatan                                                          |
| ------------------ | -------------------------------------------- | ---------------------------------------------------------------- |
| Overhead formula   | `app/Services/OverheadService.php`           | Line 1-67 menunjukkan rumus lengkap                              |
| HPP calculation    | `app/Services/OrderService.php`              | Method `calculateRealTimeHPP()` line ~180                        |
| Order logic        | `app/Services/OrderService.php`              | Methods `createOrder()`, `createPreOrder()`, `executePreOrder()` |
| Stock validation   | `app/Services/OrderService.php`              | Method `validateStockAvailability()`                             |
| Master data        | `database/seeders/MasterDataSeeder.php`      | 27 materials, 21 products, 178 BOMs                              |
| Overhead config    | `database/seeders/OverheadSettingSeeder.php` | 9 parameter settings                                             |
| Models             | `app/Models/*.php`                           | Semua model dengan relationships                                 |
| Database structure | `database/migrations/*.php`                  | 15 migration files                                               |
| API routes         | `routes/api.php`                             | Lengkap dengan endpoint documentation                            |
| Web routes         | `routes/web.php`                             | Session-based routes                                             |

---

## 17. Kesimpulan Dokumentasi

Dokumen ini menyediakan:

- Spesifikasi lengkap proyek (fitur, requirements, architecture)
- Struktur database domain dengan 10 tabel utama (`users`, `materials`, `products`, `product_materials`, `orders`, `order_items`, `stock_logs`, `material_price_logs`, `overhead_settings`, `notification_logs`) + tabel framework Laravel (`cache`, `jobs`, `personal_access_tokens`)
- Perhitungan formula overhead dan HPP dengan contoh numerik
- Route mapping untuk web dan API endpoints
- Service logic untuk order, stok, material
- Master data 27 materials, 21 products, 200 dummy orders
- Exception handling dan validasi data
- Command + scheduler reminder Telegram

Gunakan dokumen ini sebagai **bahan referensi mentah** saat menulis bab analisis, desain, implementasi, dan evaluasi skripsi.

---

**Terakhir update**: 1 Maret 2026

## 18. UI/UX Design System

### 18.1 Design Philosophy

- **Dark Theme**: Base colors #0f172a (slate-900) dan #1e293b (slate-800)
- **Accent Color**: Cyan (#06b6d4) untuk highlights dan interactive elements
- **Modern Professional**: Gradient backgrounds, smooth shadows, rounded corners
- **Accessibility-First**: Keyboard navigation, ARIA labels, focus management, screen reader support
- **Smooth Animations**: cubic-bezier transitions untuk micro-interactions

### 18.2 Tailwind CSS v4 Configuration

- **Version**: 4.0.0 dengan Vite integration via `@tailwindcss/vite`
- **Architecture**: Hybrid approach - Tailwind utilities + custom CSS components
- **Design Tokens**: Custom CSS variables di `:root` untuk konsistensi
- **Responsive**: Mobile-first dengan breakpoints sm, md, lg, xl

### 18.3 Component Library

| Component               | File                           | Features                                                                                               |
| ----------------------- | ------------------------------ | ------------------------------------------------------------------------------------------------------ |
| **Toast Notifications** | `notifications.js` + `app.css` | 4 types (success/error/warning/info), progress bar, auto-dismiss, pause on hover, enhanced readability |
| **Confirm Dialog**      | `notifications.js`             | Promise-based API, loading states, Dark Refined Minimalist design, keyboard navigation                 |
| **Dropdowns**           | `app.css`                      | Custom arrow, hover effects, mobile bottom sheet, keyboard navigation, ARIA support                    |
| **Modals**              | `app.css`                      | Backdrop blur, focus trap, keyboard navigation (Escape, Tab), smooth animations                        |
| **Buttons**             | `app.css`                      | Gradient styles, hover lift effects, loading states, multiple variants (primary, secondary, danger)    |
| **Tables**              | `app.css`                      | Dark headers, hover states, sticky headers, custom scrollbar, action buttons                           |
| **Forms**               | `app.css`                      | Custom inputs, select with icons, focus states, validation styling                                     |

### 18.4 Notification System Details

**Toast Notifications:**

- Font size: 0.9375rem (15px) untuk title, 0.875rem (14px) untuk message
- Line height: 1.5 untuk readability
- Color contrast: Title #f8fafc, message #cbd5e1
- Progress bar: Animated dengan warna sesuai tipe
- Position: Fixed top-right dengan z-index 9999

**Confirm Dialog:**

- Background: Gradient #1e293b ke #334155
- Border: 1px solid #475569 dengan cyan accent
- Shadow: Multi-layered deep shadow
- Icon: Animated bounce dengan background rounded
- Buttons: Gradient dengan hover lift effect

### 18.5 File Kunci untuk UI/UX

| Purpose             | File                               | Catatan                                   |
| ------------------- | ---------------------------------- | ----------------------------------------- |
| Main Stylesheet     | `resources/css/app.css`            | ~800 lines, contains all component styles |
| Notification System | `resources/js/notifications.js`    | Toast & Confirm dialog logic              |
| Login Styles        | `resources/css/login-tailwind.css` | Login page specific Tailwind styles       |
| UI Utilities        | `resources/js/ui.js`               | UI helper functions                       |
| Laporan JS          | `resources/js/laporan.js`          | Export dropdown, filter functionality     |

---

**Catatan**: File ini adalah dokumentasi teknis yang berubah seiring development. Pastikan selalu mereferensi ke kode sumber terbaru di repository untuk informasi paling akurat.
