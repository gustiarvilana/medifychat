import * as db from './database.js';
import { detectIntent, getFallbackResponse } from './nlp-engine.js';
import * as medifyApi from './medify-api.js';
import * as gemini from './gemini-api.js';

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

export async function handleMessage(sender, message) {
  if (!message || !message.trim()) return;
  const msg = message.trim();
  let session = await db.getSession(sender);

  if (!session) {
    await db.upsertSession(sender, 'IDLE', {});
    session = { wa_id: sender, current_state: 'IDLE', form_data: '{}' };
  }

  const state = session.current_state;
  const raw = session.form_data || {};
  const formData = typeof raw === 'string' ? JSON.parse(raw) : raw;

  if (state === 'IDLE') {
    await handleIdleState(sender, msg);
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
    case 'AWAITING_CLINIC':
      await handleClinic(sender, msg, formData);
      break;
    case 'AWAITING_DOCTOR':
      await handleDoctor(sender, msg, formData);
      break;
    case 'AWAITING_DATE':
      await handleDate(sender, msg, formData);
      break;
    case 'CONFIRM_BOOKING':
      await handleConfirmBooking(sender, msg, formData);
      break;
    default:
      await db.resetSession(sender);
      await sendWithDelay(sender, 'Sesi berakhir. Silakan ketik *Bantuan* untuk memulai lagi.');
  }
}

async function handleIdleState(sender, message) {
  const intent = detectIntent(message, 'IDLE');

  switch (intent) {
    case 'REGISTRATION':
      await db.upsertSession(sender, 'AWAITING_ID', {});
      await sendWithDelay(sender,
        'Baik, saya akan membantu Anda mendaftar rawat jalan.\n\n' +
        'Silakan kirimkan *No Rekam Medis (RM)* Anda jika pasien lama, atau *NIK* (16 digit) jika pasien baru.\n\n' +
        'Contoh: `000001` atau `3674060903970004`'
      );
      break;

    case 'CHECK_DOCTOR_SCHEDULE':
      await handleCheckDoctorSchedule(sender);
      break;

    case 'CHECK_BED':
      await handleCheckBed(sender);
      break;

    case 'HELP':
      await sendWithDelay(sender,
        '🤖 *Medify Bot - Menu Bantuan*\n\n' +
        'Saya bisa membantu Anda:\n\n' +
        '1️⃣ *Daftar Rawat Jalan*\n' +
        '   Ketik: "Saya mau daftar berobat"\n\n' +
        '2️⃣ *Cek Jadwal Dokter*\n' +
        '   Ketik: "Cek jadwal dokter"\n\n' +
        '3️⃣ *Cek Ketersediaan Tempat Tidur*\n' +
        '   Ketik: "Cek tempat tidur"\n\n' +
        '4️⃣ *Cek Status Booking*\n' +
        '   Ketik: "Cek status booking"\n\n' +
        '5️⃣ *Batalkan Proses*\n' +
        '   Ketik: "Batal" (jika sedang dalam pendaftaran)\n\n' +
        'Silakan ketik salah satu perintah di atas untuk memulai. 😊'
      );
      break;

    case 'CANCEL':
      await sendWithDelay(sender, 'Tidak ada proses yang dibatalkan.');
      break;

    default:
      const geminiReply = await gemini.chat(message);
      await sendWithDelay(sender, geminiReply || getFallbackResponse());
  }
}

