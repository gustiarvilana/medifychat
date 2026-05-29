# Penanganan Kesalahan Input No RM / NIK

## 📌 Ringkasan

Ketika user memasukkan **No RM** atau **NIK** yang tidak valid (tidak ditemukan di database Medify), bot harus memberikan respons yang jelas dan memberi kesempatan untuk mengulang atau memilih jalur alternatif (misalnya mendaftar sebagai pasien baru).

---

## 🔍 Skenario & Respon Bot

### Skenario 1: No RM Tidak Ditemukan

| # | Pengirim | Pesan | State | Aksi & Respon Bot |
|---|----------|-------|-------|-------------------|
| 1 | User | 999999 | `AWAITING_ID` | Bot memanggil API `GET /api/online/data-pasien/999999`. API merespon `404 Not Found` atau `data` kosong. |
| 2 | Bot | ❌ **Nomor Rekam Medis 999999 tidak ditemukan.** Apakah Anda pasien baru? Ketik `YA` untuk mendaftar dengan NIK, atau `ULANG` untuk memasukkan No RM kembali. | tetap `AWAITING_ID` | Simpan state sementara `AWAITING_RETRY_OR_NEW`. |
| 3a | User | YA | `AWAITING_RETRY_OR_NEW` | Bot beralih ke alur pendaftaran pasien baru (minta NIK). |
| 3b | User | ULANG | `AWAITING_RETRY_OR_NEW` | Bot meminta No RM lagi. |
| 3c | User | (pesan lain) | `AWAITING_RETRY_OR_NEW` | Bot ulangi instruksi: "Ketik `YA` untuk daftar baru, atau `ULANG` untuk coba No RM lain." |

---

### Skenario 2: NIK Tidak Ditemukan (Pasien Baru)

| # | Pengirim | Pesan | State | Aksi & Respon Bot |
|---|----------|-------|-------|-------------------|
| 1 | User | 1234567890123456 | `AWAITING_ID` | Bot memanggil API `GET /api/online/data-pasien?nik=1234567890123456`. Respon `404` (tidak ditemukan). |
| 2 | Bot | 🔍 **NIK 1234567890123456 belum terdaftar.** Kami akan membantu Anda mendaftar sebagai pasien baru. Silakan kirimkan **nama lengkap** Anda. | `AWAITING_NEW_PATIENT_DATA` | Langsung mulai alur pendaftaran pasien baru (tanpa harus konfirmasi lagi, karena sudah jelas pasien baru). |

> **Catatan:** Jika NIK ditemukan, berarti pasien sudah ada (lama). Bot akan lanjut ke pemilihan metode bayar.

---

### Skenario 3: Format No RM / NIK Tidak Valid (Validasi Sederhana)

Sebelum memanggil API, bot bisa melakukan validasi format dasar untuk mengurangi request tidak perlu.

**Aturan validasi:**
- No RM: minimal 4 digit, maksimal 20 digit, hanya angka.
- NIK: harus 16 digit angka (standar NIK Indonesia).

Jika format salah, bot langsung merespon tanpa panggil API.

| # | Pengirim | Pesan | Respon Bot |
|---|----------|-------|-------------|
| 1 | User | abc123 | ❌ **Format tidak valid.** No RM harus berupa angka (4-20 digit). Contoh: `000001`. |
| 2 | User | 123 | ❌ **No RM terlalu pendek.** Minimal 4 digit. Coba lagi. |
| 3 | User | 12345678901234567 | ❌ **NIK harus 16 digit angka.** Coba lagi. |

Bot tetap di state `AWAITING_ID`.

---

## ⚙️ Implementasi Teknis

### 1. Penanganan Error dari API Medify

API Medify dapat mengembalikan beberapa kemungkinan response untuk endpoint data pasien:

| Status Code | Arti | Penanganan Bot |
|-------------|------|----------------|
| 200 | Data ditemukan | Lanjut ke state berikutnya (`AWAITING_PAYMENT_METHOD`) |
| 401 | Token tidak valid | Refresh token, lalu ulangi request (maks 1 kali) |
| 404 | Data tidak ditemukan | Tampilkan pesan tidak ditemukan, tawarkan ulang atau daftar baru |
| 500 | Error server | Tampilkan pesan error teknis, minta user coba lagi nanti |

**Contoh kode handler:**

