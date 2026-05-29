const intentPatterns = {
  REGISTRATION: /\b(daftar|registrasi|booking|buat janji|pendaftaran|janji temu|mau daftar|butuh daftar)\b/i,
  CHECK_DOCTOR_SCHEDULE: /\b(jadwal dokter|praktek dokter|dokter praktek|cek jadwal|dokter\s+\w+\s+praktek|kapan\s+\w+\s+praktek)\b/i,
  CHECK_BED: /\b(tempat tidur|bed kosong|ketersediaan bed|rawat inap|ruang kosong|tempat tidur kosong|bed tersedia)\b/i,
  HELP: /\b(tolong|bantuan|help|menu|can you help|apa yang bisa kamu bantu|perintah|panduan|halo|hallo|hello|helo|hai|pagi|siang|sore)\b/i,
  CANCEL: /\b(batal|cancel|batalkan|urungkan|kembali|gak jadi)\b/i,
  STATUS: /\b(status|cek booking|kode booking|cek antrian|status pendaftaran)\b/i,
  CONTINUE: /\b(lanjut|continue|next|lanjutkan|ya|ok|oke)\b/i,
};

const INTENT_PRIORITY = [
  'CANCEL',
  'REGISTRATION',
  'CHECK_DOCTOR_SCHEDULE',
  'CHECK_BED',
  'STATUS',
  'HELP',
];

const INTENT_KEYWORDS = {
  REGISTRATION: ['daftar', 'registrasi', 'booking', 'janji', 'pendaftaran'],
  CHECK_DOCTOR_SCHEDULE: ['jadwal', 'praktek', 'dokter'],
  CHECK_BED: ['bangsal', 'bed', 'rawat', 'inap', 'ketersediaan'],
  HELP: ['tolong', 'bantuan', 'perintah', 'panduan', 'halo', 'hai', 'pagi', 'siang', 'sore'],
  CANCEL: ['batal', 'cancel', 'batalkan', 'urungkan', 'kembali'],
  STATUS: ['status', 'booking', 'antrian', 'kode'],
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

  for (const [intent, keywords] of Object.entries(INTENT_KEYWORDS)) {
    let score = 0;
    for (const word of words) {
      if (word.length < 3) continue;
      for (const keyword of keywords) {
        const threshold = getFuzzyThreshold(keyword.length);
        if (levenshtein(word, keyword) <= threshold) {
          score++;
        }
      }
    }
    if (score > bestScore) {
      bestScore = score;
      bestIntent = intent;
    }
  }

  return bestScore >= 1 ? bestIntent : null;
}

export function detectIntent(message, currentState = 'IDLE') {
  let cleaned = message.toLowerCase().replace(/[.,!?;:]/g, '');
  cleaned = cleaned.replace(/\s+/g, ' ').trim();

  if (currentState === 'AWAITING_CONFIRMATION' && intentPatterns.CONTINUE.test(cleaned)) {
    return 'CONTINUE';
  }

  if (intentPatterns.CANCEL.test(cleaned)) return 'CANCEL';

  for (const intent of INTENT_PRIORITY) {
    if (intentPatterns[intent].test(cleaned)) return intent;
  }

  const fuzzy = fuzzyDetectIntent(cleaned);
  if (fuzzy) return fuzzy;

  return null;
}

export function getFallbackResponse() {
  return (
    'Maaf, saya tidak mengerti. Ketik *Bantuan* untuk melihat menu yang tersedia.\n\n' +
    'Anda bisa mengatakan:\n' +
    '• "Daftar berobat" - Mendaftar rawat jalan\n' +
    '• "Cek jadwal dokter" - Lihat jadwal praktek dokter\n' +
    '• "Cek tempat tidur" - Ketersediaan bed kosong\n' +
    '• "Bantuan" - Menampilkan menu ini'
  );
}
