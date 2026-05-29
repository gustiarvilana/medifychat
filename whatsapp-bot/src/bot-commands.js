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
