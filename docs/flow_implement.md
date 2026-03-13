# Flow Implementation

> Dokumen ini menggambarkan **urutan eksekusi** yang harus diikuti, dependency antar task, dan flow data dari user action ke response.

---

## Build Order (Dependency Tree)

```
Phase 0 (Setup)
    └── Phase 1 (DB + Models)
            ├── Phase 2 (Filament Admin Panel)
            └── Phase 3 (Public Frontend + Business Logic)
                    │   Phase 3a: Layout + Pages (Blade)
                    │   Phase 3b: API endpoints (like, rate, comment, view counter)
                    │   → Frontend & API dibangun bersama, karena UI butuh endpoint
                    └── Phase 4 (Polish & Production)
```

> **Phase 2 dan Phase 3 bisa dikerjakan paralel** setelah Phase 1 selesai.
> Phase 3 menggabungkan frontend + business logic API karena saling depend.
> Business logic (like/rate/comment endpoint) harus tersedia sebelum frontend bisa berfungsi.

---

## User-Facing Flows

### Flow 0: Load Site Settings (setiap request)

```
Setiap request publik
    → Blade layout di-render
    → Panggil helper setting('site_name'), setting('site_logo'), setting('site_favicon'),
      setting('sidebar_menu'), setting('nav_filter_labels')
    → Cache check: key `site_settings`
        → hit  → return dari cache (array semua settings)
        → miss → query DB: SELECT * FROM site_settings
                 → transform ke array key-value
                 → simpan ke cache (permanen sampai di-bust)
    → Inject ke Blade view: <title>, <link rel="icon">, <img> logo, sidebar items, filter labels
```

---

### Flow 1: User Membuka Homepage

```
User buka /
    → HomeController@index
    → Ambil groups (ordered by sort_order) + videos per group
        [Cache check: homepage_groups]
        → jika cache hit → return cached data
        → jika miss → query DB, eager load videos.category, videos.group
    → Blade: render homepage dengan grid per group
    → Vite: Tailwind CSS + Alpine.js hydration
```

---

### Flow 2: User Menonton Video

```
User klik video card
    → redirect ke /video/{slug}
    → VideoController@show
    → Ambil video by slug (eager load: group, category, tags)
    → Cek session: `viewed_video_{id}` exists?
        → tidak → increment views_count di Redis cache, set session key
                  (sync ke DB via scheduled command setiap 5 menit)
        → ya → skip increment
    → Ambil related videos (same group ATAU same category, limit 10)
        [Cache check: related_videos_{id}]
    → Ambil komentar approved (ordered by created_at ASC)
    → Cek IP hash di video_likes → set `user_has_liked` boolean
    → Cek IP hash di video_ratings → set `user_rating` value
    → Blade: render video detail
    
    --- Player rendering ---
    → jika video_type = 'upload'
        → Plyr.js dengan <video src="{{ Storage::url(video.video_path) }}">
    → jika video_type = 'embed'
        → detect URL:
            - youtube.com/youtu.be → Plyr.js YouTube provider
            - vimeo.com → Plyr.js Vimeo provider
            - lainnya → raw <iframe src="{{ video.embed_url }}">
```

---

### Flow 3: User Like Video

```
User klik tombol Like
    → Alpine.js fetch POST /videos/{id}/like (with X-CSRF-TOKEN header)
    → Throttle middleware: max 10 req/menit per IP
    → LikeController@toggle
        → hash IP address
        → cek VideoLike where video_id + ip_hash
            → exists → delete (unlike), return {liked: false, count: N}
            → not exists → insert, return {liked: true, count: N}
    → Alpine.js update UI (toggle class + update count)
```

---

### Flow 4: User Rating Video

```
User klik bintang rating
    → Alpine.js fetch POST /videos/{id}/rate (with X-CSRF-TOKEN header)
        body: { score: 1-5 }
    → Throttle middleware: max 5 req/menit per IP
    → RatingController@store
        → validasi score (int, 1-5)
        → hash IP address
        → updateOrCreate VideoRating where video_id + ip_hash
        → hitung ulang average rating dari semua ratings video ini
        → return {average: X.X, total: N, user_score: N}
    → Alpine.js update UI (highlight bintang + update average)
```

---

### Flow 5: User Submit Komentar

```
User isi form komentar (username + konten) → klik submit
    → fetch POST /videos/{id}/comments (with X-CSRF-TOKEN header)
        body: { username: "...", content: "..." }
    → Throttle middleware: max 3 req/menit per IP
    → CommentController@store
        → validasi:
            username: required, string, max:50
            content: required, string, min:3, max:1000
        → sanitasi input (strip HTML)
        → simpan Comment {status: 'pending', is_from_admin: false}
        → return {message: "Komentar menunggu moderasi"}
    → Alpine.js tampilkan pesan sukses, reset form
```

