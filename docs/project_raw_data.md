# Project Raw Data — NISA Cake Management System

Dokumentasi teknis komprehensif untuk proyek NISA Cake Management System. Berisi data, konfigurasi, skema database, formula perhitungan, routes, dan spesifikasi yang berasal langsung dari kode sumber. Dokumen ini adalah bahan mentah (raw data) untuk mengisi konten skripsi Anda.

## Informasi Dasar Proyek

| Detail            | Nilai                                     |
| ----------------- | ----------------------------------------- |
| Nama Proyek       | NISA Cake Management System               |
| Tipe              | Web Application (SPA + Backend API)       |
| Bahasa Dasar      | PHP 8.2+, JavaScript/Vite                 |
| Framework         | Laravel 12.0, Eloquent ORM                |
| Database          | MySQL (migrasi dengan Laravel)            |
| Stack Frontend    | HTML5, CSS3, JavaScript, Vite             |
| Stack Backend     | PHP, Laravel, RESTful API, Sanctum (Auth) |
| Service Eksternal | Telegram Bot API                          |
| Build Tools       | npm, Vite, Composer                       |
| Lokasi Repo       | `c:\laragon\www\nisacake`                 |
| Lisensi           | MIT                                       |

## Setup & Deployment

### Perintah Setup Awal

Dari `composer.json` key `scripts.setup`:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

### Perintah Development

```bash
composer run dev
```

Menjalankan concurrent: Laravel server + queue listener + logs pail + Vite dev server.

### Perintah Testing

```bash
composer run test
```

Menjalankan PHPUnit tests (konfigurasi: `phpunit.xml`).

### Konfigurasi Service Eksternal

File: `config/services.php`

- **Telegram**:
    - Token dari env var `TELEGRAM_BOT_TOKEN`
    - Chat ID dari env var `TELEGRAM_CHAT_ID`

---

## Struktur Proyek (Folder Tree)

```
nisacake/
├── app/
│   ├── Console/
│   │   └── Commands/
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
│   │   └── Resources/ (API Resource/JSON)
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
│   │   └── ProductFactory.php
│   ├── migrations/ (15 files)
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_02_02_142805_create_materials_table.php
│   │   ├── 2026_02_02_143801_create_products_table.php
│   │   ├── 2026_02_02_144137_create_product_materials_table.php (BOM pivot)
│   │   ├── 2026_02_02_151609_create_orders_table.php
│   │   ├── 2026_02_02_151655_create_order_items_table.php
│   │   ├── 2026_02_05_061958_create_stock_logs_table.php
│   │   ├── 2026_02_12_000003_create_material_price_logs_table.php
│   │   ├── 2026_02_16_070121_create_overhead_settings_table.php
│   │   ├── 2026_02_18_120000_add_scheduled_at_to_orders_table.php
│   │   ├── 2026_02_18_120100_add_is_notified_to_orders_table.php
│   │   ├── 2026_02_19_000000_create_notification_logs_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   └── 2026_02_04_062829_create_personal_access_tokens_table.php
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
│   ├── css/ (Vite)
│   ├── js/ (Vite, SPA components)
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
│   └── Feature/
├── vendor/ (autoload, dependencies)
├── .env.example
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.js
└── README.md
```

---

## Spesifikasi Fitur Lengkap

### 1. Manajemen Autentikasi

- **User Login**: Form login dengan session (web) atau token Sanctum (API)
- **User Registration**: Endpoint API (tidak dipakai UI saat ini)
- **User Logout**: Menghapus session/token
- **Default User**: Dibuat via OwnerSeeder dengan role/permission (jika ada)

### 2. Manajemen Produk (Kue)

- **List Produk**: Menampilkan semua produk dengan harga jual, HPP kalkulasi, margin
- **Tambah Produk**: Buat produk baru dengan nama, harga jual, deskripsi
- **Edit Produk**: Update nama, harga, deskripsi, override overhead per produk
- **Hapus Produk**: Soft delete produk
- **BOM (Bill of Materials)**: Relasi produk ke material dengan `quantity_needed` per material

### 3. Manajemen Bahan Baku (Inventory)

- **List Bahan**: Menampilkan semua bahan, stok, harga, min stok level
- **Tambah Bahan**: Buat bahan baru dengan nama, unit (gram/ml/pcs), harga per unit kecil
- **Edit Bahan**: Update harga bahan (dengan auto-konversi base unit dan pencatatan history)
- **Update Harga Bahan**: Dengan pencatatan log perubahan ke `material_price_logs` (termasuk old/new price, user, timestamp)
- **Unit Konversi**: Otomatis konversi gram→kg, ml→liter, pcs→Pack sesuai `MaterialService::convertUnitPricing()`

### 4. Pembelian & Penerimaan Stok

- **Tambah Stok Manual**: Input qty bahan yang diterima dengan deskripsi (pembelian/koreksi)
- **Pencatatan**: Otomatis membuat entry `stock_logs` tipe `in`
- **Update Stok**: Tambah `current_stock` material dan log timestamp

