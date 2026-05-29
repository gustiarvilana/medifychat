<x-app-layout>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Main Column -->
        <div class="lg:col-span-8 space-y-gutter">
            <!-- Bot Engine Control -->
            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg" style="box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);">
                <div class="flex items-center justify-between border-b border-surface-container-low pb-md mb-lg">
                    <div class="flex items-center gap-sm">
                        <span class="material-symbols-outlined text-primary">power_settings_new</span>
                        <h2 class="font-title-md text-title-md">Bot Engine</h2>
                    </div>
                    <span id="engine-badge" class="bg-surface-container-high text-on-surface-variant px-sm py-xs rounded-full text-label-caps uppercase tracking-wider">Offline</span>
                </div>

                <!-- Port Configuration -->
                <div class="mb-lg">
                    <label class="font-body-sm font-bold text-on-surface" for="bot-port">Node.js Server Port</label>
                    <p class="text-on-surface-variant font-body-sm mb-base">Port for the WhatsApp bot engine (1024-65535).</p>
                    <div class="flex items-center gap-md">
                        <input id="bot-port" type="number" min="1024" max="65535" value="3001"
                            class="w-32 px-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
                        <button id="btn-start"
                            onclick="startBot()"
                            class="bg-secondary text-on-secondary px-lg py-sm rounded-xl font-body-md font-semibold hover:opacity-90 transition-all flex items-center gap-sm">
                            <span class="material-symbols-outlined">play_arrow</span>
                            Start
                        </button>
                        <button id="btn-stop"
                            onclick="stopBot()"
                            class="bg-error text-on-error px-lg py-sm rounded-xl font-body-md font-semibold hover:opacity-90 transition-all flex items-center gap-sm hidden">
                            <span class="material-symbols-outlined">stop</span>
                            Stop
                        </button>
                        <button id="btn-restart"
                            onclick="restartBot()"
                            class="border border-outline-variant text-on-surface-variant px-lg py-sm rounded-xl font-body-md font-semibold hover:bg-surface-container-low transition-all flex items-center gap-sm hidden">
                            <span class="material-symbols-outlined">refresh</span>
                            Restart
                        </button>
                    </div>
                </div>

                <!-- Status Log -->
                <div id="status-log" class="bg-surface-dim rounded-lg p-md font-code-sm text-code-sm text-on-surface min-h-[80px] max-h-[160px] overflow-y-auto">
                    Bot is offline. Set a port and click Start.
                </div>
            </section>

            <!-- QR Code Scanner -->
            <section id="qr-section" class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg hidden" style="box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);">
                <div class="flex items-center justify-between border-b border-surface-container-low pb-md mb-lg">
                    <div class="flex items-center gap-sm">
                        <span class="material-symbols-outlined text-primary">qr_code_scanner</span>
                        <h2 class="font-title-md text-title-md">WhatsApp Pairing</h2>
                    </div>
                    <span id="qr-status" class="bg-tertiary-container text-on-tertiary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider">Awaiting Scan</span>
                </div>
                <div class="flex flex-col items-center gap-lg py-lg">
                    <div id="qr-container" class="p-md bg-white rounded-xl border border-outline-variant shadow-sm">
                        <img id="qr-image" src="" alt="WhatsApp QR Code" class="w-56 h-56 hidden" />
                        <div id="qr-placeholder" class="w-56 h-56 bg-surface-container-low flex items-center justify-center rounded-lg">
                            <span class="material-symbols-outlined text-outline text-[64px]">hourglass_empty</span>
                        </div>
                    </div>
                    <div class="space-y-md text-center">
                        <h3 class="font-body-md font-bold text-on-surface">Scan with WhatsApp</h3>
                        <ul class="space-y-sm text-left">
                            <li class="flex gap-sm">
                                <span class="w-6 h-6 rounded-full bg-primary-container text-on-primary text-[12px] flex items-center justify-center flex-shrink-0">1</span>
                                <p class="font-body-sm text-on-surface-variant">Open WhatsApp on your phone.</p>
                            </li>
                            <li class="flex gap-sm">
                                <span class="w-6 h-6 rounded-full bg-primary-container text-on-primary text-[12px] flex items-center justify-center flex-shrink-0">2</span>
                                <p class="font-body-sm text-on-surface-variant">Tap Menu or Settings and select Linked Devices.</p>
                            </li>
                            <li class="flex gap-sm">
                                <span class="w-6 h-6 rounded-full bg-primary-container text-on-primary text-[12px] flex items-center justify-center flex-shrink-0">3</span>
                                <p class="font-body-sm text-on-surface-variant">Point your phone to this screen to capture the code.</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Bot Configuration -->
            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg" style="box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);">
                <div class="flex items-center justify-between border-b border-surface-container-low pb-md mb-lg">
                    <div class="flex items-center gap-sm">
                        <span class="material-symbols-outlined text-primary">psychology</span>
                        <h2 class="font-title-md text-title-md">Bot Configuration</h2>
                    </div>
                    <span id="nlp-badge" class="bg-secondary-container text-on-secondary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider">Active Engine</span>
                </div>
                <div class="space-y-lg">
                    <div class="space-y-xs">
                        <label class="font-body-sm font-bold text-on-surface" for="admin-wa">Admin WhatsApp Number</label>
                        <p class="text-on-surface-variant font-body-sm">Menerima notifikasi bot (quota habis, server down, dll). Cukup nomor saja.</p>
                        <input id="admin-wa" type="text" placeholder="6281234567890"
                            class="w-full px-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
                        <p id="admin-wa-status" class="text-on-surface-variant font-body-sm hidden mt-xs"></p>
                    </div>
                    <div class="space-y-xs">
                        <label class="font-body-sm font-bold text-on-surface" for="gemini-key">Gemini API Key</label>
                        <p class="text-on-surface-variant font-body-sm">Kosongkan jika pakai key dari file .env. Diisi jika ingin ganti tanpa restart.</p>
                        <div class="flex items-center gap-sm">
                            <input id="gemini-key" type="password" placeholder="AIzaSy..."
                                class="flex-1 px-md py-sm bg-surface-container-lowest border border-outline-variant rounded-lg font-body-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all" />
                            <button onclick="toggleGeminiVisibility()" class="p-sm rounded-lg hover:bg-surface-container-low transition-all" title="Show/Hide">
                                <span class="material-symbols-outlined text-on-surface-variant">visibility_off</span>
                            </button>
                        </div>
                        <p id="gemini-key-status" class="text-on-surface-variant font-body-sm hidden mt-xs"></p>
                    </div>
                    <div class="pt-sm border-t border-surface-container-low">
                        <button id="btn-save-settings" onclick="saveSettings()"
                            class="bg-primary text-on-primary px-lg py-sm rounded-xl font-body-md font-semibold hover:opacity-90 transition-all flex items-center gap-sm">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Save All Settings
                        </button>
                        <p id="save-status" class="text-on-surface-variant font-body-sm hidden mt-xs"></p>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right Column: Connection Status -->
        <div class="lg:col-span-4 space-y-gutter">
            <section class="bg-surface-container-highest rounded-xl p-lg border border-primary-container/20">
                <div class="flex items-center gap-sm mb-lg">
                    <span class="material-symbols-outlined text-secondary">settings_phone</span>
                    <h2 class="font-title-md text-title-md text-primary">Connection Status</h2>
                </div>
                <div class="flex flex-col items-center text-center mb-xl">
                    <div class="relative mb-md">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center border-4 border-secondary/20">
                            <span class="material-symbols-outlined text-[40px] text-secondary" style="font-variation-settings: 'FILL' 1;">phone_android</span>
                        </div>
                        <div id="online-dot" class="absolute bottom-0 right-0 w-6 h-6 bg-outline border-4 border-surface-container-highest rounded-full"></div>
                    </div>
                    <p id="online-badge" class="bg-surface-container-high text-on-surface-variant font-label-caps px-md py-xs rounded-full inline-block mb-xs">OFFLINE</p>
                    <p id="bot-port-display" class="font-body-sm text-on-surface-variant">Port: --</p>
                    <p id="instance-info" class="text-on-surface-variant font-body-sm">Instance: MED-BOT-01</p>
                </div>
                <div class="space-y-sm" id="action-buttons">
                    <button onclick="startBot()"
                        class="w-full bg-primary text-white py-sm rounded-xl font-body-md font-semibold hover:opacity-90 transition-opacity flex items-center justify-center gap-sm">
                        <span class="material-symbols-outlined">play_arrow</span>
                        Start Bot
                    </button>
                    <button onclick="stopBot()"
                        class="w-full border border-error text-error py-sm rounded-xl font-body-md font-semibold hover:bg-error-container transition-colors flex items-center justify-center gap-sm hidden">
                        <span class="material-symbols-outlined">stop</span>
                        Stop Bot
                    </button>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

