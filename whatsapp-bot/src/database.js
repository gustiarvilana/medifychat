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

export async function upsertSession(waId, currentState, formData = {}, waName = null) {
  const existing = await getSession(waId);
  if (existing) {
    const updateFields = ['current_state = ?', 'form_data = ?', 'last_activity = NOW()'];
    const params = [currentState, JSON.stringify(formData)];
    
    if (waName) {
      updateFields.push('wa_name = ?');
      params.push(waName.length > 100 ? waName.slice(0, 100) : waName);
    }
    
    params.push(waId);
    await execute(
      `UPDATE user_sessions SET ${updateFields.join(', ')} WHERE wa_id = ?`,
      params
    );
  } else {
    const truncated = waName ? (waName.length > 100 ? waName.slice(0, 100) : waName) : null;
    await execute(
      'INSERT INTO user_sessions (wa_id, wa_name, current_state, form_data) VALUES (?, ?, ?, ?)',
      [waId, truncated, currentState, JSON.stringify(formData)]
    );
  }
}

export async function updateSessionState(waId, state, waName = null) {
  if (waName) {
    const truncated = waName.length > 100 ? waName.slice(0, 100) : waName;
    await execute(
      'UPDATE user_sessions SET current_state = ?, wa_name = ?, last_activity = NOW() WHERE wa_id = ?',
      [state, truncated, waId]
    );
  } else {
    await execute(
      'UPDATE user_sessions SET current_state = ?, last_activity = NOW() WHERE wa_id = ?',
      [state, waId]
    );
  }
}

export async function updateSessionData(waId, formData) {
  await execute(
    'UPDATE user_sessions SET form_data = ?, last_activity = NOW() WHERE wa_id = ?',
    [JSON.stringify(formData), waId]
  );
}

export async function resetSession(waId) {
  await upsertSession(waId, 'IDLE', {});
  await clearChatHistory(waId);
}

export async function getBotStatus() {
  const rows = await query('SELECT * FROM bot_status WHERE id = 1');
  return rows[0] || null;
}

export async function reportError(errorMessage) {
  try {
    await execute(
      `UPDATE bot_status SET last_error = ?, last_error_at = NOW(), last_error_notified = 0, updated_at = NOW() WHERE id = 1`,
      [errorMessage]
    );
  } catch (_) {}
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

export async function setMemory(sender, key, value) {
  const phone = sender.split('@')[0];
  await execute(
    `INSERT INTO bot_memory (sender, key_name, value, updated_at) VALUES (?, ?, ?, NOW())
     ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()`,
    [phone, key, value, value]
  );
}

export async function getMemory(sender, key) {
  const phone = sender.split('@')[0];
  const rows = await query('SELECT value FROM bot_memory WHERE sender = ? AND key_name = ?', [phone, key]);
  return rows[0]?.value || null;
}

export async function getAllMemory(sender) {
  const phone = sender.split('@')[0];
  const rows = await query('SELECT key_name, value FROM bot_memory WHERE sender = ?', [phone]);
  const result = {};
  for (const row of rows) {
    result[row.key_name] = row.value;
  }
  return result;
}

export async function getChatHistory(sender) {
  const phone = sender.split('@')[0];
  const rows = await query(
    'SELECT value FROM bot_memory WHERE sender = ? AND key_name = ?',
    [phone, 'chat_history']
  );
  if (!rows[0]?.value) return [];
  try {
    return JSON.parse(rows[0].value);
  } catch {
    return [];
  }
}

export async function appendChatHistory(sender, role, text) {
  const history = await getChatHistory(sender);
  history.push({ role, text });
  if (history.length > 10) {
    history.splice(0, history.length - 10);
  }
  const phone = sender.split('@')[0];
  await execute(
    `INSERT INTO bot_memory (sender, key_name, value, updated_at) VALUES (?, 'chat_history', ?, NOW())
     ON DUPLICATE KEY UPDATE value = ?, updated_at = NOW()`,
    [phone, JSON.stringify(history), JSON.stringify(history)]
  );
}

export async function clearChatHistory(sender) {
  const phone = sender.split('@')[0];
  await execute(
    'DELETE FROM bot_memory WHERE sender = ? AND key_name = ?',
    [phone, 'chat_history']
  );
}

export async function cleanupExpiredSessions() {
  const timeout = config.bot.sessionTimeout / 1000;
  await execute(
    "UPDATE user_sessions SET current_state = 'IDLE', form_data = '{}' WHERE last_activity < NOW() - INTERVAL ? SECOND AND current_state != 'IDLE'",
    [timeout]
  );
}
