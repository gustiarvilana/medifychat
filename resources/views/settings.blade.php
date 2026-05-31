<x-app-layout>
    <x-slot name="header">
        WhatsApp Bot
    </x-slot>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-24 right-6 z-[100] flex flex-col gap-sm"></div>

    <!-- Bot & Connection Tab -->
    <div id="panel-bot-settings" class="tab-panel">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
            <!-- Left Column: Configuration -->
            <div class="lg:col-span-8 space-y-gutter">
                                <!-- WhatsApp Connection Section -->
                <section id="qr-section" class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg shadow-sm hidden">
                    <div class="flex items-center justify-between border-b border-surface-container-low pb-md mb-lg">
                        <div class="flex items-center gap-sm">
                            <span class="material-symbols-outlined text-primary">sync_saved_locally</span>
                            <h2 class="font-title-md text-title-md">WhatsApp Connection</h2>
                        </div>
                        <span id="qr-status" class="bg-tertiary-container text-on-tertiary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider">Awaiting Scan</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-xl">
                        <div class="flex flex-col items-center gap-md">
                            <div id="qr-container" class="p-md bg-white rounded-xl border border-outline-variant shadow-inner">
                                <div id="qr-placeholder" class="w-48 h-48 bg-surface-container-low flex items-center justify-center rounded-xl">
                                    <span class="material-symbols-outlined text-outline text-[64px]">hourglass_empty</span>
                                </div>
                                <img id="qr-image" src="" alt="WhatsApp QR Code" class="w-48 h-48 hidden" />
                            </div>
                            <p class="font-body-sm font-semibold text-primary">Scan to connect</p>
                        </div>
                        <div class="space-y-md">
                            <h3 class="font-body-md font-bold text-on-surface">Instructions</h3>
                            <ul class="space-y-sm">
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
                <!-- Bot Configuration Section -->
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg shadow-sm">
                    <div class="flex items-center justify-between border-b border-surface-container-low pb-md mb-lg">
                        <div class="flex items-center gap-sm">
                            <span class="material-symbols-outlined text-primary">tune</span>
                            <h2 class="font-title-md text-title-md">Bot Configuration</h2>
                        </div>
                        <span id="engine-badge" class="bg-surface-container-high text-on-surface-variant px-sm py-xs rounded-full text-label-caps uppercase tracking-wider">Checking...</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                        <div class="space-y-sm p-lg rounded-xl bg-surface-container-low/30 border border-outline-variant/20">
                            <label class="font-bold text-sm text-on-surface flex items-center gap-sm" for="rs-name">
                                <span class="material-symbols-outlined text-[18px] text-primary">local_hospital</span>
                                Nama Rumah Sakit
                            </label>
                            <p class="text-xs text-on-surface-variant">Nama RS yang tampil di setiap jawaban bot.</p>
                            <input id="rs-name" type="text" placeholder="RS Bhayangkara Setukpa Sukabumi"
                                class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface placeholder:text-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all mt-sm" />
                        </div>

                        <div class="space-y-sm p-lg rounded-xl bg-surface-container-low/30 border border-outline-variant/20">
                            <label class="font-bold text-sm text-on-surface flex items-center gap-sm" for="admin-wa">
                                <span class="material-symbols-outlined text-[18px] text-primary">admin_panel_settings</span>
                                Admin WhatsApp
                            </label>
                            <p class="text-xs text-on-surface-variant">Menerima notifikasi bot (quota habis, error server).</p>
                            <input id="admin-wa" type="text" placeholder="6281234567890"
                                class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface placeholder:text-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all mt-sm" />
                        </div>

                        <div class="space-y-sm p-lg rounded-xl bg-surface-container-low/30 border border-outline-variant/20">
                            <label class="font-bold text-sm text-on-surface flex items-center gap-sm" for="gemini-key">
                                <span class="material-symbols-outlined text-[18px] text-secondary">psychology</span>
                                Gemini API Key
                            </label>
                            <p class="text-xs text-on-surface-variant">Isi jika ingin ganti tanpa restart server.</p>
                            <div class="flex items-center gap-sm mt-sm w-full">
                                <input id="gemini-key" type="password" placeholder="AIzaSy..."
                                    class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface placeholder:text-outline-variant/60 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                                <button onclick="toggleGeminiVisibility()" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/50 hover:bg-surface-container-low transition-all shrink-0">
                                    <span class="material-symbols-outlined text-[20px] text-on-surface-variant">visibility_off</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Medify API Section -->
                    <div class="mt-lg p-lg rounded-xl bg-surface-container-low/20 border border-outline-variant/20">
                        <div class="flex items-center gap-md mb-lg">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-xl">api</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-on-surface">Medify API</h3>
                                <p class="text-xs text-on-surface-variant">Kredensial koneksi SIMRS Medify</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                            <div class="md:col-span-2 space-y-sm">
                                <label class="font-semibold text-xs text-on-surface" for="medify-api-url">API URL</label>
                                <input id="medify-api-url" type="text" placeholder="https://simrs.medify.id/api"
                                    class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                            </div>
                            <div class="space-y-sm">
                                <label class="font-semibold text-xs text-on-surface" for="medify-api-email">Email</label>
                                <input id="medify-api-email" type="text" placeholder="admin@hospital.com"
                                    class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                            </div>
                            <div class="space-y-sm">
                                <label class="font-semibold text-xs text-on-surface" for="medify-api-password">Password</label>
                                <div class="flex items-center gap-sm">
                                    <input id="medify-api-password" type="password" placeholder="••••••••"
                                        class="flex-1 px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all" />
                                    <button onclick="toggleMedifyPasswordVisibility()" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/50 hover:bg-surface-container-low transition-all shrink-0">
                                        <span class="material-symbols-outlined text-[20px] text-on-surface-variant">visibility_off</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-xl flex justify-end gap-md">
                        <button id="btn-save-settings" onclick="saveSettings()"
                            class="bg-primary text-white px-xl py-md rounded-xl font-bold text-sm hover:bg-primary-container transition-all flex items-center gap-sm shadow-md">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            Save Configuration
                        </button>
                    </div>
                </section>
            </div>

            <!-- Right Column: Status & Control -->
            <div class="lg:col-span-4 space-y-gutter">
                <!-- Connection Status Card -->
                <section class="bg-surface-container-highest rounded-xl p-lg border border-primary-container/20 shadow-sm">
                    <div class="flex items-center gap-sm mb-lg">
                        <span class="material-symbols-outlined text-secondary">settings_phone</span>
                        <h2 class="font-title-md text-title-md text-primary">Connection Status</h2>
                    </div>
                    <div class="flex flex-col items-center text-center mb-xl">
                        <div class="relative mb-md">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center border-4 border-secondary/20 shadow-inner">
                                <span class="material-symbols-outlined text-[40px] text-secondary" style="font-variation-settings: 'FILL' 1;">phone_android</span>
                            </div>
                            <div id="status-dot" class="absolute bottom-0 right-0 w-6 h-6 bg-outline-variant border-4 border-surface-container-highest rounded-full transition-all"></div>
                        </div>
                        <p id="wa-status-badge" class="bg-surface-container-high text-on-surface-variant font-label-caps px-md py-xs rounded-full inline-block mb-xs transition-all">OFFLINE</p>
                        <h3 id="wa-status-text" class="font-headline-lg text-on-surface">Not Connected</h3>
                        <p id="system-instance" class="text-on-surface-variant font-body-sm">Instance: MED-BOT-01</p>
                    </div>
                    <div class="space-y-sm">
                        <div class="flex items-center gap-sm bg-white/50 backdrop-blur-sm rounded-xl px-md py-sm border border-outline-variant/30 mb-md">
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">lan</span>
                            <input id="bot-port" type="number" min="1024" max="65535" value="3001"
                                class="w-full bg-transparent border-none text-on-surface font-bold text-center focus:outline-none focus:ring-0 p-0" />
                            <span class="text-on-surface-variant text-xs">Port</span>
                        </div>

                        <button id="btn-start" onclick="startBot()"
                            class="w-full bg-primary text-white py-sm rounded-xl font-body-md font-semibold hover:bg-primary-container transition-all shadow-md flex items-center justify-center gap-sm">
                            <span class="material-symbols-outlined">play_arrow</span>
                            Start Bot Engine
                        </button>
                        <button id="btn-stop" onclick="stopBot()"
                            class="w-full bg-error text-white py-sm rounded-xl font-body-md font-semibold hover:bg-error-container transition-all shadow-md flex items-center justify-center gap-sm hidden">
                            <span class="material-symbols-outlined">stop</span>
                            Stop Bot Engine
                        </button>
                        <button id="btn-restart" onclick="restartBot()"
                            class="w-full border border-primary text-primary py-sm rounded-xl font-body-md font-semibold hover:bg-primary/5 transition-all flex items-center justify-center gap-sm hidden">
                            <span class="material-symbols-outlined">refresh</span>
                            Restart Bot
                        </button>
                    </div>
                </section>

                <!-- Activity Log -->
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg shadow-sm">
                    <div class="flex items-center justify-between mb-md">
                        <div class="flex items-center gap-sm">
                            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                            <span class="font-bold text-xs text-on-surface">Activity Log</span>
                        </div>
                        <div class="flex items-center gap-sm">
                            <button onclick="toggleLogExpand()" class="text-[10px] text-on-surface-variant font-semibold uppercase tracking-wider hover:text-on-surface transition-colors" id="log-expand-btn">Expand</button>
                            <span class="text-[10px] text-on-surface-variant font-semibold uppercase tracking-wider">Live</span>
                        </div>
                    </div>
                    <div id="status-log" class="bg-[#0d1117] rounded-xl p-md font-mono text-[11px] text-[#e6edf3] min-h-[200px] max-h-[300px] overflow-y-auto leading-relaxed border border-outline-variant/20 shadow-inner">
                        <span class="text-[#8b949e]">// Bot status checking...</span>
                    </div>
                </section>
            </div>
        </div>
    </div><!-- /panel-bot-settings -->

    <!-- Knowledge Context Tab -->
    <div id="panel-context" class="tab-panel hidden">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-gutter">
            <!-- Left Column: Upload Section -->
            <div class="xl:col-span-4 space-y-gutter">
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg shadow-sm">
                    <div class="flex items-center gap-md mb-lg">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined text-xl">upload_file</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-on-surface">Upload Dokumen</h2>
                            <p class="text-xs text-on-surface-variant">Tambah pengetahuan untuk bot</p>
                        </div>
                    </div>

                    <div id="drop-zone" class="relative group cursor-pointer" onclick="document.getElementById('context-file').click()">
                        <div class="border-2 border-dashed border-outline-variant/50 rounded-2xl p-xl text-center hover:border-primary/50 hover:bg-primary/[0.02] transition-all">
                            <div class="w-16 h-16 rounded-2xl bg-surface-container-low flex items-center justify-center mx-auto mb-md group-hover:scale-110 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-3xl text-outline group-hover:text-primary">cloud_upload</span>
                            </div>
                            <p class="text-sm font-bold text-on-surface">Klik atau tarik file</p>
                            <p class="text-[10px] text-on-surface-variant mt-sm uppercase tracking-wider">TXT — Max 50MB</p>
                            <input id="context-file" type="file" class="hidden" accept=".txt" onchange="document.getElementById('file-name').textContent = this.files[0]?.name || ''" />
                            <p id="file-name" class="text-xs font-bold text-primary mt-lg truncate"></p>
                        </div>
                    </div>

                    <div class="space-y-lg mt-lg">
                        <div class="space-y-xs">
                            <label class="font-bold text-xs text-on-surface uppercase tracking-wider" for="context-category">Kategori</label>
                            <input id="context-category" type="text" placeholder="Contoh: Kebijakan, Tarif"
                                class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        </div>
                        <div class="space-y-xs">
                            <label class="font-bold text-xs text-on-surface uppercase tracking-wider" for="context-tags">Tags</label>
                            <input id="context-tags" type="text" placeholder="Contoh: bpjs, rawat-inap"
                                class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        </div>
                        <button id="btn-upload" onclick="uploadContext()"
                            class="w-full bg-primary text-white py-sm rounded-xl font-bold text-sm hover:bg-primary-container transition-all flex items-center justify-center gap-sm shadow-md">
                            <span class="material-symbols-outlined text-[20px]">upload</span>
                            Unggah Pengetahuan
                        </button>
                        <p id="upload-status" class="text-xs text-center hidden"></p>
                    </div>
                </section>
            </div>

            <!-- Right Column: Context List -->
            <div class="xl:col-span-8 space-y-gutter">
                <section class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg shadow-sm">
                    <div class="flex flex-col gap-md mb-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-md">
                                <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary">
                                    <span class="material-symbols-outlined text-xl">menu_book</span>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-on-surface">Daftar Pengetahuan</h2>
                                    <p class="text-xs text-on-surface-variant">Dokumen yang tersimpan dalam sistem</p>
                                </div>
                            </div>
                            <button onclick="loadContextList()" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/50 hover:bg-surface-container-low transition-all">
                                <span class="material-symbols-outlined text-[20px]">refresh</span>
                            </button>
                        </div>

                        <!-- Search & Filter -->
                        <div class="flex flex-col sm:flex-row gap-sm">
                            <div class="relative flex-1">
                                <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                                <input type="text" id="search-context" oninput="filterContexts()" placeholder="Cari dokumen..."
                                    class="w-full pl-xl pr-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:ring-2 focus:ring-primary/30" />
                            </div>
                            <div class="flex gap-sm">
                                <select id="filter-category" onchange="filterContexts()"
                                    class="px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:ring-2 focus:ring-primary/30">
                                    <option value="">Semua Kategori</option>
                                </select>
                                <select id="filter-status" onchange="filterContexts()"
                                    class="px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:ring-2 focus:ring-primary/30">
                                    <option value="">Semua Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                    <option value="processing">Proses</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="context-grid" class="grid grid-cols-1 md:grid-cols-2 gap-md">
                        <!-- Cards injected by JS -->
                        <div class="col-span-full text-center py-xl text-on-surface-variant text-sm italic">Memuat...</div>
                    </div>
                </section>
            </div>
        </div>
    </div>

