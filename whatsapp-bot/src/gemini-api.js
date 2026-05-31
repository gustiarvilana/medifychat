import config from './config.js';
import * as db from './database.js';
import { buildSystemInstruction } from './bot-profile.js';

const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

let cachedKey = null;
let cachedKeyTime = 0;
const CACHE_TTL = 60000;

let cachedContext = '';
let cachedContextTime = 0;
const CONTEXT_CACHE_TTL = 300000; // 5 minutes

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

export async function loadContext() {
  if (Date.now() - cachedContextTime < CONTEXT_CACHE_TTL && cachedContext) return cachedContext;
  
  try {
    const contextData = await db.getActiveContext();
    cachedContext = contextData ? contextData.content : '';
    cachedContextTime = Date.now();
    return cachedContext;
  } catch (error) {
    console.error('Failed to load context:', error);
    return '';
  }
}

export function refreshContextCache() {
  cachedContext = '';
  cachedContextTime = 0;
}

async function getSystemInstruction() {
  const context = await loadContext();
  return buildSystemInstruction(context);
}

export async function chat(message) {
  const apiKey = await getApiKey();
  if (!apiKey || apiKey === 'your_gemini_api_key_here') {
    return null;
  }

  const systemInstruction = await getSystemInstruction();

  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 8000);

    const response = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-goog-api-key': apiKey },
      signal: controller.signal,
      body: JSON.stringify({
        system_instruction: {
          parts: [{ text: systemInstruction }],
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
