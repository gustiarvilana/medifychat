import * as db from '../database.js';
import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

export async function handleCheckDoctorSchedule(sender) {
  try {
    const clinics = await medifyApi.getClinics();
    const clinicList = (clinics.data || clinics || [])
      .map((c, i) => `${i + 1}. ${c.nama}`)
      .join('\n');

    await db.upsertSession(sender, 'AWAITING_DOCTOR_SCHEDULE_CLINIC', { _clinics: clinics.data || clinics || [] });

    await sendWithDelay(sender,
      '🩺 *Cek Jadwal Dokter*\n\n' +
      `Silakan pilih *Poliklinik*:\n\n${clinicList}\n\n` +
      `Ketik nomor poliklinik yang ingin dicek jadwalnya.`
    );
  } catch (error) {
    console.error('Get clinics error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    await db.resetSession(sender);
  }
}

export async function handleDoctorScheduleClinic(sender, message, formData) {
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
  try {
    const doctors = await medifyApi.getDoctors(selectedClinic.id);
    const docList = doctors.data || doctors || [];
    if (!docList.length) {
      await sendWithDelay(sender,
        `📭 *Belum ada jadwal dokter* untuk poli ${selectedClinic.nama}.\n\nKetik *0* untuk menu utama.`);
      await db.resetSession(sender);
      return;
    }
    const numberedList = docList.map((d, i) => `${i + 1}. ${d.dokter?.nama || d.nama || '-'}`).join('\n');

    formData._selectedClinic = selectedClinic;
    formData._doctorList = docList;
    await db.upsertSession(sender, 'AWAITING_DOCTOR_SCHEDULE_DOCTOR', formData);

    await sendWithDelay(sender,
      `🏥 Poli: *${selectedClinic.nama}*\n\n` +
      `Pilih *Dokter*:\n\n${numberedList}\n\n` +
      `Ketik nomor dokter yang ingin dicek jadwalnya. Atau *0* untuk kembali.`
    );
  } catch (error) {
    console.error('Get doctors error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    await db.resetSession(sender);
  }
}

export async function handleDoctorScheduleDoctor(sender, message, formData) {
  const docList = formData._doctorList || [];
  const choice = parseInt(message, 10);
  if (isNaN(choice) || choice < 1 || choice > docList.length) {
    await sendWithDelay(sender,
      `❌ Nomor kurang tepat. Silakan ketik *nomor* dokter (1-${docList.length}) dari daftar di atas.\n` +
      `Atau ketik *0* untuk kembali.`
    );
    return;
  }
  const selected = docList[choice - 1];
  const namaDokter = selected.dokter?.nama || selected.nama || '-';

  try {
    const scheduleRes = await medifyApi.getSchedules(selected.dokter?.id || selected.id);
    const scheduleData = (scheduleRes.data || scheduleRes || [])[0];
    const rawJadwal = scheduleData?.jadwal || scheduleData?.schedules || {};

    let scheduleText = '';
    if (Array.isArray(rawJadwal)) {
      for (const s of rawJadwal) {
        const day = s.hari || s.day || '-';
        const time = s.jam || s.time || (s.jam_praktek_mulai ? `${s.jam_praktek_mulai}-${s.jam_praktek_selesai}` : '-');
        scheduleText += `   📅 ${day}: ${time}\n`;
      }
    } else {
      const dayMap = { senin: 'Senin', selasa: 'Selasa', rabu: 'Rabu', kamis: 'Kamis', jumat: 'Jumat', sabtu: 'Sabtu', minggu: 'Minggu' };
      for (const [key, dayData] of Object.entries(rawJadwal)) {
        if (!dayData?.text || dayData.text === 'False') continue;
        const label = dayMap[key] || key;
        scheduleText += `   📅 ${label}: ${dayData.text}\n`;
      }
    }

    if (!scheduleText) {
      scheduleText = '   📅 Belum ada jadwal tersedia.\n';
    }

    await db.resetSession(sender);

    await sendWithDelay(sender,
      `🩺 *Jadwal Dokter*\n\n` +
      `🏥 Poli: *${formData._selectedClinic?.nama || '-'}*\n` +
      `👤 Dokter: *${namaDokter}*\n\n` +
      `${scheduleText}\n` +
      `Ketik *0* untuk menu utama.`
    );
  } catch (error) {
    console.error('Get schedule error:', error.message);
    await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    await db.resetSession(sender);
  }
}