<script>
    let pollInterval = null;
    let logPollInterval = null;
    let lastLogSignature = '';

    function log(msg, type = 'info') {
        const logEl = document.getElementById('status-log');
        const time = new Date().toLocaleTimeString();
        const colors = { info: 'text-[#e6edf3]', success: 'text-[#3fb950]', error: 'text-[#f85149]', warn: 'text-[#d29922]' };
        logEl.innerHTML += `<div class="${colors[type] || colors.info}"><span class="text-[#8b949e]">[${time}]</span> ${msg}</div>`;
        logEl.scrollTop = logEl.scrollHeight;
    }

    function pollServerLogs() {
        fetch('/bot/logs/content?lines=200')
            .then(r => r.json())
            .then(data => {
                if (!data || !data.length) return;
                const sig = data[data.length - 1].file + '|' + data[data.length - 1].text;
                if (sig === lastLogSignature) return;
                const logEl = document.getElementById('status-log');
                const isAtBottom = logEl.scrollTop + logEl.clientHeight >= logEl.scrollHeight - 20;
                logEl.innerHTML = '';
                for (const entry of data) {
                    const time = new Date().toLocaleTimeString();
                    const type = entry.file === 'bot-err.log' ? 'error' : 'info';
                    const colors = { info: 'text-[#e6edf3]', error: 'text-[#f85149]' };
                    logEl.innerHTML += `<div class="${colors[type]}"><span class="text-[#8b949e]">[${time}]</span> <span class="text-[#6e7681]">[${entry.file}]</span> ${escapeHtml(entry.text)}</div>`;
                }
                if (isAtBottom) logEl.scrollTop = logEl.scrollHeight;
                lastLogSignature = sig;
            })
            .catch(() => {});
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function toggleLogExpand() {
        const el = document.getElementById('status-log');
        const btn = document.getElementById('log-expand-btn');
        const isExpanded = el.style.maxHeight === '600px';
        el.style.maxHeight = isExpanded ? '300px' : '600px';
        btn.textContent = isExpanded ? 'Expand' : 'Collapse';
        el.scrollTop = el.scrollHeight;
    }

    function updateUI(data) {
        const isRunning = data.is_running;
        const isLoggedIn = data.is_logged_in;
        const qrCode = data.qr_code;
        const port = data.port;

        // Engine badge (config section)
        const badge = document.getElementById('engine-badge');
        if (isRunning) {
            badge.className = 'bg-secondary-container text-on-secondary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
            badge.textContent = isLoggedIn ? 'Connected' : 'Running';
        } else {
            badge.className = 'bg-surface-container-high text-on-surface-variant px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
            badge.textContent = 'Offline';
        }

        // WhatsApp Status Card
        const statusDot = document.getElementById('status-dot');
        const waBadge = document.getElementById('wa-status-badge');
        const waText = document.getElementById('wa-status-text');

        if (isLoggedIn) {
            statusDot.className = 'absolute bottom-0 right-0 w-6 h-6 bg-secondary border-4 border-surface-container-highest rounded-full transition-all';
            waBadge.className = 'bg-secondary/10 text-secondary font-label-caps px-md py-xs rounded-full inline-block mb-xs transition-all';
            waBadge.textContent = 'ONLINE';
            waText.textContent = 'Connected';
            waText.className = 'font-headline-lg text-secondary';
        } else if (isRunning) {
            statusDot.className = 'absolute bottom-0 right-0 w-6 h-6 bg-tertiary border-4 border-surface-container-highest rounded-full transition-all';
            waBadge.className = 'bg-tertiary/10 text-tertiary font-label-caps px-md py-xs rounded-full inline-block mb-xs transition-all';
            waBadge.textContent = 'LINKING';
            waText.textContent = 'Awaiting Scan';
            waText.className = 'font-headline-lg text-tertiary';
        } else {
            statusDot.className = 'absolute bottom-0 right-0 w-6 h-6 bg-outline-variant border-4 border-surface-container-highest rounded-full transition-all';
            waBadge.className = 'bg-surface-container-high text-on-surface-variant font-label-caps px-md py-xs rounded-full inline-block mb-xs transition-all';
            waBadge.textContent = 'OFFLINE';
            waText.textContent = 'Not Connected';
            waText.className = 'font-headline-lg text-on-surface';
        }

        // Buttons toggle
        document.getElementById('btn-start').classList.toggle('hidden', isRunning);
        document.getElementById('btn-stop').classList.toggle('hidden', !isRunning);
        document.getElementById('btn-restart').classList.toggle('hidden', !isRunning);
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
                qrStatus.className = 'bg-secondary-container text-on-secondary-container px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
                qrStatus.textContent = 'Scan Now';
                log('QR Code ready — scan with WhatsApp', 'success');
            } else {
                qrImage.classList.add('hidden');
                qrPlaceholder.classList.remove('hidden');
                qrStatus.className = 'bg-surface-container-high text-on-surface-variant px-sm py-xs rounded-full text-label-caps uppercase tracking-wider';
                qrStatus.textContent = 'Generating...';
                log('Waiting for QR Code...', 'warn');
            }
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
        log(`Starting on port ${port}...`, 'info');
        document.getElementById('btn-start').disabled = true;
        fetch('/bot/start', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ port: parseInt(port) })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) { log(`Error: ${data.error}`, 'error'); document.getElementById('btn-start').disabled = false; }
            else {
                log(`Started (PID: ${data.pid})`, 'success');
                pollStatus();
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(pollStatus, 3000);
                document.getElementById('btn-start').disabled = false;
            }
        })
        .catch(err => { log(`Failed: ${err.message}`, 'error'); document.getElementById('btn-start').disabled = false; });
    }

    function stopBot() {
        if (!confirm('Stop the bot?')) return;
        log('Stopping...', 'warn');
        fetch('/bot/stop', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
        .then(r => r.json())
        .then(data => {
            if (data.error) log(`Error: ${data.error}`, 'error');
            else { log('Stopped', 'info'); if (pollInterval) clearInterval(pollInterval); pollStatus(); }
        })
        .catch(err => log(`Failed: ${err.message}`, 'error'));
    }

    function restartBot() {
        const port = document.getElementById('bot-port').value;
        log('Restarting...', 'warn');
        fetch('/bot/restart', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ port: parseInt(port) })
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) log(`Error: ${data.error}`, 'error');
            else { log('Restarted', 'success'); pollStatus(); if (pollInterval) clearInterval(pollInterval); pollInterval = setInterval(pollStatus, 3000); }
        })
        .catch(err => log(`Failed: ${err.message}`, 'error'));
    }

    function loadSettings() {
        fetch('/bot/settings')
            .then(r => r.json())
            .then(data => {
                if (data.rs_name) document.getElementById('rs-name').value = data.rs_name;
                if (data.admin_wa_number) document.getElementById('admin-wa').value = data.admin_wa_number;
                if (data.medify_api_url) document.getElementById('medify-api-url').value = data.medify_api_url;
                if (data.medify_api_email) document.getElementById('medify-api-email').value = data.medify_api_email;

                // Handle masked secrets
                if (data.gemini_api_key) document.getElementById('gemini-key').placeholder = '•••••••• (tersimpan)';
                if (data.medify_api_password) document.getElementById('medify-api-password').placeholder = '•••••••• (tersimpan)';
            })
            .catch(() => {});
    }

    function toggleGeminiVisibility() {
        const input = document.getElementById('gemini-key');
        const btn = event.currentTarget;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] text-on-surface-variant">visibility</span>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] text-on-surface-variant">visibility_off</span>';
        }
    }

    function toggleMedifyPasswordVisibility() {
        const input = document.getElementById('medify-api-password');
        const btn = event.currentTarget;
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] text-on-surface-variant">visibility</span>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px] text-on-surface-variant">visibility_off</span>';
        }
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `flex items-center gap-sm px-lg py-md rounded-xl shadow-lg border ${type === 'success' ? 'bg-secondary-container text-on-secondary-container border-secondary' : 'bg-error-container text-on-error-container border-error'}`;
        toast.innerHTML = `
            <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : 'error'}</span>
            <span class="font-bold text-sm">${message}</span>
        `;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    function saveSettings() {
        const rsName = document.getElementById('rs-name').value.trim();
        const number = document.getElementById('admin-wa').value.trim();
        const geminiKey = document.getElementById('gemini-key').value.trim();
        const medifyUrl = document.getElementById('medify-api-url').value.trim();
        const medifyEmail = document.getElementById('medify-api-email').value.trim();
        const medifyPassword = document.getElementById('medify-api-password').value.trim();
        const btn = document.getElementById('btn-save-settings');

        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-[20px]">sync</span> Menyimpan...';
        
        const body = {};
        if (rsName) body.rs_name = rsName;
        if (number) body.admin_wa_number = number;
        if (geminiKey) body.gemini_api_key = geminiKey;
        if (medifyUrl) body.medify_api_url = medifyUrl;
        if (medifyEmail) body.medify_api_email = medifyEmail;
        if (medifyPassword) body.medify_api_password = medifyPassword;

        fetch('/bot/settings', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(body)
        })
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                showToast(data.error, 'error');
            } else {
                showToast('Pengaturan berhasil disimpan!');
                if (geminiKey) { document.getElementById('gemini-key').value = ''; document.getElementById('gemini-key').placeholder = '•••••••• (tersimpan)'; }
                if (medifyPassword) { document.getElementById('medify-api-password').value = ''; document.getElementById('medify-api-password').placeholder = '•••••••• (tersimpan)'; }
            }
        })
        .catch(err => {
            showToast('Gagal menyimpan pengaturan.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[20px]">save</span> Save Configuration';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        pollStatus();
        loadSettings();
        pollInterval = setInterval(pollStatus, 5000);
        pollServerLogs();
        logPollInterval = setInterval(pollServerLogs, 5000);
    });
</script>
</x-app-layout>
