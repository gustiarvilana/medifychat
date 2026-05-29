import mysql from 'mysql2/promise';
const c = await mysql.createConnection({ host: '127.0.0.1', user: 'root', database: 'laravel' });
await c.execute("UPDATE bot_status SET admin_wa_number = '42000535535619@lid' WHERE id = 1");
console.log('Seeded');
await c.end();
