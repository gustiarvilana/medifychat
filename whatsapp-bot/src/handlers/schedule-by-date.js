import * as db from '../database.js';
import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

export async function handleScheduleByDate(sender) {
  await db.upsertSession(sender, 'AWAITING_SCHEDULE_DATE', {});
  await sendWithDelay(sender,
    '📅 *Cek Jadwal berdasarkan Tanggal*\n\n' +
    'Silakan kirimkan *tanggal* yang ingin dicek.\n' +
    'Format: YYYY-MM-DD — Contoh: *2026-06-01*\n\n' +
    'Nanti saya tampilkan semua dokter yang praktik di tanggal tersebut.'
  );
}

export async function handleAwaitingScheduleDate(sender, message, formData) {
  const dateStr = message.trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
    await sendWithDelay(sender, '❌ Format tanggal kurang tepat. Gunakan YYYY-MM-DD ya.\nContoh: *2026-06-01*');
    return;
  }

  try {
    const result = await medifyApi.getSchedulesByDate(dateStr);
    const data = result.data || result || [];

    if (!data.length) {
      await sendWithDelay(sender,
        `📭 *Tidak ada jadwal dokter* pada tanggal ${dateStr}.\n\n` +
        'Coba tanggal lain ya. Ketik *0* untuk menu utama.'
      );
      await db.resetSession(sender);
      return;
    }

    const lines = data.slice(0, 15).map((item, i) => {
      const dokter = item.dokter?.nama || item.nama || item.name || '-';
      const poli = item.poliklinik?.nama || item.poliklinik_nama || item.poli || '-';
      const jam = item.jam_praktek || (item.jam_mulai ? `${item.jam_mulai} - ${item.jam_selesai}` : '');
      return `${i + 1}. 🩺 *${dokter}*\n   🏥 Poli: ${poli}${jam ? `\n   ⏰ ${jam}` : ''}`;
    }).join('\n\n');

    await sendWithDelay(sender,
      `📋 *Jadwal Dokter — ${dateStr}*\n\n${lines}\n\n` +
      'Ketik *0* untuk menu utama.'
    );
    await db.resetSession(sender);
  } catch (error) {
    console.error('Get schedule by date error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    await db.resetSession(sender);
  }
}
