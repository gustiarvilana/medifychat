import * as db from '../database.js';
import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage, bookingErrorSuggestion } from './utils.js';
import { getDayKey, findJadwalId } from './constants.js';

async function proceedToClinic(sender, formData) {
  await db.updateSessionState(sender, 'AWAITING_CLINIC');
  try {
    const clinics = await medifyApi.getClinics();
    const clinicList = (clinics.data || clinics || [])
      .map((c, i) => `${i + 1}. ${c.nama}`)
      .join('\n');

    await sendWithDelay(sender,
      `💳 Metode Bayar: *${formData.metode_bayar}*\n\n` +
      `Sekarang silakan pilih *Poliklinik* tujuan:\n\n${clinicList}\n\n` +
      `Ketik *nomor* poliklinik yang diinginkan. Atau *0* untuk kembali.`
    );

    formData._clinics = clinics.data || clinics || [];
    await db.updateSessionData(sender, formData);
  } catch (error) {
    console.error('Get clinics error:', error.message);
    await sendWithDelay(sender, '⚠️ Maaf, sistem sedang sibuk. Coba lagi beberapa saat ya 🙏');
  }
}

export async function handlePaymentMethod(sender, message, formData) {
  const methods = { '1': 1, '2': 2, '3': 3 };
  const methodLabels = { '1': 'Tunai', '2': 'BPJS', '3': 'Asuransi Lain' };
  const choice = methods[message.trim()];

  if (!choice) {
    await sendWithDelay(sender, '❌ Pilihannya: *1* Tunai, *2* BPJS, atau *3* Asuransi Lain ya.');
    return;
  }

  formData.bayar_id = choice;
  formData.metode_bayar = methodLabels[message.trim()];
  await db.updateSessionData(sender, formData);

  if (choice === 3 && formData.pasien_id) {
    try {
      const result = await medifyApi.getPatientInsurance(formData.pasien_id);
      const insurances = result.data || result || [];
      if (insurances.length > 0) {
        const insuranceList = insurances.map((ins, i) =>
          `${i + 1}. ${ins.nama_asuransi || ins.nama || ins.name || '-'} (${ins.no_asuransi || ins.no_identitas || '-'})`
        ).join('\n');

        formData._insurances = insurances;
        await db.updateSessionData(sender, formData);
        await db.updateSessionState(sender, 'AWAITING_INSURANCE');

        await sendWithDelay(sender,
          `💳 Metode Bayar: *${formData.metode_bayar}*\n\n` +
          `Silakan pilih *Asuransi* yang digunakan:\n\n${insuranceList}\n\n` +
          `Ketik nomor asuransi yang dipilih.`
        );
        return;
      }
    } catch (error) {
      console.error('Get insurance error:', error.message);
    }
  }

  await proceedToClinic(sender, formData);
}

export async function handleAwaitingInsurance(sender, message, formData) {
  const idx = parseInt(message.trim()) - 1;
  const insurances = formData._insurances || [];

  if (isNaN(idx) || idx < 0 || idx >= insurances.length) {
    await sendWithDelay(sender, `❌ Pilihan tidak valid. Ketik angka 1-${insurances.length}.`);
    return;
  }

  const selected = insurances[idx];
  formData.asuransi_id = selected.id || selected.asuransi_id;
  formData.nama_asuransi = selected.nama_asuransi || selected.nama || selected.name;
  formData.no_asuransi = selected.no_asuransi || selected.no_identitas;
  await db.updateSessionData(sender, formData);

  await proceedToClinic(sender, formData);
}

export async function handleClinic(sender, message, formData) {
  const idx = parseInt(message.trim()) - 1;
  const clinics = formData._clinics || [];

  if (isNaN(idx) || idx < 0 || idx >= clinics.length) {
    await sendWithDelay(sender, `❌ Pilihan tidak valid. Ketik angka 1-${clinics.length}.`);
    return;
  }

  const clinic = clinics[idx];
  formData.poliklinik_id = clinic.id;
  formData.nama_poli = clinic.nama;
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'AWAITING_DOCTOR');

  try {
    const doctors = await medifyApi.getDoctors(clinic.id);
    const doctorList = (doctors.data || doctors || [])
      .map((d, i) => `${i + 1}. ${d.dokter.nama}`)
      .join('\n');

    await sendWithDelay(sender,
      `🏥 Poli: *${formData.nama_poli}*\n\n` +
      `Silakan pilih *Dokter* yang diinginkan:\n\n${doctorList}\n\n` +
      `Ketik *nomor* dokter. Atau *0* untuk kembali.`
    );

    formData._doctors = doctors.data || doctors || [];
    await db.updateSessionData(sender, formData);
  } catch (error) {
    console.error('Get doctors error:', error.message);
    await sendWithDelay(sender, '⚠️ Maaf, sistem sedang sibuk. Coba lagi beberapa saat ya 🙏');
  }
}