async function handleAwaitingId(sender, message, formData) {
  const id = message.trim();

  if (!/^\d{4,20}$/.test(id) && !/^\d{16}$/.test(id)) {
    await sendWithDelay(sender,
      '❌ *Format tidak valid.*\n\n' +
      'No RM harus berupa angka (4-20 digit).\n' +
      'NIK harus 16 digit angka.\n\n' +
      'Silakan coba lagi. Contoh: `000001` atau `3674060903970004`'
    );
    return;
  }

  try {
    let response;
    if (id.length === 16) {
      response = await medifyApi.getPatientByNik(id);
    } else {
      response = await medifyApi.getPatientByRm(id);
    }

    if (response?.status === 'Success' || response?.data) {
      const pasien = response.data || response;
      formData.pasien_id = pasien.id;
      formData.no_rm = pasien.no_rm;
      formData.nama = pasien.nama_pasien || pasien.name;
      await db.updateSessionData(sender, formData);
      await db.updateSessionState(sender, 'AWAITING_PAYMENT_METHOD');

      await sendWithDelay(sender,
        `Terima kasih, *${formData.nama}*.\n\n` +
        `Silakan pilih *Metode Pembayaran*:\n\n` +
        `1️⃣ Tunai\n` +
        `2️⃣ BPJS\n` +
        `3️⃣ Asuransi Lain\n\n` +
        `Ketik angka 1, 2, atau 3.`
      );
    } else {
      await handlePatientNotFound(sender, id);
    }
  } catch (error) {
    if (error.response?.status === 404) {
      await handlePatientNotFound(sender, id);
    } else {
      console.error('API Error:', error.message);
      await sendWithDelay(sender,
        '⚠️ *Terjadi kendala teknis.* Silakan coba lagi beberapa saat. (Error: ' + error.message + ')'
      );
    }
  }
}

async function handlePatientNotFound(sender, id) {
  if (id.length === 16) {
    await db.upsertSession(sender, 'AWAITING_NEW_PATIENT_DATA', { nik: id });
    await sendWithDelay(sender,
      '🔍 *NIK ' + id + ' belum terdaftar.*\n\n' +
      'Kami akan membantu Anda mendaftar sebagai pasien baru.\n' +
      'Silakan kirimkan *nama lengkap* Anda.'
    );
  } else {
    await db.upsertSession(sender, 'AWAITING_RETRY_OR_NEW', { invalid_rm: id });
    await sendWithDelay(sender,
      '❌ *Nomor Rekam Medis ' + id + ' tidak ditemukan.*\n\n' +
      'Ketik *YA* untuk mendaftar sebagai pasien baru (menggunakan NIK).\n' +
      'Ketik *ULANG* untuk memasukkan No RM kembali.'
    );
  }
}

async function handleRetryOrNew(sender, message, formData) {
  const msg = message.trim().toUpperCase();

  if (/^(YA|IYA|Y|YES)\b/i.test(msg)) {
    await db.updateSessionState(sender, 'AWAITING_NEW_PATIENT_DATA');
    await sendWithDelay(sender, 'Silakan kirimkan *NIK* Anda (16 digit angka).');
  } else if (/^(ULANG|ULANGI|COBA|LAGI)\b/i.test(msg)) {
    await db.updateSessionState(sender, 'AWAITING_ID');
    await sendWithDelay(sender, 'Silakan kirimkan *No RM* atau *NIK* Anda.');
  } else {
    await sendWithDelay(sender,
      'Ketik *YA* untuk mendaftar sebagai pasien baru.\n' +
      'Ketik *ULANG* untuk mencoba No RM lain.'
    );
  }
}

