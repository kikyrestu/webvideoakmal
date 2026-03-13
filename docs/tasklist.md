# Task List

> Centang task ini saat dikerjakan/selesai. Format: `- [x]` = done, `- [ ]` = pending, `- [~]` = in progress.
> Update juga tanggal selesai di kolom notes kalau perlu tracking.

---

## Phase 0 — Project Initialization

- [ ] `laravel new` — init project Laravel 11
- [ ] Konfigurasi `.env` (DB, APP_URL, Redis, Meilisearch)
- [ ] Install & konfigurasi TailwindCSS via Vite
- [ ] Install Alpine.js
- [ ] Install Plyr.js (npm)
- [ ] Install Filament v3
- [ ] Install Laravel Scout + Meilisearch PHP client
- [ ] Konfigurasi Scout di `config/scout.php` + `.env`
- [ ] `php artisan storage:link`
- [ ] Konfigurasi Redis (cache + queue driver)
- [ ] Verifikasi semua service jalan (DB connect, Meilisearch ping, Redis ping)

---

## Phase 1 — Database & Models

### Migrations
- [ ] Migration: `groups`
- [ ] Migration: `categories`
- [ ] Migration: `tags`
- [ ] Migration: `videos`
- [ ] Migration: `video_tags` (pivot)
- [ ] Migration: `video_likes`
- [ ] Migration: `video_ratings`
- [ ] Migration: `comments`
- [ ] Migration: `site_settings`

### Models & Relationships
- [ ] Model `Group` — fillable + hasMany Video
- [ ] Model `Category` — fillable + hasMany Video
- [ ] Model `Tag` — fillable + belongsToMany Video
- [ ] Model `Video` — fillable + casts + semua relasi + Searchable trait + toSearchableArray()
- [ ] Model `VideoLike` — fillable + belongsTo Video
- [ ] Model `VideoRating` — fillable + belongsTo Video
- [ ] Model `Comment` — fillable + belongsTo Video
- [ ] Model `SiteSetting` — fillable (key, value)
- [ ] Helper global `setting('key', $default)` — ambil dari cache atau DB, cache key `site_settings`

### Seeders
- [ ] `AdminSeeder` — 1 admin di tabel `users`
- [ ] `CategorySeeder` — default categories (Short, Live, Peristiwa, Event, Umum)
- [ ] `GroupSeeder` — sample groups
- [ ] `SiteSettingSeeder` — default site_name, logo placeholder, favicon, sidebar menu, nav filter labels
- [ ] `DatabaseSeeder` — wire semua seeders
- [ ] `php artisan migrate --seed` — verifikasi

---

## Phase 2 — Filament Admin Panel

### Setup
- [ ] Install `php artisan filament:install --panels`
- [ ] Gunakan tabel `users` bawaan Laravel (Filament default, no custom guard)

### Resource: Video
- [ ] FilamentResource `VideoResource`
- [ ] ListVideos — DataTable (judul, grup, kategori, type, status, views)
- [ ] CreateVideo form — semua field (toggle upload/embed, thumbnail, duration, group, category, tags, is_live, status, published_at)
- [ ] EditVideo form — sama dengan create
- [ ] Delete action dengan konfirmasi

### Resource: Group
- [ ] FilamentResource `GroupResource`
- [ ] List + form (nama, slug, logo upload, tipe, sort_order)

### Resource: Category
- [ ] FilamentResource `CategoryResource`
- [ ] List + form (nama, slug, color picker)

### Resource: Tag
- [ ] FilamentResource `TagResource`
- [ ] List + form (nama, slug)

### Resource: Comment
- [ ] FilamentResource `CommentResource`
- [ ] ListComments — DataTable dengan filter status
- [ ] Action: Approve
- [ ] Action: Reject
- [ ] Form admin nulis komentar baru (is_from_admin = true, auto approved)

### Page: Site Settings
- [ ] Filament custom Settings Page (`/admin/site-settings`)
- [ ] Field: Nama Website (text input)
- [ ] Field: Logo Header (file upload → `storage/settings/`)
- [ ] Field: Favicon (file upload → `storage/settings/`)
- [ ] Field: Deskripsi Website (textarea)
- [ ] Field: Sidebar Menu (repeater — label, icon, URL)
- [ ] Field: Label Filter Navbar kiri & kanan (2x text input)
- [ ] Logic: flush cache `site_settings` saat form disimpan

### Dashboard
- [ ] Widget: Total videos (published vs draft)
- [ ] Widget: Total views
- [ ] Widget: Recent videos (5 terbaru)
- [ ] Widget: Pending comments count

---

## Phase 3 — Public Frontend

### Layout
- [ ] Base layout Blade (`layouts/app.blade.php`) — dark theme
- [ ] Inject `setting()` ke layout: site_name (title), favicon, logo, sidebar menu, nav filter labels
- [ ] Top navbar component — logo dari `setting('site_logo')`, filter label dari `setting('nav_filter_labels')`
- [ ] Side navbar component — items dari `setting('sidebar_menu')` (JSON decoded)
- [ ] Hashtag scrollbar component (horizontal scroll)
- [ ] Video card component (reusable)
- [ ] Footer component

### Pages
- [ ] Homepage (`/`) — video grid per group + horizontal group logo scroll
- [ ] Video detail page (`/video/{slug}`)
- [ ] Group page (`/group/{slug}`)
- [ ] Category page (`/category/{slug}`)
- [ ] Tag page (`/tag/{slug}`)
- [ ] Search results page (`/search`)

### Video Detail Components
- [ ] Player (Plyr.js upload + YouTube/Vimeo + raw iframe)
- [ ] Deskripsi collapsible (Alpine.js)
- [ ] Like button (anonymous, Alpine.js + fetch API)
- [ ] Rating bintang (anonymous, Alpine.js + fetch API)
- [ ] Share button (copy URL ke clipboard)
- [ ] Embed modal (tampilkan iframe code)
- [ ] Komentar form (username + konten, submit → pending)
- [ ] Komentar list (tampil yang approved)
- [ ] Sidebar related videos

---

## Phase 4 — Logic & Backend Features

- [ ] View counter — increment Redis cache 1x per session per video + scheduled sync ke DB tiap 5 menit
- [ ] Like toggle endpoint (`POST /videos/{id}/like`) + rate limiting + CSRF
- [ ] Rating submit endpoint (`POST /videos/{id}/rate`) + rate limiting + CSRF
- [ ] Comment submit endpoint (`POST /videos/{id}/comments`) + validasi + rate limiting + CSRF
- [ ] Embed code generator — method di model `Video`
- [ ] Scout index — `php artisan scout:import` + hook auto di model
- [ ] Filter scope — Info Polri vs Umum di model Video
- [ ] IP hashing helper sebelum simpan ke DB

---

## Phase 5 — Polish & Production Readiness

- [ ] SEO meta tags di semua halaman (title, description, OG tags)
- [ ] Sitemap.xml dinamis (`/sitemap.xml`)
- [ ] Lazy loading thumbnail (`loading="lazy"`)
- [ ] Pagination di list pages
- [ ] Redis cache — homepage, related videos, tag list
- [ ] Cache-busting saat admin update konten
- [ ] Eager loading audit (no N+1 di semua query)
- [ ] Responsive QA — mobile, tablet, desktop
- [ ] Error pages: 404, 500
- [ ] Rate limiting via throttle middleware (like, rating, comment endpoint)
- [ ] Final security check — IP hashing, input sanitization, CSRF
