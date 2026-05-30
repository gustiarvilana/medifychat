import * as db from '../database.js';
import { detectIntent } from '../nlp-engine.js';
import { setSendMessage, sendWithDelay, goBack } from './utils.js';
import { HELP_TEXT, PREV_STATE, BACK_KEYWORDS } from './constants.js';
import { handleIdleState } from './idle.js';
import { handleAwaitingId, handleRetryOrNew, handleNewPatientData } from './registration.js';
import { handlePaymentMethod, handleClinic, handleDoctor, handleDate, handleConfirmBooking, handleAwaitingInsurance } from './booking.js';
import { handleCheckDoctorSchedule, handleDoctorScheduleClinic, handleDoctorScheduleDoctor } from './doctor-schedule.js';
import { handleCheckBed } from './bed.js';
import { handleStatus, handleAwaitingStatus } from './status.js';
import { handleCheckQueue, handleAwaitingQueueClinic, handleAwaitingQueueDate } from './queue.js';
import { handleMcu } from './mcu.js';
import { handleCancelBooking, handleAwaitingCancelBookingId, handleAwaitingCancelBookingSelect, handleAwaitingCancelBookingConfirm } from './cancel-booking.js';
import { handleScheduleByDate, handleAwaitingScheduleDate } from './schedule-by-date.js';

export { setSendMessage };

export async function handleMessage(sender, message, waName = null) {
  if (!message || !message.trim()) return;
  const msg = message.trim();
  let session = await db.getSession(sender);

  if (!session) {
    await db.upsertSession(sender, 'IDLE', {}, waName);
    session = { wa_id: sender, wa_name: waName, current_state: 'IDLE', form_data: '{}' };
  } else if (waName && session.wa_name !== waName) {
    const truncated = waName.length > 100 ? waName.slice(0, 100) : waName;
    await db.execute('UPDATE user_sessions SET wa_name = ? WHERE wa_id = ?', [truncated, sender]);
  }

  const state = session.current_state;
  const raw = session.form_data || {};
  const formData = typeof raw === 'string' ? JSON.parse(raw) : raw;

  if (state === 'IDLE') {
    await handleIdleState(sender, msg);
    return;
  }

  const isBack = BACK_KEYWORDS.includes(msg.toUpperCase());
  if (isBack && PREV_STATE[state]) {
    const prevState = PREV_STATE[state];
    const cleared = goBack(formData, state);
    await db.upsertSession(sender, prevState, cleared);
    await sendWithDelay(sender, getPrevPrompt(prevState, state, cleared));
    return;
  }

  const globalIntent = detectIntent(msg, state);
  if (globalIntent === 'CANCEL') {
    await db.resetSession(sender);
    await sendWithDelay(sender, '✅ *Proses dibatalkan.*\n\nKetik *0* untuk melihat menu utama ya 😊');
    return;
  }
  if (globalIntent === 'HELP') {
    await db.resetSession(sender);
    await sendWithDelay(sender, HELP_TEXT);
    return;
  }
  if (globalIntent === 'REGISTRATION') {
    await db.resetSession(sender);
    await handleIdleState(sender, msg);
    return;
  }
  if (globalIntent === 'CHECK_BED') {
    await handleCheckBed(sender);
    return;
  }
  if (globalIntent === 'CHECK_DOCTOR_SCHEDULE') {
    await handleCheckDoctorSchedule(sender);
    return;
  }
  if (globalIntent === 'STATUS') {
    await handleStatus(sender);
    return;
  }
  if (globalIntent === 'CHECK_QUEUE') {
    await handleCheckQueue(sender);
    return;
  }
  if (globalIntent === 'MCU') {
    await handleMcu(sender);
    return;
  }
  if (globalIntent === 'CANCEL_BOOKING') {
    await handleCancelBooking(sender);
    return;
  }
  if (globalIntent === 'CHECK_SCHEDULE_BY_DATE') {
    await handleScheduleByDate(sender);
    return;
  }

  switch (state) {
    case 'AWAITING_ID':
      await handleAwaitingId(sender, msg, formData);
      break;
    case 'AWAITING_RETRY_OR_NEW':
      await handleRetryOrNew(sender, msg, formData);
      break;
    case 'AWAITING_NEW_PATIENT_DATA':
      await handleNewPatientData(sender, msg, formData);
      break;
    case 'AWAITING_PAYMENT_METHOD':
      await handlePaymentMethod(sender, msg, formData);
      break;
    case 'AWAITING_INSURANCE':
      await handleAwaitingInsurance(sender, msg, formData);
      break;
    case 'AWAITING_CLINIC':
      await handleClinic(sender, msg, formData);
      break;
    case 'AWAITING_DOCTOR':
      await handleDoctor(sender, msg, formData);
      break;
    case 'AWAITING_DOCTOR_SCHEDULE_CLINIC':
      await handleDoctorScheduleClinic(sender, msg, formData);
      break;
    case 'AWAITING_DOCTOR_SCHEDULE_DOCTOR':
      await handleDoctorScheduleDoctor(sender, msg, formData);
      break;
    case 'AWAITING_DATE':
      await handleDate(sender, msg, formData);
      break;
    case 'AWAITING_STATUS':
      await handleAwaitingStatus(sender, msg, formData);
      break;
    case 'AWAITING_QUEUE_CLINIC':
      await handleAwaitingQueueClinic(sender, msg, formData);
      break;
    case 'AWAITING_QUEUE_DATE':
      await handleAwaitingQueueDate(sender, msg, formData);
      break;
    case 'AWAITING_SCHEDULE_DATE':
      await handleAwaitingScheduleDate(sender, msg, formData);
      break;
    case 'AWAITING_CANCEL_BOOKING_ID':
      await handleAwaitingCancelBookingId(sender, msg, formData);
      break;
    case 'AWAITING_CANCEL_BOOKING_SELECT':
      await handleAwaitingCancelBookingSelect(sender, msg, formData);
      break;
    case 'AWAITING_CANCEL_BOOKING_CONFIRM':
      await handleAwaitingCancelBookingConfirm(sender, msg, formData);
      break;
    case 'CONFIRM_BOOKING':
      await handleConfirmBooking(sender, msg, formData);
      break;
    default:
      await db.resetSession(sender);
      await sendWithDelay(sender, '🙏 *Sesi berakhir.* Silakan ketik *0* untuk memulai lagi ya 😊');
  }
}