### 5. Pemesanan (Order Management)

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
    - Saat pelunasan: jalankan `executePreOrder()` → validasi stok → kurangi stok → ubah status ke `COMPLETED`
- **Cancel Order**: Ubah status menjadi `CANCELLED` (rollback stok jika sudah dikurangi)
- **List & Detail Order**: Lihat all orders atau detail order + items

### 6. Laporan & Analytics

- **Laporan Omzet**: Group by tanggal/periode, hitung total penjualan, total HPP, margin
- **Laporan per Produk**: Hitung qty terjual, omzet per produk, HPP, kontribusi margin
- **Export Excel**: Generate file Excel dengan data laporan (via `Exports/LaporanExport.php`)
- **Dashboard Kasir**: Summary hari ini (omzet, transaksi, HPP)
- **Dashboard Gudang**: Summary stok (current stok vs min level, alert bahan kurang)

### 7. Pengaturan Overhead (Konfigurasi)

- **List Overhead Settings**: Tampilkan 9 konfigurasi (gas, listrik, tenaga kerja, depreciation, baking time, mixer time, safety margin)
- **Edit Setting**: Update nilai parameter overhead di DB
- **Kalkulasi Overhead**: Otomatis dihitung perubahan overhead saat ada perubahan setting

### 8. Notifikasi & Integrasi Eksternal

- **Telegram Integration**: Kirim notifikasi reminder ke Telegram Bot
- **Queue Job**: `SendTelegramReminderJob` dengan retry/backoff exponential (max 5 tries)
- **Notification Logging**: Catat setiap pengiriman (payload, response, status, timestamp) ke `notification_logs`
- **Health Check**: Endpoint test Telegram connection

### 9. Manajemen Stok & Validasi

- **Stock Validation**: Saat order, validasi bahwa material ada dan stok cukup (dengan lock pessimistic)
- **Stock Movement Log**: Setiap perubahan stok dicatat dengan type (`in`/`out`), qty, deskripsi, timestamp
- **Min Stock Alert**: Flag jika `current_stock` turun di bawah `min_stock_level`

---

## Entity Relationship Diagram (Tekstual)

