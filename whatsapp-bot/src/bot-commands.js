import * as db from './database.js';
import { startBot } from './baileys-client.js';

let intervalId = null;

export function startPolling(socket) {
  intervalId = setInterval(async () => {
    try {
      const commands = await db.getPendingCommands();
      for (const cmd of commands) {
        console.log(`Executing command: ${cmd.command}`);

        try {
          switch (cmd.command) {
            case 'logout':
              await db.updateBotStatus({ is_logged_in: false });
              socket?.end(new Error('Logged out by admin'));
              await db.markCommandProcessed(cmd.id, true);
              break;

            case 'restart':
              await db.updateBotStatus({ is_logged_in: false, is_running: false });
              socket?.end(new Error('Restart by admin'));
              setTimeout(() => {
                startBot().catch(console.error);
              }, 3000);
              await db.markCommandProcessed(cmd.id, true);
              break;

            case 'send_message':
              try {
                const payload = JSON.parse(cmd.payload);
                console.log(`Sending message to ${payload.target}...`);
                await socket.sendMessage(payload.target, { text: payload.message });
                console.log('Message sent successfully.');
                await db.markCommandProcessed(cmd.id, true);
              } catch (err) {
                console.error('Failed to send admin message:', err.message);
                console.error('Stack trace:', err.stack);
                
                // Log to file
                const fs = await import('fs');
                fs.appendFileSync('bot-error.log', `[${new Date().toISOString()}] Command ${cmd.id} failed: ${err.message}\n${err.stack}\n`);
                
                await db.markCommandProcessed(cmd.id, false);
              }
              break;

            default:
              await db.markCommandProcessed(cmd.id, false);
          }
        } catch (error) {
          console.error(`Command ${cmd.command} failed:`, error.message);
          await db.markCommandProcessed(cmd.id, false);
        }
      }
    } catch (error) {
      console.error('Error polling commands:', error.message);
    }
  }, 10000);
}

export function stopPolling() {
  if (intervalId) {
    clearInterval(intervalId);
    intervalId = null;
  }
}
