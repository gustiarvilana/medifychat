# Task Checklist - Medify Chat (WhatsApp Bot + Web Admin)

**Progress: 95%**

---

## 1. Project Setup & Configuration

- [x] ✅ Setup Tailwind dengan design system "Clinical Precision" (warna, font Inter, spacing, komponen) — `tailwind.config.js`, `resources/css/app.css`
- [x] ✅ Setup environment variables untuk API Medify di `.env` — `.env`, `.env.example`
- [x] ✅ Buat `opencode.json` untuk konfigurasi proyek — `opencode.json`
- [x] ✅ Instalasi dependencies Node.js (Baileys, Express, dll) — `whatsapp-bot/package.json`

## 2. Database & Migration

- [x] ✅ Buat migration tabel `user_sessions` — `database/migrations/...create_user_sessions_table.php`
- [x] ✅ Buat migration tabel `bot_status` — `database/migrations/...create_bot_status_table.php`
- [x] ✅ Buat migration tabel `bot_commands` — `database/migrations/...create_bot_commands_table.php`
- [x] ✅ Buat seeder admin default — `database/seeders/AdminSeeder.php`, update `DatabaseSeeder.php`

## 3. Backend: Login & Register Admin

- [x] ✅ Override Breeze login view dengan design (`login_medify_admin/code.html`) — `resources/views/auth/login.blade.php`
- [x] ✅ Override Breeze register view dengan design (`register_medify_admin/code.html`) — `resources/views/auth/register.blade.php`
- [x] ✅ Sesuaikan Tailwind config dengan design system Clinical Precision — `tailwind.config.js`
- [x] ✅ Update guest layout dengan Clinical Precision — `resources/views/layouts/guest.blade.php`

## 4. Backend: Dashboard Admin

- [x] ✅ Buat halaman dashboard dengan status bot (is_running, is_logged_in, last_activity) — `resources/views/dashboard.blade.php`
- [x] ✅ Integrasi polling AJAX setiap 10 detik — `dashboard.blade.php` (inline JS)
- [x] ✅ Buat route & controller untuk data status bot — `app/Http/Controllers/BotStatusController.php`, `routes/web.php`
- [x] ✅ Buat sidebar navigation layout — `resources/views/layouts/navigation.blade.php`, `resources/views/layouts/app.blade.php`

## 5. Backend: Settings / WhatsApp Connection

- [x] ✅ Buat halaman settings mengikuti `settings_whatsapp_connection_medify_admin/code.html` — `resources/views/settings.blade.php`
- [x] ✅ Tombol Logout WhatsApp & Restart Bot — `BotStatusController.php`
- [x] ✅ Tampilkan QR Code placeholder untuk pairing WhatsApp — `settings.blade.php`

## 6. WhatsApp Bot: Setup Node.js

- [x] ✅ Buat folder `whatsapp-bot/` dengan struktur proyek — `whatsapp-bot/src/`, `whatsapp-bot/auth/`
- [x] ✅ Setup Baileys client & koneksi WhatsApp — `whatsapp-bot/src/baileys-client.js`
- [x] ✅ Setup database connection (MySQL) — `whatsapp-bot/src/database.js`
- [x] ✅ Health check endpoint (Express.js) — `whatsapp-bot/src/index.js`

## 7. WhatsApp Bot: NLP Engine

- [x] ✅ Implementasi preprocessing pesan (toLowerCase, hapus tanda baca) — `whatsapp-bot/src/nlp-engine.js`
- [x] ✅ Implementasi intent detection dengan regex — `whatsapp-bot/src/nlp-engine.js`
- [x] ✅ Prioritas intent dan context-aware intent — `whatsapp-bot/src/nlp-engine.js`

## 8. WhatsApp Bot: State Machine & Alur Pendaftaran

- [x] ✅ State management (simpan di `user_sessions`) — `whatsapp-bot/src/database.js`
- [x] ✅ Semua state: IDLE, AWAITING_ID, AWAITING_RETRY_OR_NEW, AWAITING_NEW_PATIENT_DATA, AWAITING_PAYMENT_METHOD, AWAITING_CLINIC, AWAITING_DOCTOR, AWAITING_DATE, CONFIRM_BOOKING — `whatsapp-bot/src/message-handler.js`
- [x] ✅ Error handling No RM/NIK tidak valid — `message-handler.js`
- [x] ✅ Alur daftar pasien baru via API — `message-handler.js`

