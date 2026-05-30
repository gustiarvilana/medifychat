const HELP_TEXT =
  'Halo! 👋 Saya asisten dari *RS Bhayangkara Setukpa Sukabumi*, senang bisa membantu Anda.\n\n' +
  'Yang bisa saya bantu:\n' +
  '• *Daftar berobat* — booking rawat jalan\n' +
  '• *Jadwal dokter* — cek jadwal praktek\n' +
  '• *Info tempat tidur* — ketersediaan bed\n' +
  '• *Status booking* — cek pendaftaran Anda\n' +
  '• *Paket MCU* — info medical check-up\n' +
  '• *Antrian poli* — cek nomor antrian\n' +
  '• *Jadwal per tanggal* — dokter praktek hari tertentu\n' +
  '• *Batalkan booking* — pembatalan pendaftaran\n\n' +
  'Cukup tulis apa yang Anda butuhkan dengan kata-kata sendiri, ya 😊\n' +
  'Contoh: \"Saya mau daftar ke poli\" atau \"Cek jadwal dokter\"';

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
