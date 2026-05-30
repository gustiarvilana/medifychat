let sendMessageFn = null;

export function setSendMessage(fn) {
  sendMessageFn = fn;
}

function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function sendWithDelay(jid, text) {
  await delay(3000);
  await sendMessageFn(jid, text);
}

function apiErrorMessage(error) {
  return error?.response?.data?.message || null;
}

function bookingErrorSuggestion(rawMsg) {
  const msg = (rawMsg || '').toLowerCase();
  if (msg.includes('kuota') && msg.includes('penuh')) {
    return {
      title: `⚠️ *${rawMsg}*`,
      body: '💡 *Saran dari saya:*\n' +
        '• Coba daftar dengan *tanggal* atau *poli* lain\n' +
        '• Cek *jadwal dokter* lewat menu untuk lihat ketersediaan\n' +
        '• Hubungi *loket pendaftaran* RS untuk bantuan lebih lanjut\n\n' +
        '👉 Ketik *0* untuk ubah tanggal/poli\n' +
        '👉 Ketik *BATAL* untuk batal\n' +
        '👉 Ketik *KONFIRM* untuk coba lagi',
    };
  }
  if (msg.includes('telah memesan') || msg.includes('hari yang sama') || msg.includes('sudah terdaftar')) {
    return {
      title: `⚠️ *${rawMsg}*`,
      body: '💡 *Saran dari saya:*\n' +
        '• Ketik *4* untuk *Cek Status Booking* — lihat booking aktif Anda\n' +
        '• Coba daftar dengan *poli* atau *tanggal* lain\n' +
        '• Hubungi *loket pendaftaran* RS untuk bantuan\n\n' +
        '👉 Ketik *0* untuk ubah tanggal/poli\n' +
        '👉 Ketik *BATAL* untuk batal',
    };
  }
  if (msg.includes('melebihi') || msg.includes('batas')) {
    return {
      title: `⚠️ *${rawMsg}*`,
      body: '💡 *Saran dari saya:*\n' +
        '• Coba daftar dengan *tanggal* yang masih tersedia\n' +
        '• Cek *jadwal dokter* (menu 2) untuk lihat jadwal lain\n' +
        '• Hubungi *loket pendaftaran* RS untuk bantuan\n\n' +
        '👉 Ketik *0* untuk ubah tanggal\n' +
        '👉 Ketik *BATAL* untuk batal\n' +
        '👉 Ketik *KONFIRM* untuk coba lagi',
    };
  }
  return null;
}

const STEP_FIELDS = {
  AWAITING_PAYMENT_METHOD: ['bayar_id','metode_bayar','asuransi_id','nama_asuransi','no_asuransi','_insurances'],
  AWAITING_CLINIC: ['poliklinik_id','nama_poli','_clinics'],
  AWAITING_DOCTOR: ['dokter_id','nama_dokter','dokter_jadwal_id','_doctors','_schedules'],
  AWAITING_DATE: ['tanggal_pemesanan','dokter_jadwal_id'],
  CONFIRM_BOOKING: [],
  AWAITING_QUEUE_CLINIC: ['_clinics','poliklinik_id','nama_poli'],
  AWAITING_DOCTOR_SCHEDULE_CLINIC: ['_clinics'],
  AWAITING_DOCTOR_SCHEDULE_DOCTOR: ['_selectedClinic','_doctorList'],
  AWAITING_CANCEL_BOOKING_SELECT: ['_cancel_bookings'],
  AWAITING_CANCEL_BOOKING_CONFIRM: ['_cancel_target'],
};

function goBack(formData, fromState) {
  const fields = STEP_FIELDS[fromState] || [];
  const cleared = { ...formData };
  for (const k of fields) {
    delete cleared[k];
  }
  return cleared;
}

export { sendMessageFn, delay, sendWithDelay, apiErrorMessage, bookingErrorSuggestion, goBack };
