import { spawn } from 'child_process';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';
import dotenv from 'dotenv';

const PORT = parseInt(process.argv[2] || process.env.PORT || '3001');
const __dirname = path.dirname(fileURLToPath(import.meta.url));
const botScript = path.join(__dirname, 'src', 'index.js');
const logFile = path.join(__dirname, 'bot.log');
const errFile = path.join(__dirname, 'bot-err.log');

// Load .env and force override so child gets correct values
const dotenvResult = dotenv.config({ path: path.join(__dirname, '.env'), override: true });

const env = { ...process.env };

const outFd = fs.openSync(logFile, 'a');
const errFd = fs.openSync(errFile, 'a');

const child = spawn(process.execPath, [botScript, String(PORT)], {
  detached: true,
  stdio: ['ignore', outFd, errFd],
  cwd: __dirname,
  env,
});

child.unref();
console.log(String(child.pid));