---

### Flow 6: User Search

```
User ketik di search bar → tekan Enter / klik search
    → redirect ke /search?q={query}
    → SearchController@index
        → validasi q: required, min:2, max:100
        → Video::search($query)->query(fn($q) => $q->where('status', 'published'))->paginate(20)
        → Scout query ke Meilisearch
        → return hasil sebagai Blade view (video cards)
```

---

### Flow 7: Admin Update Site Settings

```
Admin buka /admin/site-settings
    → Load semua keys dari site_settings table
    → Tampilkan form terisi (site_name, logo preview, favicon preview, desc, sidebar menu repeater, filter labels)
    → Admin ubah nilai → klik Simpan
    → Validasi input
    → Update DB: foreach changed fields → SiteSetting::updateOrCreate(['key' => $key], ['value' => $value])
    → Jika ada file upload baru (logo/favicon):
        → Hapus file lama dari storage
        → Simpan file baru ke storage/settings/
        → Update value di DB dengan path baru
    → Flush cache: Cache::forget('site_settings')
    → Redirect dengan flash success
    → Request berikutnya akan rebuild cache dari DB
```

---

### Flow 8: Admin Upload Video

```
Admin buka /admin/videos/create
    → Isi form: judul, thumbnail, type toggle
    
    --- jika upload ---
    → pilih file video (mp4/webm/mkv)
    → Filament upload ke Storage::disk('public')/videos/{filename}
    → simpan video_path ke DB
    
    --- jika embed ---
    → paste embed URL
    → simpan embed_url ke DB
    
    → assign group, category, tags, status, published_at
    → Simpan → Video::create()
    → Scout otomatis index video baru (via Searchable observer)
    → Cache bust: hapus cache homepage_groups
    → redirect ke list dengan flash success
```

---

### Flow 9: Admin Moderate Komentar

```
Admin buka /admin/comments
    → filter by status: pending
    → klik action "Approve" pada komentar
        → Comment::update({status: 'approved'})
        → tampil di publik otomatis
    → atau klik "Reject"
        → Comment::update({status: 'rejected'})
        → tidak tampil di publik
    
    --- Admin nulis komentar sendiri ---
    → klik "Tambah Komentar" di halaman edit Video
    → isi username (default "Admin") + konten
    → simpan {is_from_admin: true, status: 'approved'} → langsung tampil
```

---

## Data Flow Diagram (Simplified)

```
┌─────────────┐     HTTP Request      ┌──────────────────┐
│   Browser   │ ──────────────────►  │  Laravel Router   │
│  (User)     │                       └────────┬─────────┘
└─────────────┘                                │
                                               ▼
                                    ┌──────────────────┐
                                    │   Middleware      │
                                    │ - Throttle        │
                                    │ - Session         │
                                    └────────┬─────────┘
                                             │
                                             ▼
                              ┌──────────────────────────┐
                              │       Controller          │
                              │  - Cache check            │
                              │  - Model query            │
                              │  - Business logic         │
                              └──────┬───────────────┬───┘
                                     │               │
                              ┌──────▼──┐      ┌─────▼────┐
                              │  MySQL  │      │  Redis   │
                              │  (Data) │      │  (Cache) │
                              └─────────┘      └──────────┘
                                     │
                              ┌──────▼──────────────────┐
                              │   Blade View / JSON      │
                              │   Response               │
                              └─────────────────────────┘
```

---

## Route Structure

```
Public Routes (web.php)
    GET  /                          HomeController@index
    GET  /video/{slug}              VideoController@show
    GET  /group/{slug}              GroupController@show
    GET  /category/{slug}           CategoryController@show
    GET  /tag/{slug}                TagController@show
    GET  /search                    SearchController@index

Interactive Routes (web.php — pakai session + CSRF protection)
    POST /videos/{id}/like          LikeController@toggle      (throttled)
    POST /videos/{id}/rate          RatingController@store     (throttled)
    POST /videos/{id}/comments      CommentController@store    (throttled)

Admin Routes (Filament — auto-generated)
    /admin                          Dashboard
    /admin/videos                   VideoResource
    /admin/groups                   GroupResource
    /admin/categories               CategoryResource
    /admin/tags                     TagResource
    /admin/comments                 CommentResource
    /admin/site-settings            SiteSettingsPage (custom)
```