async function handleNewPatientData(sender, message, formData) {
  if (!formData.nik) {
    const nik = message.trim();
    if (!/^\d{16}$/.test(nik)) {
      await sendWithDelay(sender, '❌ *NIK harus 16 digit angka.* Silakan coba lagi.');
      return;
    }
    formData.nik = nik;
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, 'Silakan kirimkan *nama lengkap* Anda.');
    return;
  }

  if (!formData.nama) {
    formData.nama = message.trim();
    if (formData.nama.length < 3) {
      await sendWithDelay(sender, '❌ Nama terlalu pendek. Silakan masukkan nama lengkap Anda.');
      return;
    }
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, 'Silakan kirimkan *tempat lahir* Anda.');
    return;
  }

  if (!formData.tempat_lahir) {
    formData.tempat_lahir = message.trim();
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender,
      'Silakan kirimkan *tanggal lahir* Anda (format: YYYY-MM-DD).\n' +
      'Contoh: `1990-05-15`'
    );
    return;
  }

  if (!formData.tanggal_lahir) {
    const tgl = message.trim();
    if (!/^\d{4}-\d{2}-\d{2}$/.test(tgl)) {
      await sendWithDelay(sender, '❌ Format tanggal salah. Gunakan format YYYY-MM-DD. Contoh: `1990-05-15`');
      return;
    }
    formData.tanggal_lahir = tgl;
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, 'Silakan kirimkan *jenis kelamin* (L/P).');
    return;
  }

  if (!formData.gender) {
    const gender = message.trim().toUpperCase();
    if (gender !== 'L' && gender !== 'P') {
      await sendWithDelay(sender, '❌ Ketik *L* untuk Laki-laki atau *P* untuk Perempuan.');
      return;
    }
    formData.gender = gender === 'L' ? 'Laki-laki' : 'Perempuan';
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, 'Silakan kirimkan *alamat* lengkap Anda.');
    return;
  }

  if (!formData.alamat) {
    formData.alamat = message.trim();
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, 'Silakan kirimkan *nomor telepon* Anda (contoh: 08123456789).');
    return;
  }

  if (!formData.phone) {
    const phone = message.trim().replace(/[^0-9]/g, '');
    if (phone.length < 10) {
      await sendWithDelay(sender, '❌ Nomor telepon tidak valid. Minimal 10 digit.');
      return;
    }
    formData.phone = phone;
    await db.updateSessionData(sender, formData);

    try {
      const result = await medifyApi.createPatient({
        nik: formData.nik,
        nama_pasien: formData.nama,
        tempat_lahir: formData.tempat_lahir,
        tanggal_lahir: formData.tanggal_lahir,
        gender: formData.gender,
        alamat: formData.alamat,
        phone: formData.phone,
      });

      const pasien = result.data || result;
      formData.pasien_id = pasien.id;
      formData.no_rm = pasien.no_rm;
      await db.updateSessionData(sender, formData);
      await db.updateSessionState(sender, 'AWAITING_PAYMENT_METHOD');

      await sendWithDelay(sender,
        `✅ *Pendaftaran berhasil!*\n\n` +
        `No RM: ${formData.no_rm}\n` +
        `Nama: ${formData.nama}\n\n` +
        `Silakan pilih *Metode Pembayaran*:\n\n` +
        `1️⃣ Tunai\n` +
        `2️⃣ BPJS\n` +
        `3️⃣ Asuransi Lain\n\n` +
        `Ketik angka 1, 2, atau 3.`
      );
    } catch (error) {
      console.error('Create patient error:', error.message);
      await sendWithDelay(sender,
        '⚠️ Gagal mendaftarkan pasien baru. Silakan coba lagi. (Error: ' + error.message + ')'
      );
    }
    return;
  }

  await db.updateSessionState(sender, 'AWAITING_PAYMENT_METHOD');
  await sendWithDelay(sender,
    'Silakan pilih *Metode Pembayaran*:\n\n' +
    '1️⃣ Tunai\n' +
    '2️⃣ BPJS\n' +
    '3️⃣ Asuransi Lain\n\n' +
    'Ketik angka 1, 2, atau 3.'
  );
}

async function handlePaymentMethod(sender, message, formData) {
  const methods = { '1': 1, '2': 2, '3': 3 };
  const methodLabels = { '1': 'Tunai', '2': 'BPJS', '3': 'Asuransi Lain' };
  const choice = methods[message.trim()];

  if (!choice) {
    await sendWithDelay(sender, '❌ Pilihan tidak valid. Ketik 1 untuk Tunai, 2 untuk BPJS, atau 3 untuk Asuransi Lain.');
    return;
  }

  formData.bayar_id = choice;
  formData.metode_bayar = methodLabels[message.trim()];
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'AWAITING_CLINIC');

  try {
    const clinics = await medifyApi.getClinics();
    const clinicList = (clinics.data || clinics || [])
      .map((c, i) => `${i + 1}. ${c.nama_poli || c.name}`)
      .join('\n');

    await sendWithDelay(sender,
      `Metode pembayaran: *${formData.metode_bayar}*\n\n` +
      `Silakan pilih *Poliklinik*:\n\n${clinicList}\n\n` +
      `Ketik nomor poliklinik yang dipilih.`
    );

    formData._clinics = clinics.data || clinics || [];
    await db.updateSessionData(sender, formData);
  } catch (error) {
    console.error('Get clinics error:', error.message);
    await sendWithDelay(sender, '⚠️ Gagal memuat daftar poliklinik. Silakan coba lagi.');
  }
}