## 9. WhatsApp Bot: API Medify Wrapper

- [x] ✅ Token management (login, refresh, simpan di memory) — `whatsapp-bot/src/medify-api.js`
- [x] ✅ Wrapper untuk setiap endpoint API Medify — `whatsapp-bot/src/medify-api.js`
- [x] ✅ Error handling (401 refresh token, 404, 500) — `medify-api.js`

## 10. WhatsApp Bot: Fitur Tambahan

- [x] ✅ Cek Jadwal Dokter (pilih poli → lihat jadwal) — `message-handler.js`
- [x] ✅ Cek Ketersediaan Tempat Tidur (per bangsal) — `message-handler.js`
- [x] ✅ Session timeout 30 menit — `database.js`
- [x] ✅ Fallback response untuk pesan tidak dikenal — `nlp-engine.js`

## 11. Integrasi Bot ↔ Laravel

- [x] ✅ Bot membaca `bot_commands` setiap 10 detik (logout/restart) — `whatsapp-bot/src/bot-commands.js`
- [x] ✅ Bot menulis status ke `bot_status` setiap 10 detik (heartbeat) — `index.js`
- [x] ✅ Admin dashboard polling status via AJAX — `dashboard.blade.php`, `settings.blade.php`

## 6. WhatsApp Bot: Setup Node.js

- [ ] Buat folder `whatsapp-bot/` dengan struktur proyek
- [ ] Setup Baileys client & koneksi WhatsApp
- [ ] Setup database connection (MySQL)
- [ ] Health check endpoint (Express.js)

## 7. WhatsApp Bot: NLP Engine

- [ ] Implementasi preprocessing pesan (toLowerCase, hapus tanda baca)
- [ ] Implementasi intent detection dengan regex (REGISTRATION, CHECK_DOCTOR_SCHEDULE, CHECK_BED, HELP, CANCEL, STATUS, CONTINUE)
- [ ] Prioritas intent dan context-aware intent

## 8. WhatsApp Bot: State Machine & Alur Pendaftaran

- [ ] State management (simpan di `user_sessions`)
- [ ] State `IDLE` → deteksi intent
- [ ] State `AWAITING_ID` → validasi No RM/NIK, cek API
- [ ] State `AWAITING_RETRY_OR_NEW` → error handling No RM tidak ditemukan
- [ ] State `AWAITING_NEW_PATIENT_DATA` → daftar pasien baru via API
- [ ] State `AWAITING_PAYMENT_METHOD` → pilih metode bayar
- [ ] State `AWAITING_CLINIC` → pilih poliklinik
- [ ] State `AWAITING_DOCTOR` → pilih dokter + jadwal
- [ ] State `AWAITING_DATE` → pilih tanggal + kuota
- [ ] State `CONFIRM_BOOKING` → konfirmasi & panggil API booking
- [ ] Interactive buttons/list untuk pilihan

## 9. WhatsApp Bot: API Medify Wrapper

- [ ] Token management (login, refresh, simpan di memory)
- [ ] Wrapper untuk setiap endpoint API Medify
- [ ] Error handling (401 refresh token, 404, 500)

## 10. WhatsApp Bot: Fitur Tambahan

- [ ] Cek Jadwal Dokter (pilih poli → lihat jadwal)
- [ ] Cek Ketersediaan Tempat Tidur (per bangsal)
- [ ] Session timeout 30 menit
- [ ] Fallback response untuk pesan tidak dikenal

## 11. Integrasi Bot ↔ Laravel

- [ ] Bot membaca `bot_commands` setiap 10 detik (logout/restart)
- [ ] Bot menulis status ke `bot_status` setiap 10 detik (heartbeat)
- [ ] Admin dashboard polling status via AJAX

## 12. Final Polish & Testing

- [x] ✅ PHP Pint linter passed
- [x] ✅ Vite build sukses (CSS + JS terkompilasi)
- [ ] Testing alur pendaftaran lengkap (manual)
- [ ] Testing error handling (No RM/NIK tidak valid, timeout)
- [ ] Testing admin login, logout, restart bot
- [ ] Update README dengan cara instalasi & konfigurasi
