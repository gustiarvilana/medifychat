Berikut adalah **PRD lengkap dan detail** yang mencakup seluruh rencana teknis, termasuk penggunaan **NLP Engine sederhana (Regex + Keyword Matching)** untuk percakapan natural, serta integrasi dengan **Baileys**, **Laravel admin**, dan **API Medify**.

---

# PRD: WhatsApp Bot Pendaftaran Online Medify

**Versi:** 1.0  
**Tanggal:** 29 Mei 2026  
**Status:** Final

---

## 1. Latar Belakang

Saat ini pasien Medify hanya bisa mendaftar rawat jalan melalui aplikasi mobile. Hal ini menyulitkan pasien yang lebih nyaman menggunakan WhatsApp (terutama lansia atau pengguna dengan keterbatasan akses aplikasi). Untuk meningkatkan aksesibilitas dan mengurangi beban operator, diperlukan **WhatsApp Bot** yang dapat melakukan pendaftaran rawat jalan, cek jadwal dokter, dan cek ketersediaan tempat tidur. Selain itu, tim admin membutuhkan **web dashboard sederhana** untuk memonitor status bot dan mengontrol koneksi WhatsApp.

---

## 2. Tujuan

- Memudahkan pasien (lama dan baru) mendaftar rawat jalan via WhatsApp.
- Menyediakan informasi real-time jadwal dokter dan ketersediaan bed.
- Mengurangi antrean di loket pendaftaran.
- Memberikan admin kemampuan untuk logout/restart bot melalui web.
- Mengimplementasikan **NLP sederhana** agar percakapan awal terasa alami (seperti berbicara dengan manusia), bukan sekadar pilihan angka kaku.

---

## 3. Ruang Lingkup

### 3.1 Termasuk dalam Proyek

- **WhatsApp Bot** (Node.js + Baileys) dengan state machine dan NLP ringan (Regex + Keyword Matching).
- **Fitur pendaftaran rawat jalan** (6 langkah) – setelah masuk mode pilihan, gunakan interactive buttons/list.
- **Fitur cek jadwal dokter** (berdasarkan poliklinik).
- **Fitur cek ketersediaan tempat tidur** (per bangsal).
- **Web Admin** (Laravel 11):
  - Login admin.
  - Dashboard menampilkan status bot (online/offline, login WhatsApp, last activity).
  - Tombol logout WhatsApp dan restart bot.
- **Database MySQL** untuk session pengguna, status bot, dan perintah admin.
- **Integrasi penuh dengan API Medify** yang sudah ada (berdasarkan Postman collection).
- **Dokumentasi** (README, cara instalasi, cara pakai, demo video).

### 3.2 Tidak Termasuk

- Aplikasi mobile baru.
- Pembayaran online (pilih metode bayar saja, tanpa transaksi finansial).
- Notifikasi push atau email.
- Fitur edit/cancel booking via WhatsApp (bisa rilis berikutnya).

---

## 4. User Stories

| ID | Sebagai | Saya ingin | Sehingga |
|----|---------|------------|----------|
| US-01 | Pasien | Berbicara dengan bot secara alami (tanpa harus menekan angka dari awal) | Saya merasa seperti ngobrol dengan petugas medis |
| US-02 | Pasien | Mendaftar rawat jalan dengan mengirim No RM (pasien lama) | Data saya langsung dikenali |
| US-03 | Pasien baru | Mendaftar dengan mengirim NIK | Sistem membuat rekam medis baru secara otomatis via API |
| US-04 | Pasien | Memilih metode bayar (Tunai/BPJS/Asuransi) | Data pembayaran terekam dengan benar |
| US-05 | Pasien | Memilih poliklinik dari daftar yang diberikan | Saya bisa pilih poli sesuai penyakit |
| US-06 | Pasien | Memilih dokter dan jam pelayanan | Saya tahu dokter mana yang praktek dan jamnya |
| US-07 | Pasien | Memilih tanggal kunjungan dan melihat kuota tersisa | Saya bisa pilih tanggal yang masih ada slot |
| US-08 | Pasien | Mendapatkan kode booking dan estimasi waktu tunggu | Saya tahu pendaftaran berhasil dan kapan harus datang |
| US-09 | Pasien | Mengecek jadwal dokter per poliklinik | Saya bisa rencanakan kunjungan |
| US-10 | Pasien | Mengecek ketersediaan tempat tidur | Saya tahu ruang rawat inap mana yang kosong |
| US-11 | Admin | Login ke web admin | Hanya yang berwenang yang dapat mengontrol bot |
| US-12 | Admin | Melihat status bot secara real-time | Saya yakin bot berfungsi normal |
| US-13 | Admin | Menekan tombol logout WhatsApp | Bot terputus dari akun WhatsApp (keamanan/reset) |

