const intentPatterns = {
  REGISTRATION:
    /\b(daftar\s*(berobat|rawat\s*jalan|poli|periksa|dokter)?|registrasi|mau\s*(booking|daftar|buat\s*janji)|buat\s*(booking|janji|pendaftaran)|pendaftaran|janji\s*(temu|dokter|poli)?|booking\s*dokter|mau\s*berobat|butuh\s*(daftar|periksa|berobat)|saya\s*(mau\s*daftar|ingin\s*daftar|hendak\s*daftar)|daftarin|cara\s*daftar|mendaftar)\b/i,
  CHECK_DOCTOR_SCHEDULE:
    /\b(jadwal\s*(dokter|praktek|praktik)?|praktek\s*dokter|dokter\s*(praktek|praktik|jadwal)|cek\s*jadwal|dokter\s+\w+\s+(praktek|praktik)|kapan\s+\w+\s+(praktek|praktik|jadwal|prakteknya)|siapa\s*dokter|daftar\s*dokter|informasi\s*dokter)\b/i,
  CHECK_BED:
    /\b(tempat\s*tidur|bed\s*kosong|ketersediaan\s*(bed|tempat\s*tidur)|rawat\s*inap|ruang\s*kosong|tempat\s*tidur\s*kosong|bed\s*tersedia|kapasitas\s*bed|bangsal\s*kosong|info\s*(bed|tempat\s*tidur)|ada\s*kamar|kamar\s*kosong|cek\s*bed|cek\s*kamar|cek\s*tempat\s*tidur)\b/i,
  CHECK_QUEUE:
    /\b(antrian|antrean|nomor\s*antrian|cek\s*antrian|antrian\s*poli|nomor\s*berap[a]|panggilan|sudah\s*dipanggil|berap[a]\s*antrian|lihat\s*antrian)\b/i,
  MCU:
    /\b(mcu|medical\s*checkup|cek\s*mcu|paket\s*(mcu|medical)|medical\s*check.up|medical\s*check\s*up|check.?up|tes\s*kesehatan|medical\s*chek)\b/i,
  CHECK_SCHEDULE_BY_DATE:
    /\b(jadwal\s*(hari\s*ini|hari\s*ini\s*aja|besok|sekarang|tanggal|per\s*tanggal)?|dokter\s*(tersedia|available|praktek\s*hari\s*ini)?|praktek\s*(hari\s*ini|besok|tanggal)|cari\s*dokter|siapa\s*(saja|a?ja)\s*(dokter|praktek)|dokter\s*apa\s*(saja|a?ja)|jadwal\s+per\s+tanggal)\b/i,
  HELP:
    /\b(tolong|bantuan|help|menu|can\s*you\s*help|apa\s*((yang|a?ja)\s+)?(bisa|dapat)\s*dibantu|pilihan|perintah|panduan|fitur|halo|hallo|hello|helo|hii|hai|hay|pagi|siang|sore|malam|asisst|mulai|awal|gimana\s*cara|cara\s*pakai|bisa\s*apa)\b/i,
  CANCEL:
    /\b(batal|cancel|batalkan|urungkan|gak\s*jadi|nggak\s*jadi|tidak\s*jadi|hentikan|stop\s*(proses|session|bot)?|selesaiin|sudah\s*dulu)\b/i,
  STATUS:
    /\b(status|cek\s+(booking|pendaftaran|status)|kode\s*booking|cek\s*(status\s*)?booking\s*?(saya|ku)?|status\s*(pendaftaran|booking)?|lihat\s*(booking|status|pendaftaran)|info\s*booking|booking\s*(saya|ku)?\s*(bagaimana|gimana|status)|pendaftaran\s*(saya|ku)?|riwayat\s*(booking|pendaftaran)?)\b/i,
  CANCEL_BOOKING:
    /\b(batalkan\s*(booking|pendaftaran|janji)?|cancel\s*booking|hapus\s*(booking|pendaftaran|janji)|batalin|pembatalan|membatalkan|saya\s*mau\s*batal|ingin\s*batal)\b/i,
};

