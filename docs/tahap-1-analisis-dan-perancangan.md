# DOKUMEN TAHAP 1: ANALISIS DAN PERANCANGAN SISTEM
## CargoTrace — Global Cocoa & Coffee Shipment Visibility Platform

**Versi:** 1.0  
**Tanggal:** 22 November 2026  
**Domain Bisnis:** Cocoa & Coffee International Sea Freight Logistics  
**Tujuan Proyek:** Proyek Portofolio & Tugas UAS Pemrograman Web Berbasis Laravel

---

### 1. Ringkasan Eksekutif & Identitas Aplikasi
- **Nama Aplikasi:** CargoTrace
- **Tagline:** Global Cocoa & Coffee Shipment Visibility Platform
- **Fokus Domain:** Pelacakan dan visibilitas operasional kargo kontainer laut (ocean freight) khusus komoditas Kakao (Cocoa) dan Kopi (Coffee).
- **Batasan Tegas:** 
  - Bukan e-commerce/marketplace (tidak ada keranjang belanja, checkout, transaksi pembayaran).
  - Bukan sistem inventaris gudang kompleks atau manajemen pertanian.
  - Fokus 100% pada *Shipment Visibility, Tracking Timeline, Delay Detection, Exception Management, dan Operational Analytics*.

---

### 2. Domain Produk & Rute Logistik Maritim

#### A. Komoditas Kakao (Cocoa)
1. *Fermented Cocoa Beans* (Biji Kakao Fermentasi - Standard Bulk/Bags)
2. *Dried Cocoa Beans* (Biji Kakao Kering)
3. *Cocoa Butter* (Lemak Kakao - Perlu kontrol suhu kontainer reefer)
4. *Cocoa Powder* (Bubuk Kakao Industri)

#### B. Komoditas Kopi (Coffee)
1. *Green Arabica Coffee Beans* (Biji Kopi Hijau Arabika - Specialty/Grade 1)
2. *Green Robusta Coffee Beans* (Biji Kopi Hijau Robusta - Komersial/Tinggi Kafein)
3. *Roasted Arabica Coffee* (Kopi Sangrai Arabika)
4. *Roasted Robusta Coffee* (Kopi Sangrai Robusta)

#### C. Rute Pelayaran & Hub Pelabuhan Maritim Utama
- **Indonesia (ID) → Belanda (NL):** Belawan / Tanjung Priok → Port of Rotterdam (±24–28 hari via Selat Malaka & Terusan Suez).
- **Indonesia (ID) → Jerman (DE):** Tanjung Perak / Belawan → Port of Hamburg.
- **Indonesia (ID) → Italia (IT):** Tanjung Priok → Port of Genoa.
- **Ghana (GH) → Jerman (DE):** Port of Tema → Port of Hamburg (±18–22 hari).
- **Pantai Gading (CI) → Belanda (NL):** Port of Abidjan → Port of Rotterdam.
- **Vietnam (VN) → Belgia (BE):** Port of Da Nang / Hai Phong → Port of Antwerp.
- **Ethiopia (ET) → Amerika Serikat (US):** Port of Djibouti (Ethiopia Gateway) → Port of New York & New Jersey.

---

### 3. Kebutuhan Fungsional (Functional Requirements - FR)
- **FR-01 (Auth & RBAC):** Autentikasi aman berbasis sesi dan token Sanctum dengan 3 role: `admin`, `operator`, `customer`.
- **FR-02 (Shipment Management):** Pengelolaan shipment dengan nomor unik berformat `CTC-2026-XXXX`, mencakup relasi customer, carrier, origin, destination, cargo type, berat, volume, ETA, dan instruksi penanganan.
- **FR-03 (Shipment Items):** Pencatatan multi-item per shipment mencakup jenis produk (Cocoa/Coffee), varietas/grade, jumlah kemasan, berat bersih, kadar air (*moisture content* %), dan spesifikasi khusus.
- **FR-04 (Lifecycle State Machine):** Transisi status terstruktur: `CREATED` → `BOOKED` → `PICKED_UP` → `IN_TRANSIT` → `DELIVERED` (dengan status pembatalan terminal `CANCELLED`). Transisi harus divalidasi dan dicatat otomatis ke riwayat audit.
- **FR-05 (Append-Only Tracking Timeline):** Setiap pergerakan kargo dicatat dalam `tracking_events` secara kronologis dengan validasi waktu yang tidak boleh mundur secara tidak masuk akal.
- **FR-06 (ETA & Deterministic Delay Detection):** Sistem membandingkan `estimated_arrival` dengan `expected_arrival` secara otomatis. Jika terlambat, klasifikasikan severity:
  - <= 6 jam: `LOW`
  - > 6 s/d 24 jam: `MEDIUM`
  - > 24 jam: `HIGH`
  - Threshold dapat dikonfigurasi melalui sistem pengaturan (`system_settings`).