```
users
├── 1:N relationship dengan orders (customer history)
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
├── Relation ke User (customer_name stored, tidak FK eksplisit)
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

## Routes & API Mapping

### Web Routes (Session-Based Auth)

File: `routes/web.php`

| Method | Route                    | Controller                   | Fungsi            | Auth |
| ------ | ------------------------ | ---------------------------- | ----------------- | ---- |
| GET    | `/login`                 | LoginController@index        | Tampil form login | No   |
| POST   | `/login`                 | LoginController@authenticate | Proses login      | No   |
| POST   | `/logout`                | LoginController@logout       | Logout            | Yes  |
| GET    | `/`                      | Redirect ke `/kasir`         | Home redirect     | Yes  |
| GET    | `/kasir`                 | View render                  | Dashboard kasir   | Yes  |
| GET    | `/gudang`                | InventoryController@index    | Dashboard gudang  | Yes  |
| GET    | `/admin/telegram/test`   | TelegramController@test      | Test Telegram     | Yes  |
| GET    | `/admin/telegram/health` | TelegramController@health    | Health check      | Yes  |
| GET    | `/laporan`               | View render                  | Halaman laporan   | Yes  |
| POST   | `/stocks/add`            | StockController@store        | Tambah stok       | Yes  |
| GET    | `/laporan/export`        | ReportController@export      | Export Excel      | Yes  |

### API Routes (Sanctum Token-Based Auth)

File: `routes/api.php`

| Method        | Endpoint                        | Controller                         | Fungsi                          | Status      |
| ------------- | ------------------------------- | ---------------------------------- | ------------------------------- | ----------- |
| POST          | `/login`                        | AuthController@login               | API login (token)               | OK          |
| POST          | `/register`                     | AuthController@register            | Register (tidak dipakai)        | Unused      |
| POST          | `/logout`                       | AuthController@logout              | Logout token                    | OK          |
| GET           | `/user`                         | inline (tidak dipakai)             | Get current user                | Unused      |
| **Order**:    |                                 |                                    |                                 |             |
| POST          | `/buat-pesanan`                 | OrderController@store              | Create order direct             | OK          |
| POST          | `/jadwal-pesanan`               | OrderController@preOrder           | Create pre-order                | OK          |
| GET           | `/jadwal-pesanan`               | OrderController@getScheduledOrders | List pre-orders                 | OK          |
| POST          | `/orders/{id}/execute-preorder` | OrderController@executePreOrder    | Execute pre-order               | OK          |
| GET           | `/orders`                       | OrderController@index              | List orders (tidak dipakai)     | Unused      |
| GET           | `/orders/{id}`                  | OrderController@show               | Detail order (tidak dipakai)    | Unused      |
| PATCH         | `/orders/{id}/complete`         | OrderController@complete           | Mark complete                   | OK          |
| **Product**:  |                                 |                                    |                                 |             |
| GET           | `/products`                     | ProductController@index            | List products                   | OK          |
| POST          | `/products`                     | ProductController@store            | Create product                  | OK          |
| GET           | `/products/{id}`                | ProductController@show             | Detail (tidak dipakai)          | Unused      |
| PATCH         | `/products/{id}`                | ProductController@update           | Update product                  | OK          |
| **Material**: |                                 |                                    |                                 |             |
| GET           | `/materials`                    | MaterialController@index           | List materials                  | OK          |
| PATCH         | `/materials/{id}/price`         | MaterialController@updatePrice     | Update harga bahan              | OK          |
| POST          | `/materials/reduce`             | MaterialController@reduceStock     | Reduce manually (tidak dipakai) | Unused      |
| GET           | `/materials/price-history`      | MaterialPriceLogController@index   | List price history              | OK          |
| **Stock**:    |                                 |                                    |                                 |             |
| POST          | `/stocks/add`                   | StockController@store              | Add stock                       | OK          |
| GET           | `/stocks/history`               | StockController@index              | Stock logs history              | OK          |
| **Report**:   |                                 |                                    |                                 |             |
| GET           | `/reports`                      | ReportController@index             | Get reports (FIXME)             | Calculating |
| **Overhead**: |                                 |                                    |                                 |             |
| GET           | `/overhead-settings`            | OverheadSettingController@index    | List overhead config            | OK          |

**Catatan**: Endpoint dengan status "Unused" atau "tidak dipakai" masih ada di route definition tetapi belum diakses oleh UI frontend saat ini.

---

## Database Schema (Ringkasan Lengkap)

### Tabel: materials

**Penyimpanan master bahan baku dengan harga dan stok**

| Kolom               | Tipe          | Nullable | Default        | Keterangan                           |
| ------------------- | ------------- | -------- | -------------- | ------------------------------------ |
| id                  | bigint        | No       | auto_increment | Primary key                          |
| name                | string        | No       | -              | Nama bahan (misal: Tepung Terigu)    |
| unit                | string        | No       | -              | Unit kecil (gram, ml, butir, pcs)    |
| price_per_unit      | decimal(10,2) | No       | -              | Harga per unit kecil (Rp)            |
| base_unit           | string        | No       | gram           | Konversi base (kg, liter, Pack, dll) |
| price_per_base_unit | decimal(10,2) | Yes      | -              | Harga per base unit (Rp)             |
| current_stock       | integer       | No       | 0              | Stok saat ini (dalam unit kecil)     |
| min_stock_level     | integer       | No       | 0              | Alert jika stok di bawah ini         |
| created_at          | timestamp     | -        | -              | -                                    |
| updated_at          | timestamp     | -        | -              | -                                    |
| deleted_at          | timestamp     | Yes      | -              | Soft delete                          |

**Relasi**:

- `1:N` dengan `stock_logs` (riwayat perubahan stok)
- `1:N` dengan `material_price_logs` (riwayat harga)
- `N:M` dengan `products` (via `product_materials` pivot)

**Cast di Model**: `price_per_unit`, `price_per_base_unit` → decimal:2; `current_stock`, `min_stock_level` → integer

---

### Tabel: products

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

**Cast di Model**: `selling_price`, `production_cost`, `overhead_cost_per_unit` → decimal:2

---

### Tabel: product_materials (Pivot / Bill of Materials)

**Menghubungkan produk ke bahan dengan kuantitas yang dibutuhkan**

| Kolom           | Tipe      | Nullable | Keterangan                                     |
| --------------- | --------- | -------- | ---------------------------------------------- |
| id              | bigint    | -        | Primary key                                    |
| product_id      | bigint FK | No       | Ref ke `products.id` (cascade delete)          |
| material_id     | bigint FK | No       | Ref ke `materials.id` (cascade delete)         |
| quantity_needed | integer   | No       | Qty bahan per 1 unit produk (dalam unit bahan) |
| created_at      | timestamp | -        | -                                              |
| updated_at      | timestamp | -        | -                                              |

**Contoh**: Produk id=1 (Kue Tart Bolu 14) butuh material id=1 (Tepung Terigu) sebanyak 120 gram per 1 keu.

---

### Tabel: orders

**Header pesanan dengan total harga dan HPP**

| Kolom         | Tipe          | Nullable | Default        | Keterangan                                                |
| ------------- | ------------- | -------- | -------------- | --------------------------------------------------------- |
| id            | bigint        | -        | auto_increment | Primary key                                               |
| customer_name | string        | No       | -              | Nama pembeli                                              |
| order_date    | datetime      | No       | -              | Tanggal pemesanan (saat create order)                     |
| status        | enum/string   | No       | -              | Status order (pre_order, completed, cancelled)            |
| total_price   | decimal(15,2) | No       | -              | Total harga jual bruto (Rp)                               |
| total_hpp     | decimal(15,2) | No       | -              | Total harga pokok penjualan (Rp)                          |
| scheduled_at  | datetime      | Yes      | -              | Waktu pelunasan pre-order (nullable untuk order langsung) |
| is_notified   | boolean       | No       | false          | Apakah sudah dikirim notifikasi Telegram                  |
| created_at    | timestamp     | -        | -              | -                                                         |
| updated_at    | timestamp     | -        | -              | -                                                         |

**Cast di Model**: `status` → OrderStatus enum; `order_date`, `scheduled_at` → datetime; `total_price`, `total_hpp` → decimal:2; `is_notified` → boolean

**Relasi**: `1:N` dengan `order_items`, `1:N` dengan `notification_logs`

---

### Tabel: order_items

**Line items dalam pesanan (detail per produk)**

| Kolom          | Tipe          | Nullable | Keterangan                                                |
| -------------- | ------------- | -------- | --------------------------------------------------------- |
| id             | bigint        | -        | Primary key                                               |
| order_id       | bigint FK     | No       | Ref ke `orders.id` (cascade delete)                       |
| product_id     | bigint FK     | No       | Ref ke `products.id`                                      |
| quantity       | integer       | No       | Qty produk yang dibeli                                    |
| price_per_unit | decimal(10,2) | No       | Harga jual per unit saat order dibuat (Rp)                |
| hpp_per_unit   | decimal(10,2) | No       | HPP per unit saat order dibuat (Rp) — historical snapshot |
| created_at     | timestamp     | -        | -                                                         |
| updated_at     | timestamp     | -        | -                                                         |

**Cast di Model**: `quantity` → integer; `price_per_unit`, `hpp_per_unit` → decimal:2

---

### Tabel: stock_logs

**Riwayat perubahan stok bahan**

| Kolom       | Tipe      | Nullable | Keterangan                                                 |
| ----------- | --------- | -------- | ---------------------------------------------------------- |
| id          | bigint    | -        | Primary key                                                |
| material_id | bigint FK | No       | Ref ke `materials.id` (cascade delete)                     |
| type        | enum      | No       | Tipe: 'in' (masuk), 'out' (keluar), 'adjustment' (koreksi) |
| amount      | integer   | No       | Jumlah perubahan (dalam unit material)                     |
| description | string    | Yes      | Keterangan (misal: "Belanja", "Produksi Order #123")       |
| created_at  | timestamp | -        | -                                                          |
| updated_at  | timestamp | -        | -                                                          |

**Cast di Model**: `type` → StockLogType enum; `amount` → integer

---

### Tabel: material_price_logs

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

### Tabel: overhead_settings

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

- `gas_price_per_tube`: 22000 (Rp)
- `gas_capacity_minutes`: 620 (menit)
- `electricity_rate_kwh`: 605 (Rp/kWh)
- `mixer_power_kw`: 0.16 (kW)
- `labor_rate_per_hour`: 10000 (Rp/jam)
- `depreciation_per_batch`: 800 (Rp/batch)
- `baking_minutes_per_batch`: 50 (menit)
- `mixer_minutes_per_batch`: 12 (menit)
- `safety_margin_percent`: 5 (%)

---

### Tabel: notification_logs

**Log pengiriman notifikasi (khususnya Telegram reminder)**

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

### Tabel: users

**User (admin/staff) yang login ke sistem**

Standard Laravel users table dengan fields: id, name, email, password, email_verified_at, remember_token, created_at, updated_at.

---

## Enumerasi (Enum)

### OrderStatus Enum (`app/Enums/OrderStatus.php`)

| Value     | Case      | Label      | Keterangan                                                   |
| --------- | --------- | ---------- | ------------------------------------------------------------ |
| pre_order | PRE_ORDER | Pre-Order  | Pesanan terjadwal, belum kurangi stok, pembayaran belakangan |
| completed | COMPLETED | Selesai    | Pesanan selesai (stok sudah dikurangi)                       |
| cancelled | CANCELLED | Dibatalkan | Pesanan dibatalkan (rollback stok jika perlu)                |

**Method**: `label()` mengembalikan teks readable untuk ditampilkan di UI.

---

### StockLogType Enum (`app/Enums/StockLogType.php`)

| Value | Case | Label  | Keterangan                        |
| ----- | ---- | ------ | --------------------------------- |
| in    | IN   | Masuk  | Stok masuk (pembelian/penerimaan) |
| out   | OUT  | Keluar | Stok keluar (produksi/order)      |

---

## Exception Classes

### InsufficientStockException (`app/Exceptions/InsufficientStockException.php`)

**Dilempar ketika**: Stok material tidak mencukupi saat membuat order langsung

**Parameter konstruktor**:

- `$material_name`: Nama bahan yang kurang stok
- `$current_stock`: Stok saat ini
- `$needed`: Qty yang dibutuhkan

---

### MaterialNotFoundException (`app/Exceptions/MaterialNotFoundException.php`)

**Dilempar ketika**: Material atau produk tidak ditemukan saat validasi

---

## Master Data dari Seeder

### Material Master Data (27 items)

Dari `database/seeders/MasterDataSeeder.php`:

| ID  | Nama             | Unit  | Harga/Unit (Rp) | Min Stok | Base Unit | Harga/Base | Kategori         |
| --- | ---------------- | ----- | --------------- | -------- | --------- | ---------- | ---------------- |
| 1   | Tepung Terigu    | gram  | 12.0            | 2000     | kg        | 12000      | Bahan Utama      |
| 2   | Telur Ayam       | butir | 1800.0          | 60       | Pack      | 180000     | Bahan Utama      |
| 3   | SP (Emulsifier)  | gram  | 80.0            | 200      | kg        | 80000      | Bahan Pengembang |
| 4   | Minyak Goreng    | gram  | 17.0            | 1000     | kg        | 17000      | Minyak           |
| 5   | Gula Pasir       | gram  | 17.0            | 2000     | kg        | 17000      | Gula             |
| 6   | Pewarna Pandan   | gram  | 110.0           | 100      | kg        | 110000     | Pewarna          |
| 7   | Coklat Bubuk     | gram  | 150.0           | 500      | kg        | 150000     | Coklat           |
| 8   | Coklat Batang    | gram  | 60.0            | 1200     | kg        | 60000      | Coklat           |
| 9   | Butter Cream     | gram  | 28.0            | 1000     | kg        | 28000      | Topping          |
| 10  | Mentega/Margarin | gram  | 30.0            | 500      | kg        | 30000      | Lemak            |
| 11  | Tepung Maizena   | gram  | 21.0            | 400      | kg        | 21000      | Bahan            |
| 12  | Susu Bubuk       | gram  | 32.0            | 300      | kg        | 32000      | Susu             |
| 13  | Selai            | gram  | 24.0            | 400      | kg        | 24000      | Topping          |
| 14  | Pisang           | gram  | 10.0            | 1500     | kg        | 10000      | Buah             |
| 15  | Santan Cair      | gram  | 83.0            | 500      | liter     | 83000      | Cair             |
| 16  | Garam            | gram  | 7.0             | 200      | kg        | 7000       | Bumbu            |
| 17  | Air Lemon        | gram  | 25.0            | 100      | liter     | 25000      | Asam             |
| 18  | Parutan Kelapa   | gram  | 18.0            | 100      | kg        | 18000      | Kelapa           |
| 19  | Mika Kue 14      | pcs   | 3000.0          | 5        | Pack      | 300000     | Kemasan          |
| 20  | Mika Kue 16      | pcs   | 3200.0          | 5        | Pack      | 320000     | Kemasan          |
| 21  | Mika Kue 18      | pcs   | 3500.0          | 5        | Pack      | 350000     | Kemasan          |
| 22  | Mika Kue 20      | pcs   | 5000.0          | 5        | Pack      | 500000     | Kemasan          |
| 23  | Mika Kue 22      | pcs   | 6500.0          | 5        | Pack      | 650000     | Kemasan          |
| 24  | Mika Kue 24      | pcs   | 8000.0          | 5        | Pack      | 800000     | Kemasan          |
| 25  | Kardus Kue 16    | pcs   | 15000.0         | 5        | Pack      | 1500000    | Kardus           |
| 26  | Kardus Kue 24    | pcs   | 2500.0          | 5        | Pack      | 250000     | Kardus           |
| 27  | Plastik          | pcs   | 18.0            | 50       | Pack      | 1800       | Plastik          |

### Product Master Data (21 items — ringkasan)

| ID  | Nama                       | Harga Jual (Rp) | Kategori | Bahan Utama                 |
| --- | -------------------------- | --------------- | -------- | --------------------------- |
| 1   | Kue Tart Bolu 14           | 50000           | Tart     | Tepung, Telur, Butter Cream |
| 2   | Kue Tart Bolu 16           | 60000           | Tart     | Tepung, Telur, Butter Cream |
| 3   | Kue Tart Bolu 18           | 70000           | Tart     | Tepung, Telur, Butter Cream |
| 4   | Kue Tart Bolu 20           | 80000           | Tart     | Tepung, Telur, Butter Cream |
| 5   | Kue Tart Bolu 22           | 90000           | Tart     | Tepung, Telur, Butter Cream |
| 6   | Kue Tart Bolu 24           | 100000          | Tart     | Tepung, Telur, Butter Cream |
| 7   | Kue Tart Brownies 14       | 60000           | Brownies | Tepung, Coklat, Telur       |
| 8   | Kue Tart Brownies 16       | 75000           | Brownies | Tepung, Coklat, Telur       |
| 9   | Kue Tart Brownies 18       | 100000          | Brownies | Tepung, Coklat, Telur       |
| 10  | Kue Tart Brownies 20       | 120000          | Brownies | Tepung, Coklat, Telur       |
| 11  | Kue Tart Brownies 22       | 135000          | Brownies | Tepung, Coklat, Telur       |
| 12  | Kue Tart Brownies 24       | 155000          | Brownies | Tepung, Coklat, Telur       |
| 13  | Bolu Gulung (12 biji)      | 35000           | Bolu     | Tepung, Telur               |
| 14  | Bolu Pisang 18             | 55000           | Bolu     | Tepung, Pisang              |
| 15  | Brownis Kukus 24 (30 biji) | 105000          | Brownies | Coklat, Telur               |
| 16  | Brownis Panggang (15 biji) | 75000           | Brownies | Coklat, Minyak              |
| 17  | Bolen pisang (40 biji)     | 100000          | Bolen    | Tepung, Pisang              |
| 18  | Bolu jadul (24 biji)       | 65000           | Bolu     | Tepung, Telur               |
| 19  | Shifon cake (16 biji)      | 56000           | Chiffon  | Tepung, Santan              |
| 20  | Bolu Kukus biasa (24 biji) | 60000           | Bolu     | Tepung, Santan              |
| 21  | Putu ayu (25 biji)         | 75000           | Putu     | Tepung, Santan              |

_Total 178 entries `product_materials` di seeder mendefinisikan BOM lengkap setiap produk._

---

## Perhitungan & Formula

### Perhitungan Overhead Per Unit

**Sumber**: `app/Services/OverheadService.php` → `calculateOverheadPerUnit()`

Overhead adalah biaya produksi tidak langsung (gas, listrik, tenaga kerja, penyusutan) per unit/batch.

**Asumsi**: 1 unit produk = 1 batch pemanggang

**Rumus**:

```
Biaya Gas per Batch = (Gas Price per Tube / Gas Capacity Minutes) × Baking Minutes per Batch

