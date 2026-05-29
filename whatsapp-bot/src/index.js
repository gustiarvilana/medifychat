import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import express from 'express';
import config from './config.js';
import { startBot, sendMessage, isLoggedIn } from './baileys-client.js';
import { setSendMessage } from './message-handler.js';
import { startPolling, stopPolling } from './bot-commands.js';
import { getPool, updateBotStatus, cleanupExpiredSessions, getBotStatus } from './database.js';

const PORT = parseInt(process.argv[2] || process.env.PORT || '3001');

async function main() {
  console.log(`Starting Medify WhatsApp Bot on port ${PORT}...`);

  // Write PID file for external process management
  const __dirname = path.dirname(fileURLToPath(import.meta.url));
  const pidFile = path.join(__dirname, '..', 'bot.pid');
  fs.writeFileSync(pidFile, String(process.pid));

  // Update port in database
  await updateBotStatus({ port: PORT });

  // Initialize database pool
  await getPool();
  console.log('Database connected');

  // Set up message sender
  setSendMessage(sendMessage);

  // Start WhatsApp bot
  const sock = await startBot();

  // Start polling for admin commands
  startPolling(sock);

  // Track previous quota state to avoid repeated notifications
  let prevQuotaExhausted = false;

  // Start heartbeat - update status every 10 seconds
  setInterval(async () => {
    try {
      const status = await getBotStatus();
      await updateBotStatus({
        is_running: true,
        is_logged_in: isLoggedIn(),
        last_activity: new Date(),
        port: status?.port || PORT,
      });

      const quotaExhausted = status?.quota_exhausted ? true : false;
      const quotaNotified = status?.quota_notified ? true : false;
      let adminNumber = status?.admin_wa_number || config.admin.whatsappNumber;
      if (adminNumber && !adminNumber.includes('@')) {
        adminNumber += '@lid';
      }

      if (quotaExhausted && !quotaNotified && adminNumber) {
        await sendMessage(adminNumber,
          '⚠️ *Peringatan: Kuota Gemini API Habis*\n\n' +
          'Bot AI tidak bisa merespons pesan natural karena kuota harian Google Gemini API telah habis.\n' +
          'Bot masih berfungsi untuk perintah dasar (daftar, jadwal, cek bed).\n\n' +
          'Solusi:\n' +
          '• Buat API key baru di https://aistudio.google.com\n' +
          '• Tunggu reset quota (tengah malam waktu Pacific)\n' +
          '• Upgrade ke paid tier'
        );
        await updateBotStatus({ quota_notified: 1 });
        console.log('Quota notification sent to admin');
      }

      if (prevQuotaExhausted && !quotaExhausted && adminNumber) {
        await sendMessage(adminNumber,
          '✅ *Kuota Gemini API Pulih*\n\n' +
          'Bot AI sudah bisa merespons pesan natural kembali.'
        );
        console.log('Quota restored notification sent to admin');
      }

      prevQuotaExhausted = quotaExhausted;
    } catch (error) {
      console.error('Heartbeat error:', error.message);
    }
  }, config.bot.heartbeatInterval);

  // Cleanup expired sessions every 5 minutes
  setInterval(async () => {
    try {
      await cleanupExpiredSessions();
      console.log('Cleaned up expired sessions');
    } catch (error) {
      console.error('Session cleanup error:', error.message);
    }
  }, 5 * 60 * 1000);

  // Express health check server
  const app = express();

  app.get('/health', (req, res) => {
    res.json({
      status: 'ok',
      running: true,
      logged_in: isLoggedIn(),
      port: PORT,
    });
  });

  app.get('/qr-code', async (req, res) => {
    try {
      const status = await getBotStatus();
      res.json({ qr_code: status?.qr_code || null });
    } catch (error) {
      res.status(500).json({ error: error.message });
    }
  });

  app.listen(PORT, () => {
    console.log(`Health check server running on port ${PORT}`);
  });

  console.log('Medify WhatsApp Bot is ready!');
}

// Graceful shutdown
async function shutdown() {
  console.log('Shutting down...');
  stopPolling();
  try {
    fs.unlinkSync(pidFile);
  } catch (_) {}
  await updateBotStatus({
    is_running: false,
    is_logged_in: false,
    qr_code: null,
  });
  process.exit(0);
}

process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);

main().catch((error) => {
  console.error('Fatal error:', error);
  updateBotStatus({ is_running: false, is_logged_in: false, qr_code: null })
    .then(() => process.exit(1))
    .catch(() => process.exit(1));
});
