import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

export async function handleCheckBed(sender) {
  try {
    const beds = await medifyApi.getBedAvailability();
    const bedData = beds.data || beds || [];

    const bedList = bedData.map(b => {
      const kelasList = (b.data || []).map(k =>
        `  • Kelas ${k.kelas}: ${k.bed_kosong} kosong / ${k.bed_kapasitas} total`
      ).join('\n');
      return `🏥 *${b.nama}* (${b.total_kapasitas_bed} bed)\n${kelasList}`;
    }).join('\n\n');

    await sendWithDelay(sender,
      '🛏️ *Ketersediaan Tempat Tidur*\n\n' +
      (bedList || 'Data tidak tersedia saat ini.') +
      '\n\nKetik *0* untuk menu utama.'
    );
  } catch (error) {
    console.error('Get bed availability error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
  }
}
