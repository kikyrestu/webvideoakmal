# Decision Log

> Rekaman semua keputusan teknis dan produk yang diambil selama project berlangsung.
> Format: tanggal, topik, opsi yang dipertimbangkan, keputusan, dan alasan.

---

## [2026-03-12] Stack — Admin Panel

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Custom Livewire panel, Filament v3 |
| **Keputusan** | **Filament v3** |
| **Alasan** | Filament menyediakan DataTable, Form builder, Widget, dan Auth out-of-the-box di atas Livewire. Lebih cepat develop tanpa kehilangan fleksibilitas Laravel. |

---

## [2026-03-12] User System — No User Login

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Full user auth, Guest-only (no login) |
| **Keputusan** | **Guest-only — tidak ada user registration/login** |
| **Alasan** | Klien ingin platform read-only untuk publik. Semua konten dikontrol admin. Tidak ada user account. |

---

## [2026-03-12] Like & Rating — Anonymous

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Butuh login, Anonymous (IP-based), Hapus fitur |
| **Keputusan** | **Anonymous berbasis IP (di-hash sebelum disimpan)** |
| **Alasan** | Klien ingin fitur interaktif tanpa user account. IP di-hash untuk privasi. Bukan bulletproof tapi cukup untuk use case ini. |

---

## [2026-03-12] Komentar

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Hapus total, Login required, Guest (username input), Admin-only |
| **Keputusan** | **Guest bisa komentar dengan input username** (no login), admin moderasi sebelum tampil. Admin juga bisa nulis komentar langsung dari panel (auto approved). |
| **Alasan** | Klien ingin ada interaksi komentar tapi tidak ada akun user. Moderasi dari admin mencegah abuse. |

---

## [2026-03-12] Video — Grouping/Channel

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | A) Grouping dikontrol admin (bukan user channel), B) Flat tanpa grouping |
| **Keputusan** | **Opsi A — Group dikontrol admin** |
| **Alasan** | Referensi desain menampilkan grup seperti "Mabes Polri", "Polda" dsb. Ini bukan user-created channel, melainkan kategori organisasi yang dikelola admin. |

---

## [2026-03-12] Video — Storage & Embed

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Upload only, YouTube embed only, Upload + arbitrary embed URL |
| **Keputusan** | **Upload langsung ke server + embed dari URL manapun** |
| **Alasan** | Klien ingin fleksibilitas maksimal. Embed bukan hanya YouTube tapi bisa dari platform apapun. |

---

## [2026-03-12] Video Player

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Video.js, Plyr.js, native HTML5 `<video>` |
| **Keputusan** | **Plyr.js** |
| **Alasan** | Plyr support HTML5 upload, YouTube, dan Vimeo dalam satu API yang konsisten. UI bersih dan customizable dengan CSS. Untuk embed URL arbitrary (non-YT/Vimeo) fallback ke raw `<iframe>`. |

---

## [2026-03-12] Search

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | MySQL LIKE query, Laravel Scout + Meilisearch, Algolia |
| **Keputusan** | **Laravel Scout + Meilisearch** |
| **Alasan** | Full-text search yang cepat dan relevan. Meilisearch self-hosted (gratis). Scout memberikan abstraksi yang bersih. MySQL LIKE tidak cukup untuk full-text. Algolia berbayar per search. |

---

## [2026-03-12] Admin — Single vs Multi

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Multi-admin, Single admin account |
| **Keputusan** | **Single admin account** |
| **Alasan** | Requirement klien. Tidak perlu role/permission system. |

---

## [2026-03-12] Konfigurasi Tampilan Website — Dinamis vs Hardcode

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Hardcode di config/env, Dinamis via DB + cache |
| **Keputusan** | **Dinamis via tabel `site_settings` (key-value) + Redis cache** |
| **Alasan** | Header, sidebar menu, nama website, logo, dan favicon harus bisa diubah admin dari panel tanpa menyentuh kode. Pola key-value di DB fleksibel untuk penambahan setting ke depan. Cache permanen mencegah query DB tiap request, di-bust hanya saat admin menyimpan perubahan. |
| **Yang termasuk dinamis** | Nama website, logo header, favicon, deskripsi website, item sidebar menu (label+icon+URL), label filter navbar |

---

## [2026-03-12] Fitur Laporkan Video

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Hapus, Kirim notif ke admin |
| **Keputusan** | **Hapus** |
| **Alasan** | Tidak dalam scope project. Klien tidak membutuhkan. |

---

## [2026-03-12] Live Video

| | Detail |
|---|---|
| **Opsi dipertimbangkan** | Real live streaming (WebRTC/RTMP), Embed live stream dari platform lain |
| **Keputusan** | **Embed live stream URL (sama seperti embed biasa) + flag `is_live` di DB** |
| **Alasan** | Infrastruktur live streaming sendiri kompleks dan mahal. Klien cukup embed dari platform seperti YouTube Live, menandai video sebagai live dengan toggle. |
