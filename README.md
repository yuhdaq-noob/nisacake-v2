# NISA Cake Management System

Sistem manajemen katering kue dengan fitur lengkap untuk mengelola inventory, pesanan, dan laporan keuangan.

---

## Fitur Utama

- **Manajemen Produk** - CRUD produk kue dengan Bill of Materials (BOM)
- **Manajemen Inventory** - Pengelolaan bahan baku dengan sistem konversi unit otomatis
- **Manajemen Pesanan** - Support untuk order langsung dan pre-order
- **Kalkulasi HPP** - Perhitungan Harga Pokok Penjualan real-time berdasarkan BOM dan overhead
- **Laporan & Analytics** - Laporan omzet, margin, dan export ke Excel
- **Notifikasi Telegram** - Reminder otomatis untuk pre-order melalui bot Telegram

---

## Tech Stack

| Kategori       | Teknologi                            |
| -------------- | ------------------------------------ |
| Backend        | PHP 8.2+, Laravel 12.0               |
| Frontend       | HTML5, CSS3, JavaScript, Vite        |
| Database       | MySQL                                |
| Authentication | Laravel Sanctum (API), Session (Web) |
| External       | Telegram Bot API                     |

---

## Instalasi

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL 8.0+

### Langkah Instalasi

```
bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations & seeders
php artisan migrate --seed

# Build assets
npm run build
```

### Menjalankan Aplikasi

```
bash
# Development mode (Laravel + Vite + Queue + Logs)
composer run dev
```

Aplikasi akan tersedia di `http://localhost:8000`

---

## Struktur Database

Sistem ini menggunakan 9 tabel utama:

| Tabel                 | Deskripsi                               |
| --------------------- | --------------------------------------- |
| `users`               | Data pengguna sistem                    |
| `materials`           | Master bahan baku                       |
| `products`            | Master produk kue                       |
| `product_materials`   | Bill of Materials (relasi produk-bahan) |
| `orders`              | Header pesanan                          |
| `order_items`         | Detail item pesanan                     |
| `stock_logs`          | Riwayat perubahan stok                  |
| `material_price_logs` | Riwayat perubahan harga bahan           |
| `overhead_settings`   | Konfigurasi parameter overhead          |
| `notification_logs`   | Log pengiriman notifikasi               |

---

## Perhitungan HPP

Sistem menghitung HPP (Harga Pokok Penjualan) menggunakan formula:

```
HPP = Biaya Material + Overhead

Dimana:
- Biaya Material = Sum(price_per_unit x quantity_needed) dari BOM
- Overhead = Biaya gas + Listrik + Tenaga Kerja + Depreciation
```

Formula dan contoh perhitungan dapat dilihat di `docs/project_raw_data.md`.

---

## API Endpoints

### Authentication

| Method | Endpoint      | Deskripsi          |
| ------ | ------------- | ------------------ |
| POST   | `/api/login`  | Login dengan token |
| POST   | `/api/logout` | Logout             |

### Products

| Method | Endpoint             | Deskripsi          |
| ------ | -------------------- | ------------------ |
| GET    | `/api/products`      | List semua produk  |
| POST   | `/api/products`      | Tambah produk baru |
| PATCH  | `/api/products/{id}` | Update produk      |

### Materials

| Method | Endpoint                       | Deskripsi          |
| ------ | ------------------------------ | ------------------ |
| GET    | `/api/materials`               | List semua bahan   |
| PATCH  | `/api/materials/{id}/price`    | Update harga bahan |
| GET    | `/api/materials/price-history` | Riwayat harga      |

### Orders

| Method | Endpoint                            | Deskripsi           |
| ------ | ----------------------------------- | ------------------- |
| POST   | `/api/buat-pesanan`                 | Buat order langsung |
| POST   | `/api/jadwal-pesanan`               | Buat pre-order      |
| POST   | `/api/orders/{id}/execute-preorder` | Eksekusi pre-order  |
| PATCH  | `/api/orders/{id}/complete`         | Tandai selesai      |

### Reports & Settings

| Method | Endpoint                 | Deskripsi            |
| ------ | ------------------------ | -------------------- |
| GET    | `/api/reports`           | Data laporan         |
| GET    | `/api/overhead-settings` | Konfigurasi overhead |
| POST   | `/api/stocks/add`        | Tambah stok          |
| GET    | `/api/stocks/history`    | Riwayat stok         |

---

## Lisensi

MIT License

---

## Author

Dikembangkan untuk mendukung operasional NISA Cake Management.
