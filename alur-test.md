# Alur Test — SSoT + RS Name Dinamis + Context System Instruction

---

## Test 1: HELP_TEXT Konsisten (9 Fitur)

**Langkah:**
1. Chat WA: `bantuan` atau `menu`
2. Chat WA: `0`

**Cek:**
- [ ] Menampilkan 8 fitur (daftar, jadwal dokter, tempat tidur, status booking, mcu, antrian, jadwal per tanggal, batalkan booking)
- [ ] Nama RS muncul: `asisten dari *RS Bhayangkara Setukpa Sukabumi*`
- [ ] Format sama persis untuk `bantuan` dan `0`

---

## Test 2: Gemini Tahu Semua Fitur

**Langkah:**
1. Chat WA: `ada fitur apa saja?`

**Cek:**
- [ ] Gemini menyebutkan 8 fitur (bukan 5 seperti sebelumnya)
- [ ] Menyebutkan MCU, antrian, jadwal per tanggal, batalkan booking
- [ ] Nama RS disebutkan dengan benar

---

## Test 3: Ganti Nama RS dari Admin

**Langkah:**
1. Buka admin → Settings
2. Ganti "Nama Rumah Sakit" → `RS Baru Sehat`
3. Klik Save
4. Tunggu ±10 detik (heartbeat)
5. Chat WA: `bantuan`
6. Chat WA: `halo`

**Cek:**
- [ ] HELP_TEXT sekarang: `asisten dari *RS Baru Sehat*`
- [ ] Greeting: `Saya asisten dari RS Baru Sehat`
- [ ] Test Gemini: `ada fitur apa saja?` → menyebutkan RS Baru Sehat

---

## Test 4: Context Upload ke System Instruction

**Langkah:**
1. Buka admin → Context Manager
2. Upload file TXT berisi informasi RS (contoh di bawah)
3. Tunggu status jadi "Siap" (completed)
4. Chat WA: `info jam besuk`

**File test (`info-rs.txt`):**
```
Jam Besuk Pasien:
- Pagi: 08.00 - 11.00
- Sore: 14.00 - 16.00
- Khusus ICU: 15.00 - 16.00

Parkir: Tersedia parkir gratis untuk pengunjung.
Kantin: Buka 07.00 - 20.00.
```

**Cek:**
- [ ] Gemini menjawab tentang jam besuk sesuai isi file
- [ ] Tidak perlu menunggu lama (context sudah di system instruction)
- [ ] Jawaban akurat, bukan tebakan

---

## Test 5: Context Masih Berfungsi Saat Gemini Error (Fallback)

**Langkah:**
1. Upload file TXT dengan konten tentang RS (contoh di Test 4)
2. Matikan Gemini API key sementara (isi `xxxx` di Settings)
3. Chat WA: `info parkir`

**Cek:**
- [ ] Bot tetap merespons dengan konten dari file (via searchContext fallback)
- [ ] Bukan fallback "Maaf, kurang paham"

---

## Test 6: Chat History Masih Berfungsi

**Langkah:**
1. Chat WA: `nama saya Budi`
2. Chat WA: `siapa nama saya?`

**Cek:**
- [ ] Gemini ingat nama "Budi" dari chat sebelumnya
- [ ] Jawaban: "Nama kamu Budi" atau sejenis

---

## Test 7: Konsistensi Nama RS

**Cek di semua source berikut konsisten:**
- [ ] `HELP_TEXT` (`constants.js`) — pakai `BOT.rsName`
- [ ] Greeting (`idle.js:72`) — pakai `BOT.rsName`
- [ ] System instruction (`bot-profile.js:44`) — pakai `BOT.rsName`
- [ ] Menu text (`bot-profile.js:26`) — pakai `BOT.rsName`

Tidak ada lagi hardcoded "RS Bhayangkara Setukpa Sukabumi" di file selain `bot-profile.js` (sebagai default).

---

## Rollback Plan (jika perlu)

Jika bot error setelah update:
1. **Node.js error** → Cek log di terminal/konsol bot, pastikan import path benar
2. **Gemini error** → Cek apakah `gemini-api.js` bisa import `bot-profile.js` (circular dependency check)
3. **Migration error** → `php artisan migrate:rollback` untuk hapus kolom `rs_name`
4. **Kembalikan file** ke versi git: `git checkout -- whatsapp-bot/src/`