const INTENT_PRIORITY = [
  'CANCEL_BOOKING',
  'CANCEL',
  'REGISTRATION',
  'CHECK_DOCTOR_SCHEDULE',
  'CHECK_BED',
  'CHECK_QUEUE',
  'MCU',
  'CHECK_SCHEDULE_BY_DATE',
  'STATUS',
  'HELP',
];

const INTENT_KEYWORDS = {
  REGISTRATION: ['daftar', 'registrasi', 'janji', 'pendaftaran', 'berobat', 'booking', 'periksa'],
  CHECK_DOCTOR_SCHEDULE: ['jadwal', 'praktek', 'praktik', 'dokter', 'prakteknya'],
  CHECK_BED: ['bangsal', 'bed', 'rawat', 'inap', 'ketersediaan', 'kamar', 'tidur'],
  CHECK_QUEUE: ['antrian', 'antrean', 'nomor', 'panggil', 'panggilan'],
  MCU: ['mcu', 'medical', 'checkup', 'paket', 'kesehatan'],
  CHECK_SCHEDULE_BY_DATE: ['jadwal', 'tanggal', 'tersedia', 'hari', 'besok', 'sekarang'],
  HELP: ['tolong', 'bantuan', 'perintah', 'panduan', 'halo', 'hai', 'pagi', 'siang', 'sore', 'malam'],
  CANCEL: ['batal', 'cancel', 'batalkan', 'urungkan', 'hentikan'],
  CANCEL_BOOKING: ['batalkan', 'hapus', 'pembatalan'],
  STATUS: ['status', 'booking', 'kode', 'riwayat'],
};

function levenshtein(a, b) {
  const m = a.length, n = b.length;
  const dp = [];
  for (let i = 0; i <= n; i++) dp[i] = [i];
  for (let j = 0; j <= m; j++) dp[0][j] = j;
  for (let i = 1; i <= n; i++) {
    for (let j = 1; j <= m; j++) {
      if (b[i - 1] === a[j - 1]) {
        dp[i][j] = dp[i - 1][j - 1];
      } else {
        dp[i][j] = Math.min(dp[i - 1][j - 1], dp[i - 1][j], dp[i][j - 1]) + 1;
      }
    }
  }
  return dp[n][m];
}

function getFuzzyThreshold(keywordLen) {
  if (keywordLen <= 3) return 0;
  if (keywordLen <= 5) return 1;
  return Math.min(2, Math.floor(keywordLen / 3));
}

function fuzzyDetectIntent(message) {
  const words = message.toLowerCase().split(/\s+/);
  let bestIntent = null;
  let bestScore = 0;
  let bestExact = false;

  for (const [intent, keywords] of Object.entries(INTENT_KEYWORDS)) {
    let score = 0;
    let exact = false;
    for (const word of words) {
      if (word.length < 3) continue;
      for (const keyword of keywords) {
        const threshold = getFuzzyThreshold(keyword.length);
        const dist = levenshtein(word, keyword);
        if (dist <= threshold) {
          score++;
          if (dist === 0) exact = true;
        }
      }
    }
    if (score > bestScore || (score === bestScore && exact && !bestExact)) {
      bestScore = score;
      bestIntent = intent;
      bestExact = exact;
    }
  }

  return bestScore >= 1 && bestExact ? bestIntent : null;
}

export function detectIntent(message, currentState = 'IDLE') {
  let cleaned = message.toLowerCase().replace(/[.,!?;:]/g, '');
  cleaned = cleaned.replace(/\s+/g, ' ').trim();

  for (const intent of INTENT_PRIORITY) {
    if (intentPatterns[intent].test(cleaned)) return intent;
  }

  const fuzzy = fuzzyDetectIntent(cleaned);
  if (fuzzy) return fuzzy;

  return null;
}

export function getFallbackResponse() {
  return (
    'Halo! 😊 Saya asisten RS. Kalau ada yang bisa dibantu, silakan tulis saja ya.\n\n' +
    'Ketik *0* untuk lihat menu lengkap.\n\n' +
    'Beberapa contoh:\n' +
    '• "Saya mau daftar berobat"\n' +
    '• "Cek jadwal dokter penyakit dalam"\n' +
    '• "Info tempat tidur kosong"\n' +
    '• "Cek status booking saya"'
  );
}
