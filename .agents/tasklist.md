# Task Checklist — SSoT + Context Dinamis + RS Name Dinamis

**Progress: 100%**

---

## 🟢 Phase 1: Backend Laravel (RS Name Dinamis)

- [x] ✅ Migration baru — tambah kolom `rs_name` di `bot_status`
  - File: `database/migrations/2026_05_31_160000_add_rs_name_to_bot_status_table.php`
- [x] ✅ Update `BotSettingsController` — index + update handle rs_name
  - File: `app/Http/Controllers/BotSettingsController.php`
- [x] ✅ Update `settings.blade.php` — input field Nama RS + JS load/save
  - File: `resources/views/settings.blade.php`

## 🟢 Phase 2: SSoT bot-profile.js

- [x] ✅ Buat `whatsapp-bot/src/bot-profile.js` — BOT object, builders, setRsName
  - File: `whatsapp-bot/src/bot-profile.js` (NEW)
- [x] ✅ Update `constants.js` — import HELP_TEXT dari bot-profile
  - File: `whatsapp-bot/src/handlers/constants.js`
- [x] ✅ Update `gemini-api.js` — dynamic system instruction + context dari DB
  - File: `whatsapp-bot/src/gemini-api.js`
- [x] ✅ Update `idle.js` — pakai BOT.rsName + simplify prompt
  - File: `whatsapp-bot/src/handlers/idle.js`
- [x] ✅ Update `index.js` — heartbeat: refresh RS name + context cache
  - File: `whatsapp-bot/src/index.js`

## 🟢 Phase 3: Finalisasi

- [x] ✅ Run migration `php artisan migrate`
- [x] ✅ Bot restart + verifikasi

## Catatan

- Semua perubahan sudah selesai dan migration sudah dijalankan.
- Bot sudah diperbaiki:
  - **Auto Port Cleanup**: Otomatis mematikan proses lama di port yang sama saat startup (dinamis).
  - **Safety Heartbeat**: Tidak akan crash jika mencoba kirim pesan sebelum login WA.
  - **Pairing Code Fix**: Pengecekan status socket sebelum request pairing code.
- Admin bisa ganti Nama RS dari halaman Settings → langsung terlihat di jawaban bot.
- Context dari upload file TXT otomatis masuk ke system instruction Gemini (cache 5 menit, refresh via heartbeat).
- Edit fitur cukup di 1 file: `whatsapp-bot/src/bot-profile.js`.