<script>
    let pollInterval = null;

    function log(msg, type = 'info') {
        const logEl = document.getElementById('status-log');
        const time = new Date().toLocaleTimeString();
        const colors = { info: 'text-on-surface', success: 'text-secondary', error: 'text-error', warn: 'text-tertiary' };
        logEl.innerHTML += `<div class="${colors[type] || colors.info}">[${time}] ${msg}</div>`;
        logEl.scrollTop = logEl.scrollHeight;
    }

    function updateUI(data) {
        const isRunning = data.is_running;
        const isLoggedIn = data.is_logged_in;
        const qrCode = data.qr_code;
        const port = data.port;

        // Engine badge
        const badge = document.getElementById('engine-badge');
        if (isRunning) {
            badge.className = 'bg-secondary-container text-on-secondary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
            badge.textContent = isLoggedIn ? 'Connected' : 'Running';
        } else {
            badge.className = 'bg-surface-container-high text-on-surface-variant px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
            badge.textContent = 'Offline';
        }

        // Online status
        const onlineBadge = document.getElementById('online-badge');
        const onlineDot = document.getElementById('online-dot');
        const botPortDisplay = document.getElementById('bot-port-display');

        if (isLoggedIn) {
            onlineBadge.className = 'bg-secondary/10 text-secondary font-label-caps px-md py-xs rounded-full inline-block mb-xs';
            onlineBadge.textContent = 'ONLINE';
            onlineDot.className = 'absolute bottom-0 right-0 w-6 h-6 bg-secondary border-4 border-surface-container-highest rounded-full';
        } else if (isRunning) {
            onlineBadge.className = 'bg-tertiary-container/30 text-tertiary font-label-caps px-md py-xs rounded-full inline-block mb-xs';
            onlineBadge.textContent = 'CONNECTING';
            onlineDot.className = 'absolute bottom-0 right-0 w-6 h-6 bg-tertiary border-4 border-surface-container-highest rounded-full';
        } else {
            onlineBadge.className = 'bg-surface-container-high text-on-surface-variant font-label-caps px-md py-xs rounded-full inline-block mb-xs';
            onlineBadge.textContent = 'OFFLINE';
            onlineDot.className = 'absolute bottom-0 right-0 w-6 h-6 bg-outline border-4 border-surface-container-highest rounded-full';
        }

        botPortDisplay.textContent = port ? `Port: ${port}` : 'Port: --';

        // Start/Stop buttons visibility
        document.getElementById('btn-start').classList.toggle('hidden', isRunning);
        document.getElementById('btn-stop').classList.toggle('hidden', !isRunning);
        document.getElementById('btn-restart').classList.toggle('hidden', !isRunning);
        document.querySelectorAll('#action-buttons button').forEach(b => b.classList.toggle('hidden', false));
        document.querySelector('#action-buttons button:first-child').classList.toggle('hidden', isRunning);
        document.querySelector('#action-buttons button:last-child').classList.toggle('hidden', !isRunning);
        document.getElementById('bot-port').disabled = isRunning;

        // QR Code
        const qrSection = document.getElementById('qr-section');
        const qrImage = document.getElementById('qr-image');
        const qrPlaceholder = document.getElementById('qr-placeholder');
        const qrStatus = document.getElementById('qr-status');

        if (isRunning && !isLoggedIn) {
            qrSection.classList.remove('hidden');

            if (qrCode) {
                qrImage.src = qrCode;
                qrImage.classList.remove('hidden');
                qrPlaceholder.classList.add('hidden');
                qrStatus.className = 'bg-tertiary-container text-on-tertiary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
                qrStatus.textContent = 'Scan to connect';
                log('QR Code ready - scan with WhatsApp', 'success');
            } else {
                qrImage.classList.add('hidden');
                qrPlaceholder.classList.remove('hidden');
                qrStatus.className = 'bg-surface-container-high text-on-surface-variant px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
                qrStatus.textContent = 'Generating QR...';
                log('Waiting for QR Code...', 'warn');
            }
        } else if (isLoggedIn) {
            qrSection.classList.remove('hidden');
            qrImage.classList.add('hidden');
            qrPlaceholder.classList.remove('hidden');
            qrPlaceholder.innerHTML = '<span class="material-symbols-outlined text-secondary text-[64px]">check_circle</span>';
            qrStatus.className = 'bg-secondary-container text-on-secondary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
            qrStatus.textContent = 'Connected';
            log('WhatsApp connected successfully!', 'success');
        } else {
            qrSection.classList.add('hidden');
        }
    }

    function pollStatus() {
        fetch('/bot/status')
            .then(r => r.json())
            .then(data => updateUI(data))
            .catch(() => {});
    }

    function startBot() {
        const port = document.getElementById('bot-port').value;
        if (!port || port < 1024 || port > 65535) {
            log('Invalid port number (1024-65535)', 'error');
            return;
        }

        log(`Starting bot on port ${port}...`, 'info');
        document.getElementById('btn-start').disabled = true;

        fetch('/bot/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ port: parseInt(port) })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                log(`Error: ${data.error}`, 'error');
                document.getElementById('btn-start').disabled = false;
            } else {
                log(`Bot started successfully (PID: ${data.pid})`, 'success');
                pollStatus();
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(pollStatus, 3000);
                document.getElementById('btn-start').disabled = false;
            }
        })
        .catch(err => {
            log(`Failed to start: ${err.message}`, 'error');
            document.getElementById('btn-start').disabled = false;
        });
    }

    function stopBot() {
        if (!confirm('Are you sure you want to stop the bot?')) return;
        log('Stopping bot...', 'warn');

        fetch('/bot/stop', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) log(`Error: ${data.error}`, 'error');
            else {
                log('Bot stopped', 'info');
                if (pollInterval) clearInterval(pollInterval);
                pollStatus();
            }
        })
        .catch(err => log(`Failed to stop: ${err.message}`, 'error'));
    }

    function restartBot() {
        const port = document.getElementById('bot-port').value;
        log('Restarting bot...', 'warn');

        fetch('/bot/restart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ port: parseInt(port) })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) log(`Error: ${data.error}`, 'error');
            else {
                log('Bot restarted', 'success');
                pollStatus();
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(pollStatus, 3000);
            }
        })
        .catch(err => log(`Failed to restart: ${err.message}`, 'error'));
    }

    function loadSettings() {
        fetch('/bot/settings')
            .then(r => r.json())
            .then(data => {
                if (data.admin_wa_number) {
                    document.getElementById('admin-wa').value = data.admin_wa_number;
                }
            })
            .catch(() => {});
    }

    function toggleGeminiVisibility() {
        const input = document.getElementById('gemini-key');
        const btn = event.currentTarget;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<span class="material-symbols-outlined text-on-surface-variant">visibility</span>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<span class="material-symbols-outlined text-on-surface-variant">visibility_off</span>';
        }
    }

    function saveSettings() {
        const number = document.getElementById('admin-wa').value.trim();
        const geminiKey = document.getElementById('gemini-key').value.trim();
        const btn = document.getElementById('btn-save-settings');
        const status = document.getElementById('save-status');

        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">sync</span> Saving...';

        const body = {};
        if (number) body.admin_wa_number = number;
        if (geminiKey) body.gemini_api_key = geminiKey;

        fetch('/bot/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(data => {
            status.textContent = '✓ Settings saved' + (geminiKey ? ' — API key akan dipakai bot dalam 1 menit' : '');
            status.className = 'text-secondary font-body-sm mt-xs';
            status.classList.remove('hidden');
            if (geminiKey) {
                document.getElementById('gemini-key').value = '';
                document.getElementById('gemini-key').placeholder = '•••••••• (saved)';
            }
            setTimeout(() => status.classList.add('hidden'), 5000);
        })
        .catch(err => {
            status.textContent = '✗ Failed to save';
            status.className = 'text-error font-body-sm mt-xs';
            status.classList.remove('hidden');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span> Save All Settings';
        });
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', () => {
        pollStatus();
        loadSettings();
        pollInterval = setInterval(pollStatus, 5000);
    });
</script>
