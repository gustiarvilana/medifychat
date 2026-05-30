import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

export async function handleMcu(sender) {
  try {
    const result = await medifyApi.getMcuPackages();
    const paketList = (result.data || result || []).map((p, i) =>
      `${i + 1}. *${p.nama_paket}* — Rp ${(p.harga_paket || 0).toLocaleString('id-ID')}`
    ).join('\n');

    await sendWithDelay(sender,
      '🩺 *Paket Medical Check-Up*\n\n' +
      (paketList || 'Tidak ada paket MCU tersedia saat ini.') +
      '\n\nUntuk info lebih detail, silakan hubungi *loket pendaftaran* RS.\n\nKetik *0* untuk menu utama.'
    );
  } catch (error) {
    console.error('Get MCU error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
  }
}