Biaya Listrik per Batch (Mixer) = (Mixer Power kW × Electricity Rate kWh / 60) × Mixer Minutes per Batch

Biaya Tenaga Kerja per Batch = (Labor Rate per Hour / 60) × Baking Minutes per Batch

Base Cost per Batch = Biaya Gas + Biaya Listrik + Biaya Tenaga Kerja + Depreciation per Batch

Overhead per Unit (Final) = Base Cost per Batch × (1 + Safety Margin Percent / 100)
```

**Perhitungan Numerik (menggunakan default seeder)**:

```
Input Parameters dari overhead_settings:
  gas_price_per_tube = 22000 (Rp/tabung)
  gas_capacity_minutes = 620 (menit/tabung)
  electricity_rate_kwh = 605 (Rp/kWh)
  mixer_power_kw = 0.16 (kW)
  labor_rate_per_hour = 10000 (Rp/jam)
  depreciation_per_batch = 800 (Rp/batch)
  baking_minutes_per_batch = 50 (menit)
  mixer_minutes_per_batch = 12 (menit)
  safety_margin_percent = 5 (%)

Step 1 — Biaya Gas per Menit:
  gasPerMinute = 22000 / 620 = 35.48 Rp/menit

Step 2 — Biaya Gas per Batch:
  gasCostPerBatch = 35.48 × 50 = 1774.19 Rp

