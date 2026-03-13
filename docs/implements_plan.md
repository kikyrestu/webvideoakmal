# Implementation Plan

> Dokumen ini menjelaskan **apa** yang dibangun di setiap phase, **mengapa** urutannya seperti ini, dan **output konkret** yang dihasilkan.

---

## Phase 0 — Project Initialization

**Tujuan:** Semua tooling dan dependency siap sebelum menulis satu baris logic pun.

### Steps
1. `laravel new akmal-project-webvideos` — init project Laravel 11
2. Konfigurasi `.env` — DB, APP_URL, storage, queue driver (Redis)
3. Install & setup TailwindCSS via Vite
4. Install Alpine.js
5. Install Plyr.js (via npm)
6. Install Filament v3 (`composer require filament/filament`)
7. Install Laravel Scout + Meilisearch driver (`composer require laravel/scout meilisearch/meilisearch-php`)
8. Konfigurasi Scout di `.env` (MEILISEARCH_HOST, MEILISEARCH_KEY)
9. `php artisan storage:link` — public storage symlink
10. Konfigurasi Redis untuk cache dan queue

### Output
- Laravel project berjalan di localhost
- Filament panel accessible di `/admin`
- Meilisearch instance running
- Vite build pipeline siap (Tailwind + Alpine + Plyr)

---

## Phase 1 — Database & Models

**Tujuan:** Fondasi data tersedia dan semua relationship terdefinisi dengan benar.

### Steps
1. Buat migrations:
   - `groups`
   - `categories`
   - `tags`
   - `videos`
   - `video_tags` (pivot)
   - `video_likes`
   - `video_ratings`
   - `comments`
   - `site_settings`
2. Buat Models dengan fillable, casts, dan relationships:
   - `Group`, `Category`, `Tag`, `Video`, `VideoLike`, `VideoRating`, `Comment`, `SiteSetting`
3. Buat global helper `setting('key', $default = null)` — ambil value dari cache atau DB
4. Tambahkan `Searchable` trait ke model `Video` + define `toSearchableArray()`
5. Buat Seeders:
   - `AdminSeeder` — 1 admin account (seed ke tabel `users` bawaan Laravel)
   - `CategorySeeder` — default categories (Short, Live, Peristiwa, Event, Umum, dll)
   - `GroupSeeder` — sample groups (Mabes Polri, Polda Sumsel, dll)
   - `SiteSettingSeeder` — default values (site_name, logo placeholder, favicon, sidebar menu, nav filter labels)
6. `php artisan migrate --seed`

### Output
- Semua tabel terbuat di DB
- Model siap digunakan dengan relasi lengkap
- Data awal tersedia untuk development

---

## Phase 2 — Filament Admin Panel

**Tujuan:** Admin bisa manage semua konten dari panel yang bersih dan functional.

### Steps

#### 2.1 — Admin Auth
- Gunakan tabel `users` bawaan Laravel (Filament default, tidak perlu custom guard)
- Seed 1 account admin via `AdminSeeder`
- Pastikan hanya 1 account yang bisa login

#### 2.2 — Resource: Video
- List videos (DataTable: judul, grup, kategori, type, status, views, published_at)
- Create/Edit form:
  - Toggle: Upload File vs Embed URL
  - Field upload video (jika upload) → Laravel Storage
  - Field embed_url (jika embed)
  - Upload thumbnail
  - Select group (nullable)
  - Select category
  - Multi-select tags (dengan create inline)
  - Toggle is_live
  - Select status (draft/published)
  - DateTimePicker published_at
- Delete dengan konfirmasi

#### 2.3 — Resource: Group
- List groups (sortable by sort_order)
- Form: nama, slug (auto), logo upload, tipe, sort_order

#### 2.4 — Resource: Category
- Form: nama, slug (auto), color picker

#### 2.5 — Resource: Tag
- Simple list + form: nama, slug (auto)

#### 2.6 — Resource: Comment
- List comments (DataTable: video, username, preview konten, status, is_from_admin)
- Action: Approve / Reject
- Form untuk admin nulis komentar baru ke video tertentu (auto approved)

#### 2.7 — Filament Page: Site Settings
- Filament custom **Settings Page** (bukan resource, tapi satu halaman form)
- Form fields:
  - Text input: Nama Website (`site_name`)
  - File upload: Logo Header (`site_logo`) → simpan ke `storage/settings/`
  - File upload: Favicon (`site_favicon`) → simpan ke `storage/settings/`
  - Textarea: Deskripsi Website (`site_description`)
  - Repeater: Sidebar Menu (`sidebar_menu`) — tiap item: label, icon (string class), URL
  - Dua text input: Label filter navbar kiri & kanan (`nav_filter_labels`)
- Saat simpan: flush cache `site_settings`, update DB

#### 2.8 — Dashboard Widget
- Total videos (published vs draft)
- Total views keseluruhan
- 5 video terbaru
- Comments pending moderation count

