# Medify Chat

Sistem chatbot WhatsApp untuk pendaftaran pasien rumah sakit terintegrasi dengan SIMRS Medify.

## Fitur

- **Pendaftaran Berobat** — Pasien daftar via WhatsApp, pilih poli & dokter
- **Cek Jadwal Dokter** — Lihat jadwal dokter per poli
- **Cek Tempat Tidur** — Info ketersediaan bed rawat inap
- **Cek Status Booking** — Lacak status pendaftaran
- **Cek Antrian** — Info antrian online
- **Cek Paket MCU** — Informasi paket medical check-up
- **Jadwal per Tanggal** — Cari jadwal dokter berdasarkan tanggal
- **Batalkan Booking** — Pembatalan pendaftaran
- **AI Natural Language** — Google Gemini untuk percakapan bebas (opsional)
- **Admin Dashboard** — Monitoring, kontrol bot, upload knowledge base

## Arsitektur

```
┌──────────────────────┐     MySQL     ┌──────────────────────┐
│  Laravel Dashboard   │◄────────────►│   WhatsApp Bot       │
│  (PHP 8.3)           │              │   (Node.js + Baileys) │
│  Port 8000           │              │   Port 3001           │
└──────────────────────┘              └──────────┬───────────┘
                                                 │
                                        ┌────────▼───────────┐
                                        │  Medify SIMRS API  │
                                        └────────────────────┘
```

Bot dan dashboard tidak saling terhubung via HTTP. Mereka berbagi state melalui tabel MySQL (`bot_status`, `bot_commands`, `user_sessions`).

## Prerequisites

- PHP 8.3+
- Node.js 20.x
- MySQL 8.0
- Composer

## Instalasi

```bash
# Clone & masuk direktori
git clone <repo-url>
cd medifychat

# Setup otomatis (install deps, .env, key, migrate, build)
composer run setup

# Setup WhatsApp bot
cd whatsapp-bot
npm install
cd ..
```

## Konfigurasi

### 1. Database

Edit `.env` di root project:
```
DB_DATABASE=medifychat
DB_USERNAME=root
DB_PASSWORD=
```

### 2. WhatsApp Bot

Edit `whatsapp-bot/.env`:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=medifychat

PORT=3001
BOT_SESSION_TIMEOUT=30
BOT_HEARTBEAT_INTERVAL=10000
BOT_MESSAGE_DELAY=3000
```

### 3. Medify API (dari Dashboard)

Login ke admin → **Settings**, isi:
- **API URL** — URL SIMRS Medify
- **Email & Password** — Kredensial API

Atau set langsung di DB `bot_status` kolom `medify_api_url`, `medify_api_email`, `medify_api_password`.

### 4. Gemini AI (opsional)

Isi **Gemini API Key** di halaman Settings dashboard atau env (`GEMINI_API_KEY`). Jika dikosongkan, bot hanya merespon perintah terstruktur (menu).

## Menjalankan Aplikasi

Jalankan **dua terminal** secara bersamaan:

### Terminal 1: Admin Dashboard

```bash
composer run dev
```

Ini menjalankan 4 proses sekaligus:
- `php artisan serve` — HTTP server (port 8000)
- `php artisan queue:listen` — Queue worker
- `php artisan pail` — Log viewer
- `npm run dev` — Vite dev server

### Terminal 2: WhatsApp Bot

```bash
cd whatsapp-bot
npm run dev
```

Mode `dev` menggunakan `--watch` (auto-restart saat ada perubahan file).

### Atau via Docker

```bash
docker compose up -d
```

## Menggunakan Admin Dashboard

Akses `http://localhost:8000` di browser.

### Login

- Register akun admin pertama via halaman `/register`
- Atau jalankan seeder jika tersedia

### Halaman Utama (Dashboard)

Menampilkan status sistem:

| Kartu | Deskripsi |
|-------|-----------|
| **Status Bot** | Apakah engine bot sedang berjalan |
| **WhatsApp** | Status koneksi WhatsApp (Online/Offline) |
| **Mesin AI** | Status kuota Gemini API |
| **Aktivitas Terakhir** | Waktu terakhir bot menerima pesan |

Jika bot berjalan tapi WhatsApp belum terhubung, **QR Code** akan muncul. Scan dengan WhatsApp HP.

### Kontrol Bot

Panel samping menyediakan tombol:
- **Mulai** — Start bot engine
- **Berhenti** — Stop bot engine
- **Mulai Ulang Bot** — Restart bot
- **Keluar WhatsApp** — Logout dari WhatsApp (menghapus session)

### Settings

Halaman ini memiliki 2 tab:

**1. WhatsApp Bot** — Konfigurasi:
- Nama Rumah Sakit — Nama RS yang tampil di setiap jawaban bot
- Admin WhatsApp — Nomor untuk notifikasi error/quota habis
- Gemini API Key — API key untuk AI natural language
- Medify API — URL, email, password koneksi SIMRS
- Port — Port bot engine (default 3001)

**2. Context Manager** — Upload knowledge base:
- Upload file `.txt` maksimal 50MB
- Atur kategori & tags untuk organisasi
- Bot membaca konten ini sebagai sistem instruksi (untuk Gemini) atau fallback pencarian
- Status: **Aktif / Nonaktif / Proses / Gagal**

## Menggunakan WhatsApp Bot

Kirim pesan ke nomor WhatsApp yang sudah terhubung.

### Menu Utama

Ketik `menu`, `bantuan`, atau `0` untuk melihat daftar fitur:

