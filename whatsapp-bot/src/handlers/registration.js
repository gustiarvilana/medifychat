import * as db from '../database.js';
import * as medifyApi from '../medify-api.js';
import { sendWithDelay, apiErrorMessage } from './utils.js';

async function handlePatientNotFound(sender, id) {
  if (id.length === 16) {
    await db.upsertSession(sender, 'AWAITING_NEW_PATIENT_DATA', { nik: id });
    await sendWithDelay(sender,
      `🔍 *NIK ${id} belum terdaftar* di sistem RS.\n\n` +
      'Tenang, saya bantu daftarkan sebagai *pasien baru* ya 😊\n\n' +
      'Langkah pertama, silakan kirimkan *nama lengkap* Anda.'
    );
  } else {
    await db.upsertSession(sender, 'AWAITING_RETRY_OR_NEW', { invalid_rm: id });
    await sendWithDelay(sender,
      `❌ *No Rekam Medis ${id}* tidak ditemukan.\n\n` +
      'Mungkin ada kesalahan ketik. Silakan pilih:\n\n' +
      '👉 Ketik *YA* → Daftar sebagai *pasien baru* (menggunakan NIK)\n' +
      '👉 Ketik *ULANG* → Coba masukkan *No RM* lain'
    );
  }
}

export async function handleAwaitingId(sender, message, formData) {
  const id = message.trim();

  if (!/^\d{4,20}$/.test(id) && !/^\d{16}$/.test(id)) {
    await sendWithDelay(sender,
      '❌ *Yang Anda masukkan kurang sesuai.*\n\n' +
      '🔹 *No RM*: 4–20 digit angka (contoh: `000001`)\n' +
      '🔹 *NIK*: 16 digit angka (contoh: `3674060903970004`)\n\n' +
      'Coba periksa kembali ya 🙏'
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
        `👋 Halo *${formData.nama}*! Senang bertemu lagi 😊\n\n` +
        'Sekarang pilih *Metode Pembayaran*:\n\n' +
        '1️⃣ *Tunai / Bayar Langsung*\n' +
        '2️⃣ *BPJS*\n' +
        '3️⃣ *Asuransi Lain*\n\n' +
        'Ketik angka 1, 2, atau 3. Atau *0* untuk kembali.'
      );
    } else {
      await handlePatientNotFound(sender, id);
    }
  } catch (error) {
    if (error.response?.status === 404) {
      await handlePatientNotFound(sender, id);
    } else {
      console.error('API Error:', error.message);
      await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, sedang ada gangguan. Coba lagi nanti ya 🙏');
    }
  }
}

export async function handleRetryOrNew(sender, message, formData) {
  const msg = message.trim().toUpperCase();

  if (/^(YA|IYA|Y|YES)\b/i.test(msg)) {
    await db.updateSessionState(sender, 'AWAITING_NEW_PATIENT_DATA');
    await sendWithDelay(sender, '✏️ Silakan kirimkan *NIK* Anda (16 digit angka).');
  } else if (/^(ULANG|ULANGI|COBA|LAGI)\b/i.test(msg)) {
    await db.updateSessionState(sender, 'AWAITING_ID');
    await sendWithDelay(sender, '🔁 Silakan kirimkan *No RM* atau *NIK* lagi ya.');
  } else {
    await sendWithDelay(sender,
      '👉 Ketik *YA* → Daftar sebagai pasien baru\n' +
      '👉 Ketik *ULANG* → Coba No RM lain'
    );
  }
}

export async function handleNewPatientData(sender, message, formData) {
  if (!formData.nik) {
    const nik = message.trim();
    if (!/^\d{16}$/.test(nik)) {
      await sendWithDelay(sender, '❌ *NIK harus 16 digit angka.* Coba periksa KTP Anda ya.');
      return;
    }
    formData.nik = nik;
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, '✏️ Sekarang kirimkan *nama lengkap* Anda (sesuai KTP).');
    return;
  }

  if (!formData.nama) {
    formData.nama = message.trim();
    if (formData.nama.length < 3) {
      await sendWithDelay(sender, '❌ Nama terlalu pendek. Masukkan nama lengkap sesuai KTP ya.');
      return;
    }
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, '✏️ Kirimkan *tempat lahir* Anda.');
    return;
  }

  if (!formData.tempat_lahir) {
    formData.tempat_lahir = message.trim();
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender,
      '✏️ Kirimkan *tanggal lahir* (format: YYYY-MM-DD).\n' +
      'Contoh: `1990-05-15`'
    );
    return;
  }

  if (!formData.tanggal_lahir) {
    const tgl = message.trim();
    if (!/^\d{4}-\d{2}-\d{2}$/.test(tgl)) {
      await sendWithDelay(sender, '❌ Format tanggal kurang tepat. Gunakan YYYY-MM-DD ya. Contoh: `1990-05-15`');
      return;
    }
    formData.tanggal_lahir = tgl;
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, '✏️ *Jenis Kelamin* — Ketik *L* (Laki-laki) atau *P* (Perempuan).');
    return;
  }

  if (!formData.gender) {
    const gender = message.trim().toUpperCase();
    if (gender !== 'L' && gender !== 'P') {
      await sendWithDelay(sender, '❌ Ketik *L* untuk Laki-laki atau *P* untuk Perempuan ya.');
      return;
    }
    formData.gender = gender === 'L' ? 1 : 2;
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, '✏️ Kirimkan *alamat* lengkap Anda.');
    return;
  }

  if (!formData.alamat) {
    formData.alamat = message.trim();
    await db.updateSessionData(sender, formData);
    await sendWithDelay(sender, '✏️ Kirimkan *nomor telepon* (contoh: 08123456789).');
    return;
  }

  if (!formData.phone) {
    const phone = message.trim().replace(/[^0-9]/g, '');
    if (phone.length < 10) {
      await sendWithDelay(sender, '❌ Nomor telepon kurang tepat. Minimal 10 digit ya.');
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
        `✅ Selamat! *Pendaftaran berhasil.*\n\n` +
        `Data Anda tercatat:\n` +
        `   🆔 No RM: *${formData.no_rm}*\n` +
        `   👤 Nama: *${formData.nama}*\n\n` +
        `Sekarang pilih *Metode Pembayaran*:\n\n` +
        `1️⃣ *Tunai / Bayar Langsung*\n` +
        `2️⃣ *BPJS*\n` +
        `3️⃣ *Asuransi Lain*\n\n` +
        `Ketik angka 1, 2, atau 3. Atau *0* untuk kembali.`
      );
    } catch (error) {
      console.error('Create patient error:', error.message);
      await sendWithDelay(sender, apiErrorMessage(error) || '⚠️ Maaf, gagal mendaftarkan pasien baru. Coba lagi nanti ya 🙏');
    }
    return;
  }

  await db.updateSessionState(sender, 'AWAITING_PAYMENT_METHOD');
  await sendWithDelay(sender,
    'Sekarang pilih *Metode Pembayaran*:\n\n' +
    '1️⃣ *Tunai / Bayar Langsung*\n' +
    '2️⃣ *BPJS*\n' +
    '3️⃣ *Asuransi Lain*\n\n' +
    'Ketik angka 1, 2, atau 3.'
  );
}
