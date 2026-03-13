# Architecture

## Tech Stack

| Layer            | Tech                                        |
|------------------|---------------------------------------------|
| Framework        | Laravel 11                                  |
| Admin Panel      | Filament v3                                 |
| Frontend         | Blade + TailwindCSS + Alpine.js             |
| Video Player     | Plyr.js                                     |
| Full-text Search | Laravel Scout + Meilisearch                 |
| Database         | MySQL 8                                     |
| Cache            | Redis (view count, search cache)            |
| Storage          | Laravel Storage — local (migratable ke S3)  |
| Queue            | Laravel Queue (Redis driver)                |

---

## Database Schema

### `groups`
Pengelompokan video (setara "channel" tapi dikontrol admin, bukan user).

| Column       | Type         | Notes                             |
|--------------|--------------|-----------------------------------|
| id           | bigIncrements |                                  |
| name         | string       |                                   |
| slug         | string       | unique                            |
| logo_path    | string       | nullable                          |
| type         | enum         | mabes, polda, polres, other       |
| sort_order   | integer      | default 0                         |
| created_at   | timestamp    |                                   |
| updated_at   | timestamp    |                                   |

---

### `categories`
Label kategori berwarna yang muncul di video card & detail.

| Column     | Type         | Notes                    |
|------------|--------------|--------------------------|
| id         | bigIncrements |                         |
| name       | string       |                          |
| slug       | string       | unique                   |
| color      | string       | hex color, e.g. #E53E3E  |
| created_at | timestamp    |                          |
| updated_at | timestamp    |                          |

---

### `tags`

| Column     | Type         | Notes  |
|------------|--------------|--------|
| id         | bigIncrements |       |
| name       | string       |        |
| slug       | string       | unique |
| created_at | timestamp    |        |
| updated_at | timestamp    |        |

---

### `videos`

| Column         | Type         | Notes                                              |
|----------------|--------------|----------------------------------------------------|
| id             | bigIncrements |                                                   |
| title          | string       |                                                    |
| slug           | string       | unique                                             |
| description    | text         | nullable                                           |
| thumbnail_path | string       | nullable                                           |
| group_id       | FK           | nullable → videos bisa tanpa group                 |
| category_id    | FK           | nullable                                           |
| video_type     | enum         | upload \| embed                                    |
| video_path     | string       | nullable — hanya jika video_type = upload          |
| embed_url      | string       | nullable — hanya jika video_type = embed           |
| is_live        | boolean      | default false                                      |
| views_count    | unsignedBigInt | default 0                                        |
| duration       | unsignedInt  | nullable — durasi video dalam detik. Upload: auto-extract via getID3/FFmpeg. Embed: admin input manual |
| status         | enum         | draft \| published                                 |
| published_at   | timestamp    | nullable                                           |
| created_at     | timestamp    |                                                    |
| updated_at     | timestamp    |                                                    |

---

### `video_tags` *(pivot)*

| Column   | Type |
|----------|------|
| video_id | FK   |
| tag_id   | FK   |

---

### `video_likes` *(anonymous)*

| Column     | Type         | Notes                          |
|------------|--------------|--------------------------------|
| id         | bigIncrements |                               |
| video_id   | FK           |                                |
| ip_address | string       | hashed sebelum disimpan        |
| created_at | timestamp    |                                |

> Constraint: unique(video_id, ip_address)

---

### `video_ratings` *(anonymous)*

| Column     | Type         | Notes                          |
|------------|--------------|--------------------------------|
| id         | bigIncrements |                               |
| video_id   | FK           |                                |
| ip_address | string       | hashed sebelum disimpan        |
| score      | tinyInt      | 1–5                            |
| created_at | timestamp    |                                |
| updated_at | timestamp    |                                |

> Constraint: unique(video_id, ip_address) — 1 rating per IP per video, bisa diupdate

---

### `comments`