---

## 5. Arsitektur Teknis

### 5.1 Komponen Utama

```
[WhatsApp User] ↔ [Baileys Client] ↔ [Message Handler + NLP Engine] ↔ [State Manager (MySQL)]
                                          ↓
                                    [API Medify]
                                          ↓
                                    [SIMRS Database]

[Admin Web] ↔ [Laravel Admin] ↔ [MySQL (bot_status, bot_commands)] ↔ [Baileys (polling)]
```

### 5.2 Stack Teknologi

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| WhatsApp Library | `@whiskeysockets/baileys` | ^6.6.0 |
| Runtime Bot | Node.js | 20.x |
| NLP Engine | Regex + Keyword Matching (custom) | - |
| Web Framework Bot | Express.js (opsional, untuk health check) | ^4.18 |
| Web Admin | Laravel | 11.x |
| Database | MySQL | 8.0 |
| Autentikasi Admin | Laravel Breeze | - |
| Deployment | Docker Compose (opsional) | - |

---

## 6. NLP Engine: Regex + Keyword Matching

Karena bot harus bisa merespon secara natural sebelum masuk ke mode pilihan angka, kita akan membangun NLP sederhana berbasis **Regex** dan **Keyword Matching**.

### 6.1 Cara Kerja

1. **Pesan masuk** dibersihkan (toLowerCase, hapus tanda baca).
2. Dicocokkan dengan pola regex untuk mendeteksi **intent**:
   - `daftar|registrasi|booking|buat janji` → intent `REGISTRATION`
   - `jadwal dokter|praktek dokter|dokter praktek` → intent `CHECK_DOCTOR_SCHEDULE`
   - `tempat tidur|bed kosong|ketersediaan bed` → intent `CHECK_BED`
   - `help|tolong|bantuan` → intent `HELP`
3. Jika intent terdeteksi dan bot dalam state `IDLE`, maka bot akan memulai alur yang sesuai.
4. Jika tidak ada intent yang cocok, bot akan merespon dengan pesan ramah dan memberikan menu bantuan.

### 6.2 Contoh Mapping Intent & Response

| Intent | Contoh Pesan User | Response Bot |
|--------|------------------|---------------|
| `REGISTRATION` | "Saya mau daftar berobat" / "Booking poli jantung" | "Baik, silakan kirimkan No RM atau NIK Anda untuk memulai pendaftaran." |
| `CHECK_DOCTOR_SCHEDULE` | "Cek jadwal dokter anak" / "Dokter jantung praktek kapan?" | "Silakan pilih poliklinik: [tampilkan list]." |
| `CHECK_BED` | "Ada tempat tidur kosong?" / "Ketersediaan bed ICU" | Bot panggil API dan tampilkan data bed. |
| `HELP` | "Tolong" / "Bantuan" / "Apa yang bisa kamu bantu?" | Tampilkan menu lengkap (daftar, cek jadwal, cek bed, dll) |

### 6.3 Kelebihan Pendekatan Ini

- **Gratis & cepat** – tidak perlu API eksternal.
- **Ringan** – cocok untuk resource terbatas.
- **Mudah di-improve** – cukup tambahkan pola regex baru.
- **Percakapan natural** – pengguna tidak diharuskan mengetik angka dari awal.

### 6.4 Contoh Implementasi (Node.js)

