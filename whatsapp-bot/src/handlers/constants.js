const HELP_TEXT =
  '🤖 *Halo! Saya Asisten Medify RS* — Senang bisa membantu Anda 😊\n\n' +
  'Silakan pilih salah satu di bawah ini, atau tulis langsung kebutuhan Anda dengan kata-kata sendiri.\n\n' +
  '━━━ *PENDAFTARAN* ━━━\n' +
  '1️⃣ *Daftar Berobat* — Booking rawat jalan\n' +
  '   Contoh: "Saya mau daftar ke poli" atau "Booking dokter"\n\n' +
  '━━━ *INFORMASI* ━━━\n' +
  '2️⃣ *Jadwal Dokter* — Cek jadwal praktek per poli\n' +
  '   Contoh: "Jadwal dokter penyakit dalam" atau "Cek jadwal"\n\n' +
  '3️⃣ *Ketersediaan Tempat Tidur* — Info bed kosong\n' +
  '   Contoh: "Ada tempat tidur kosong?" atau "Cek bed"\n\n' +
  '4️⃣ *Status Booking* — Cek status pendaftaran Anda\n' +
  '   Contoh: "Cek status booking saya" atau "Lihat status"\n\n' +
  '5️⃣ *Paket Medical Check-Up* — Info & harga MCU\n' +
  '   Contoh: "Info MCU" atau "Paket medical checkup"\n\n' +
  '6️⃣ *Antrian Poli* — Cek nomor antrian terkini\n' +
  '   Contoh: "Antrian poli umum" atau "Cek antrian"\n\n' +
  '7️⃣ *Jadwal per Tanggal* — Lihat semua dokter praktik di tanggal tertentu\n' +
  '   Contoh: "Dokter praktek hari ini" atau "Jadwal tanggal 5 Juni"\n\n' +
  '━━━ *PENGELOLAAN* ━━━\n' +
  '8️⃣ *Batalkan Booking* — Batalkan pendaftaran yang sudah dibuat\n' +
  '   Contoh: "Saya mau batalkan booking" atau "Cancel booking"\n\n' +
  '━━━ *BANTUAN* ━━━\n' +
  '0️⃣ *Bantuan* — Tampilkan menu ini lagi\n' +
  '💬 Atau tulis saja apa yang Anda butuhkan!\n\n' +
  'Ketik *Batal* kapan saja untuk kembali ke menu utama.';

const MENU_NUMBERS = {
  '0': 'HELP',
  '1': 'REGISTRATION',
  '2': 'CHECK_DOCTOR_SCHEDULE',
  '3': 'CHECK_BED',
  '4': 'STATUS',
  '5': 'MCU',
  '6': 'CHECK_QUEUE',
  '7': 'CHECK_SCHEDULE_BY_DATE',
  '8': 'CANCEL_BOOKING',
};

const DAY_KEYS = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

function getDayKey(dateStr) {
  const [y, m, d] = dateStr.split('-').map(Number);
  const date = new Date(y, m - 1, d);
  return DAY_KEYS[date.getDay()];
}

function findJadwalId(jadwal, dayKey) {
  const dayData = jadwal[dayKey];
  if (!dayData?.data?.length) return null;
  return dayData.data[0].id || dayData.data[0].jadwal_dokter_id || null;
}

const PREV_STATE = {
  AWAITING_ID: 'IDLE',
  AWAITING_NEW_PATIENT_DATA: 'AWAITING_ID',
  AWAITING_RETRY_OR_NEW: 'AWAITING_ID',
  AWAITING_PAYMENT_METHOD: 'AWAITING_ID',
  AWAITING_INSURANCE: 'AWAITING_PAYMENT_METHOD',
  AWAITING_CLINIC: 'AWAITING_PAYMENT_METHOD',
  AWAITING_DOCTOR: 'AWAITING_CLINIC',
  AWAITING_DATE: 'AWAITING_DOCTOR',
  CONFIRM_BOOKING: 'AWAITING_DATE',
  AWAITING_STATUS: 'IDLE',
  AWAITING_QUEUE_CLINIC: 'IDLE',
  AWAITING_QUEUE_DATE: 'AWAITING_QUEUE_CLINIC',
  AWAITING_DOCTOR_SCHEDULE_CLINIC: 'IDLE',
  AWAITING_DOCTOR_SCHEDULE_DOCTOR: 'AWAITING_DOCTOR_SCHEDULE_CLINIC',
  AWAITING_SCHEDULE_DATE: 'IDLE',
  AWAITING_CANCEL_BOOKING_ID: 'IDLE',
  AWAITING_CANCEL_BOOKING_SELECT: 'AWAITING_CANCEL_BOOKING_ID',
  AWAITING_CANCEL_BOOKING_CONFIRM: 'AWAITING_CANCEL_BOOKING_SELECT',
};

const BACK_KEYWORDS = ['0', 'BACK', 'KEMBALI', 'MUNDUR', 'SEBELUMNYA'];

export { HELP_TEXT, MENU_NUMBERS, DAY_KEYS, getDayKey, findJadwalId, PREV_STATE, BACK_KEYWORDS };
