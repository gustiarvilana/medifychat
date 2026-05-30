import makeWASocket, {
  useMultiFileAuthState,
  DisconnectReason,
  makeCacheableSignalKeyStore,
} from '@whiskeysockets/baileys';
import QRCode from 'qrcode';
import * as db from './database.js';
import { handleMessage, handleMessageForState } from './message-handler.js';
import pino from 'pino';

let sock = null;
let isConnected = false;

export async function startBot() {
  const { state, saveCreds } = await useMultiFileAuthState('auth');

  const logger = pino({ level: 'silent' });

  sock = makeWASocket({
    auth: {
      creds: state.creds,
      keys: makeCacheableSignalKeyStore(state.keys, logger),
    },
    printQRInTerminal: false,
    logger,
    browser: ['Medify Bot', 'Chrome', '1.0.0'],
    markOnlineOnConnect: false,
    syncFullHistory: true,
    generateHighQualityLinkPreview: false,
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('lid-mapping.update', (mappings) => {
    for (const [lid, pn] of Object.entries(mappings)) {
      console.log(`LID Mapping found: ${lid} -> ${pn}`);
    }
  });

  sock.ev.on('connection.update', async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      console.log('QR Code received');
      try {
        const qrImage = await QRCode.toDataURL(qr, { width: 400, margin: 2 });
        await db.updateBotStatus({
          qr_code: qrImage,
          is_logged_in: false,
          is_running: true,
          last_activity: new Date(),
        });
      } catch (err) {
        console.error('Failed to generate QR image:', err);
      }
    }

    if (connection === 'open') {
      console.log('WhatsApp connected!');
      isConnected = true;
      await db.updateBotStatus({
        is_logged_in: true,
        is_running: true,
        qr_code: null,
        last_activity: new Date(),
      });
    }

    if (connection === 'close') {
      isConnected = false;
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      console.log('Connection closed, statusCode:', statusCode);

      const isLoggedOut = statusCode === DisconnectReason.loggedOut;
      await db.updateBotStatus({
        is_logged_in: false,
        is_running: isLoggedOut ? false : true,
        last_activity: new Date(),
      });

      if (isLoggedOut) {
        console.log('Logged out, clearing auth for fresh QR...');
        try {
          const { readdirSync, unlinkSync } = await import('fs');
          const { join } = await import('path');
          const authDir = join(process.cwd(), 'auth');
          for (const file of readdirSync(authDir)) {
            unlinkSync(join(authDir, file));
          }
          console.log('Auth files cleared');
        } catch (_) {}
        await db.updateBotStatus({ is_running: false });
        setTimeout(() => {
          startBot().catch(err => console.error('StartBot after logout failed:', err));
        }, 3000);
      } else {
        console.log('Reconnecting in 5 seconds...');
        setTimeout(startBot, 5000);
      }
    }
  });

  sock.ev.on('messages.upsert', async (msg) => {
    for (const message of msg.messages) {
      if (!message.key || message.key.fromMe) continue;

      const text =
        message.message?.conversation ||
        message.message?.extendedTextMessage?.text;
      if (!text) continue;

      let sender = message.key.remoteJid;
      const realSender = message.key.remoteJidAlt || message.key.participantAlt;
      
      if (realSender && sender.endsWith('@lid')) {
        console.log(`LID detected: ${sender}, mapping to PN: ${realSender}`);
        sender = realSender;
      } else if (sender.endsWith('@lid')) {
        // Try to get from store if available
        const lidStore = sock.signalRepository?.lidMapping;
        if (lidStore) {
          const pnJid = await lidStore.getPNForLID(sender);
          if (pnJid) {
            console.log(`LID store mapping: ${sender} -> ${pnJid}`);
            sender = pnJid;
          }
        }
      }

      const name = message.pushName || null;
      console.log(`Message from ${sender} (${name}): ${text}`);

      try {
        const session = await db.getSession(sender);
        const state = session?.current_state || 'IDLE';
        const raw = session?.form_data || {};
        const formData = typeof raw === 'string' ? JSON.parse(raw) : raw;

        const handled = await handleMessageForState(sender, text, state, formData, name);
        if (!handled) {
          await handleMessage(sender, text, name);
        }

        await db.updateBotStatus({ last_activity: new Date() });
      } catch (error) {
        console.error('Error handling message:', error);
      }
    }
  });

  sock.ev.on('messages.update', async (event) => {
    for (const { key, update } of event) {
      if (update.pollUpdates) {
        console.log('Poll update received, key:', key.id);
      }
    }
  });

  return sock;
}

export function getSocket() {
  return sock;
}

export async function sendMessage(jid, text) {
  if (!sock) throw new Error('Socket not initialized');
  await sock.sendMessage(jid, { text });
}

export function isLoggedIn() {
  return isConnected;
}
