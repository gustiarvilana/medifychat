// whatsapp-bot/src/bot-profile.js

let rsName = "Medify Hospital"; // Default value

const BOT = {
  name: "MedifyBot",
  get rsName() {
    return rsName;
  },
  features: [
    { id: 1, label: "Daftar berobat", keyword: "booking rawat jalan" },
    { id: 2, label: "Jadwal dokter", keyword: "cek jadwal praktek" },
    { id: 3, label: "Info tempat tidur", keyword: "ketersediaan bed" },
    { id: 4, label: "Status booking", keyword: "cek pendaftaran Anda" },
    { id: 5, label: "Paket MCU", keyword: "info medical check-up" },
    { id: 6, label: "Antrian poli", keyword: "cek nomor antrian" },
    { id: 7, label: "Jadwal per tanggal", keyword: "dokter praktek hari tertentu" },
    { id: 8, label: "Batalkan booking", keyword: "pembatalan pendaftaran" },
    { id: 9, label: "Bantuan", keyword: "bantuan lainnya" },
  ],
};

function setRsName(name) {
  rsName = name;
}

function buildMenuText() {
  let text = `Halo! 👋 Saya ${BOT.name}, asisten dari *${BOT.rsName}*, senang bisa membantu Anda.\n\n`;
  text += 'Yang bisa saya bantu:\n';
  BOT.features.forEach(f => {
    text += `• *${f.label}* — ${f.keyword}\n`;
  });
  text += '\nCukup tulis apa yang Anda butuhkan dengan kata-kata sendiri, ya 😊\n';
  text += 'Contoh: "Saya mau daftar ke poli" atau "Cek jadwal dokter"';
  return text;
}

function buildSystemInstruction(contextStr = '') {
  let instruction = `Anda adalah ${BOT.name}, asisten virtual dari ${BOT.rsName}.\n`;
  instruction += 'Tugas Anda adalah membantu pasien dengan ramah dan profesional.\n\n';
  instruction += 'Fitur yang Anda dukung:\n';
  BOT.features.forEach(f => {
    instruction += `- ${f.label}: ${f.keyword}\n`;
  });
  
  if (contextStr) {
    instruction += `\nInformasi tambahan (konteks): \n${contextStr}\n`;
  }
  
  instruction += '\nJika pengguna bertanya hal di luar fitur, arahkan dengan sopan ke menu bantuan.';
  return instruction;
}

export { BOT, setRsName, buildMenuText, buildSystemInstruction };