export async function handleDoctor(sender, message, formData) {
  const idx = parseInt(message.trim()) - 1;
  const doctors = formData._doctors || [];

  if (isNaN(idx) || idx < 0 || idx >= doctors.length) {
    await sendWithDelay(sender, `❌ Pilihan tidak valid. Ketik angka 1-${doctors.length}.`);
    return;
  }

  const doctor = doctors[idx].dokter;
  formData.dokter_id = doctor.id;
  formData.nama_dokter = doctor.nama;
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'AWAITING_DATE');

  try {
    const [schedules, quotaRes] = await Promise.all([
      medifyApi.getSchedules(doctor.id),
      medifyApi.getServiceQuota(doctor.id, formData.poliklinik_id).catch(e => {
        console.error('Quota fetch error:', e.response?.data ? JSON.stringify(e.response.data) : e.message);
        return null;
      }),
    ]);

    const scheduleData = (schedules.data || schedules || [])[0];
    const jadwal = scheduleData?.jadwal || {};
    const dayMap = { senin: 'Senin', selasa: 'Selasa', rabu: 'Rabu', kamis: 'Kamis', jumat: 'Jumat', sabtu: 'Sabtu', minggu: 'Minggu' };

    let quotaNote = '';
    try {
      if (quotaRes?.data?.kuota) {
        const rawKuota = quotaRes.data.kuota;
        const kuotaList = Array.isArray(rawKuota)
          ? rawKuota
          : Object.entries(rawKuota).map(([day, d]) => ({ day, data: d }));

        const dayQuotas = kuotaList.map(item => {
          const dayLabel = dayMap[item.day];
          if (!dayLabel || !jadwal[item.day]) return null;
          const data = item.data || item;
          const allBatches = [
            ...(data.batas_kuota_bpjs || []),
            ...(data.batas_kuota_non_bpjs || []),
          ];
          const online = allBatches.find(b => b.jenis_kuota === 'online');
          const kuota = online?.kuota ?? allBatches.reduce((sum, b) => sum + (b.kuota || 0), 0);
          if (!kuota) return null;
          return `  • ${dayLabel}: ${kuota} kuota tersisa`;
        }).filter(Boolean);

        if (dayQuotas.length > 0) {
          quotaNote = '\n\n📊 *Sisa Kuota Online:*\n' + dayQuotas.join('\n');
        }
      }
    } catch (quotaErr) {
      console.error('Quota parse error:', quotaErr.message);
    }

    const scheduleList = Object.entries(dayMap)
      .filter(([key]) => jadwal[key] && jadwal[key].text && jadwal[key].text !== 'False')
      .map(([key, label]) => `  • ${label}: ${jadwal[key].text}`)
      .join('\n');

    await sendWithDelay(sender,
      `🩺 Dokter: *${formData.nama_dokter}*\n\n` +
      `*Jadwal Praktek:*\n${scheduleList}${quotaNote}\n\n` +
      `Silakan kirimkan *tanggal kunjungan* yang diinginkan.\n` +
      `Format: YYYY-MM-DD — Contoh: *2026-06-01*\n\n` +
      `Ketik *0* untuk kembali ke pilihan dokter.`
    );

    formData._schedules = jadwal;
    await db.updateSessionData(sender, formData);
  } catch (error) {
    console.error('Get schedules error:', error.message);
    await sendWithDelay(sender, '⚠️ Maaf, sistem sedang sibuk. Coba lagi beberapa saat ya 🙏');
  }
}

export async function handleDate(sender, message, formData) {
  const dateStr = message.trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
    await sendWithDelay(sender, '❌ Format tanggal kurang tepat. Gunakan YYYY-MM-DD ya.\nContoh: *2026-06-01*');
    return;
  }

  formData.tanggal_pemesanan = dateStr;
  const dayKey = getDayKey(dateStr);
  formData.dokter_jadwal_id = findJadwalId(formData._schedules || {}, dayKey);
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'CONFIRM_BOOKING');

  await sendWithDelay(sender,
    '📋 *Konfirmasi Pendaftaran*\n\n' +
    `👤 Nama: *${formData.nama}*\n` +
    `🆔 No RM: *${formData.no_rm || 'Baru'}*\n` +
    `🏥 Poli: *${formData.nama_poli}*\n` +
    `🩺 Dokter: *${formData.nama_dokter}*\n` +
    `📅 Tanggal: *${formData.tanggal_pemesanan}*\n` +
    `💳 Bayar: *${formData.metode_bayar}*\n\n` +
    `Sudah benar semua?\n\n` +
    `👉 Ketik *KONFIRM* untuk lanjutkan\n` +
    `👉 Ketik *0* untuk ubah tanggal/dokter\n` +
    `👉 Ketik *BATAL* untuk batalkan`
  );
}