- **FR-07 (Exception Management):** Pencatatan dan eskalasi 6 jenis insiden (`CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`) dengan siklus status `OPEN` → `INVESTIGATING` → `RESOLVED`.
- **FR-08 (Interactive Map Visualization):** Integrasi Leaflet.js dan OpenStreetMap menampilkan titik pelabuhan asal, pelabuhan tujuan, koordinat posisi kargo terkini, popup informasi, dan visualisasi rute maritim dengan tanda visual *"Simulated Route"*.
- **FR-09 (External API & Resiliency):** Integrasi layanan Geocoding (Nominatim OpenStreetMap) dan Prakiraan Cuaca Pelabuhan (Open-Meteo API) dengan timeout handling, circuit fallback, dan in-memory/file caching.
- **FR-10 (Operational Dashboard):** Dashboard metrik real-time berbasis agregasi database (bukan angka statis) dengan Chart.js untuk admin, operator, dan customer.
- **FR-11 (In-App Notifications):** Notifikasi in-app untuk pembaruan status shipment, keterlambatan, dan resolusi insiden.
- **FR-12 (Audit Log):** Pencatatan menyeluruh terhadap mutasi data penting (aktor, entitas, ID, payload sebelum dan sesudah, IP, user-agent, timestamp).
- **FR-13 (REST API):** Endpoint JSON lengkap dengan standardisasi format response, pagination, filter, dan error envelope.

---

