import * as db from '../database.js';
import { detectIntent, getFallbackResponse } from '../nlp-engine.js';
import * as gemini from '../gemini-api.js';
import { sendWithDelay } from './utils.js';
import { HELP_TEXT, MENU_NUMBERS } from './constants.js';
import { handleCheckDoctorSchedule } from './doctor-schedule.js';
import { handleCheckBed } from './bed.js';
import { handleStatus } from './status.js';
import { handleCheckQueue } from './queue.js';
import { handleMcu } from './mcu.js';
import { handleCancelBooking } from './cancel-booking.js';
import { handleScheduleByDate } from './schedule-by-date.js';

const GREATINGS = /\b(halo|hallo|hello|helo|hii|hai|hay|pagi|siang|sore|malam|selamat)\b/i;

function truncateContent(content, maxLen = 400) {
  if (!content || content.length <= maxLen) return content || '';
  const breakPoint = content.lastIndexOf('.', maxLen);
  if (breakPoint > maxLen * 0.6) return content.slice(0, breakPoint + 1) + '\n... *(dan seterusnya)*';
  return content.slice(0, maxLen) + '\n... *(dan seterusnya)*';
}

async function searchContext(message) {
  try {
    const rows = await db.query(
      `SELECT content FROM bot_context
       WHERE active = 1 AND status = 'completed' AND content IS NOT NULL
       ORDER BY created_at DESC`
    );

    if (!rows || rows.length === 0) return null;

    const words = message.toLowerCase().split(/\s+/).filter(w => w.length > 3);
    if (words.length === 0) return null;

    const relevant = rows.filter(r =>
      words.some(w => r.content && r.content.toLowerCase().includes(w))
    );

    if (relevant.length === 0) return null;

    return relevant.slice(0, 2).map(r => r.content);
  } catch (e) {
    console.error('Context query error:', e.message);
    return null;
  }
}

function buildContextResponse(contents) {
  const info = contents.map(c => truncateContent(c)).join('\n\n');
  return `📋 Berdasarkan informasi yang ada di sistem:\n\n${info}\n\n💡 Ada yang bisa saya bantu lagi?`;
}

export async function handleIdleState(sender, message) {
  const trimmed = message.trim();
  const menuIntent = MENU_NUMBERS[trimmed];
  const intent = menuIntent || detectIntent(message, 'IDLE');

  const nameMatch = message.match(/(?:nama\s+(?:saya|aku|gue)\s+|perkenalkan\s+(?:nama\s+(?:saya|aku|gue)\s+)?|panggil\s+(?:saya|aku)\s+)(.+)/i);
  if (nameMatch) {
    const name = nameMatch[1].replace(/[.,!?;]+$/, '').trim();
    if (name.length > 1 && name.length <= 50) await db.setMemory(sender, 'user_name', name);
  }

  const allMemory = await db.getAllMemory(sender);
  const userName = allMemory.user_name || null;

  if (GREATINGS.test(message) && !intent) {
    const greeting = userName
      ? `Halo Kak *${userName}*! 👋 Senang bertemu lagi. Ada yang bisa saya bantu?\n\nKetik *0* untuk melihat menu.`
      : HELP_TEXT;
    await sendWithDelay(sender, greeting);
    return;
  }

  switch (intent) {
    case 'REGISTRATION':
      await db.upsertSession(sender, 'AWAITING_ID', {});
      await sendWithDelay(sender,
        '😊 *Baik, saya bantu daftar rawat jalan!*\n\n' +
        'Silakan siapkan data berikut:\n\n' +
        '✏️ *Pasien Lama* → Kirimkan *No Rekam Medis (RM)*\n' +
        '✏️ *Pasien Baru* → Kirimkan *NIK* (16 digit)\n\n' +
        'Contoh:\n' +
        '• Punya RM? Ketik: `000001`\n' +
        '• Baru pertama? Ketik: `3674060903970004`\n\n' +
        'Atau ketik *0* untuk kembali ke menu utama.'
      );
      break;

    case 'CHECK_DOCTOR_SCHEDULE':
      await handleCheckDoctorSchedule(sender);
      break;

    case 'CHECK_BED':
      await handleCheckBed(sender);
      break;

    case 'STATUS':
      await handleStatus(sender);
      break;

    case 'CHECK_QUEUE':
      await handleCheckQueue(sender);
      break;

    case 'MCU':
      await handleMcu(sender);
      break;

    case 'CANCEL_BOOKING':
      await handleCancelBooking(sender);
      break;

    case 'CHECK_SCHEDULE_BY_DATE':
      await handleScheduleByDate(sender);
      break;

    case 'HELP':
      if (userName) {
        await sendWithDelay(sender, `Halo Kak *${userName}*! 👋 Ada yang bisa saya bantu?\n\n${HELP_TEXT}`);
      } else {
        await sendWithDelay(sender, HELP_TEXT);
      }
      break;

    case 'CANCEL':
      await sendWithDelay(sender, '✅ Tidak ada proses yang berjalan. Ketik *0* untuk melihat menu ya 😊');
      break;

    default:
      const contextContents = await searchContext(message);
      const contextStr = contextContents ? contextContents.join('\n\n') : '';
      const memoryStr = userName ? `Nama user: ${userName}. ` : '';
      const geminiPrompt = memoryStr || contextStr
        ? `[Informasi RS]\n${contextStr}\n\n[Data User]\n${memoryStr}\n\n[Pesan User]\n${message}`
        : message;
      const geminiReply = await gemini.chat(geminiPrompt);
      if (geminiReply) {
        await sendWithDelay(sender, geminiReply);
      } else if (userName) {
        await sendWithDelay(sender,
          `Halo Kak *${userName}*! 😊 Ada yang bisa saya bantu hari ini?\n\n` +
          `Ketik *0* untuk melihat menu lengkap.`
        );
      } else if (contextContents) {
        await sendWithDelay(sender, buildContextResponse(contextContents));
      } else {
        await sendWithDelay(sender, getFallbackResponse());
      }
  }
}
