# Medify Chat (Bot WhatsApp + Dashboard Admin)

Medify Chat adalah sistem terintegrasi yang menggabungkan Bot WhatsApp untuk pendaftaran pasien dan Dashboard Admin untuk manajemen serta monitoring.

## 🚀 Fitur

- **Bot WhatsApp:**
  - **1️⃣ Daftar Berobat** — Booking rawat jalan (pasien baru & lama).
  - **2️⃣ Jadwal Dokter** — Cek jadwal praktek dokter per poliklinik.
  - **3️⃣ Tempat Tidur** — Info ketersediaan bed kosong.
  - **4️⃣ Status Booking** — Cek status pendaftaran yang sudah dibuat.
  - **5️⃣ Paket MCU** — Informasi & harga paket medical check-up.
  - **6️⃣ Antrian Poli** — Cek nomor antrian terkini per poli.
  - **7️⃣ Jadwal per Tanggal** — Lihat jadwal semua dokter di tanggal tertentu.
  - **8️⃣ Batalkan Booking** — Pembatalan pendaftaran yang sudah dikonfirmasi.
  - **NLP Natural Language** — Didukung Gemini AI + fuzzy matching untuk memahami percakapan natural dan typo.
- **Dashboard Admin:**
  - Monitoring Status Bot Real-time.
  - Manajemen Koneksi WhatsApp (Logout/Restart).
  - Manajemen Konteks untuk Basis Pengetahuan AI (PDF, DOCX, XLSX, JSON, TXT).
  - Log & Tracking Aktivitas Terperinci.

## 💬 Skenario Chat

### Mendaftar Rawat Jalan (Pasien Baru)
```
User: Saya mau daftar berobat
Bot: 😊 Baik, saya bantu daftar rawat jalan!
     Pasien Baru → Kirimkan NIK (16 digit)
     Pasien Lama → Kirimkan No Rekam Medis (RM)

User: 3674060903970004
Bot: 🧑 Data ditemukan! Nama: BUDI SANTOSO
     Sekarang pilih Metode Pembayaran:
     1️⃣ Tunai
     2️⃣ BPJS
     3️⃣ Asuransi Lain

User: 1
Bot: 💳 Metode Bayar: Tunai
     Pilih Poliklinik tujuan:
     1. POLI PENYAKIT DALAM
     2. POLI UMUM
     3. POLI ANAK
     ...

User: 2
Bot: 🏥 Poli: POLI UMUM
     Pilih Dokter:
     1. dr. Andi
     2. dr. Siti

User: 1
Bot: 🩺 Dokter: dr. Andi
     📋 Konfirmasi Pendaftaran
     👤 Nama: BUDI SANTOSO
     🏥 Poli: POLI UMUM
     🩺 Dokter: dr. Andi
     📅 Tanggal: 2026-06-01
     💳 Bayar: Tunai
     Ketik KONFIRM untuk lanjut

User: KONFIRM
Bot: 🎉 Pendaftaran Berhasil!
     📌 Kode Booking: MED-12345
     🚶 No. Antrian: 12
```

### Cek Jadwal Dokter (Natural Language)
```
User: Jadwal dokter penyakit dalam
Bot: 🏥 Poli PENYAKIT DALAM
     Pilih dokter:
     1. dr. Andi (Penyakit Dalam)
     2. dr. Budi (Penyakit Dalam)

User: 1
Bot: 🩺 dr. Andi
     Jadwal Praktek:
     • Senin: 08:00 - 12:00
     • Rabu: 08:00 - 14:00
     • Jumat: 13:00 - 16:00
```

### Cek Status Booking (Dengan Typo)
```
User: cek statuss bookingku
Bot: 📋 Silakan masukkan No Rekam Medis (RM) Anda.

User: 001234
Bot: 📋 Status Booking Anda:
     🏥 Poli: POLI UMUM
     🩺 Dokter: dr. Andi
     📅 Tanggal: 2026-06-01
     ✅ Status: Terdaftar
```

### Cek Ketersediaan Tempat Tidur
```
User: Ada bed kosong?
Bot: 🏥 Ketersediaan Tempat Tidur:
     • Rawat Inap A: 5 bed tersedia
     • Rawat Inap B: 2 bed tersedia
     • ICU: 3 bed tersedia
     • IGD: 4 bed tersedia
```

### Cek Antrian Poli
```
User: Antrian poli umum
Bot: 🚶 Antrian POLI UMUM — 01 Juni 2026
     Nomor antrian saat ini: 08
     Total terdaftar: 15 pasien
```

### Navigasi & Bantuan
```
User: 0
Bot: 🤖 Halo! Saya Asisten Medify RS
     Silakan pilih:
     1️⃣ Daftar Berobat
     2️⃣ Jadwal Dokter
     ... (menu lengkap)

User: batal
Bot: ✅ Proses dibatalkan.
```

## 🛠️ Tech Stack

- **Backend:** Laravel 11, MySQL.
- **Bot Engine:** Node.js, `@whiskeysockets/baileys`.
- **AI:** Google Gemini API.
- **Integrasi:** Medify SIMRS API.

## 📦 Instalasi

### Prasyarat
- PHP 8.3+
- Node.js 20.x
- MySQL 8.0
- Composer

### Setup Lengkap
Jalankan perintah berikut dari root proyek untuk melakukan setup awal:
```powershell
composer run setup
```

## 🏃 Menjalankan Aplikasi

### Dashboard Admin (Laravel)
```powershell
composer run dev
```

### Bot WhatsApp (Node.js)
```powershell
cd whatsapp-bot
npm install
npm run dev
```

## 🧪 Testing

Untuk menjalankan testing Laravel:
```powershell
php artisan test
```

## 📄 Lisensi
Proyek ini bersifat privat dan diperuntukkan bagi penggunaan internal RS Medify.