Step 3 — Biaya Listrik per Menit:
  electricityPerMinute = (0.16 × 605) / 60 = 1.613 Rp/menit

Step 4 — Biaya Listrik per Batch:
  electricityCostPerBatch = 1.613 × 12 = 19.36 Rp

Step 5 — Biaya Tenaga Kerja per Menit:
  laborPerMinute = 10000 / 60 = 166.67 Rp/menit

Step 6 — Biaya Tenaga Kerja per Batch:
  laborCostPerBatch = 166.67 × 50 = 8333.33 Rp

Step 7 — Biaya Dasar per Batch:
  basePerBatch = 1774.19 + 19.36 + 8333.33 + 800 = 10926.88 Rp

Step 8 — Overhead Final (dengan Safety Margin 5%):
  overheadPerUnit = 10926.88 × 1.05 = 11473.23 Rp/unit
```

**Kesimpulan**: Setiap 1 unit produk menambah biaya overhead minimal 11473.23 Rp (sebelum faktor lain).

---

### Perhitungan HPP (Harga Pokok Penjualan) Real-Time

**Sumber**: `app/Services/OrderService.php` → `calculateRealTimeHPP()`

HPP dihitung saat order dibuat, menggunakan harga material saat itu dan overhead configuration.

**Algoritma**:

```
FOR EACH item dalam order:

  HPP_item = 0

  FOR EACH material dalam product BOM:
    qty_needed = material.pivot.quantity_needed
    current_price = material.price_per_unit (jika ada)
                   ELSE material.price_per_base_unit (fallback)

    HPP_item += qty_needed × current_price

  overhead_unit = product.overhead_cost_per_unit (jika > 0)
                 ELSE OverheadService::calculateOverheadPerUnit() [global]

  HPP_per_unit = HPP_item + overhead_per_unit

  Total HPP untuk item = HPP_per_unit × order_item.quantity

