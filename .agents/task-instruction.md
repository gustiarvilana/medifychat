# Task Instruction untuk OpenCode

**Proyek:** Medify Chat — WhatsApp Bot Optimization (SSoT + Context Dinamis)

---

## Konteks

Bot WhatsApp Medify saat ini memiliki beberapa masalah:

1. **Inkonsistensi identitas** — Gemini menggunakan "MedifyBot", hardcoded text menggunakan "RS Bhayangkara Setukpa Sukabumi"
2. **Fitur mismatch** — Gemini hanya tahu 5 fitur, padahal bot punya 9 fitur
3. **Context upload tidak optimal** — context dari file hanya diproses via word matching sederhana dan tidak selalu tersedia di Gemini
4. **Nama RS hardcoded** — tidak bisa diubah dari admin panel

## Tujuan

Implementasi pola **Single Source of Truth (SSoT)** untuk konsistensi dan maintenance yang lebih baik, plus **Context dinamis di system instruction Gemini**.

---

## Spesifikasi Teknis

### 1. File Baru: `whatsapp-bot/src/bot-profile.js`

Single source of truth berisi:

- **`BOT`** object dengan:
  - `name` — "MedifyBot" (statis)
  - `rsName` — getter yang membaca dari mutable variable (bisa diubah runtime)
  - `features` — array of 9 fitur (id, label, keyword)
- **`setRsName(name)`** — update RS name runtime
- **`buildMenuText()`** — generate HELP_TEXT dari `BOT`
- **`buildSystemInstruction(contextStr = '')`** — generate system instruction dari `BOT` + context RS

### 2. File Modify: `whatsapp-bot/src/handlers/constants.js`

- Hapus `HELP_TEXT` hardcoded
- Import `buildMenuText` dari `bot-profile.js`
- Export `HELP_TEXT = buildMenuText()`

### 3. File Modify: `whatsapp-bot/src/gemini-api.js`

- Hapus `SYSTEM_INSTRUCTION` hardcoded
- Tambah:
  - `loadContext()` — query `bot_context` aktif, cache 5 menit
  - `getSystemInstruction()` — build system instruction with context
  - `refreshContextCache()` — export untuk heartbeat
- `chat()` panggil `getSystemInstruction()` tiap kali

### 4. File Modify: `whatsapp-bot/src/handlers/idle.js`

- Import `BOT` dari `bot-profile.js`
- Ganti hardcoded "RS Bhayangkara Setukpa Sukabumi" → `BOT.rsName`
- Default case: hapus contextStr dari prompt Gemini (sudah di system instruction)
- `searchContext()` tetap untuk fallback saat Gemini null

### 5. File Modify: `whatsapp-bot/src/index.js`

- Import `setRsName` dan `refreshContextCache`
- Di heartbeat: update RS name + refresh context periodik

### 6. Laravel — Migration Baru

Tambah kolom `rs_name` di `bot_status` table.

### 7. Laravel — Update `BotSettingsController`

- `index()` — sertakan `rs_name` di response
- `update()` — validasi & simpan `rs_name`

### 8. Laravel — Update `resources/views/settings.blade.php`

- Tambah input field "Nama Rumah Sakit"
- Update JS `loadSettings()` dan `saveSettings()`

---

## Aturan Wajib

1. **Baca file yang akan dimodifikasi** sebelum melakukan edit
2. Update `.agents/tasklist.md` setiap selesai satu task
3. Tandai task selesai dengan `[x]` dan emoji ✅
4. Update progress keseluruhan
5. Tambahkan catatan file apa yang dibuat/diubah

---

## Urutan Pengerjaan

1. Migration Laravel (kolom `rs_name`)
2. Update Laravel Controller
3. Update Laravel Blade View
4. Buat `bot-profile.js`
5. Update `constants.js`
6. Update `gemini-api.js`
7. Update `idle.js`
8. Update `index.js`
9. Run migration
10. Restart bot + testing

---

## Referensi File Penting

| Path | Peran |
|------|-------|
| `whatsapp-bot/src/bot-profile.js` | **BARU** — SSoT |
| `whatsapp-bot/src/gemini-api.js` | Gemini integration |
| `whatsapp-bot/src/handlers/idle.js` | IDLE handler + Gemini call |
| `whatsapp-bot/src/handlers/constants.js` | HELP_TEXT, MENU_NUMBERS |
| `whatsapp-bot/src/index.js` | Heartbeat, startup |
| `whatsapp-bot/src/database.js` | DB queries |
| `app/Http/Controllers/BotSettingsController.php` | Settings CRUD |
| `resources/views/settings.blade.php` | Settings UI |
| `database/migrations/*_bot_status*.php` | Existing migration |