```javascript
const intentPatterns = {
  REGISTRATION: /\b(daftar|registrasi|booking|buat janji|pendaftaran)\b/i,
  CHECK_DOCTOR_SCHEDULE: /\b(jadwal dokter|praktek dokter|dokter praktek|cek jadwal)\b/i,
  CHECK_BED: /\b(tempat tidur|bed kosong|ketersediaan bed|rawat inap)\b/i,
  HELP: /\b(tolong|bantuan|help|menu|can you help)\b/i,
};

function detectIntent(message) {
  const lowerMsg = message.toLowerCase();
  for (const [intent, pattern] of Object.entries(intentPatterns)) {
    if (pattern.test(lowerMsg)) return intent;
  }
  return null;
}
```

---

## 7. Fitur Detail

### 7.1 Pendaftaran Rawat Jalan (6 Langkah)

**State Machine** (disimpan di `user_sessions`):

```
IDLE
  ↓ (intent REGISTRATION terdeteksi)
AWAITING_ID (No RM atau NIK)
  ↓ (API cek pasien)
AWAITING_PAYMENT_METHOD
  ↓
AWAITING_CLINIC (tampilkan list poli, user pilih angka)
  ↓
AWAITING_DOCTOR (tampilkan list dokter + jadwal)
  ↓
AWAITING_DATE (tampilkan tanggal + kuota)
  ↓
CONFIRM_BOOKING → panggil API booking-create → tampilkan kode booking & estimasi → KEMBALI KE IDLE
```

**Setiap langkah setelah AWAITING_ID** akan menggunakan **interactive buttons/list** agar user cukup menekan pilihan, bukan mengetik manual.

**Estimasi Waktu:**
- Ambil nomor antrian terakhir dari response `booking-create` atau dari API `antrian-pelayanan`.
- Hitung estimasi = (nomor_antrian - 1) × 15 menit + jam_buka_poli.
- Tampilkan dalam format: "Estimasi waktu tunggu Anda: pukul 09:45 WIB".

### 7.2 Cek Jadwal Dokter

- NLP deteksi intent `CHECK_DOCTOR_SCHEDULE`.
- Bot fetch daftar poliklinik dari API `/clinics`.
- Tampilkan interactive list poli → user pilih.
- Panggil API `/doctors?clinic_id=...` dan `/schedules?dokter_id=...`.
- Format response:
  ```
  🩺 Jadwal Dokter Poli Jantung:
  1. dr. Farhanah Meutia, Sp.JP
     Senin: 08:00-23:00
     Selasa: 08:00-23:00
  2. Dr. Annisa, Sp.B
     Rabu: 13:00-16:00
  ```

### 7.3 Cek Ketersediaan Bed

- Intent `CHECK_BED`.
- Panggil API `/ketersediaan-tempat-tidur`.
- Tampilkan ringkasan per bangsal:
  ```
  🛏️ Ketersediaan Bed per Bangsal:
  - ICU: Kelas 1 (16 kosong / 20 total)
  - Anggrek: Kelas 1 (0 kosong / 4 total)
  - Lantai 2 Intensif: Kelas 1 (10/25), Kelas 2 (0/5), Kelas 3 (0/6), VIP (1/5)
  ```

### 7.4 Web Admin (Laravel)

**Fitur:**
- Login dengan email & password (admin seeder).
- Dashboard menampilkan:
  - Status `is_running` (proses bot hidup).
  - Status `is_logged_in` (WhatsApp authenticated).
  - `last_activity` (timestamp terakhir bot berinteraksi atau heartbeat).
- Tombol:
  - **Logout WhatsApp** → insert command 'logout' ke tabel `bot_commands`.
  - **Restart Bot** → insert command 'restart' (opsional, jika bot mendukung).
- Status diperbarui setiap 10 detik (AJAX polling atau refresh manual).

**Integrasi:**  
Bot membaca tabel `bot_commands` setiap 10 detik dan mengeksekusi perintah. Bot juga menulis status ke `bot_status` setiap 10 detik.

---

## 8. Database Schema (MySQL)

### 8.1 Tabel `user_sessions`

