import * as db from '../database.js';
import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

export async function handleCancelBooking(sender) {
  await db.upsertSession(sender, 'AWAITING_CANCEL_BOOKING_ID', {});
  await sendWithDelay(sender,
    '🗑️ *Batalkan Booking*\n\n' +
    'Silakan kirimkan *No Rekam Medis (RM)* atau *NIK* untuk melihat booking aktif Anda.\n\n' +
    'Contoh:\n' +
    '• No RM: `000001`\n' +
    '• NIK: `3674060903970004`'
  );
}

export async function handleAwaitingCancelBookingId(sender, message, formData) {
  const id = message.trim();
  if (!/^\d{4,20}$/.test(id) && !/^\d{16}$/.test(id)) {
    await sendWithDelay(sender, '❌ Format kurang tepat. Masukkan No RM (4-20 digit) atau NIK (16 digit).');
    return;
  }

  try {
    let patient;
    if (id.length === 16) {
      const res = await medifyApi.getPatientByNik(id);
      patient = res.data || res;
    } else {
      const res = await medifyApi.getPatientByRm(id);
      patient = res.data || res;
    }

    formData.no_rm = patient.no_rm || id;
    formData.pasien_id = patient.id;
    await db.updateSessionData(sender, formData);

    const regs = await medifyApi.getPatientRegistrations(patient.no_rm || id);
    const bookings = (regs.data || []).filter(b => !b.is_cancel);

    if (!bookings.length) {
      await sendWithDelay(sender, '📭 *Tidak ada booking aktif* yang bisa dibatalkan.');
      await db.resetSession(sender);
      return;
    }

    const bookingList = bookings.slice(0, 5).map((b, i) =>
      `${i + 1}. ${b.poliklinik_nama || '-'} — ${b.dokter_nama || '-'} (${b.waktu_estimasi ? b.waktu_estimasi.split(' ')[0] : '-'})`
    ).join('\n');

    formData._cancel_bookings = bookings;
    await db.updateSessionData(sender, formData);
    await db.updateSessionState(sender, 'AWAITING_CANCEL_BOOKING_SELECT');

    await sendWithDelay(sender,
      `📋 *Booking Aktif — ${patient.no_rm || id}*\n\n${bookingList}\n\n` +
      `Ketik *nomor* booking yang ingin dibatalkan. Atau *0* untuk kembali.`
    );
  } catch (error) {
    console.error('Cancel booking error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    await db.resetSession(sender);
  }
}

export async function handleAwaitingCancelBookingSelect(sender, message, formData) {
  const idx = parseInt(message.trim()) - 1;
  const bookings = formData._cancel_bookings || [];

  if (isNaN(idx) || idx < 0 || idx >= bookings.length) {
    await sendWithDelay(sender, `❌ Pilihan tidak valid. Ketik angka 1-${bookings.length}.`);
    return;
  }

  formData._cancel_target = bookings[idx];
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'AWAITING_CANCEL_BOOKING_CONFIRM');

  await sendWithDelay(sender,
    '⚠️ *Konfirmasi Pembatalan*\n\n' +
    `🏥 Poli: *${bookings[idx].poliklinik_nama || '-'}*\n` +
    `🩺 Dokter: *${bookings[idx].dokter_nama || '-'}*\n` +
    `📅 Tanggal: *${bookings[idx].waktu_estimasi ? bookings[idx].waktu_estimasi.split(' ')[0] : '-'}*\n\n` +
    `Yakin ingin membatalkan booking ini?\n\n` +
    `👉 Ketik *KONFIRM* untuk batalkan\n` +
    `👉 Ketik *BATAL* untuk kembali`
  );
}

export async function handleAwaitingCancelBookingConfirm(sender, message, formData) {
  const msg = message.trim().toUpperCase();
  const confirmWords = ['KONFIRM', 'LANJUT', 'JADI', 'YAKIN', 'YA', 'YES', 'OK', 'OKE', 'Y'];

  const isConfirm = confirmWords.some(w => msg.startsWith(w)) || confirmWords.includes(msg);
  const isCancel = /^(BATAL|CANCEL|TIDAK|KEMBALI)\b/i.test(msg);

  if (isConfirm) {
    try {
      const target = formData._cancel_target;
      const result = await medifyApi.cancelBooking({
        no_rm: formData.no_rm,
        id: target.id,
      });

      await db.resetSession(sender);
      await sendWithDelay(sender,
        '✅ *Booking Berhasil Dibatalkan!*\n\n' +
        `🏥 Poli: ${target.poliklinik_nama || '-'}\n` +
        `🩺 Dokter: ${target.dokter_nama || '-'}\n` +
        `📅 Tanggal: ${target.waktu_estimasi ? target.waktu_estimasi.split(' ')[0] : '-'}\n\n` +
        'Ada yang bisa saya bantu lagi? Ketik *0* untuk menu utama.'
      );
    } catch (error) {
      console.error('Cancel booking API error:', error.message);
      await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, gagal membatalkan booking. Coba lagi nanti ya 🙏');
    }
  } else if (isCancel) {
    await db.resetSession(sender);
    await sendWithDelay(sender, '✅ Pembatalan dibatalkan. Ketik *0* untuk menu utama.');
  } else {
    await sendWithDelay(sender,
      '👉 Ketik *KONFIRM* untuk batalkan\n' +
      '👉 Ketik *BATAL* untuk kembali'
    );
  }
}