export async function handleMessageForState(sender, message, state, formData, waName = null) {
  const msg = message.trim();
  const isBack = BACK_KEYWORDS.includes(msg.toUpperCase());
  if (isBack && PREV_STATE[state]) {
    const prevState = PREV_STATE[state];
    const cleared = goBack(formData, state);
    await db.upsertSession(sender, prevState, cleared);
    await sendWithDelay(sender, getPrevPrompt(prevState, state, cleared));
    return true;
  }

  const globalIntent = detectIntent(message, state);
  if (globalIntent === 'CANCEL') {
    await db.resetSession(sender);
    await sendWithDelay(sender, '✅ *Proses dibatalkan.*\n\nKetik *0* untuk menu utama ya 😊');
    return true;
  }
  if (globalIntent === 'HELP') {
    await sendWithDelay(sender, HELP_TEXT);
    return true;
  }
  if (globalIntent === 'STATUS') {
    await handleStatus(sender);
    return true;
  }
  if (globalIntent === 'CHECK_QUEUE') {
    await handleCheckQueue(sender);
    return true;
  }
  if (globalIntent === 'MCU') {
    await handleMcu(sender);
    return true;
  }
  if (globalIntent === 'CANCEL_BOOKING') {
    await handleCancelBooking(sender);
    return true;
  }

  if (state === 'AWAITING_DOCTOR_SCHEDULE_CLINIC') {
    await handleDoctorScheduleClinic(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_DOCTOR_SCHEDULE_DOCTOR') {
    await handleDoctorScheduleDoctor(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_INSURANCE') {
    await handleAwaitingInsurance(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_QUEUE_CLINIC') {
    await handleAwaitingQueueClinic(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_QUEUE_DATE') {
    await handleAwaitingQueueDate(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_CANCEL_BOOKING_ID') {
    await handleAwaitingCancelBookingId(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_CANCEL_BOOKING_SELECT') {
    await handleAwaitingCancelBookingSelect(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_CANCEL_BOOKING_CONFIRM') {
    await handleAwaitingCancelBookingConfirm(sender, message, formData);
    return true;
  }
  if (state === 'AWAITING_SCHEDULE_DATE') {
    await handleAwaitingScheduleDate(sender, message, formData);
    return true;
  }
  return false;
}

function getPrevPrompt(prevState, currentState, formData) {
  const dayMap = { senin: 'Senin', selasa: 'Selasa', rabu: 'Rabu', kamis: 'Kamis', jumat: 'Jumat', sabtu: 'Sabtu', minggu: 'Minggu' };

  switch (prevState) {
    case 'AWAITING_ID':
      return '⬅️ *Kembali ke langkah awal.*\n\n' +
        'Silakan kirimkan:\n' +
        '✏️ *Pasien Lama*: No Rekam Medis (RM) — contoh: `000001`\n' +
        '✏️ *Pasien Baru*: NIK 16 digit — contoh: `3674060903970004`\n\n' +
        'Ketik *0* untuk kembali ke menu utama.';

    case 'AWAITING_PAYMENT_METHOD':
      return '⬅️ *Kembali ke pilih metode pembayaran.*\n\n' +
        '1️⃣ *Tunai*\n' +
        '2️⃣ *BPJS*\n' +
        '3️⃣ *Asuransi Lain*\n\n' +
        'Ketik angka 1, 2, atau 3.';

    case 'AWAITING_CLINIC': {
      const clinics = formData._clinics || [];
      if (!clinics.length) return '⬅️ Kembali. Silakan pilih poli (data tidak tersedia).';
      const list = clinics.map((c, i) => `${i + 1}. ${c.nama}`).join('\n');
      return `⬅️ *Kembali ke pilih poliklinik.*\n\n${list}\n\nKetik *nomor* poliklinik yang dituju.`;
    }

    case 'AWAITING_DOCTOR': {
      const doctors = formData._doctors || [];
      if (!doctors.length) return '⬅️ Kembali. Silakan pilih dokter (data tidak tersedia).';
      const list = doctors.map((d, i) => `${i + 1}. ${d.dokter.nama}`).join('\n');
      return `⬅️ *Kembali ke pilih dokter.*\n\n${list}\n\nKetik *nomor* dokter yang diinginkan.`;
    }

    case 'AWAITING_DATE': {
      const jadwal = formData._schedules || {};
      const scheduleList = Object.entries(dayMap)
        .filter(([key]) => jadwal[key] && jadwal[key].text && jadwal[key].text !== 'False')
        .map(([key, label]) => `  • ${label}: ${jadwal[key].text}`)
        .join('\n');
      return `⬅️ *Kembali ke input tanggal.*\n\n*Jadwal Praktek Dokter:*\n${scheduleList}\n\nSilakan kirimkan *tanggal kunjungan* (format: YYYY-MM-DD).\nContoh: 2026-06-01`;
    }

    case 'CONFIRM_BOOKING':
      return '⬅️ *Kembali ke konfirmasi.*\n\nKetik *KONFIRM* untuk lanjut, *0* untuk ubah data, atau *BATAL* untuk batal.';

    case 'AWAITING_DOCTOR_SCHEDULE_CLINIC': {
      const clinics = formData._clinics || [];
      if (!clinics.length) return '⬅️ Kembali. Silakan pilih poli (data tidak tersedia).';
      const list = clinics.map((c, i) => `${i + 1}. ${c.nama}`).join('\n');
      return `⬅️ *Kembali ke pilih poliklinik.*\n\n${list}\n\nKetik nomor poliklinik yang ingin dicek jadwalnya.`;
    }

    case 'AWAITING_STATUS':
      return '⬅️ *Kembali.*\n\nSilakan kirimkan No Rekam Medis (RM) untuk cek status. Atau ketik *0* untuk menu utama.';

    case 'AWAITING_QUEUE_CLINIC':
      return '⬅️ *Kembali.*\n\nSilakan pilih poliklinik untuk cek antrian. Atau ketik *0* untuk menu utama.';

    case 'AWAITING_QUEUE_DATE':
      return '⬅️ *Kembali ke pilih tanggal.*\n\nSilakan kirimkan tanggal (YYYY-MM-DD) untuk cek antrian.';

    case 'AWAITING_SCHEDULE_DATE':
      return '⬅️ *Kembali.*\n\nSilakan kirimkan tanggal (YYYY-MM-DD) untuk lihat jadwal dokter. Atau ketik *0* untuk menu utama.';

    case 'AWAITING_CANCEL_BOOKING_ID':
      return '⬅️ *Kembali.*\n\nSilakan kirimkan No Rekam Medis (RM) untuk melihat booking. Atau ketik *0* untuk menu utama.';

    case 'AWAITING_CANCEL_BOOKING_SELECT':
      return '⬅️ *Kembali.*\n\nSilakan kirimkan No Rekam Medis (RM) untuk melihat booking. Atau ketik *0* untuk menu utama.';

    case 'AWAITING_CANCEL_BOOKING_CONFIRM':
      return '⬅️ *Kembali ke pilih booking.*\n\nSilakan pilih booking yang ingin dibatalkan.';

    case 'IDLE':
      return '⬅️ *Kembali ke menu utama.*\n\n' + HELP_TEXT;

    default:
      return '⬅️ *Kembali ke langkah sebelumnya.* Silakan pilih ulang ya.';
  }
}