```javascript
async function handleIdentification(sender, message, currentState) {
  const id = message.trim();
  let pasien = null;
  
  // Validasi format sederhana
  if (!/^\d{4,20}$/.test(id) && !/^\d{16}$/.test(id)) {
    await sendMessage(sender, '❌ Format tidak valid. No RM (4-20 digit) atau NIK (16 digit). Coba lagi.');
    return;
  }
  
  try {
    // Coba cari berdasarkan No RM atau NIK
    let response;
    if (id.length === 16) {
      response = await medifyApi.get(`/data-pasien?nik=${id}`);
    } else {
      response = await medifyApi.get(`/data-pasien/${id}`);
    }
    
    if (response.status === 'Success') {
      pasien = response.data;
      // Simpan pasien_id, no_rm ke session
      updateSession(sender, 'AWAITING_PAYMENT_METHOD', { pasien_id: pasien.id, no_rm: pasien.no_rm });
      await sendMessage(sender, `Terima kasih, ${pasien.name}. Silakan pilih metode bayar: 1️⃣ Tunai, 2️⃣ BPJS, 3️⃣ Asuransi Lain`);
    }
  } catch (error) {
    if (error.response?.status === 404) {
      // Data tidak ditemukan
      if (id.length === 16) {
        // NIK tidak ditemukan -> arahkan ke daftar baru
        await sendMessage(sender, '🔍 NIK belum terdaftar. Kami akan bantu daftar sebagai pasien baru. Kirimkan nama lengkap Anda.');
        updateSession(sender, 'AWAITING_NEW_PATIENT_DATA', { nik: id });
      } else {
        // No RM tidak ditemukan -> tawarkan opsi
        await sendMessage(sender, '❌ Nomor Rekam Medis tidak ditemukan. Ketik YA untuk daftar baru, atau ULANG untuk coba No RM lain.');
        updateSession(sender, 'AWAITING_RETRY_OR_NEW', { invalid_rm: id });
      }
    } else {
      // Error lain (401, 500)
      await sendMessage(sender, '⚠️ Terjadi kendala teknis. Silakan coba lagi beberapa saat. (Error: ' + error.message + ')');
    }
  }
}
```

### 2. State `AWAITING_RETRY_OR_NEW`

- User dapat mengirim `YA` → pindah ke state `AWAITING_NEW_PATIENT_DATA` (minta NIK).
- User mengirim `ULANG` → kembali ke state `AWAITING_ID` (minta No RM/NIK lagi).
- User mengirim selain itu → bot ulangi instruksi: "Ketik YA untuk daftar baru, atau ULANG untuk coba lagi."

### 3. Timeout dan Reset

Jika user tidak merespon dalam **30 menit** pada state `AWAITING_RETRY_OR_NEW`, session akan direset ke `IDLE` dan user harus memulai dari awal.

---

## 📝 Contoh Percakapan Lengkap (Error Handling)

**Skenario A: No RM salah, lalu memilih daftar baru**

```
User: 999999
Bot: ❌ Nomor Rekam Medis 999999 tidak ditemukan. 
     Apakah Anda pasien baru? Ketik YA untuk mendaftar dengan NIK, atau ULANG untuk memasukkan No RM kembali.
User: YA
Bot: Silakan kirimkan NIK Anda (16 digit angka).
User: 3674060903970004
Bot: 🔍 NIK 3674060903970004 belum terdaftar. Kami akan membantu Anda mendaftar sebagai pasien baru. 
     Silakan kirimkan nama lengkap Anda.
User: Kevin Fachreza
... (lanjut alur pendaftaran pasien baru)
```

**Skenario B: Format salah**

```
User: 12A45
Bot: ❌ Format tidak valid. No RM harus berupa angka (4-20 digit). Contoh: 000001
User: 000001
Bot: Terima kasih, Kevin Fachreza. Silakan pilih metode bayar...
```

**Skenario C: No RM salah, lalu memilih ulang**

```
User: 888888
Bot: ❌ Nomor Rekam Medis 888888 tidak ditemukan. 
     Ketik YA untuk daftar baru, atau ULANG untuk coba No RM lain.
User: ULANG
Bot: Silakan kirimkan No RM atau NIK Anda.
User: 000001
Bot: Terima kasih, Kevin Fachreza. Silakan pilih metode bayar...
```

---

## ✅ Kesimpulan

Dengan penanganan error yang baik, bot akan:
- Memberikan feedback spesifik (tidak generik "maaf error").
- Membantu user memperbaiki input (validasi format).
- Memberikan opsi untuk beralih ke pendaftaran baru jika data tidak ditemukan.
- Tetap berada dalam state yang aman dan tidak menggantung.

Ini meningkatkan **user experience** dan mengurangi frustrasi.

--- 

*Lampiran PRD Medify - WhatsApp Bot*  
*Dokumen ini melengkapi mockup percakapan.*