| Column | Type | Description |
|--------|------|-------------|
| wa_id | VARCHAR(100) PRIMARY KEY | Nomor WhatsApp user (format: `628xxx@s.whatsapp.net`) |
| current_state | VARCHAR(50) NOT NULL | State saat ini (IDLE, AWAITING_RM, AWAITING_PAYMENT, ...) |
| form_data | JSON | Data sementara (pasien_id, clinic_id, doctor_id, payment_method, booking_date, dll) |
| updated_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

### 8.2 Tabel `bot_status`

| Column | Type | Description |
|--------|------|-------------|
| id | INT PRIMARY KEY DEFAULT 1 | Hanya satu baris |
| is_running | BOOLEAN DEFAULT FALSE | Apakah proses bot berjalan |
| is_logged_in | BOOLEAN DEFAULT FALSE | Apakah sudah login ke WhatsApp |
| last_activity | TIMESTAMP NULL | Terakhir ada pesan atau heartbeat |
| updated_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP |

### 8.3 Tabel `bot_commands`

| Column | Type | Description |
|--------|------|-------------|
| id | INT AUTO_INCREMENT PRIMARY KEY | - |
| command | VARCHAR(50) NOT NULL | 'logout', 'restart' |
| status | ENUM('pending','processed','failed') DEFAULT 'pending' | Status eksekusi |
| created_at | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | - |
| processed_at | TIMESTAMP NULL | - |

### 8.4 Tabel `users` (Laravel default)

Untuk autentikasi admin (menggunakan Laravel Breeze).

---

## 9. Integrasi dengan API Medify

Semua panggilan ke API Medify akan melalui wrapper yang menangani **token management** (login setiap 7 hari atau saat token expired). Berikut daftar endpoint yang digunakan:

| Fungsi | Endpoint | Metode | Body/Param |
|--------|----------|--------|-------------|
| Login | `/api/online/token` | POST | `{email, password}` |
| Cek pasien by NIK | `/api/online/data-pasien?nik=...` | GET | - |
| Cek pasien by No RM | `/api/online/data-pasien/{no_rm}` | GET | - |
| Buat pasien baru | `/api/online/pasien-create` | POST | `{nik, nama_pasien, tempat_lahir, tanggal_lahir, gender, alamat, phone, no_asuransi}` |
| Daftar poliklinik | `/api/online/clinics` | GET | - |
| Daftar dokter by poli | `/api/online/doctors?clinic_id={id}` | GET | - |
| Jadwal dokter | `/api/online/schedules?dokter_id={id}` | GET | - |
| Kuota layanan | `/api/online/data-kuota-layanan?dokter_id={id}` | GET | - |
| Ketersediaan bed | `/api/online/ketersediaan-tempat-tidur` | GET | - |
| Buat booking | `/api/online/booking-create` | POST | `{pasien_id, dokter_id, poliklinik_id, dokter_jadwal_id, tanggal_pemesanan, bayar_id}` |

**Catatan:**  
- Setiap request (kecuali login) harus menyertakan `Authorization: Bearer {token}`.  
- Bot akan menyimpan token di memory dan memperbarui jika sudah mendekati expired.

---

## 10. Non-Fungsional Requirements

| Parameter | Target | Keterangan |
|-----------|--------|-------------|
| Waktu respons bot (end-to-end) | ≤ 5 detik | Rata-rata, termasuk panggilan API |
| Uptime | 99% | Diluar maintenance terjadwal |
| Keamanan | Token API di environment variable, admin login wajib | - |
| Skalabilitas | Dapat menangani ≥ 1000 pengguna aktif | Dengan state di MySQL |
| Logging | Setiap pesan masuk/keluar dan error tercatat | File log rotasi harian |
| NLP Akurasi | ≥ 90% untuk intent yang terdefinisi | Dengan pengujian dan improvement regex |

---

## 11. Deliverables

1. **Kode sumber** di GitHub public:
   - `whatsapp-bot/` (Node.js + Baileys + NLP)
   - `laravel-admin/` (Laravel 11)
   - `docker-compose.yml` (opsional)
