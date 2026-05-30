import config from './config.js';
import * as db from './database.js';

const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

let cachedKey = null;
let cachedKeyTime = 0;
const CACHE_TTL = 60000;

async function getApiKey() {
  if (Date.now() - cachedKeyTime < CACHE_TTL && cachedKey) return cachedKey;

  try {
    const status = await db.getBotStatus();
    if (status?.gemini_api_key) {
      cachedKey = status.gemini_api_key;
      cachedKeyTime = Date.now();
      return cachedKey;
    }
  } catch (_) {}

  return config.gemini.apiKey;
}

const SYSTEM_INSTRUCTION = `Kamu adalah MedifyBot, asisten rumah sakit yang membantu pasien mendaftar rawat jalan.

Fitur yang tersedia:
1. Daftar rawat jalan (kata kunci: daftar, registrasi, booking)
2. Cek jadwal dokter (kata kunci: jadwal dokter, praktek dokter)
3. Cek ketersediaan tempat tidur (kata kunci: tempat tidur, bed kosong)
4. Cek status booking (kata kunci: status booking, kode booking)
5. Bantuan (kata kunci: bantuan, menu)

Jika user ingin mendaftar, arahkan mereka untuk mengetik kata "daftar" atau "saya mau daftar".
Jika user bertanya di luar konteks rumah sakit, jawab dengan ramah dan arahkan kembali ke fitur yang tersedia.
Gunakan bahasa Indonesia yang natural dan ramah seperti seorang customer service rumah sakit.
Jangan pernah memberikan saran medis atau diagnosis.
Jangan membuat janji palsu tentang ketersediaan dokter atau obat.
Jawab singkat dan jelas, maksimal 3 paragraf.`;

export async function chat(message) {
  const apiKey = await getApiKey();
  if (!apiKey || apiKey === 'your_gemini_api_key_here') {
    return null;
  }

  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 8000);

    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-goog-api-key': apiKey },
      signal: controller.signal,
      body: JSON.stringify({
        system_instruction: {
          parts: [{ text: SYSTEM_INSTRUCTION }],
        },
        contents: [{
          parts: [{ text: message }],
        }],
        generationConfig: {
          temperature: 0.7,
          maxOutputTokens: 500,
        },
      }),
    });

    clearTimeout(timeout);

    if (!response.ok) {
      const errBody = await response.text();
      console.error(`Gemini API error (${response.status}):`, errBody.substring(0, 500));

      if (response.status === 429) {
        db.updateBotStatus({ quota_exhausted: 1, quota_notified: 0 }).catch(() => {});
      }
      return null;
    }

    const data = await response.json();

    db.updateBotStatus({ quota_exhausted: 0, quota_notified: 0 }).catch(() => {});

    return data.candidates?.[0]?.content?.parts?.[0]?.text || null;
  } catch (error) {
    console.error('Gemini API call failed:', error.message);
    return null;
  }
}