Order.total_hpp = SUM(Total HPP untuk semua item)
```

**Contoh Perhitungan Produk ID=1 (Kue Tart Bolu 14)**

```
Harga Jual: 50000 Rp

BOM Breakdown:
  Material #1 (Tepung Terigu): 120 gram × 12.0 Rp/gram = 1440 Rp
  Material #2 (Telur Ayam): 2 butir × 1800.0 Rp/butir = 3600 Rp
  Material #3 (SP): 2 gram × 80.0 Rp/gram = 160 Rp
  Material #4 (Minyak Goreng): 60 gram × 17.0 Rp/gram = 1020 Rp
  Material #5 (Gula Pasir): 90 gram × 17.0 Rp/gram = 1530 Rp
  Material #6 (Pewarna Pandan): 2 gram × 110.0 Rp/gram = 220 Rp
  Material #9 (Butter Cream): 250 gram × 28.0 Rp/gram = 7000 Rp
  Material #19 (Mika Kue 14): 1 pcs × 3000.0 Rp/pcs = 3000 Rp
  ─────────────────────────────────────────────────
  Subtotal Material = 17970 Rp

Overhead per Unit (kondisi product.overhead_cost_per_unit = 0):
  Gunakan kalkulasi global = 11473.23 Rp (dari perhitungan sebelumnya)