async function handleClinic(sender, message, formData) {
  const idx = parseInt(message.trim()) - 1;
  const clinics = formData._clinics || [];

  if (isNaN(idx) || idx < 0 || idx >= clinics.length) {
    await sendWithDelay(sender, `❌ Pilihan tidak valid. Ketik angka 1-${clinics.length}.`);
    return;
  }

  const clinic = clinics[idx];
  formData.poliklinik_id = clinic.id;
  formData.nama_poli = clinic.nama_poli || clinic.name;
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'AWAITING_DOCTOR');

  try {
    const doctors = await medifyApi.getDoctors(clinic.id);
    const doctorList = (doctors.data || doctors || [])
      .map((d, i) => `${i + 1}. ${d.nama_dokter || d.name}`)
      .join('\n');

    await sendWithDelay(sender,
      `Poli: *${formData.nama_poli}*\n\n` +
      `Silakan pilih *Dokter*:\n\n${doctorList}\n\n` +
      `Ketik nomor dokter yang dipilih.`
    );

    formData._doctors = doctors.data || doctors || [];
    await db.updateSessionData(sender, formData);
  } catch (error) {
    console.error('Get doctors error:', error.message);
    await sendWithDelay(sender, '⚠️ Gagal memuat daftar dokter. Silakan coba lagi.');
  }
}

async function handleDoctor(sender, message, formData) {
  const idx = parseInt(message.trim()) - 1;
  const doctors = formData._doctors || [];

  if (isNaN(idx) || idx < 0 || idx >= doctors.length) {
    await sendWithDelay(sender, `❌ Pilihan tidak valid. Ketik angka 1-${doctors.length}.`);
    return;
  }

  const doctor = doctors[idx];
  formData.dokter_id = doctor.id;
  formData.nama_dokter = doctor.nama_dokter || doctor.name;
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'AWAITING_DATE');

  try {
    const schedules = await medifyApi.getSchedules(doctor.id);
    const scheduleList = (schedules.data || schedules || [])
      .map((s, i) => `${i + 1}. ${s.hari || s.day}: ${s.jam_mulai || s.start_time}-${s.jam_selesai || s.end_time}`)
      .join('\n');

    await sendWithDelay(sender,
      `Dokter: *${formData.nama_dokter}*\n\n` +
      `*Jadwal Praktek:*\n${scheduleList}\n\n` +
      `Silakan kirimkan *tanggal kunjungan* (format: YYYY-MM-DD).\n` +
      `Contoh: 2026-06-01`
    );

    formData._schedules = schedules.data || schedules || [];
    await db.updateSessionData(sender, formData);
  } catch (error) {
    console.error('Get schedules error:', error.message);
    await sendWithDelay(sender, '⚠️ Gagal memuat jadwal dokter. Silakan coba lagi.');
  }
}

async function handleDate(sender, message, formData) {
  const dateStr = message.trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
    await sendWithDelay(sender, '❌ Format tanggal salah. Gunakan format YYYY-MM-DD. Contoh: 2026-06-01');
    return;
  }

  formData.tanggal_pemesanan = dateStr;
  await db.updateSessionData(sender, formData);
  await db.updateSessionState(sender, 'CONFIRM_BOOKING');

  await sendWithDelay(sender,
    '📋 *Konfirmasi Pendaftaran*\n\n' +
    `Nama: ${formData.nama}\n` +
    `No RM: ${formData.no_rm || 'Baru'}\n` +
    `Poli: ${formData.nama_poli}\n` +
    `Dokter: ${formData.nama_dokter}\n` +
    `Tanggal: ${formData.tanggal_pemesanan}\n` +
    `Pembayaran: ${formData.metode_bayar}\n\n` +
    `Ketik *KONFIRM* untuk melanjutkan, atau *BATAL* untuk membatalkan.`
  );
}

