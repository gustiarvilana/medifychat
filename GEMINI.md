# Medify Chat

Medify Chat is an integrated WhatsApp bot and admin dashboard system designed to facilitate online registration for patients at Medify Hospital. It bridges the gap between traditional hospital registration systems and patient convenience by providing a natural language interface for common tasks.

## Project Overview

- **Core Functionality:** Automated patient registration, doctor schedule lookup, and bed availability checks via WhatsApp.
- **Bot Engine:** A Node.js application powered by `@whiskeysockets/baileys` and a custom Regex-based NLP engine.
- **Admin Dashboard:** A Laravel 13 application for managing bot status, monitoring activity, and controlling the bot process (start/stop/restart/logout).
- **Integration:** Directly interacts with the Medify SIMRS API.

## Project Structure

- `whatsapp-bot/`: The core Node.js bot application.
  - `src/`: Bot source code (message handlers, NLP engine, Baileys client).
  - `auth/`: Baileys authentication state (stored locally).
- `app/`: Laravel application logic.
  - `Http/Controllers/`: Controllers for dashboard, bot management, and settings.
  - `Models/`: Eloquent models for bot status, sessions, and commands.
- `database/`: Database schema, migrations, and seeders.
- `resources/views/`: Blade templates for the admin UI.
- `.agents/`: Critical project documentation (PRD, Design, Tasklists).
  - `prd.md`: Full Product Requirements Document (Priority: High).
  - `design.md`: UI/UX and aesthetic guidelines.
  - `tasklist.md`: Progress tracking (Update this after every task!).

## Building and Running

### Prerequisites
- PHP 8.3+
- Node.js 20.x
- MySQL 8.0
- Composer

### Setup
Run the following command from the project root to perform a full initial setup:
```powershell
composer run setup
```
This will install dependencies (PHP & NPM), generate keys, run migrations, and build frontend assets.

### Running for Development

**Laravel Admin Dashboard:**
```powershell
composer run dev
```
Starts the PHP server, queue listener, log tailing, and Vite dev server concurrently.

**WhatsApp Bot:**
```powershell
cd whatsapp-bot
npm run dev
```
Starts the bot in watch mode.

### Testing
```powershell
composer run test
```

## Development Conventions

1.  **Mandatory Documentation:** Always read `.agents/prd.md` and `.agents/design.md` before starting work. They contain the source of truth for features and aesthetics.
2.  **Task Tracking:** You **MUST** update `.agents/tasklist.md` after completing any task. Mark tasks with `[x]` and add the ✅ emoji.
3.  **Surgical Edits:** Prefer using the `replace` tool for targeted changes to maintain code integrity.
4.  **Verification:** Every code change must be followed by empirical verification (running the code or tests).
5.  **State Management:** The bot uses MySQL (`bot_status`, `bot_commands`, `user_sessions`) for state persistence, allowing the admin panel to communicate with the bot process.
6.  **Style:** Follow Laravel's idiomatic style (PSR-12) for PHP and modern ESM standards for Node.js. Use Vanilla CSS/Sass for styling as per `design.md`.

## Key Files
- `whatsapp-bot/src/index.js`: Entry point for the bot.
- `whatsapp-bot/src/message-handler.js`: Main logic for processing incoming messages.
- `routes/web.php`: Admin panel routing and bot management endpoints.
- `app/Http/Controllers/BotStatusController.php`: Handles bot status reporting to the dashboard.
