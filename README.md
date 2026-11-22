# CargoTrace: Global Cocoa & Coffee Shipment Visibility Platform
### "Connecting Global Coffee and Cocoa Markets"

[![Web Programming 2 - UAS Final Project](https://img.shields.io/badge/Course-Web%20Programming%202-blue.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B%20%7C%208.3%2B-indigo.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12%2B-red.svg)](https://laravel.com)
[![Tests](https://img.shields.io/badge/Tests-75%20Passed%20(280%20assertions)-brightgreen.svg)](tests/)
[![REST API](https://img.shields.io/badge/REST%20API-Sanctum%20Authenticated-emerald.svg)](postman/CargoTrace_API.postman_collection.json)
[![Map](https://img.shields.io/badge/Map-Leaflet%20%2B%20OSM%20Maritime-gold.svg)](resources/views/map/index.blade.php)
[![License](https://img.shields.io/badge/License-MIT-gray.svg)](LICENSE)

> **UAS Web Programming 2 • Portofolio Rekayasa Perangkat Lunak**  
> CargoTrace adalah platform pemantauan logistik dan pelacakan pengiriman kargo laut internasional (*international sea freight*) yang dirancang khusus untuk komoditas **Kakao (Cocoa)** dan **Kopi (Coffee)** dari pelabuhan asal (*origin port*) hingga pelabuhan tujuan (*destination port*).

---

### 📚 Dokumentasi Teknis Lengkap
- 🗺️ **[Peta Kargo Global & Posisi Barang Saat Ini](http://127.0.0.1:8000/map)**
- 🔍 **[Portal Lacak Kargo Publik](http://127.0.0.1:8000/track)**
- 📮 **[Postman Collection v2.1.0](postman/CargoTrace_API.postman_collection.json)**
- 📊 **[Entity-Relationship Diagram & Data Dictionary](docs/erd.md)**
- 🏛️ **[Arsitektur Layered Service & State Machine](docs/architecture.md)**
- 📡 **[Spesifikasi Lengkap REST API](docs/api-documentation.md)**
- 🎯 **[UAS Defense & Code Ownership Interview Q&A](docs/uas-defense-guide.md)**
- 📋 **[Dokumentasi Tahap 1: Analisis & Perancangan](docs/tahap-1-analisis-dan-perancangan.md)**
- 📝 **[Engineering Activity Log](docs/engineering-activity-log.md)**

---

## 1. Domain Bisnis & Batasan Sistem

CargoTrace dirancang khusus untuk mencatat, mengelola, memantau, dan melacak pergerakan kontainer produk kakao dan kopi melalui jalur pelayaran maritim internasional.

### A. Komoditas yang Dikelola
* **COCOA / KAKAO:**
  - *Fermented Cocoa Beans* (Biji Kakao Terfermentasi - HS 1801.00)
  - *Dried Cocoa Beans* (Biji Kakao Kering - HS 1801.00)
  - *Cocoa Butter* (Lemak Kakao - HS 1804.00)
  - *Cocoa Powder* (Bubuk Kakao Alkalized - HS 1805.00)
  - *Cocoa Liquor / Mass* (Pasta Kakao Alami - HS 1803.10)
* **COFFEE / KOPI:**
  - *Green Arabica Coffee Beans* (Biji Kopi Hijau Arabika: Gayo, Mandheling, Toraja - HS 0901.11)
  - *Green Robusta Coffee Beans* (Biji Kopi Hijau Robusta: Lampung, Vietnam - HS 0901.11)
  - *Specialty Roasted Arabica* (Kopi Sangrai Arabika - HS 0901.21)
  - *Roasted Robusta Coffee* (Kopi Sangrai Robusta - HS 0901.21)
  - *Liberica Coffee Beans* (Biji Kopi Liberika Langka - HS 0901.11)

### B. Batasan Sistem (Strict Boundaries)
* ❌ **BUKAN MARKETPLACE & BUKAN E-COMMERCE:** Tidak ada keranjang belanja, checkout, invoice pembayaran konsumen, atau transaksi jual-beli retail.
* ❌ **TIDAK ADA MANAJEMEN PERTANIAN:** Tidak ada pencatatan panen pohon atau kebun.
* ❌ **TIDAK ADA GUDANG KOMPLEKS YANG TIDAK RELEVAN.**
* ✅ **FOKUS UTAMA:** *Shipment visibility*, pelacakan posisi kargo maritim secara real-time (*"di mana barang itu sudah sampai"*), status transisi *state machine*, kalkulasi *ETA & delay* deterministik, penanganan *incident exceptions*, serta *role-based operational dashboard*.

### C. Jalur & Koridor Pelayaran Utama
* **Belawan Port / Tanjung Priok (Indonesia) &rarr; Port of Rotterdam (Belanda)** via Selat Malaka & Terusan Suez.
* **Tanjung Perak (Indonesia) &rarr; Port of Hamburg (Jerman)** via Samudra Hindia.
* **Tema Port (Ghana) / Port of Abidjan (Pantai Gading) &rarr; Port of Antwerp (Belgia)** via Koridor Atlantik Timur.
* **Haiphong Port (Vietnam) &rarr; Port of Genoa (Italia)** via Laut Merah & Mediterania.
* **Djibouti / Port of Mombasa (Ethiopia/Afrika Timur) &rarr; Port of New York (Amerika Serikat)**.

---

## 2. Kredensial Pengujian & Akun Demo (Quick Login)

Untuk keperluan pengujian UAS oleh dosen penilai, sistem telah dilengkapi dengan tombol *One-Click Quick Login* pada halaman `/login`:

| Peran (Role) | Email Akun | Kata Sandi | Lingkup Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@cargotrace.com` | `password123` | Akses penuh: manajemen kargo, carrier, pelabuhan, pengguna, audit log, system rules & preset skenario. |
| **Operator Logistik** | `operator@cargotrace.com` | `password123` | Operasional: input pengiriman, update transisi status, pencatatan waypoint AIS, pencatatan & investigasi insiden/eksepsi. |
| **Customer (Importer/Exporter)** | `customer1@cargotrace.com` | `password123` | Terisolasi: hanya dapat melihat dan melacak kargo milik perusahaannya sendiri (*query-scoped data segregation*). |
| **Customer 2** | `customer2@cargotrace.com` | `password123` | Akun pembanding untuk menguji pembuktian data isolation antar customer. |

---

## 3. Fitur Unggulan Rekayasa Perangkat Lunak

### A. Peta Kargo Interaktif & Posisi Barang ("Di Mana Barang Itu Sudah Sampai")
* **Peta Armada Global (`GET /map`):** Menampilkan seluruh kapal laut pembawa kakao dan kopi di lautan dunia menggunakan Leaflet.js dan OpenStreetMap. Dilengkapi filter komoditas (Coffee vs Cocoa), status pelayaran, dan maritime carrier.
* **Penanda Radar Berkedip (*Pulsing Vessel Beacon*):** Ikon kapal emas dengan animasi CSS radar ping (`animate-ping`) yang menunjukkan secara persis lokasi kapal saat ini (*current latitude & longitude*).
* **Rute Polylines Dinamis:** Menghubungkan pelabuhan asal, titik lintang selat/terusan (*waypoints*), posisi kapal terkini, hingga pelabuhan tujuan.
* **HUD Posisi Kargo Real-Time:** Menampilkan nama lokasi perairan (cth: *Malacca Strait - Northbound Transit Lane* atau *Suez Canal Transit Zone*), persentase perjalanan tuntas, dan waktu laporan terakhir.
* **Portal Pelacakan Publik (`GET /track` dan `GET /track/{tracking_number}`):** Memungkinkan consignee/importir melacak nomor kargo (cth: `CTC-2026-0001`) tanpa harus login.

### B. State Machine Lifecycle & Append-Only Immutability
* **Siklus Hidup Terstandarisasi:**  
  $$\text{CREATED} \longrightarrow \text{BOOKED} \longrightarrow \text{PICKED\_UP} \longrightarrow \text{IN\_TRANSIT} \longrightarrow \text{DELIVERED}$$
* **Aturan Pembatalan:** Status `CANCELLED` hanya diperbolehkan sebelum kapal berlayar (`CREATED`, `BOOKED`, `PICKED_UP`). Kapal yang sedang berlayar (`IN_TRANSIT`) atau sudah tiba (`DELIVERED`) tidak dapat dibatalkan sembarangan.
* **Append-Only Immutability:** Event tracking (`tracking_events`) terkunci secara immutable pada model boot level: operasi `update()` dan `delete()` dilarang untuk menjaga validitas bukti riwayat logistik.

### C. Engine Kalkulasi Keterlambatan Deterministik & Eksepsi
* **Formula Keterlambatan:**  
  $$\text{delay\_hours} = \max(0, \text{ETA} - \text{STA})$$
* **Klasifikasi Keparahan:**
  - `NONE`: 0 jam
  - `LOW`: $0 < \text{delay\_hours} \le 24$ jam
  - `MEDIUM`: $24 < \text{delay\_hours} \le 72$ jam
  - `HIGH`: $> 72$ jam
* **8 Tipe Eksepsi Operasional:** `WEATHER_DELAY`, `PORT_CONGESTION`, `CUSTOMS_HOLD`, `CONTAINER_DAMAGE`, `TEMPERATURE_EXCURSION`, `DOCUMENTATION_ISSUE`, `ACCIDENT`, `TECHNICAL_FAILURE`.
* **Workflow Penyelesaian:** `OPEN` $\rightarrow$ `INVESTIGATING` $\rightarrow$ `RESOLVED`.
* **Artisan CLI Delay Checker:**
  ```bash
  php artisan cargo:check-delays
  # Alias yang didukung:
  php artisan shipments:check-delays
  php artisan check:delayed-shipments
  ```

### D. Integrasi Layanan Eksternal (External APIs)
* **Open-Meteo Marine Weather API:** Mengambil data cuaca real-time (temperatur, kelembapan udara, kecepatan angin, kondisi laut) berdasarkan koordinat posisi kapal atau pelabuhan, dengan sistem *in-memory caching* 10 menit dan fallback *offline-mode*.
* **OpenStreetMap Nominatim Geocoding:** Pencarian koordinat pelabuhan global otomatis untuk kemudahan operator.

### E. REST API & Postman Collection v2.1.0
* Menggunakan **Laravel Sanctum** Bearer Token Authentication.
* File koleksi Postman siap pakai di: [`postman/CargoTrace_API.postman_collection.json`](postman/CargoTrace_API.postman_collection.json).
* 16+ Endpoint lengkap mencakup: Auth, Shipments CRUD, Status Transition, Tracking Events Append, Exception Logging & Resolution, Carriers & Locations Master Data, dan External Services.

---

## 4. Panduan Instalasi & Menjalankan Aplikasi

### Kebutuhan Sistem:
- PHP 8.2 atau lebih baru (dengan ekstensi `pdo_sqlite` atau `pdo_mysql`, `curl`, `mbstring`)
- Composer 2.x
- Git

### Langkah Menjalankan:
```bash
# 1. Clone repository
git clone https://github.com/nurulaini23ag-cyber/CargoTrace.git
cd CargoTrace

# 2. Install dependensi PHP
composer install

# 3. Konfigurasi file environment
cp .env.example .env
php artisan key:generate

# 4. Migrasi dan Seeding Database Uji (15 Kargo, 5 Carrier, 13 Pelabuhan, 55 Waypoint)
php artisan migrate:fresh --seed

# 5. Jalankan server lokal
php artisan serve
```
Akses platform melalui browser: **`http://127.0.0.1:8000`**.

---

## 5. Ringkasan Pengujian Otomatis (Automated Test Suite)

Seluruh 75 pengujian fitur (*feature & unit tests*) berhasil lulus 100% tanpa ada kegagalan:

```text
Tests:    75 passed (280 assertions)
Duration: 5.30s
Status:   ALL TESTS PASSED
```

Jalankan pengujian kapan saja dengan:
```bash
php artisan test
# Atau:
php vendor/phpunit/phpunit/phpunit
```

---

## 6. Struktur Matriks Kepatuhan PowerPoint UAS

| No | Spesifikasi PowerPoint Tugas UAS | Implementasi dalam CargoTrace | Status |
| :---: | :--- | :--- | :---: |
| 1 | **Domain Bisnis Kargo Kakao & Kopi** | Model `Shipment` & `ShipmentItem` khusus komoditas Cocoa & Coffee (grade, varietas, kadar air, suhu, hs_code) | **100% LULUS** |
| 2 | **Autentikasi & RBAC 3 Role** | Admin, Operator, Customer dengan role middleware & *scoped query isolation* | **100% LULUS** |
| 3 | **State Machine & Status Validasi** | Transisi terstandarisasi dengan proteksi pembatalan dan append-only event immutability | **100% LULUS** |
| 4 | **Formula Delay & Severity Matrix** | Delay deterministik dengan 4 tingkatan keparahan dan rule preset group skenario | **100% LULUS** |
| 5 | **Incident Exception Workflow** | 8 tipe eksepsi maritim, status Open/Investigating/Resolved, penyesuaian ETA otomatis | **100% LULUS** |
| 6 | **Peta Kargo & Posisi Barang Terkini** | Leaflet.js + OpenStreetMap, radar beacon berkedip, peta armada global `/map`, dan lacak publik `/track` | **100% LULUS** |
| 7 | **Integrasi API Eksternal** | Open-Meteo Weather API & Nominatim Geocoding dengan auto-caching dan offline fallback | **100% LULUS** |
| 8 | **REST API & Postman Collection** | Sanctum bearer token, 16+ endpoint, file `postman/CargoTrace_API.postman_collection.json` v2.1.0 | **100% LULUS** |
| 9 | **Artisan CLI Delay Checker** | Command `cargo:check-delays` dengan alias `shipments:check-delays` | **100% LULUS** |
| 10 | **Pengujian Otomatis (Testing)** | 75 skenario pengujian PHPUnit lengkap mencakup otorisasi, lifecycle, API, delay, dan peta | **100% LULUS** |
| 11 | **Security Hardening** | CSRF protection, SQL injection prevention, XSS escaping, rate limiting, and RBAC isolation | **100% LULUS** |
| 12 | **Dokumentasi & Git Repository** | README lengkap, ERD, arsitektur, activity log, dan commit history terstruktur di GitHub | **100% LULUS** |

---
*Dikembangkan dengan standar rekayasa perangkat lunak internasional.*  
*Repository: [https://github.com/nurulaini23ag-cyber/CargoTrace](https://github.com/nurulaini23ag-cyber/CargoTrace)*