### 4. Kebutuhan Non-Fungsional (Non-Functional Requirements - NFR)
- **NFR-01 (Security):** Proteksi CSRF di seluruh form Blade, sanitasi input form request, prepared statements (Eloquent ORM), hashing kata sandi Argon2id/Bcrypt, pembatasan rate limit API (Sanctum 60 req/min).
- **NFR-02 (Performance):** Respon halaman <= 500ms pada lingkungan lokal, eager loading (`with()`) untuk meniadakan masalah N+1 queries, composite indexing pada foreign keys dan kolom status.
- **NFR-03 (Reliability & Determinism):** Business logic shipment diisolasi di Service Layer (`ShipmentService`, `TrackingService`, `EtaService`, `ExceptionService`) sehingga sepenuhnya dapat diuji secara otomatis melalui unit test.
- **NFR-04 (Usability & Responsiveness):** Antarmuka responsif (Mobile, Tablet, Desktop) menggunakan Tailwind CSS dengan palet warna nautikal/kopi: *Deep Navy (#0F172A), Ocean Blue (#0284C7), Cocoa Brown (#795548), Warm Beige (#E8D7B9), Off-White (#F8FAFC), Gold Accent (#C49A55)*.
- **NFR-05 (Maintainability):** Kode mengikuti prinsip Clean Code, PSR-12, arsitektur MVC + Service Layer, tanpa dependensi frontend JavaScript heavy (React/Vue), murni Blade + Vanilla JS.

---

### 5. Use Case Matrix per Role

| ID Use Case | Aktor | Deskripsi Aksi | Pre-kondisi |
|---|---|---|---|
| **UC-01** | Admin | Mengelola master data User, Carrier, Location | Login sebagai Admin |
| **UC-02** | Admin | Mengatur parameter Delay Threshold & Konfigurasi Sistem | Login sebagai Admin |
| **UC-03** | Admin | Memantau System Health & Audit Log | Login sebagai Admin |
| **UC-04** | Operator | Mendaftarkan Shipment baru & mencatat Shipment Items (Kakao/Kopi) | Login sebagai Operator / Admin |
| **UC-05** | Operator | Melakukan Transisi Status Siklus Pengiriman | Shipment berada dalam status valid |
| **UC-06** | Operator | Menambahkan Tracking Event kronologis beserta koordinat | Shipment aktif |
| **UC-07** | Operator | Merekam & menginvestigasi Exception (Kondisi Cuaca/Kongesti) | Terjadi insiden operasional |
| **UC-08** | Customer | Melihat daftar dan detail shipment milik akunnya sendiri | Login sebagai Customer |
| **UC-09** | Customer | Melacak status pengiriman real-time, peta rute, dan ETA | Shipment terdaftar atas customer tsb |
| **UC-10** | Customer | Menerima notifikasi perubahan status & peringatan keterlambatan | Ada mutasi event pada shipment |
| **UC-11** | Publik | Melacak kargo via nomor pelacakan (`/track`) tanpa login | Nomor kargo valid |

---

### 6. Diagram Alur Kerja Utama (Shipment Workflow)
```
[Dibuat: CREATED]
       │
       ▼
[Dipesan: BOOKED] ── (Assign Carrier & Schedule)
       │
       ▼
[Dimuat: PICKED_UP] ── (Container Stuffed & Sealed at Port of Origin)
       │
       ▼
[Berlayar: IN_TRANSIT] ◄─── (Tracking Events, Weather Checks & ETA Recalculation)
       │                        │
       │                   [EXCEPTION DETECTED]
       │                   (Customs Hold / Weather / Port Congestion)
       │                        │
       │                        ▼
       │                   [EXCEPTION RESOLVED]
       │                        │
       ▼                        ▼
[Tiba & Selesai: DELIVERED] ── (Terminal State, Locked from Rollback)
```

---

### 7. Entity Relationship Diagram (ERD) & Kardinalitas

```
┌──────────────┐         1:N         ┌───────────────┐
│    users     │────────────────────<│ notifications │
└──────┬───────┘                     └───────────────┘
       │ 1:N (as customer)
       ▼
┌──────────────┐         1:N         ┌───────────────────┐
│  shipments   │────────────────────<│   shipment_items  │
└──────┬───────┘                     └───────────────────┘
       │ 1:N
       ├────────────────────────────<┌───────────────────┐
       │                             │  tracking_events  │
       │ 1:N                         └───────────────────┘
       ├────────────────────────────<┌───────────────────┐
       │                             │shipment_exceptions│
       │ N:1                         └───────────────────┘
       ├────────────────────────────>┌───────────────────┐
       │                             │     carriers      │
       │ N:1 (origin/dest)           └───────────────────┘
       └────────────────────────────>┌───────────────────┐
                                     │     locations     │
                                     └───────────────────┘
```

#### Kardinalitas Relasi:
1. `users` (1) ───< (N) `shipments`: Satu customer memiliki banyak shipment; setiap shipment terikat ke 1 customer.
2. `carriers` (1) ───< (N) `shipments`: Satu maskapai/pelayaran melayani banyak shipment.
3. `locations` (1) ───< (N) `shipments`: Lokasi dapat menjadi origin atau destination dari banyak shipment.
4. `shipments` (1) ───< (N) `shipment_items`: Satu shipment memuat satu atau lebih varian komoditas kakao/kopi.
5. `shipments` (1) ───< (N) `tracking_events`: Satu shipment memiliki riwayat jejak perjalanan kronologis.
6. `shipments` (1) ───< (N) `shipment_exceptions`: Satu shipment dapat mengalami beberapa insiden operasional.
7. `users` (1) ───< (N) `notifications`: Setiap user memiliki notifikasi in-app tersendiri.
8. `users` (1) ───< (N) `audit_logs`: Aksi perubahan dicatat mereferensikan user yang bertindak.

---

### 8. Data Dictionary Lengkap

#### A. Tabel `users`
| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Pengenal unik pengguna |
| `name` | VARCHAR(255) | NOT NULL | Nama lengkap pengguna / PIC |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Alamat email login |
| `password` | VARCHAR(255) | NOT NULL | Hash kata sandi |
| `role` | VARCHAR(30) | NOT NULL, DEFAULT 'customer' | `admin`, `operator`, `customer` |
| `phone` | VARCHAR(50) | NULLABLE | Nomor kontak |
| `company_name`| VARCHAR(255) | NULLABLE | Nama perusahaan / entitas bisnis |
| `timestamps` | TIMESTAMP | NULLABLE | Created at & updated at |

#### B. Tabel `carriers`
| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Pengenal unik carrier |
| `code` | VARCHAR(20) | UNIQUE, NOT NULL | Kode pelayaran (e.g. `MAEU`, `MSCU`) |
| `name` | VARCHAR(255) | NOT NULL | Nama perusahaan kapal laut |
| `service_type`| VARCHAR(50) | NOT NULL | Jenis layanan (`OCEAN_FREIGHT`) |
| `contact_email`| VARCHAR(255)| NULLABLE | Kontak operasional carrier |
| `phone` | VARCHAR(50) | NULLABLE | Telepon carrier |
| `is_active` | BOOLEAN | DEFAULT TRUE | Status ketersediaan carrier |

#### C. Tabel `locations`
| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Pengenal unik lokasi pelabuhan |
| `code` | VARCHAR(10) | UNIQUE, NOT NULL | UN/LOCODE (e.g. `IDBLW`, `NLRTM`, `DEHAM`) |
| `name` | VARCHAR(255) | NOT NULL | Nama pelabuhan maritim |
| `city` | VARCHAR(100) | NOT NULL | Kota pelabuhan |
| `country` | VARCHAR(100) | NOT NULL | Negara |
| `country_code`| VARCHAR(3) | NOT NULL | Kode ISO 2/3 karakter |
| `latitude` | DECIMAL(10, 7)| NOT NULL | Garis lintang untuk peta |
| `longitude` | DECIMAL(10, 7)| NOT NULL | Garis bujur untuk peta |
| `type` | VARCHAR(50) | NOT NULL | `PORT`, `CONTAINER_TERMINAL` |

#### D. Tabel `shipments`
| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | ID unik internal |
| `shipment_number`| VARCHAR(50)| UNIQUE, INDEX, NOT NULL | Kode pelacakan (e.g. `CTC-2026-0001`) |
| `customer_id` | BIGINT UNSIGNED | FK ke `users(id)` | Pemilik barang / consignee |
| `carrier_id` | BIGINT UNSIGNED | FK ke `carriers(id)` | Operator kapal pengangkut |
| `origin_id` | BIGINT UNSIGNED | FK ke `locations(id)` | Pelabuhan muat asal |
| `destination_id`| BIGINT UNSIGNED | FK ke `locations(id)` | Pelabuhan bongkar tujuan |
| `cargo_type` | VARCHAR(50) | NOT NULL | `COCOA_BEANS`, `COFFEE_BEANS`, dll |
| `weight` | DECIMAL(10, 2)| NOT NULL | Berat kargo kotor (KG/MT) |
| `volume` | DECIMAL(10, 2)| NOT NULL | Volume kargo (CBM) |
| `current_status`| VARCHAR(30) | INDEX, NOT NULL | `CREATED`, `BOOKED`, `PICKED_UP`, `IN_TRANSIT`, `DELIVERED`, `CANCELLED` |
| `expected_departure`| DATETIME| NOT NULL | Jadwal keberangkatan resmi |
| `expected_arrival` | DATETIME| NOT NULL | Jadwal kedatangan rencana |
| `estimated_arrival`| DATETIME| NULLABLE | Estimasi kedatangan dinamis (ETA) |
| `actual_departure` | DATETIME| NULLABLE | Realisasi waktu tolak pelabuhan asal |
| `actual_arrival` | DATETIME| NULLABLE | Realisasi waktu sandar pelabuhan tujuan |
| `is_delayed` | BOOLEAN | DEFAULT FALSE | Status keterlambatan terhitung |
| `delay_hours` | DECIMAL(8, 2) | DEFAULT 0 | Durasi selisih keterlambatan (jam) |
| `delay_severity`| VARCHAR(20) | DEFAULT 'NONE' | `NONE`, `LOW`, `MEDIUM`, `HIGH` |
| `delay_reason` | TEXT | NULLABLE | Keterangan penyebab keterlambatan |
| `special_instructions`| TEXT | NULLABLE | Catatan suhu reefer / ventilasi biji |

#### E. Tabel `shipment_items`
| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | ID item |
| `shipment_id` | BIGINT UNSIGNED | FK ke `shipments(id)` ON DELETE CASCADE | Relasi shipment |
| `product_category`| VARCHAR(50)| NOT NULL | `COCOA` atau `COFFEE` |
| `product_name` | VARCHAR(255) | NOT NULL | Nama komoditas |
| `variety` | VARCHAR(100) | NULLABLE | Varietas (e.g. Forastero, Arabica Typica) |
| `grade` | VARCHAR(50) | NULLABLE | Kualitas (Specialty Grade 1, Standard Fairtrade) |
| `package_type` | VARCHAR(50) | NOT NULL | `JUTE_BAG_60KG`, `BULK_LINER`, `BOX` |
| `package_count`| INT | NOT NULL | Jumlah kemasan |
| `net_weight` | DECIMAL(10, 2)| NOT NULL | Berat bersih |
| `moisture_content`| DECIMAL(5, 2)| NULLABLE | Kadar air (%) untuk QC biji |
| `temperature_target`| DECIMAL(5, 2)| NULLABLE | Target suhu kontainer (khusus mentega kakao) |

#### F. Tabel `tracking_events`
| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | ID event pelacakan |
| `shipment_id` | BIGINT UNSIGNED | FK ke `shipments(id)` ON DELETE CASCADE | Relasi shipment |
| `event_type` | VARCHAR(50) | NOT NULL | Tipe aktivitas operasional |
| `status` | VARCHAR(30) | NOT NULL | Status shipment saat event dicatat |
| `event_time` | DATETIME | INDEX, NOT NULL | Waktu nyata kejadian operasional |
| `location_id` | BIGINT UNSIGNED | FK ke `locations(id)` NULLABLE | Referensi lokasi pelabuhan |
| `location_name`| VARCHAR(255) | NOT NULL | Nama lokasi / titik koordinat |
| `latitude` | DECIMAL(10, 7)| NULLABLE | Koordinat lintang |
| `longitude` | DECIMAL(10, 7)| NULLABLE | Koordinat bujur |
| `description` | TEXT | NOT NULL | Uraian detail logistik |
| `is_exception_event`| BOOLEAN| DEFAULT FALSE | Penanda insiden keterlambatan |
| `created_by` | BIGINT UNSIGNED | FK ke `users(id)` | Petugas pencatat event |

#### G. Tabel `shipment_exceptions`
| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | ID insiden exception |
| `shipment_id` | BIGINT UNSIGNED | FK ke `shipments(id)` ON DELETE CASCADE | Relasi shipment |
| `exception_type`| VARCHAR(50) | NOT NULL | `CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE` |
| `severity` | VARCHAR(20) | NOT NULL | `LOW`, `MEDIUM`, `HIGH` |
| `status` | VARCHAR(30) | DEFAULT 'OPEN' | `OPEN`, `INVESTIGATING`, `RESOLVED` |
| `description` | TEXT | NOT NULL | Rincian hambatan logistik |
| `detected_at` | DATETIME | NOT NULL | Waktu deteksi insiden |
| `resolved_at` | DATETIME | NULLABLE | Waktu penyelesaian insiden |
| `resolved_by` | BIGINT UNSIGNED | FK ke `users(id)` NULLABLE | Petugas yang menyelesaikan |
| `resolution_notes`| TEXT | NULLABLE | Tindakan mitigasi yang diambil |

#### H. Tabel `notifications` & `audit_logs` & `system_settings`
- `notifications`: Menyimpan pesan in-app terikat ke `user_id` dengan status `is_read`.
- `audit_logs`: Menyimpan jejak mutasi data (`actor_id`, `action`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `created_at`).
- `system_settings`: Konfigurasi threshold keterlambatan (dalam jam) dan aturan penalti delay.

---

### 9. Business Rules Spesifik

1. **Aturan Siklus Hidup Kargo (Shipment Lifecycle):**
   - Transisi hanya diizinkan searah: `CREATED` → `BOOKED` → `PICKED_UP` → `IN_TRANSIT` → `DELIVERED`.
   - Transisi ke `CANCELLED` hanya dapat dilakukan sebelum status mencapai `IN_TRANSIT`.
   - Status `DELIVERED` adalah status terminal absolut; tidak boleh dikembalikan ke status sebelumnya tanpa intervensi Administrator.
   - Setiap mutasi status wajib diapit dalam database transaction dan secara otomatis memicu pembuatan entri `tracking_events` dan `audit_logs`.

2. **Aturan Integritas Jejak Kronologis (Tracking Chronology):**
   - Waktu kejadian (`event_time`) dari tracking event baru tidak boleh lebih awal dari event terakhir yang tercatat pada shipment yang sama.
   - Posisi koordinat kargo terkini (*current location*) pada peta selalu merujuk pada `latitude` dan `longitude` dari tracking event terbaru yang valid.

3. **Aturan Perhitungan Keterlambatan Deterministik (ETA & Delay Detection):**
   - Selisih waktu keterlambatan dihitung:  
     $$\Delta t = \text{estimated\_arrival} - \text{expected\_arrival}$$
   - Jika $\Delta t \le 0$, maka `is_delayed = false`, `delay_severity = 'NONE'`, `delay_hours = 0`.
   - Jika $\Delta t > 0$, maka `is_delayed = true`, `delay_hours =` selisih dalam jam.
   - Severity ditentukan berdasarkan parameter dinamis:
     - $0 < \text{delay\_hours} \le 6 \implies \text{LOW}$
     - $6 < \text{delay\_hours} \le 24 \implies \text{MEDIUM}$
     - $\text{delay\_hours} > 24 \implies \text{HIGH}$

4. **Aturan Manajemen Hambatan (Exception Workflow):**
   - Insiden baru selalu bermula pada status `OPEN`.
   - Hanya Operator atau Admin yang dapat memperbarui status menjadi `INVESTIGATING` dan `RESOLVED`.
   - Ketika status exception beralih ke `RESOLVED`, kolom `resolved_at`, `resolved_by`, dan `resolution_notes` wajib terisi.

---

### 10. Rancangan Arsitektur Laravel & Struktur Folder

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/                     # REST API Controllers (Sanctum)
│   │   │   ├── AuthApiController.php
│   │   │   ├── CarrierApiController.php
│   │   │   ├── ExceptionApiController.php
│   │   │   ├── LocationApiController.php
│   │   │   ├── ShipmentApiController.php
│   │   │   └── TrackingApiController.php
│   │   └── Web/                     # Blade Web Controllers
│   │       ├── AuditLogController.php
│   │       ├── AuthController.php
│   │       ├── CarrierController.php
│   │       ├── DashboardController.php
│   │       ├── ExceptionController.php
│   │       ├── LocationController.php
│   │       ├── NotificationController.php
│   │       ├── PublicTrackingController.php
│   │       ├── ShipmentController.php
│   │       ├── SystemSettingController.php
│   │       ├── TrackingController.php
│   │       └── UserController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php       # RBAC Authorization Filter
│   ├── Requests/                    # Server-side Form Request Validations
│   └── Resources/                   # API Resource JSON Transformers
├── Models/                          # Eloquent Relational Models
├── Policies/                        # Authorization Policies
└── Services/                        # Isolated Business Logic Layer
    ├── AuditService.php             # Audit Logging Trail
    ├── EtaService.php               # Delay Detection & Threshold Computation
    ├── ExceptionService.php         # Incident Management & Mitigation
    ├── ExternalApiService.php       # Open-Meteo & Nominatim Resilient Client
    ├── NotificationService.php      # In-App Notification Dispatcher
    ├── ShipmentService.php          # Lifecycle State Machine Orchestration
    └── TrackingService.php          # Append-Only Timeline Integrity Guard
```

---

### 11. Daftar Endpoint REST API

| Method | Endpoint URI | Deskripsi | Proteksi |
|---|---|---|---|
| `POST` | `/api/login` | Autentikasi dan penerbitan Sanctum Token | Publik |
| `POST` | `/api/logout` | Revokasi Sanctum Token aktif | Auth (Sanctum) |
| `GET` | `/api/user` | Mendapatkan profil akun login | Auth (Sanctum) |
| `GET` | `/api/shipments` | Daftar shipment (filter, search, pagination) | Auth (RBAC Filter) |
| `POST` | `/api/shipments` | Membuat shipment kargo baru | Auth (Operator/Admin) |
| `GET` | `/api/shipments/{id}` | Detail shipment & manifest barang | Auth (RBAC Filter) |
| `PUT` | `/api/shipments/{id}` | Pembaruan data shipment | Auth (Operator/Admin) |
| `DELETE` | `/api/shipments/{id}` | Penghapusan shipment (Soft Delete) | Auth (Admin Only) |
| `GET` | `/api/shipments/{id}/tracking` | Timeline pergerakan kargo | Auth |
| `POST` | `/api/shipments/{id}/tracking` | Tambah tracking event kronologis baru | Auth (Operator/Admin) |
| `GET` | `/api/shipments/{id}/exceptions` | Daftar exception pada shipment | Auth |
| `POST` | `/api/shipments/{id}/exceptions` | Laporkan hambatan/exception baru | Auth (Operator/Admin) |
| `PATCH` | `/api/exceptions/{id}` | Investigasi & resolusi exception | Auth (Operator/Admin) |
| `GET` | `/api/carriers` | Daftar maskapai pelayaran maritim | Auth |
| `GET` | `/api/locations` | Daftar pelabuhan maritim & koordinat | Auth |
| `GET` | `/api/health` | Health check koneksi sistem & database | Publik |

---

### 12. Rancangan Antarmuka & Navigasi UI
1. **Public Landing Page (`/`):** Tampilan profil bisnis maritim profesional, showcase komoditas kakao & kopi, statistik armada kargo, peta interaktif, dan form pencarian cepat nomor pelacakan.
2. **Public Tracking Portal (`/track`):** Pencarian tanpa login untuk verifikasi status pengiriman, milestone progres, peta perjalanan, dan detail komoditas tanpa membocorkan rahasia komersial.
3. **Role Dashboards (`/dashboard`):**
   - *Admin Dashboard:* Metrik agregat kargo global, grafik status pengiriman, distribusi pelayaran kargo, rasio keterlambatan, dan log audit mutasi.
   - *Operator Dashboard:* Antrean pengiriman mendesak, monitor kargo *In Transit*, deteksi keterlambatan, dan insiden exception aktif yang membutuhkan investigasi.
   - *Customer Dashboard:* Ringkasan kargo pesanan milik customer, milestone ETA, dan notifikasi terkini.
4. **Shipment Management Suite (`/shipments`):**
   - Indeks data dengan pencarian kargo, multi-filter (status, pelabuhan, carrier, delay), sorting, dan pagination.
   - Detail tampilan dengan integrasi Leaflet OpenStreetMap, vertical chronological timeline, breakdown kontainer kakao/kopi, dan drawer exception.

---

### 13. Acceptance Criteria (Kriteria Keberterimaan)
1. Seluruh transisi status mematuhi alur bisnis `CREATED` s/d `DELIVERED` dan menolak transisi ilegal.
2. Form shipment items mencatat atribut spesifik kakao (*fermentation, moisture %*) dan kopi (*variety, grade, packaging*).
3. Peta Leaflet memuat koordinat pelabuhan asal, tujuan, dan pergerakan kargo dengan akurat, disertai label *"Simulated Route"*.
4. Perhitungan keterlambatan mendeteksi selisih `estimated_arrival` vs `expected_arrival` secara deterministik dan mengklasifikasikan severity secara otomatis.
5. Hak akses RBAC teruji di level middleware dan policy backend: customer tidak dapat mengakses data customer lain.
6. Seluruh API endpoint merespon dalam format JSON standar dan lolos verifikasi Postman.
7. Automated test suite (PHPUnit/Pest) lolos dengan status 100% hijau.