2. **Video demo** (maks 10 menit):
   - Demo percakapan natural (pendaftaran pasien baru/lama, cek jadwal, cek bed)
   - Demo web admin (login, lihat status, logout)
3. **README.md**:
   - Cara instalasi (Node.js, PHP, Composer, NPM, MySQL)
   - Konfigurasi `.env`
   - Cara menjalankan bot dan web admin
   - Screenshot atau link video
4. **Database schema** (file `database.sql` atau migration lengkap).
5. **Dokumentasi internal API bot** (jika ada endpoint untuk health check).

---

## 12. Timeline Pengerjaan

| Fase | Aktivitas | Durasi |
|------|-----------|--------|
| 1 | Setup proyek, integrasi Baileys, koneksi MySQL | 3 jam |
| 2 | Implementasi NLP Engine (regex + keyword matching) + state machine dasar | 3 jam |
| 3 | Integrasi API Medify (token, cek pasien, create pasien) | 3 jam |
| 4 | Alur pendaftaran 6 langkah + interactive buttons | 5 jam |
| 5 | Fitur cek jadwal dokter dan cek bed | 2 jam |
| 6 | Web admin Laravel (login, dashboard, kontrol logout) | 4 jam |
| 7 | Polling perintah dari admin, update status bot | 2 jam |
| 8 | Testing, debugging, penyempurnaan response natural | 3 jam |
| 9 | Dokumentasi (README, video demo) | 3 jam |
| **Total** | | **28 jam** (≈3.5 hari) |

*Batas pengumpulan: 01 Juni 2026, 08.00 – waktu masih mencukupi.*

---

## 13. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|----------|
| Akun WhatsApp diblokir karena penggunaan library unofficial | Tinggi | Gunakan akun WhatsApp cadangan; patuhi rate limit (minimal delay 2 detik antar pesan); hindari spam |
| API Medify downtime atau perubahan endpoint | Sedang | Implementasikan retry (3x) dan fallback message; notifikasi admin via log |
| Web admin tidak terhubung dengan bot karena database terpisah | Rendah | Pastikan bot dan Laravel menggunakan MySQL yang sama atau dapat diakses bersama |
| NLP gagal mendeteksi intent karena variasi bahasa | Sedang | Tambahkan pola regex secara bertahap; sediakan fallback menu bantuan jika intent tidak dikenali |
| State session hilang karena server restart | Rendah | State disimpan di MySQL, bot akan membaca ulang saat start |

---

## 14. Rencana Improvement NLP ke Depan

Meskipun menggunakan Regex + Keyword Matching sudah cukup untuk MVP, beberapa improvement dapat dilakukan untuk meningkatkan akurasi:
- **Fuzzy matching** (Levenshtein distance) untuk menangani typo.
- **Synonym dictionary** (misal "daftar" = "registrasi" = "booking").
- **Contextual intent** berdasarkan state saat ini (misal jika sudah dalam proses pendaftaran, keyword "batal" akan membatalkan, bukan memulai intent baru).
- **Sentiment analysis** sederhana (positif/negatif) untuk eskalasi ke human.

Semua improvement ini tetap tidak memerlukan API eksternal, cukup dengan logika tambahan di dalam bot.

---

## 15. Kesimpulan

Dokumen PRD ini mendefinisikan secara lengkap dan detail **WhatsApp Bot + Web Admin untuk Medify**, dengan penekanan pada **NLP ringan (Regex + Keyword Matching)** untuk memberikan pengalaman percakapan natural, serta integrasi penuh dengan API Medify yang sudah ada. Bot akan dibangun menggunakan **Baileys** di Node.js, dengan state management di MySQL, dan **Laravel** sebagai web admin. Seluruh rencana telah disusun untuk dapat diselesaikan dalam batas waktu yang diberikan.

**Dokumen ini disetujui oleh:**

- Product Owner: _______________
- Tech Lead: _______________

---

**Lampiran:**  
- Postman collection API Medify (@.agents/api_medify.json)  
- Contoh pola regex untuk NLP (@.agents/pola_regex_nlp.md)  
- Mockup alur percakapan (@.agents/mockup_alur_percakapan.md)
