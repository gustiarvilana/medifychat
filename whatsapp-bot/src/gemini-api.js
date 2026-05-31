import config from './config.js';
import * as db from './database.js';
import { buildSystemInstruction } from './bot-profile.js';

const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

let cachedKey = null;
let cachedKeyTime = 0;
const CACHE_TTL = 60000;

let cachedContext = '';
let cachedContextTime = 0;
const CONTEXT_CACHE_TTL = 5 * 60 * 1000;

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

async function loadContext() {
  try {
    const rows = await db.query(
      `SELECT content FROM bot_context WHERE active = 1 AND status = 'completed' AND content IS NOT NULL ORDER BY created_at DESC`
    );
    if (!rows || rows.length === 0) return '';
    const combined = rows.map(r => r.content).join('\n\n');
    return combined.length > 10000 ? combined.slice(0, 10000) + '\n\n... *(dan seterusnya)*' : combined;
  } catch (e) {
    console.error('Load context error:', e.message);
    return '';
  }
}

async function getSystemInstruction() {
  if (Date.now() - cachedContextTime > CONTEXT_CACHE_TTL) {
    cachedContext = await loadContext();
    cachedContextTime = Date.now();
  }
  return buildSystemInstruction(cachedContext);
}

export async function refreshContextCache() {
  cachedContext = await loadContext();
  cachedContextTime = Date.now();
  console.log('Context cache refreshed');
}

export async function chat(message, history = []) {
  const apiKey = await getApiKey();
  if (!apiKey || apiKey === 'your_gemini_api_key_here') {
    return null;
  }

  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 8000);

    const contents = [
      ...history.map(h => ({
        role: h.role === 'model' ? 'model' : 'user',
        parts: [{ text: h.text }],
      })),
      {
        role: 'user',
        parts: [{ text: message }],
      },
    ];

    const systemInstruction = await getSystemInstruction();

    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-goog-api-key': apiKey },
      signal: controller.signal,
      body: JSON.stringify({
        system_instruction: {
          parts: [{ text: systemInstruction }],
        },
        contents,
        generationConfig: {
          temperature: 0.3,
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
