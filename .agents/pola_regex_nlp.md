Berikut adalah file **`nlp-regex-patterns.md`** yang berisi contoh pola regex untuk NLP Engine pada WhatsApp Bot Medify.

---

# NLP Regex Patterns - WhatsApp Bot Medify

**Versi:** 1.0  
**Tujuan:** Mendeteksi intent pengguna secara alami sebelum memasuki mode pilihan angka.

---

## 📌 Daftar Intent dan Pola Regex

| Intent | Deskripsi | Pola Regex (Case-Insensitive) | Contoh Pesan User |
|--------|-----------|-------------------------------|-------------------|
| `REGISTRATION` | Pendaftaran rawat jalan baru | `\b(daftar|registrasi|booking|buat janji|pendaftaran|janji temu|mau daftar|butuh daftar)\b` | "Saya mau daftar berobat", "Booking poli jantung", "Buat janji dengan dokter" |
| `CHECK_DOCTOR_SCHEDULE` | Cek jadwal praktek dokter | `\b(jadwal dokter|praktek dokter|dokter praktek|cek jadwal|dokter (.*) praktek|kapan (.*) praktek)\b` | "Cek jadwal dokter anak", "Dokter jantung praktek kapan?", "Jadwal poli saraf" |
| `CHECK_BED` | Cek ketersediaan tempat tidur | `\b(tempat tidur|bed kosong|ketersediaan bed|rawat inap|ruang kosong|tempat tidur kosong|bed tersedia)\b` | "Ada tempat tidur kosong?", "Cek bed ICU", "Ketersediaan rawat inap" |
| `HELP` | Bantuan / menu utama | `\b(tolong|bantuan|help|menu|can you help|apa yang bisa kamu bantu|perintah|panduan)\b` | "Tolong", "Bantuan", "Menu", "Apa yang bisa kamu bantu?" |
| `CANCEL` | Membatalkan proses pendaftaran (jika dalam sesi) | `\b(batal|cancel|batalkan|urungkan|kembali|gak jadi)\b` | "Batal", "Saya batalkan pendaftaran", "Cancel" |
| `STATUS` | Cek status pendaftaran yang sudah dibuat | `\b(status|cek booking|kode booking|cek antrian|status pendaftaran)\b` | "Cek status booking saya", "Status antrian" |
| `CONTINUE` | Melanjutkan sesi yang tertunda (tanpa mengulang) | `\b(lanjut|continue|next|lanjutkan|ya|ok|oke)\b` | "Lanjut", "Ya", "Ok" |

---

## 🧠 Aturan Tambahan

1. **Prioritas Intent**  
   Jika sebuah pesan cocok dengan lebih dari satu pola, prioritas diberikan ke intent dengan urutan:  
   `CANCEL` > `REGISTRATION` > `CHECK_DOCTOR_SCHEDULE` > `CHECK_BED` > `STATUS` > `HELP`  
   (kecuali `CONTINUE` hanya diproses jika state saat ini sedang menunggu konfirmasi).

2. **Preprocessing Pesan**  
   Sebelum dicocokkan dengan regex, pesan akan:  
   - Diubah menjadi huruf kecil (`.toLowerCase()`).  
   - Tanda baca seperti `.,!?;:` dihapus.  
   - Spasi ganda dinormalisasi.

3. **Context-Aware Intent**  
   - Jika user dalam state `AWAITING_CONFIRMATION`, maka pesan "ya" / "lanjut" akan dipetakan ke `CONTINUE`, bukan ke intent lain.  
   - Jika user dalam state `IDLE`, `CONTINUE` diabaikan.

4. **Fallback untuk Pesan Tidak Dikenal**  
   Jika tidak ada intent yang cocok, bot akan merespon:  
   > "Maaf, saya tidak mengerti. Ketik 'Bantuan' untuk melihat menu yang tersedia."

---

## 📝 Contoh Implementasi di Node.js

```javascript
const intentPatterns = {
  REGISTRATION: /\b(daftar|registrasi|booking|buat janji|pendaftaran|janji temu|mau daftar|butuh daftar)\b/i,
  CHECK_DOCTOR_SCHEDULE: /\b(jadwal dokter|praktek dokter|dokter praktek|cek jadwal|dokter\s+\w+\s+praktek|kapan\s+\w+\s+praktek)\b/i,
  CHECK_BED: /\b(tempat tidur|bed kosong|ketersediaan bed|rawat inap|ruang kosong|tempat tidur kosong|bed tersedia)\b/i,
  HELP: /\b(tolong|bantuan|help|menu|can you help|apa yang bisa kamu bantu|perintah|panduan)\b/i,
  CANCEL: /\b(batal|cancel|batalkan|urungkan|kembali|gak jadi)\b/i,
  STATUS: /\b(status|cek booking|kode booking|cek antrian|status pendaftaran)\b/i,
  CONTINUE: /\b(lanjut|continue|next|lanjutkan|ya|ok|oke)\b/i,
};

function detectIntent(message, currentState = 'IDLE') {
  let cleaned = message.toLowerCase().replace(/[.,!?;:]/g, '');
  cleaned = cleaned.replace(/\s+/g, ' ').trim();

  // Jika sedang menunggu konfirmasi, prioritaskan CONTINUE
  if (currentState === 'AWAITING_CONFIRMATION' && intentPatterns.CONTINUE.test(cleaned)) {
    return 'CONTINUE';
  }

  // Cek cancel terlebih dahulu
  if (intentPatterns.CANCEL.test(cleaned)) return 'CANCEL';

  // Cek intent lainnya sesuai urutan prioritas
  if (intentPatterns.REGISTRATION.test(cleaned)) return 'REGISTRATION';
  if (intentPatterns.CHECK_DOCTOR_SCHEDULE.test(cleaned)) return 'CHECK_DOCTOR_SCHEDULE';
  if (intentPatterns.CHECK_BED.test(cleaned)) return 'CHECK_BED';
  if (intentPatterns.STATUS.test(cleaned)) return 'STATUS';
  if (intentPatterns.HELP.test(cleaned)) return 'HELP';

  return null; // no intent detected
}
```

---

## 🔄 Cara Improvement (Evolusi NLP)

Pola regex dapat dikembangkan seiring waktu dengan:
- Menambahkan **sinonim** atau **variasi bahasa daerah**.
- Menggunakan **Levenshtein distance** untuk mengatasi typo (misal: "daftar" menjadi "daftra").
- Menambahkan **kata kunci kontekstual** seperti `poli`, `dokter spesialis`, `kelas 1`, dll.
- Melakukan **logging pesan yang tidak terdeteksi** untuk dianalisis dan ditambahkan pola baru.

Dengan pola ini, bot dapat memberikan pengalaman percakapan yang cukup alami tanpa memerlukan API NLP eksternal yang mahal.

---

**Lampiran PRD Medify - WhatsApp Bot**  
