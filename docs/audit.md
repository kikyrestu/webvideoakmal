# Audit Log

> Rekaman semua perubahan signifikan yang terjadi selama development.
> Update dokumen ini setiap kali ada perubahan besar: schema change, refactor, bug fix penting, atau keputusan baru.

---

## Format Entry

```
## [YYYY-MM-DD] Judul Perubahan

- **Type**: schema_change | bug_fix | refactor | feature_add | feature_remove | config_change
- **Phase**: Phase 0–5
- **Oleh**: (nama / "AI")
- **Detail**: Deskripsi perubahan
- **Dampak**: Apa yang terpengaruh
- **File terkait**: (list file yang diubah)
```

---

## [2026-03-12] Initial Project Planning & Documentation

- **Type**: feature_add
- **Phase**: Pre-development
- **Oleh**: AI (brainstorming session)
- **Detail**: Finalisasi seluruh arsitektur, stack, schema DB, task list, flow implementation, dan decision log. Dokumen dibuat di folder `docs/`.
- **Dampak**: Fondasi planning selesai. Siap masuk Phase 0.
- **File terkait**:
  - `docs/architecture.md`
  - `docs/implements_plan.md`
  - `docs/tasklist.md`
  - `docs/flow_implement.md`
  - `docs/decisions.md`
  - `docs/audit.md`

---

## [2026-03-12] Penambahan Fitur Site Settings Dinamis

- **Type**: feature_add
- **Phase**: Phase 1 & Phase 2
- **Oleh**: AI (diskusi dengan klien)
- **Detail**: Header, sidebar menu, nama website, logo, dan favicon harus bisa diubah admin dari Filament panel. Ditambahkan tabel `site_settings` (key-value), model `SiteSetting`, global helper `setting()`, seeder default, Filament custom Settings Page, dan caching strategy (`site_settings` cache key, bust on save).
- **Dampak**: Architecture, DB schema, implements_plan, tasklist, flow_implement, decisions semua diupdate.
- **File terkait**:
  - `docs/architecture.md`
  - `docs/implements_plan.md`
  - `docs/tasklist.md`
  - `docs/flow_implement.md`
  - `docs/decisions.md`

## [2026-03-12] Audit #1 — Logic & Consistency Review

- **Type**: bug_fix + schema_change
- **Phase**: Pre-development
- **Oleh**: AI (audit session)
- **Detail**: 
  1. Fixed numbering errors di `implements_plan.md` (Phase 1 step 5 duplikat, Phase 2 skip 2.7)
  2. Changed `admins` table → gunakan `users` bawaan Laravel (Filament default, kurangi custom config)
  3. Added `duration` field (unsignedInt, nullable) ke tabel `videos` — dibutuhkan untuk tampil di video card
  4. Moved interactive endpoints dari `api.php` ke `web.php` — agar dapat session + CSRF protection
  5. Fixed dependency tree: Phase 4 (Business Logic) digabung ke Phase 3 karena frontend butuh API endpoints untuk berfungsi
  6. Fixed view counter logic: increment ke Redis dulu, sync ke DB via scheduled command (prevent race condition)
  7. Ensured `SiteSettingSeeder` masuk di semua docs
  8. Added "Short" ke default categories list
- **Dampak**: architecture.md, implements_plan.md, tasklist.md, flow_implement.md diupdate
- **File terkait**:
  - `docs/architecture.md`
  - `docs/implements_plan.md`
  - `docs/tasklist.md`
  - `docs/flow_implement.md`

<!-- Tambahkan entry baru di bawah sini, format sesuai template di atas -->
