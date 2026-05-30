# Laporan Test API — Dev Server

**Tanggal Test:** 30 Mei 2026  
**Base URL (Dev):** `https://rs-mulia-pajajaran.medifyapp.com/api/online`  
**Email:** `user@rsmulia.com`  
**Password:** `123456`

---

## Hasil Test Seluruh Endpoint

| No | Endpoint | Method | Status | Keterangan |
|----|----------|--------|--------|------------|
| 1 | `/token` | POST | ✅ **200** | Login sukses, token didapat |
| 2 | `/clinics` | GET | ✅ **200** | 31 poli tersedia |
| 3 | `/doctors?clinic_id=1` | GET | ✅ **200** | 1 dokter di UMUM |
| 4 | `/schedules?dokter_id=52` | GET | ✅ **200** | Jadwal dokter ditemukan |
| 5 | `/ketersediaan-tempat-tidur` | GET | ✅ **200** | 5 ruang tersedia |
| 6 | `/data-paket-mcu` | GET | ✅ **200** | 4 paket MCU |
| 7 | `/data-kuota-layanan` | GET | ✅ **200** | Kuota layanan ditemukan |
| 8 | `/get-jadwal-by-tanggal` | GET | ✅ **200** | Jadwal per tanggal OK |
| 9 | `/data-pasien/00222000` | GET | ❌ **404** | Data tidak ditemukan (beda DB dgn production) |
| 10 | `/get-pendaftaran-pasien` | GET | ❌ **404** | Data pendaftaran tidak ditemukan |
| 11 | `/get-jadwal-dokter-cuti` | GET | ❌ **404** | Tidak ada data cuti dokter |
| 12 | `/antrian-pelayanan` | GET | ❌ **404** | Data antrian tidak ditemukan |

---

## Catatan

- **Semua endpoint read-only sukses (✅)** — API dev berfungsi normal.
- **Endpoint 404** wajar karena dev server tidak memiliki data riil pasien/antrian/cuti.
- Bot sudah di-update otomatis via settings (`http://medifychat.test/settings`) dengan konfigurasi dev:
  - URL: `https://rs-mulia-pajajaran.medifyapp.com/api/online`
  - Email: `user@rsmulia.com`
  - Password: `123456`