### Output
- Admin bisa full CRUD semua konten
- Comment moderation workflow jalan
- Dashboard informatif

---

## Phase 3 — Public Frontend

**Tujuan:** Halaman publik yang bisa diakses user tanpa login, sesuai referensi desain.

### Steps

#### 3.1 — Global Layout
- Dark theme base layout (Blade component)
- Semua elemen dinamis diambil via helper `setting()` yang di-cache:
  - `<title>` dan `<meta>` menggunakan `setting('site_name')`
  - `<link rel="icon">` menggunakan `setting('site_favicon')`
  - Logo header menggunakan `setting('site_logo')`
  - Item sidebar menu di-render dari JSON `setting('sidebar_menu')`
  - Label filter navbar dari JSON `setting('nav_filter_labels')`
- Top navbar: logo (dinamis), search bar, filter toggle (label dinamis)
- Side navbar: item menu (dinamis dari DB)
- Hashtag/tag scrollbar horizontal di bawah navbar
- Footer

#### 3.2 — Homepage
- Render video dikelompokkan per group
- Tiap group: logo horizontal scroll (semua group icons) + section video grid
- Video card: thumbnail, judul, nama group, views, badge kategori, timestamp relative

#### 3.3 — Video Detail Page (`/video/{slug}`)
- Player:
  - Upload → Plyr.js `<video>`
  - YouTube/Vimeo → Plyr.js embed mode
  - URL lain → raw `<iframe>`
- Logo group + label kategori berwarna
- Judul + deskripsi collapsible (Alpine.js)
- Like button (anonymous, cek IP) + jumlah like
- Rating bintang 1-5 (anonymous, cek IP, tampil average + jumlah rating)
- Tombol Share (copy URL) + Embed (modal dengan iframe code)
- Section komentar:
  - Form: username (text) + konten → submit → status pending
  - Tampilkan komentar yang approved (chronological)
- Sidebar: related videos (same group atau same category)
- Auto increment views_count (1x per session per video)

#### 3.4 — Group Page (`/group/{slug}`)
- Header: logo group + nama + jumlah video
- Grid semua video dari group tersebut (paginated)

#### 3.5 — Category Page (`/category/{slug}`)
- Grid semua video dengan kategori tersebut (paginated)

#### 3.6 — Tag/Hashtag Page (`/tag/{slug}`)
- Grid semua video dengan tag tersebut (paginated)

#### 3.7 — Search Results Page (`/search?q=...`)
- Input dari top navbar
- Hasil: video cards (Scout Meilisearch)
- Empty state jika tidak ada hasil

### Output
- Semua halaman publik functional
- Video bisa diputar (upload & embed)
- Like, rating, komentar guest bekerja
- Navigasi antar halaman lancar

---

## Phase 4 — Logic & Backend Features

**Tujuan:** Semua business logic berjalan benar, aman, dan efisien.

### Steps
1. **View Counter** — middleware atau service yang increment `views_count` 1x per session per video (pakai session key `viewed_video_{id}`). Increment ke cache (Redis) terlebih dahulu, lalu sync ke DB via scheduled command setiap 5 menit untuk menghindari race condition pada traffic tinggi
2. **Like Logic** — controller method: cek hash(IP) di `video_likes`, toggle, return updated count via JSON
3. **Rating Logic** — cek hash(IP) di `video_ratings`, insert/update, return average + total via JSON
4. **Comment Submit** — validasi input (username max 50 char, konten max 1000 char), simpan dengan status pending
5. **Embed Code Generator** — method di model Video yang return string `<iframe>` siap pakai
6. **Scout Indexing** — hook via `Searchable` trait, manual reindex command tersedia
7. **Filter Info Polri | Umum** — scope di model Video berdasarkan category/group type

### Output
- Semua interaksi user (like, rating, komentar, views) bekerja dan aman dari abuse sederhana
- Search akurat dan cepat

---

## Phase 5 — Polish & Production Readiness

**Tujuan:** Website siap deploy, cepat, SEO-friendly, dan tidak ada edge case yang meledak.

### Steps
1. SEO meta tags (title, description, OG image) per halaman via Blade stack
2. `sitemap.xml` dinamis (auto-generate dari published videos)
3. Lazy loading thumbnail (native `loading="lazy"`)
4. Pagination atau infinite scroll di list pages
5. Redis caching untuk data berat (homepage sections, related videos)
6. Eager loading audit — pastikan tidak ada N+1 query
7. Responsive QA di mobile/tablet/desktop
8. Error pages: 404, 500, 403
9. Rate limiting pada endpoint like/rating/comment (via Laravel throttle middleware)
10. IP hashing sebelum disimpan ke DB (privacy + security)

### Output
- Website production-ready
- Performa baik (Lighthouse score target: 85+)
- Tidak ada security issue pada fitur interaktif
