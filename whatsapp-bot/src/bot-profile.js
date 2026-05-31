let _rsName = 'RS Bhayangkara Setukpa Sukabumi';

export const BOT = {
  name: 'MedifyBot',
  get rsName() {
    return _rsName;
  },
  features: [
    { id: 1, label: 'Daftar rawat jalan',       keyword: 'daftar' },
    { id: 2, label: 'Cek jadwal dokter',        keyword: 'jadwal dokter' },
    { id: 3, label: 'Cek ketersediaan tempat tidur', keyword: 'tempat tidur' },
    { id: 4, label: 'Cek status booking',       keyword: 'status booking' },
    { id: 5, label: 'Paket MCU',                keyword: 'mcu' },
    { id: 6, label: 'Cek antrian poli',         keyword: 'antrian' },
    { id: 7, label: 'Jadwal per tanggal',       keyword: 'jadwal per tanggal' },
    { id: 8, label: 'Batalkan booking',         keyword: 'batalkan booking' },
  ],
};

export function setRsName(name) {
  if (name && name.trim()) _rsName = name.trim();
}

export function buildMenuText() {
  const items = BOT.features.map(f => `• *${f.label}* — ${f.keyword}`);
  return `Halo! 👋 Saya asisten dari *${BOT.rsName}*, senang bisa membantu Anda.\n\n` +
    `Yang bisa saya bantu:\n${items.join('\n')}\n\n` +
    `Cukup tulis apa yang Anda butuhkan dengan kata-kata sendiri ya 😊\n` +
    `Contoh: "Saya mau daftar ke poli" atau "Cek jadwal dokter"`;
}

export function buildSystemInstruction(contextStr = '') {
  const items = BOT.features.map(f => `${f.id}. ${f.label} (kata kunci: ${f.keyword})`);
  let instruction = `Kamu adalah ${BOT.name}, asisten rumah sakit yang membantu pasien mendaftar rawat jalan.\n\n` +
    `Fitur yang tersedia:\n${items.join('\n')}\n\n` +
    `Jika user ingin mendaftar, arahkan mereka untuk mengetik kata "daftar" atau "saya mau daftar".\n` +
    `Jika user bertanya di luar konteks rumah sakit, jawab dengan ramah dan arahkan kembali ke fitur yang tersedia.\n` +
    `Gunakan bahasa Indonesia yang natural dan ramah seperti seorang customer service rumah sakit.\n` +
    `Jangan pernah memberikan saran medis atau diagnosis.\n` +
    `Jangan membuat janji palsu tentang ketersediaan dokter atau obat.\n` +
    `Jawab singkat dan jelas, maksimal 3 paragraf.`;

  if (contextStr) {
    instruction += `\n\n[Informasi ${BOT.rsName}]\n${contextStr}`;
  }
  return instruction;
}
