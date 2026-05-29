import mysql from 'mysql2/promise';
import config from './config.js';

let pool;

export async function getPool() {
  if (!pool) {
    pool = mysql.createPool({
      ...config.db,
      waitForConnections: true,
      connectionLimit: 10,
    });
  }
  return pool;
}

export async function query(sql, params = []) {
  const p = await getPool();
  const [rows] = await p.query(sql, params);
  return rows;
}

export async function execute(sql, params = []) {
  const p = await getPool();
  const [result] = await p.execute(sql, params);
  return result;
}

export async function getSession(waId) {
  const rows = await query('SELECT * FROM user_sessions WHERE wa_id = ?', [waId]);
  return rows[0] || null;
}

export async function upsertSession(waId, currentState, formData = {}) {
  const existing = await getSession(waId);
  if (existing) {
    await execute(
      'UPDATE user_sessions SET current_state = ?, form_data = ?, last_activity = NOW() WHERE wa_id = ?',
      [currentState, JSON.stringify(formData), waId]
    );
  } else {
    await execute(
      'INSERT INTO user_sessions (wa_id, current_state, form_data) VALUES (?, ?, ?)',
      [waId, currentState, JSON.stringify(formData)]
    );
  }
}

export async function updateSessionState(waId, state) {
  await execute(
    'UPDATE user_sessions SET current_state = ?, last_activity = NOW() WHERE wa_id = ?',
    [state, waId]
  );
}

export async function updateSessionData(waId, formData) {
  await execute(
    'UPDATE user_sessions SET form_data = ?, last_activity = NOW() WHERE wa_id = ?',
    [JSON.stringify(formData), waId]
  );
}

export async function resetSession(waId) {
  await upsertSession(waId, 'IDLE', {});
}

export async function getBotStatus() {
  const rows = await query('SELECT * FROM bot_status WHERE id = 1');
  return rows[0] || null;
}

export async function updateBotStatus(data) {
  const fields = [];
  const values = [];
  for (const [key, value] of Object.entries(data)) {
    fields.push(`${key} = ?`);
    values.push(value);
  }
  values.push(1);
  await execute(
    `UPDATE bot_status SET ${fields.join(', ')}, updated_at = NOW() WHERE id = ?`,
    values
  );
}

export async function getPendingCommands() {
  return await query(
    "SELECT * FROM bot_commands WHERE status = 'pending' ORDER BY created_at ASC"
  );
}

export async function markCommandProcessed(id, success = true) {
  await execute(
    "UPDATE bot_commands SET status = ?, processed_at = NOW() WHERE id = ?",
    [success ? 'processed' : 'failed', id]
  );
}

export async function cleanupExpiredSessions() {
  const timeout = config.bot.sessionTimeout / 1000;
  await execute(
    "UPDATE user_sessions SET current_state = 'IDLE', form_data = '{}' WHERE last_activity < NOW() - INTERVAL ? SECOND AND current_state != 'IDLE'",
    [timeout]
  );
}