HPP per Unit:
  HPP = 17970 + 11473.23 = 29443.23 Rp

Margin per Unit:
  Margin = 50000 - 29443.23 = 20556.77 Rp
  Margin % = (20556.77 / 50000) × 100 = 41.11%

Jika beli 2 unit:
  Order.total_price = 50000 × 2 = 100000 Rp
  Order.total_hpp = 29443.23 × 2 = 58886.46 Rp
  Total margin = 100000 - 58886.46 = 41113.54 Rp
```

---

### Konversi Unit Bahan

**Sumber**: `app/Services/MaterialService.php` → `convertUnitPricing()`

Konversi otomatis dari unit kecil ke base unit untuk normalisasi harga.

| Unit Kecil      | Base Unit     | Konversi | Contoh                       |
| --------------- | ------------- | -------- | ---------------------------- |
| gram (g)        | kilogram (kg) | × 1000   | 12 Rp/gram → 12000 Rp/kg     |
| milliliter (ml) | liter (L)     | × 1000   | 25 Rp/ml → 25000 Rp/L        |
| pcs             | Pack          | × 100    | 1800 Rp/pcs → 180000 Rp/Pack |
| (lainnya)       | (sama)        | × 1      | Keep as-is                   |

**Contoh**: Material #2 (Telur Ayam)

- Input: unit=butir, price_per_unit=1800 Rp
- Setelah konversi: base_unit=Pack, price_per_base_unit=180000 Rp (asumsi 1 Pack = 100 butir)

---

## Service Classes & Business Logic

### OrderService (`app/Services/OrderService.php`)

#### createOrder($data)

Membuat order langsung dengan kurangkan stok otomatis (status = COMPLETED).

**Langkah**:

1. Hitung total kebutuhan material dari `$data['items']`
2. Lock material di DB (pessimistic lock)
3. Validasi stok cukup → InsufficientStockException jika kurang
4. Hitung order items (price, HPP) via `calculateOrderItemsData()`
5. Buat order + order_items
6. Kurangi stok (deduct) dan catat di stock_logs
7. Log ke business log

#### createPreOrder($data)

Membuat pre-order tanpa kurangi stok. Status = PRE_ORDER, set `scheduled_at`.

#### executePreOrder(Order $preOrder)

Konversi pre-order dari PRE_ORDER ke COMPLETED:

1. Recalculate kebutuhan material dari order items
2. Validasi stok + lock
3. Kurangi stok
4. Update status → COMPLETED, set order_date = now()

#### calculateRealTimeHPP(Product $product, int $quantity)

Hitung HPP real-time sesuai BOM dan harga material saat ini. Returnr total HPP untuk qty item tersebut.

#### calculateTotalNeeds(array $items)

Kalkulasi total kebutuhan material dari array items (aggregate qty per material).

---

### OverheadService (`app/Services/OverheadService.php`)

#### calculateOverheadPerUnit()

Kalkulasi overhead global berdasarkan `overhead_settings`. Lihat rumus di atas.

**Usage**: Dipanggil oleh `OrderService::calculateRealTimeHPP()` jika product tidak punya override `overhead_cost_per_unit`.

---

### StockService (`app/Services/StockService.php`)

#### addStock($data)

Tambah stok dengan catat log.

**Input**: `material_id`, `amount`, `description`

#### reduceStock($data)

Kurangi stok dengan validasi dan lock.

**Input**: `material_id`, `amount`, `description`

**Exception**: InsufficientStockException jika stok kurang

---

### TelegramService (`app/Services/TelegramService.php`)

#### sendMessage(string $message)

Kirim pesan ke Telegram Bot API (endpoint `/sendMessage`).

**Parse Mode**: HTML (subset HTML tag support)

**Timeout**: 10 detik

**Return**: Boolean success/fail

---

### MaterialService (`app/Services/MaterialService.php`)

#### convertUnitPricing(string $unit, float $pricePerUnit)

Konversi harga unit kecil → base unit. Return array `['base_unit' => ..., 'price_per_base_unit' => ...]`

---

## Job Queue & Asynchronous Processing

### SendTelegramReminderJob (`app/Jobs/SendTelegramReminderJob.php`)

**Trigger**: Ketika order dibuat (khususnya pre-order reminder menjelang scheduled_at).

**Parameter**: `$orderId`, `$payload` (isi pesan)

**Retry Policy**:

- Max tries: 5
- Backoff (exponential): 60s, 300s, 900s, 3600s

**Process**:

1. Buat entry `notification_logs` dengan status=processing
2. Kirim pesan via `TelegramService::sendMessage()`
3. Update log status → sent (success) atau failed (error)
4. Update order.is_notified = true jika sukses
5. Jika gagal semua, log final failure entry

---

## Data Validation & Error Handling

### Exception Hierarchy

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

### Validasi di Controller/Request Layer

Setiap endpoint API menggunakan `FormRequest` validation (jika ada) untuk:

- Type casting (integer, string, datetime)
- Required/optional field check
- Custom rules (misal: product exists, material valid, qty > 0)

### PHPUnit Tests

**Lokasi**: `tests/Unit/`, `tests/Feature/`

**Konfigurasi**: `phpunit.xml`

**Menjalankan tests**:

```bash
composer run test
```

Atau untuk test spesifik:

```bash
php artisan test tests/Unit/OrderServiceTest.php
```

### Test Data (Dummy Orders)

**OrderSeeder** membuat 200 dummy orders dengan perhitungan HPP otomatis yang sesuai runtime logic.

**Cara menjalankan**:

```bash
php artisan db:seed --class=OrderSeeder
```

**Atau semua seeder**:

```bash
php artisan migrate --seed
```

**Kegunaan data dummy**: Analisis laporan, testing performa, validasi HPP calculation, export Excel testing.

---

## Dependencies & Packages

### Production Dependencies (dari `composer.json`)

| Package                 | Versi   | Fungsi                                       |
| ----------------------- | ------- | -------------------------------------------- |
| laravel/framework       | ^12.0   | Framework utama                              |
| laravel/sanctum         | ^4.0    | API authentication (token-based)             |
| laravel/tinker          | ^2.10.1 | Interactive shell (artisan tinker)           |
| barryvdh/laravel-dompdf | ^3.1    | PDF generation (DOMPDF wrapper)              |
| maatwebsite/excel       | ^3.1    | Excel import/export (PhpSpreadsheet wrapper) |

### Development Dependencies

| Package              | Versi   | Fungsi                         |
| -------------------- | ------- | ------------------------------ |
| fakerphp/faker       | ^1.23   | Fake data generation (seeders) |
| laravel/pail         | ^1.2.2  | Real-time log monitoring       |
| laravel/pint         | ^1.24   | Code style fixer               |
| laravel/sail         | ^1.41   | Docker dev environment         |
| mockery/mockery      | ^1.6    | Mocking library (tests)        |
| nunomaduro/collision | ^8.6    | Error handler                  |
| phpunit/phpunit      | ^11.5.3 | Testing framework              |

### JavaScript Dependencies (dari `package.json`)

- **Vite**: Build tool & dev server
- Frontend framework/components (jika ada, sesuai `resources/js/`)

---

## Catatan Penting untuk Skripsi

### Asumsi Desain System

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

### Rekomendasi untuk Penelitian/Skripsi

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

### File Kunci untuk Referensi Skripsi

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

## Kesimpulan Dokumentasi

Dokumen ini menyediakan:

- **Spesifikasi lengkap** proyek (fitur, requirements, architecture)
- **Struktur database** dengan 9 tabel utama + relationships
- **Perhitungan formula** overhead dan HPP dengan contoh numerik
- **Route mapping** untuk web dan API endpoints
- **Service logic** untuk order, stok, material
- **Master data** 27 materials, 21 products, 200 dummy orders
- **Exception handling** dan validasi data

Gunakan dokumen ini sebagai **bahan referensi mentah** saat menulis bab analisis, desain, implementasi, dan evaluasi skripsi Anda.

---

**Terakhir update**: 22 Februari 2026

**Catatan**: File ini adalah dokumentasi teknis yang berubah seiring development. Pastikan selalu mereferensi ke kode sumber terbaru di repository untuk informasi paling akurat.
