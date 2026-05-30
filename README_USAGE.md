# Panduan Penggunaan Medify Chat

Panduan ini mencakup langkah-langkah mulai dari instalasi hingga skenario penggunaan aplikasi.

## 1. Instalasi (Mulai dari Clone)

Pastikan Anda telah menginstal **PHP 8.3+**, **Node.js 20.x**, **MySQL 8.0**, dan **Composer**.

1.  **Clone Repository:**
    ```bash
    git clone <url-repo-anda>
    cd medifychat
    ```

2.  **Instalasi & Setup Awal:**
    Jalankan perintah berikut untuk menginstal dependensi PHP & NPM, mengatur *environment*, dan menjalankan migrasi database:
    ```bash
    composer run setup
    ```

3.  **Konfigurasi `.env`:**
    Buka file `.env` di root dan `whatsapp-bot/.env`, sesuaikan konfigurasi database (`DB_...`) dan kredensial API Medify (`MEDIFY_API_...`).

## 2. Menjalankan Aplikasi

Aplikasi terdiri dari dua bagian yang harus berjalan bersamaan:

### Admin Dashboard (Laravel)
Jalankan server dashboard:
```bash
composer run dev
```

### WhatsApp Bot (Node.js)
Buka terminal baru, masuk ke direktori bot, dan jalankan:
```bash
cd whatsapp-bot
npm install
npm run dev
```

## 3. Skenario Penggunaan

### A. Admin Dashboard
1.  Buka browser dan akses `http://localhost:8000` (atau sesuaikan dengan konfigurasi Vite).
2.  **Login:** Masuk dengan akun admin.
3.  **Koneksi Bot:** Jika bot belum terhubung, pindai kode QR yang muncul di dashboard menggunakan WhatsApp Anda.
4.  **Manajemen Konteks:** Buka menu "Settings" > "Context" untuk mengunggah dokumen (.pdf, .docx, .txt, dll) sebagai basis pengetahuan bot.
5.  **Monitoring:** Pantau aktivitas pengguna dan status bot melalui dasbor utama.

### B. WhatsApp Bot (Pasien)
Bot dapat merespon secara natural. Berikut beberapa skenario:

*   **Daftar Berobat:**
    *   *User:* "Saya mau daftar berobat"
    *   *Bot:* (Meminta No RM atau NIK)
    *   *User:* (Kirim No RM/NIK)
    *   *Bot:* (Menampilkan pilihan poli, dokter, jadwal, lalu konfirmasi booking)
*   **Cek Jadwal Dokter:**
    *   *User:* "Jadwal dokter penyakit dalam"
    *   *Bot:* (Menampilkan daftar dokter dan jadwal di poli tersebut)
*   **Cek Ketersediaan Bed:**
    *   *User:* "Ada tempat tidur kosong?"
    *   *Bot:* (Menampilkan ringkasan ketersediaan bed)
*   **Cek Status Booking:**
    *   *User:* "Status booking saya"
    *   *Bot:* (Meminta No RM/NIK, lalu menampilkan detail booking aktif)

## 4. Tips & Troubleshooting
- Jika bot tidak merespons secara natural, pastikan API key Gemini di `whatsapp-bot/.env` valid dan kuota masih tersedia.
- Gunakan perintah `restart` dari Admin Dashboard jika bot terasa melambat.
- Selalu cek log error di dashboard jika bot tidak berfungsi.
