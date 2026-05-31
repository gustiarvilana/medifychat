<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <!-- Quota Alert Banner -->
    <div id="quota-alert" class="hidden mb-lg p-lg bg-error-container border border-error rounded-xl flex items-center gap-md shadow-sm">
        <span class="material-symbols-outlined text-error text-2xl">warning</span>
        <div class="flex-1">
            <p class="font-bold text-error">AI Engine Status Issue</p>
            <p class="text-body-sm text-on-surface-variant">The AI engine is not configured or not responding. Please check your API key settings.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-lg">
        <!-- Main Content Area -->
        <div class="xl:col-span-8 space-y-lg">
            
            <!-- Bot Status Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-md">
                @php
                    $cards = [
                        ['label' => 'Bot Status', 'id' => 'status', 'icon' => 'smart_toy'],
                        ['label' => 'WhatsApp', 'id' => 'wa', 'icon' => 'chat'],
                        ['label' => 'AI Engine', 'id' => 'quota', 'icon' => 'psychology'],
                        ['label' => 'Last Activity', 'id' => 'last-activity', 'icon' => 'history']
                    ];
                @endphp
                
                @foreach($cards as $card)
                <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg transition-all duration-300 hover:shadow-md">
                    <div class="flex items-center justify-between mb-sm">
                        <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-primary">
                            <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                        </div>
                        <span id="{{ $card['id'] }}-badge" class="px-sm py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-outline-variant text-on-surface-variant">
                            Checking
                        </span>
                    </div>
                    <div>
                        <p class="text-label-caps text-on-surface-variant uppercase mb-xs">{{ $card['label'] }}</p>
                        <p id="{{ $card['id'] }}-text" class="text-title-md font-bold text-on-surface truncate">--</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- QR Code Section (Hidden by default) -->
            <div id="qr-section" class="hidden bg-surface-container-lowest rounded-xl border border-outline-variant p-lg shadow-sm">
                <div class="flex flex-col md:flex-row items-center gap-lg">
                    <div id="qr-container" class="w-64 h-64 bg-white border border-outline-variant rounded-xl flex items-center justify-center p-md">
                        <div class="animate-pulse flex flex-col items-center gap-sm">
                            <span class="material-symbols-outlined text-4xl text-outline-variant">qr_code_2</span>
                            <p class="text-body-sm text-on-surface-variant">Waiting for QR...</p>
                        </div>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="text-headline-lg font-bold text-primary mb-sm">Link WhatsApp</h3>
                        <p class="text-body-md text-on-surface-variant mb-lg">Scan this QR code with your WhatsApp to connect the bot. Make sure your phone stays online.</p>
                        <div class="flex flex-col gap-sm">
                            <div class="flex items-center gap-sm text-on-surface">
                                <span class="material-symbols-outlined text-primary">check_circle</span>
                                <span class="text-body-sm font-semibold">Open WhatsApp on your phone</span>
                            </div>
                            <div class="flex items-center gap-sm text-on-surface">
                                <span class="material-symbols-outlined text-primary">check_circle</span>
                                <span class="text-body-sm font-semibold">Tap Menu or Settings and select Linked Devices</span>
                            </div>
                            <div class="flex items-center gap-sm text-on-surface">
                                <span class="material-symbols-outlined text-primary">check_circle</span>
                                <span class="text-body-sm font-semibold">Point your phone to this screen to capture the code</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="xl:col-span-4">
            <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-lg sticky top-24 shadow-sm">
                <div class="flex items-center gap-md mb-lg">
                    <div class="w-12 h-12 rounded-xl bg-primary-container/10 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-2xl">bolt</span>
                    </div>
                    <div>
                        <h2 class="text-title-md font-bold text-on-surface">System Control</h2>
                        <p class="text-body-sm text-on-surface-variant">Immediate bot actions</p>
                    </div>
                </div>
                
                <div class="space-y-md">
                    <button onclick="sendCommand('restart')"
                        class="w-full flex items-center justify-center gap-sm bg-primary text-on-primary py-lg rounded-xl font-bold hover:bg-primary-container transition-all duration-200 shadow-sm">
                        <span class="material-symbols-outlined">refresh</span>
                        Restart Bot Engine
                    </button>
                    
                    <div class="grid grid-cols-2 gap-md">
                        <button onclick="sendCommand('start')" id="start-btn"
                            class="flex flex-col items-center justify-center gap-xs border border-outline-variant text-secondary p-md rounded-xl font-bold hover:bg-secondary-container/10 transition-all duration-200">
                            <span class="material-symbols-outlined text-2xl">play_arrow</span>
                            <span class="text-[10px] uppercase">Start Bot</span>
                        </button>
                        <button onclick="sendCommand('stop')" id="stop-btn"
                            class="flex flex-col items-center justify-center gap-xs border border-outline-variant text-error p-md rounded-xl font-bold hover:bg-error-container/10 transition-all duration-200">
                            <span class="material-symbols-outlined text-2xl">stop</span>
                            <span class="text-[10px] uppercase">Stop Bot</span>
                        </button>
                    </div>

                    <button onclick="sendCommand('logout')"
                        class="w-full flex items-center justify-center gap-sm border border-outline text-on-surface-variant py-md rounded-xl font-bold hover:bg-surface-container-low transition-all duration-200">
                        <span class="material-symbols-outlined">logout</span>
                        Logout WhatsApp
                    </button>
                </div>

                <div class="mt-xl pt-xl border-t border-outline-variant">
                    <h3 class="text-label-caps text-on-surface-variant uppercase mb-md">System Info</h3>
                    <div class="space-y-sm">
                        <div class="flex justify-between items-center text-body-sm">
                            <span class="text-on-surface-variant">Version</span>
                            <span class="font-bold text-on-surface">v1.0.0</span>
                        </div>
                        <div class="flex justify-between items-center text-body-sm">
                            <span class="text-on-surface-variant">Node Port</span>
                            <span id="system-port" class="font-bold text-on-surface">--</span>
                        </div>
                        <div class="flex justify-between items-center text-body-sm">
                            <span class="text-on-surface-variant">Process ID</span>
                            <span id="system-pid" class="font-bold text-on-surface">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
    let currentQr = null;

    function formatTime(dateStr) {
        if (!dateStr) return '--';
        const date = new Date(dateStr);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function updateStatus(data) {
        const statusBadge = document.getElementById('status-badge');
        const statusText = document.getElementById('status-text');
        const waBadge = document.getElementById('wa-badge');
        const waText = document.getElementById('wa-text');
        const quotaBadge = document.getElementById('quota-badge');
        const quotaText = document.getElementById('quota-text');
        const lastActivityBadge = document.getElementById('last-activity-badge');
        const lastActivityText = document.getElementById('last-activity-text');
        const quotaAlert = document.getElementById('quota-alert');
        const qrSection = document.getElementById('qr-section');
        const qrContainer = document.getElementById('qr-container');

        // Bot running status
        if (data.is_running) {
            statusBadge.className = 'px-md py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-secondary/10 text-secondary';
            statusBadge.textContent = 'RUNNING';
            statusText.textContent = 'Active';
            document.getElementById('start-btn').disabled = true;
            document.getElementById('start-btn').classList.add('opacity-50');
            document.getElementById('stop-btn').disabled = false;
            document.getElementById('stop-btn').classList.remove('opacity-50');
        } else {
            statusBadge.className = 'px-md py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-surface-container-high text-on-surface-variant';
            statusBadge.textContent = 'OFFLINE';
            statusText.textContent = 'Stopped';
            document.getElementById('start-btn').disabled = false;
            document.getElementById('start-btn').classList.remove('opacity-50');
            document.getElementById('stop-btn').disabled = true;
            document.getElementById('stop-btn').classList.add('opacity-50');
        }

        // WhatsApp login status
        if (data.is_logged_in) {
            waBadge.className = 'px-md py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-secondary/10 text-secondary';
            waBadge.textContent = 'ONLINE';
            waText.textContent = 'Connected';
            qrSection.classList.add('hidden');
            currentQr = null;
        } else {
            waBadge.className = 'px-md py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-tertiary/10 text-tertiary';
            waBadge.textContent = 'UNLINKED';
            waText.textContent = 'Disconnected';
            
            if (data.is_running && data.qr_code) {
                qrSection.classList.remove('hidden');
                if (currentQr !== data.qr_code) {
                    currentQr = data.qr_code;
                    qrContainer.innerHTML = '<canvas id="qrcode-canvas" class="w-full h-full"></canvas>';
                    QRCode.toCanvas(document.getElementById('qrcode-canvas'), data.qr_code, {
                        width: 256,
                        margin: 2,
                        color: {
                            dark: '#00288e',
                            light: '#ffffff'
                        }
                    });
                }
            } else {
                qrSection.classList.add('hidden');
            }
        }

        // AI Engine status
        if (data.is_ai_ready) {
            quotaBadge.className = 'px-sm py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-secondary-container text-on-secondary-container';
            quotaBadge.textContent = 'Ready';
            quotaText.textContent = 'AI Active';
            quotaAlert.classList.add('hidden');
        } else {
            quotaBadge.className = 'px-sm py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-error-container text-on-error-container';
            quotaBadge.textContent = 'Not Configured';
            quotaText.textContent = 'Check Settings';
            quotaAlert.classList.remove('hidden');
        }

        // Last activity
        lastActivityBadge.className = 'px-sm py-xs rounded-full text-[10px] font-bold uppercase tracking-wider bg-surface-container text-primary';
        lastActivityBadge.textContent = 'Pulse';
        if (data.last_activity) {
            lastActivityText.textContent = formatTime(data.last_activity);
        } else {
            lastActivityText.textContent = '--:--';
        }

        // System Info
        document.getElementById('system-port').textContent = data.port || '--';
        document.getElementById('system-pid').textContent = data.pid || '--';
    }

    function pollStatus() {
        fetch('/bot/status')
            .then(response => response.json())
            .then(data => updateStatus(data))
            .catch(() => {});
    }

    function sendCommand(command) {
        if (!confirm(`Are you sure you want to ${command} the bot?`)) return;

        let url = `/bot/${command}`;
        if (command === 'restart') url = '/bot/restart'; // Force restart process for better reliability

        fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(response => response.json())
            .then(data => {
                if (data.error) alert(data.error);
                else alert(data.message);
                pollStatus();
            })
            .catch(() => alert('Failed to send command'));
    }

    // Poll every 5 seconds
    document.addEventListener('DOMContentLoaded', () => {
        pollStatus();
        setInterval(pollStatus, 5000); // Faster polling for dashboard
    });
</script>
