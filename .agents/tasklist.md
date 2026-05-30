# Task Checklist - Medify Chat (WhatsApp Bot + Web)

**Progress: 100%**

---

## ✅ Sudah Berjalan
- [x] Cek Tempat Tidur
- [x] Cek Jadwal Dokter (pilih poli → lihat jadwal per dokter)
- [x] Daftar Rawat Jalan (full flow: RM/NIK → payment → poli → dokter → tanggal → konfirmasi)
- [x] Cek Status Booking (menu 4 / ketik "status")
- [x] Cek Antrian Live (pilih poli + tanggal)
- [x] Cek Jadwal per Tanggal
- [x] Paket MCU
- [x] Booking Cancel (full flow: cari pasien → pilih booking → konfirmasi)
- [x] Daftar Asuransi saat pilih Asuransi Lain
- [x] Global intent intercept (CANCEL, HELP, REGISTRATION, CHECK_BED, CHECK_DOCTOR_SCHEDULE, STATUS, CHECK_QUEUE, MCU, CANCEL_BOOKING, CHECK_SCHEDULE_BY_DATE)
- [x] Number-based menu (1-9 di IDLE)
- [x] Gemini AI fallback untuk pesan tidak dikenal
- [x] Session timeout 30 menit
- [x] Bot commands (logout/restart dari admin dashboard)
- [x] Heartbeat & status reporting ke database
- [x] Modular handlers folder (`src/handlers/`)
- [x] Testing alur pendaftaran lengkap ✅
- [x] Update README ✅

## 🔴 P1 — Fix Critical ✅
- [x] `getDayKey` timezone bug → parse `new Date(y, m-1, d)` instead of `new Date(dateStr + 'T00:00:00')`
- [x] STATUS handler diverifikasi: routing dari menu 4 dan NLP "status" sudah benar
- [x] Missing exports fixed: `handleAwaitingQueueClinic`, `handleAwaitingQueueDate`, `handleDoctorScheduleClinic`
- [x] Error messages di-warm-up: "Gagal... Silakan coba lagi" → "Maaf, sedang ada gangguan. Coba lagi nanti ya."

## 🔵 P4 — Cleanup & Polish ✅
- [x] Hapus `getQuota()` (dead code)
- [x] Hapus dep `@hapi/boom`, `qrcode-terminal`
- [x] User-facing messages warmed (16 messages)
- [x] Redesign layout Manajemen Konteks/Konten menjadi berbasis kartu (cards) dengan fitur pencarian dan penyaringan kategori/status ✅

## 🟢 P2 — Fitur Baru: Context Management (Web) ✅

### Infrastructure
- [x] **Migration** — `bot_context` table (status, progress, content Markdown, error_message)
- [x] **Composer** — Install `smalot/pdfparser`, `phpoffice/phpword`, `phpoffice/phpspreadsheet`

### Backend (Laravel)
- [x] **Controller** — `ContextController.php` (index, store, show, update, destroy, toggle, retry)
- [x] **Artisan Command** — `context:process {id}` (background processing dengan progress stages)
- [x] **Routes** — 7 routes di `routes/web.php` dalam group `auth`
- [x] **Storage** — Upload file ke `storage/app/context/{id}/`

### Frontend (Blade)
- [x] **Tab navigation** — Tab "Bot Settings" | "Context Manager" di `settings.blade.php`
- [x] **Upload form** — Drag & drop + browse, validasi tipe/ukuran, progress upload
- [x] **Daftar konteks table** — List semua entry dengan status badges:
  - `pending` → badge "Menunggu"
  - `processing` → progress bar animated + persentase (poll tiap 3 detik)
  - `completed` → badge "Siap" + toggle aktif/nonaktif
  - `failed` → badge merah + tombol "Coba Lagi" + error tooltip
- [x] **Sidebar nav** — Tambah link "Context" di `navigation.blade.php`
- [x] **JS polling** — Auto-poll progress untuk item yang sedang diproses

### Document Processing (Artisan Command) ✅
- [x] **TXT** → `file_get_contents` → Markdown (code block untuk teks)
- [x] **JSON** → `json_decode` → Markdown (table/code block)
- [x] **DOCX** → `PhpWord` → extract paragraf → Markdown
- [x] **PDF** → `PdfParser` → extract per halaman → Markdown (progress per page)
- [x] **XLSX** → `PhpSpreadsheet` → per sheet → Markdown table (progress per sheet)
- [x] **Error handling**:
  - File corrupt → status failed + pesan jelas
  - PDF password protected → error spesifik
  - .doc (old format) → error minta konversi ke .docx
  - Memory limit → `ini_set('memory_limit', '512M')` + `set_time_limit(0)`
  - File > 50MB → tolak di validasi upload

### Bot AI Integration (Node.js) ✅
- [x] **Query konteks** — `idle.js`: sebelum Gemini, cari konteks aktif relevan via keyword match
- [x] **Inject ke prompt** — Gabung 3 konteks teratas ke system prompt Gemini
- [x] **Fallback** — Jika Gemini error, tetap fallback seperti biasa

## 📋 Pending
- (Semua tugas utama telah selesai)

---

## Endpoint API vs Implementasi

| Endpoint | Status | Handler |
|----------|--------|---------|
| `POST /token` | ✅ | medify-api.js (internal) |
| `GET /data-pasien?nik=` | ✅ | registration.js, status.js, cancel-booking.js |
| `GET /data-pasien/{no_rm}` | ✅ | registration.js, status.js, cancel-booking.js |
| `POST /pasien-create` | ✅ | registration.js |
| `GET /clinics` | ✅ | booking.js, doctor-schedule.js, queue.js |
| `GET /doctors?clinic_id=` | ✅ | booking.js, doctor-schedule.js |
| `GET /schedules?dokter_id=` | ✅ | booking.js |
| `GET /ketersediaan-tempat-tidur` | ✅ | bed.js |
| `GET /get-pendaftaran-pasien` | ✅ | status.js, cancel-booking.js |
| `POST /booking-create` | ✅ | booking.js |
| `POST /booking-cancel` | ✅ | cancel-booking.js |
| `POST /booking-edit` | 🔧 | medify-api.js (belum ada handler) |
| `GET /antrian-pelayanan` | ✅ | queue.js |
| `GET /data-paket-mcu` | ✅ | mcu.js |
| `GET /get-list-asuransi` | ✅ | booking.js (saat pilih Asuransi Lain) |
| `GET /get-jadwal-dokter-cuti` | 🔧 | medify-api.js (belum ada handler) |
| `GET /get-jadwal-by-tanggal` | ✅ | schedule-by-date.js |