| Column        | Type         | Notes                                       |
|---------------|--------------|---------------------------------------------|
| id            | bigIncrements |                                            |
| video_id      | FK           |                                             |
| username      | string       | guest input atau "Admin" jika dari_admin    |
| content       | text         |                                             |
| is_from_admin | boolean      | default false — auto-approved jika true     |
| status        | enum         | pending \| approved \| rejected             |
| created_at    | timestamp    |                                             |
| updated_at    | timestamp    |                                             |

---

### `users` *(admin)*
Filament menggunakan tabel `users` bawaan Laravel. Single account, seed via `AdminSeeder`.

| Column     | Type         |
|------------|--------------|
| id         | bigIncrements |
| name       | string       |
| email      | string       | unique |
| password   | string       | hashed |
| created_at | timestamp    |
| updated_at | timestamp    |

---
### `site_settings`
Konfigurasi tampilan website yang dapat diubah admin dari panel. Menggunakan pola key-value.

| Column     | Type         | Notes                                               |
|------------|--------------|-----------------------------------------------------|
| id         | bigIncrements |                                                    |
| key        | string       | unique — identifier setting                         |
| value      | text         | nullable — nilai setting                            |
| created_at | timestamp    |                                                     |
| updated_at | timestamp    |                                                     |

**Keys yang digunakan:**

| Key                  | Deskripsi                          | Contoh nilai                  |
|----------------------|------------------------------------|-------------------------------|
| `site_name`          | Nama website                       | `PoliceTube`                  |
| `site_logo`          | Path logo header                   | `settings/logo.png`           |
| `site_favicon`       | Path favicon                       | `settings/favicon.ico`        |
| `site_description`   | Deskripsi singkat (SEO/meta)       | `Portal video resmi Polri`    |
| `sidebar_menu`       | JSON array item menu sidebar       | `[{"label":"Live","icon":"...","url":"/live"}]` |
| `nav_filter_labels`  | JSON dua label filter navbar       | `["Info Polri","Umum"]`        |

> Data ini di-load sekali via helper `setting('key')` dan di-cache dengan key `site_settings`.
> Cache di-bust otomatis setiap kali admin menyimpan perubahan setting.

---
## Relationships Summary

```
Group        hasMany    Video
Category     hasMany    Video
Video        belongsTo  Group (nullable)
Video        belongsTo  Category (nullable)
Video        belongsToMany  Tag (via video_tags)
Video        hasMany    VideoLike
Video        hasMany    VideoRating
Video        hasMany    Comment
```

---

## Embed URL Handling Logic

```
if video_type == 'upload'
    → render Plyr.js dengan <video src="...">

if video_type == 'embed'
    → detect URL:
        - youtube.com / youtu.be  → Plyr.js YouTube mode
        - vimeo.com               → Plyr.js Vimeo mode
        - selain itu              → raw <iframe src="embed_url">
```

---

## Search Indexing (Scout + Meilisearch)

Fields yang diindex di model `Video`:
- `title`
- `description`
- `tags.name` (via toSearchableArray)
- `category.name`
- `group.name`

---

## Storage Structure

```
storage/app/public/
├── videos/          ← uploaded video files
├── thumbnails/      ← video thumbnails
├── groups/          ← group logos
└── settings/        ← site logo, favicon
```

---

## Caching Strategy

| Data               | Cache Key                    | TTL        |
|--------------------|------------------------------|------------|
| views_count        | `video_views_{id}`           | 5 menit    |
| homepage sections  | `homepage_groups`            | 10 menit   |
| related videos     | `related_videos_{id}`        | 15 menit   |
| tag list           | `tags_all`                   | 60 menit   |
| site settings      | `site_settings`              | permanen*  |

> `site_settings` cache hanya di-bust saat admin menyimpan perubahan di panel. Tidak ada TTL otomatis.

Cache di-flush otomatis saat admin update/delete konten terkait.
