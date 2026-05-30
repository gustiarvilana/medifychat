import * as db from '../database.js';
import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

export async function handleCheckQueue(sender) {
  try {
    const clinics = await medifyApi.getClinics();
    const clinicList = (clinics.data || clinics || [])
      .map((c, i) => `${i + 1}. ${c.nama}`)
      .join('\n');

    await db.upsertSession(sender, 'AWAITING_QUEUE_CLINIC', { _clinics: clinics.data || clinics || [] });

    await sendWithDelay(sender,
      '🚶 *Cek Antrian Poli*\n\n' +
      `Silakan pilih *Poliklinik*:\n\n${clinicList}\n\n` +
      `Ketik nomor poliklinik yang ingin dicek.`
    );
  } catch (error) {
    console.error('Get clinics error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    await db.resetSession(sender);
  }
}

export async function handleAwaitingQueueClinic(sender, message, formData) {
  const clinics = formData._clinics || [];
  const choice = parseInt(message, 10);
  if (isNaN(choice) || choice < 1 || choice > clinics.length) {
    await sendWithDelay(sender,
      `❌ Nomor kurang tepat. Silakan ketik *nomor* poliklinik (1-${clinics.length}) dari daftar di atas.\n` +
      `Atau ketik *0* untuk kembali ke menu utama.`
    );
    return;
  }
  const selectedClinic = clinics[choice - 1];
  const today = new Date();
  const dateStr = today.toISOString().slice(0, 10);

  await db.upsertSession(sender, 'AWAITING_QUEUE_DATE', {
    ...formData,
    poliklinik_id: selectedClinic.id,
    nama_poli: selectedClinic.nama,
  });

  await sendWithDelay(sender,
    `🏥 Poli: *${selectedClinic.nama}*\n\n` +
    `Masukkan *tanggal* antrian yang ingin dicek.\n` +
    `Format: YYYY-MM-DD — Contoh: *${dateStr}*`
  );
}

export async function handleAwaitingQueueDate(sender, message, formData) {
  try {
    const queue = await medifyApi.getQueue({
      poliklinik_id: formData.poliklinik_id,
      tanggal: message.trim(),
    });
    const data = queue.data || queue || [];
    if (!data.length) {
      await sendWithDelay(sender, '📭 *Tidak ada antrian* pada tanggal tersebut.');
      await db.resetSession(sender);
      return;
    }
    const queueText = data
      .map((q, i) => `${i + 1}. ${q.nama_pasien || q.nama || q.pasien || '-'} — ${q.no_antrian || q.antrian || '-'}`)
      .join('\n');
    await sendWithDelay(sender,
      `🚶 *Antrian ${formData.nama_poli}*\n📅 ${message.trim()}\n\n${queueText}\n\n` +
      'Ketik *0* untuk menu utama.'
    );
    await db.resetSession(sender);
  } catch (error) {
    console.error('Get queue error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    await db.resetSession(sender);
  }
}