```
─── RS Bhayangkara Setukpa Sukabumi ───

  1  Daftar Berobat
  2  Cek Jadwal Dokter
  3  Cek Tempat Tidur (Bed)
  4  Cek Status Booking
  5  Cek Paket MCU
  6  Cek Antrian
  7  Cek Jadwal per Tanggal
  8  Batalkan Booking

  0  Bantuan (Menu Ini)
  00  Bicara dengan AI
```

### Skenario Penggunaan

**Daftar Berobat:**
```
User: 1
Bot: Masukkan No. RM atau NIK Anda
User: 123456
Bot: Pilih Poli:
     1. Poli Umum
     2. Poli Gigi
     ...
User: 1
Bot: Pilih Dokter:
     1. dr. Andi (Senin, Rabu)
     2. dr. Bunga (Selasa, Kamis)
User: 1
Bot: Pilih tanggal (YYYY-MM-DD):
User: 2026-06-01
Bot: Konfirmasi booking:
     Poli: Umum
     Dokter: dr. Andi
     Tanggal: 01 Juni 2026
     Ketik "ya" untuk konfirmasi
User: ya
Bot: ✅ Booking berhasil! No: BK-20260601-001
```

**Cek Jadwal Dokter:**
```
User: 2
Bot: Masukkan nama poli (contoh: Umum, Gigi, Mata)
User: Penyakit Dalam
Bot: Jadwal Poli Penyakit Dalam:
     dr. Citra — Senin 08:00-12:00, Rabu 13:00-16:00
     dr. Dedi — Selasa 09:00-14:00, Kamis 08:00-12:00
```

**AI Conversation:**
```
User: 00
Bot: Halo, saya asisten RS. Ada yang bisa dibantu?
User: Jam besuk ICU jam berapa?
Bot: Jam besuk ICU: 15.00 - 16.00 WIB setiap hari.
```
(Membutuhkan Gemini API key dan knowledge base yang diupload)

### State Machine

Bot menggunakan state machine per-user untuk melacak percakapan:

```
IDLE → AWAITING_ID → AWAITING_RETRY_OR_NEW → AWAITING_NEW_PATIENT_DATA
     → AWAITING_PAYMENT_METHOD → AWAITING_INSURANCE → AWAITING_CLINIC
     → AWAITING_DOCTOR → AWAITING_DATE → CONFIRM_BOOKING
```

Session timeout default 30 menit. Setelah itu kembali ke `IDLE`.

## Struktur Direktori

```
medifychat/
├── app/
│   └── Http/Controllers/
│       ├── BotStatusController.php     # Status, logout, restart command
│       ├── BotProcessController.php    # Start/stop process
│       ├── BotSettingsController.php   # Settings page
│       └── AlertLogController.php      # Bot log streaming
├── database/migrations/                # Schema migrations
├── resources/views/                    # Blade templates
│   ├── dashboard.blade.php             # Main dashboard
│   ├── settings.blade.php              # Bot settings + context
│   └── context.blade.php               # Context upload
├── routes/web.php                      # All routes
├── whatsapp-bot/                       # Node.js bot (standalone)
│   ├── src/
│   │   ├── index.js                    # Entry point
│   │   ├── baileys-client.js           # WhatsApp connection + QR
│   │   ├── database.js                 # MySQL queries
│   │   ├── config.js                   # Environment config
│   │   ├── message-handler.js          # Message routing
│   │   ├── bot-commands.js             # Command polling
│   │   ├── bot-profile.js              # RS identity builder
│   │   ├── medify-api.js               # SIMRS API client
│   │   ├── nlp-engine.js               # Regex + Levenshtein NLP
│   │   ├── gemini-api.js               # Google Gemini integration
│   │   └── handlers/                   # State-specific handlers
│   │       ├── index.js                # Main routing
│   │       ├── idle.js                 # IDLE state
│   │       ├── registration.js         # Patient registration
│   │       ├── booking.js              # Booking flow
│   │       ├── doctor-schedule.js      # Doctor schedule
│   │       ├── schedule-by-date.js     # Schedule by date
│   │       ├── bed.js                  # Bed availability
│   │       ├── status.js               # Booking status
│   │       ├── queue.js                # Queue check
│   │       ├── mcu.js                  # MCU packages
│   │       ├── cancel-booking.js       # Cancellation
│   │       └── constants.js            # Help text, menus
│   ├── auth/                           # Baileys session (gitignored)
│   ├── .env                            # Bot configuration
│   └── package.json
├── composer.json
└── .env                                # Laravel configuration
```

## Troubleshooting

### QR Code tidak muncul
1. Pastikan bot berjalan (cek log)
2. Stop bot → hapus folder `whatsapp-bot/auth/` → start ulang
3. Cek port tidak conflict

### Bot disconnected terus (status 401)
- Hapus folder `whatsapp-bot/auth/` dan restart
- Jika masih gagal, WhatsApp mungkin memblokir sementara (rate limit). Tunggu 30-60 menit

### Bot tidak merespon natural
- Pastikan Gemini API key valid dan kuota tersedia
- Cek status kuota di dashboard (kartu "Mesin AI")

### "Cannot read properties of undefined (reading 'id')"
- Bot restarting karena disconnected. Biarkan proses auto-reconnect
- Jika terus terjadi, stop & start ulang bot

### Melihat log
- Dashboard: buka Settings → lihat Activity Log
- File: `whatsapp-bot/bot.log` (stdout) dan `whatsapp-bot/bot-err.log` (stderr)
