import * as db from '../database.js';
import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

export async function handleStatus(sender) {
  await db.upsertSession(sender, 'AWAITING_STATUS', {});
  await sendWithDelay(sender,
    '🔍 *Cek Status Booking*\n\n' +
    'Silakan kirimkan *No Rekam Medis (RM)* atau *NIK* untuk melihat status pendaftaran Anda.\n\n' +
    'Contoh:\n' +
    '• No RM: `000001`\n' +
    '• NIK: `3674060903970004`'
  );
}

export async function handleAwaitingStatus(sender, message, formData) {
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

    const noRm = patient.no_rm || id;
    let dataList = [];
    try {
      const regs = await medifyApi.getPatientRegistrations(noRm);
      dataList = regs.data || [];
    } catch (regErr) {
      console.error('Status API error:', regErr.message);
      const errMsg = apiErrorMessage(regErr) || 'Layanan pengecekan status sedang bermasalah.';
      await sendWithDelay(sender,
        `⚠️ *${errMsg}*\n\n` +
        'Mohon coba lagi nanti ya 🙏'
      );
      await db.resetSession(sender);
      return;
    }

    if (!dataList.length) {
      await sendWithDelay(sender, '📭 *Tidak ada booking aktif* untuk pasien ini saat ini.');
    } else {
      const lines = dataList.slice(0, 5).map((b, i) => {
        const statusIcon = b.is_cancel ? '❌' : b.is_selesai_pelayanan ? '✅' : '⏳';
        return `${i + 1}. ${statusIcon} *${b.poliklinik_nama || '-'}*\n` +
          `   🩺 Dokter: ${b.dokter_nama || '-'}\n` +
          `   📅 Tanggal: ${b.waktu_estimasi ? b.waktu_estimasi.split(' ')[0] : '-'}\n` +
          `   📌 Status: ${b.status_string || (b.is_cancel ? 'Dibatalkan' : 'Aktif')}`;
      }).join('\n\n');

      await sendWithDelay(sender,
        '📋 *Status Booking*\n\n' + lines +
        '\n\nKetik *0* untuk menu utama.'
      );
    }

    await db.resetSession(sender);
  } catch (error) {
    if (error.response?.status === 404) {
      await sendWithDelay(sender, '❌ Data pasien tidak ditemukan. Periksa No RM / NIK Anda ya.');
    } else {
      console.error('Status error:', error.message);
      await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    }
    await db.resetSession(sender);
  }
}