export async function handleConfirmBooking(sender, message, formData) {
  const msg = message.trim().toUpperCase();
  const confirmWords = ['KONFIRM', 'LANJUT', 'JADI', 'YAKIN'];
  const confirmExact = ['YA', 'YES', 'OK', 'OKE', 'Y'];
  const cancelWords = ['BATAL', 'CANCEL', 'GAGAL', 'KEMBALI', 'TIDAK'];
  const cancelExact = ['NGGAK', 'ENGGAK'];

  const isConfirm = confirmWords.some(w => msg.startsWith(w)) || confirmExact.includes(msg);
  const isCancel = cancelWords.some(w => msg.startsWith(w)) || cancelExact.includes(msg);

  if (isConfirm) {
    try {
      const result = await medifyApi.createBooking({
        pasien_id: formData.pasien_id,
        dokter_id: formData.dokter_id,
        poliklinik_id: formData.poliklinik_id,
        dokter_jadwal_id: formData.dokter_jadwal_id || null,
        tanggal_pemesanan: formData.tanggal_pemesanan,
        bayar_id: formData.bayar_id,
      });

      const booking = result.data || result;

      await db.resetSession(sender);

      const parseEstimasi = (val) => {
        if (!val) return null;
        const parts = val.split(' ');
        return parts.length > 1 ? parts[1].slice(0, 5) : parts[0].slice(0, 5);
      };

      let estimasiText = 'Silakan cek di loket RS';
      let queueInfo = '';
      try {
        const q = await medifyApi.getQueue({
          poliklinik_id: formData.poliklinik_id,
          tanggal: formData.tanggal_pemesanan,
        });
        const qData = q.data || q || [];
        if (qData.length > 0) {
          const active = qData.find(p => !p.is_selesai && !p.is_cancel);
          const noAntrian = active?.antrian || active?.no_antrian || qData.length;
          queueInfo = `🚶 *No. Antrian:* ${noAntrian} (${qData.length} terdaftar)\n`;
          estimasiText = parseEstimasi(active?.waktu_estimasi) || estimasiText;
        }
      } catch (_) {}
      if (!estimasiText || estimasiText === 'Silakan cek di loket RS') {
        estimasiText = parseEstimasi(booking.waktu_estimasi) || booking.estimasi || estimasiText;
      }

      await sendWithDelay(sender,
        '🎉 *Pendaftaran Berhasil!*\n\n' +
        'Terima kasih sudah menggunakan Medify Bot 😊\n\n' +
        `📌 *Kode Booking:* ${booking.kode_booking || booking.booking_code || '-'}\n` +
        `🏥 Poli: *${formData.nama_poli}*\n` +
        `🩺 Dokter: *${formData.nama_dokter}*\n` +
        `📅 Tanggal: *${formData.tanggal_pemesanan}*\n` +
        (queueInfo || '') +
        `⏰ Estimasi: *${estimasiText}*\n\n` +
        '💡 *Tips:*\n' +
        '• Datang 15 menit lebih awal\n' +
        '• Bawa kartu identitas & No RM\n\n' +
        'Ada yang bisa saya bantu lagi? Ketik *0* untuk menu utama.'
      );
    } catch (error) {
      console.error('Booking error:', error.message);
      const errMsg = apiErrorMessage(error) || 'Maaf, sistem sedang sibuk.';
      const suggestion = bookingErrorSuggestion(errMsg);
      await sendWithDelay(sender,
        suggestion
          ? `${suggestion.title}\n\n${suggestion.body}`
          : `⚠️ *${errMsg}*\n\n` +
            '💡 *Saran:*\n' +
            '• Coba daftar dengan *tanggal* atau *poli* lain\n' +
            '• Hubungi *loket pendaftaran* RS untuk bantuan\n\n' +
            '👉 Ketik *0* untuk ubah tanggal/dokter\n' +
            '👉 Ketik *BATAL* untuk batal\n' +
            '👉 Ketik *KONFIRM* untuk coba lagi'
      );
    }
  } else if (isCancel) {
    await db.resetSession(sender);
    await sendWithDelay(sender, '✅ *Pendaftaran dibatalkan.*\n\nKetik *0* untuk menu utama ya 😊');
  } else {
    await sendWithDelay(sender,
      'Silakan ketik:\n' +
      '👉 *KONFIRM* → Lanjutkan pendaftaran\n' +
      '👉 *BATAL* → Batalkan'
    );
  }
}