async function handleConfirmBooking(sender, message, formData) {
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
        dokter_jadwal_id: formData._schedules?.[0]?.id || null,
        tanggal_pemesanan: formData.tanggal_pemesanan,
        bayar_id: formData.bayar_id,
      });

      const booking = result.data || result;

      await db.resetSession(sender);
      await sendWithDelay(sender,
        '✅ *Pendaftaran Berhasil!*\n\n' +
        `Kode Booking: *${booking.kode_booking || booking.booking_code || '-'}*\n` +
        `Estimasi Waktu: *${booking.estimasi || booking.estimated_time || 'Silakan cek di loket'}*\n\n` +
        'Terima kasih telah mendaftar melalui Medify Bot. 😊\n' +
        'Datanglah sesuai jadwal untuk mengurai antrean.'
      );
    } catch (error) {
      console.error('Booking error:', error.message);
      await sendWithDelay(sender,
        '⚠️ Gagal membuat booking. Silakan coba lagi. (Error: ' + error.message + ')'
      );
    }
  } else if (isCancel) {
    await db.resetSession(sender);
    await sendWithDelay(sender, 'Proses pendaftaran dibatalkan. Ketik *Bantuan* untuk menu utama.');
  } else {
    await sendWithDelay(sender,
      'Ketik *KONFIRM* untuk melanjutkan pendaftaran.\n' +
      'Ketik *BATAL* untuk membatalkan.'
    );
  }
}

async function handleCheckDoctorSchedule(sender) {
  try {
    const clinics = await medifyApi.getClinics();
    const clinicList = (clinics.data || clinics || [])
      .map((c, i) => `${i + 1}. ${c.nama_poli || c.name}`)
      .join('\n');

    await db.upsertSession(sender, 'AWAITING_DOCTOR_SCHEDULE_CLINIC', { _clinics: clinics.data || clinics || [] });

    await sendWithDelay(sender,
      '🩺 *Cek Jadwal Dokter*\n\n' +
      `Silakan pilih *Poliklinik*:\n\n${clinicList}\n\n` +
      `Ketik nomor poliklinik.`
    );
  } catch (error) {
    console.error('Get clinics error:', error.message);
    await sendWithDelay(sender, '⚠️ Gagal memuat data poliklinik. Silakan coba lagi.');
  }
}

export async function handleDoctorScheduleClinic(sender, message, formData) {
  const idx = parseInt(message.trim()) - 1;
  const clinics = formData._clinics || [];

  if (isNaN(idx) || idx < 0 || idx >= clinics.length) {
    await sendWithDelay(sender, `❌ Pilihan tidak valid. Ketik angka 1-${clinics.length}.`);
    return;
  }

  try {
    const doctors = await medifyApi.getDoctors(clinics[idx].id);
    const doctorList = (doctors.data || doctors || []).map(d => {
      return `🩺 *${d.nama_dokter || d.name}*\n` +
        (d.jadwal || d.schedules || [])
          .map(s => `   ${s.hari || s.day}: ${s.jam_mulai || s.start_time}-${s.jam_selesai || s.end_time}`)
          .join('\n');
    }).join('\n\n');

    await db.resetSession(sender);
    await sendWithDelay(sender,
      `🩺 *Jadwal Dokter ${clinics[idx].nama_poli || clinics[idx].name}*\n\n${doctorList}\n\n` +
      'Ketik *Bantuan* untuk menu utama.'
    );
  } catch (error) {
    console.error('Get doctors error:', error.message);
    await sendWithDelay(sender, '⚠️ Gagal memuat jadwal dokter. Silakan coba lagi.');
  }
}

async function handleCheckBed(sender) {
  try {
    const beds = await medifyApi.getBedAvailability();
    const bedData = beds.data || beds || [];

    const bedList = bedData.map(b => {
      return `- *${b.nama_bangsal || b.name}*: ${b.kelas || b.class} (${b.jumlah_kosong || b.available}/${b.jumlah_total || b.total})`;
    }).join('\n');

    await sendWithDelay(sender,
      '🛏️ *Ketersediaan Bed per Bangsal*\n\n' +
      (bedList || 'Data tidak tersedia.') +
      '\n\nKetik *Bantuan* untuk menu utama.'
    );
  } catch (error) {
    console.error('Get bed availability error:', error.message);
    await sendWithDelay(sender, '⚠️ Gagal memuat data ketersediaan tempat tidur. Silakan coba lagi.');
  }
}

export async function handleMessageForState(sender, message, state, formData) {
  if (state === 'AWAITING_DOCTOR_SCHEDULE_CLINIC') {
    await handleDoctorScheduleClinic(sender, message, formData);
    return true;
  }
  return false;
}
