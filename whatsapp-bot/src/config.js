import 'dotenv/config';

export default {
  db: {
    host: process.env.DB_HOST || '127.0.0.1',
    port: parseInt(process.env.DB_PORT || '3306'),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'medifychat',
  },
  medify: {
    apiUrl: process.env.MEDIFY_API_URL || 'http://localhost/api/online',
    email: process.env.MEDIFY_API_EMAIL || '',
    password: process.env.MEDIFY_API_PASSWORD || '',
  },
  bot: {
    sessionTimeout: parseInt(process.env.BOT_SESSION_TIMEOUT || '30') * 60 * 1000,
    heartbeatInterval: parseInt(process.env.BOT_HEARTBEAT_INTERVAL || '10000'),
    messageDelay: parseInt(process.env.BOT_MESSAGE_DELAY || '3000'),
  },
  gemini: {
    apiKey: process.env.GEMINI_API_KEY || '',
  },
  admin: {
    whatsappNumber: process.env.ADMIN_WHATSAPP_NUMBER || '',
  },
  port: parseInt(process.env.PORT || '3001'),
};
