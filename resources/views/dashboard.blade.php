<x-app-layout>
    <x-slot name="header">
        Beranda
    </x-slot>

    <!-- Quota Alert Banner -->
    <div id="quota-alert" class="hidden mb-6 p-6 bg-[#fee2e2] border border-[#ef4444] rounded-xl flex items-center gap-4 shadow-sm">
        <span class="material-symbols-outlined text-[#ef4444] text-2xl">warning</span>
        <div class="flex-1">
            <p class="font-bold text-[#b91c1c]">Kuota AI Habis</p>
            <p class="text-sm text-[#7f1d1d]">Mesin AI tidak dapat merespons pesan natural. Harap perbarui kunci API Anda di pengaturan atau tingkatkan paket Anda.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Main Content Area -->
        <div class="xl:col-span-8 space-y-6">
            
            <!-- Bot Status Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $cards = [
                        ['label' => 'Status Bot', 'id' => 'status', 'icon' => 'smart_toy'],
                        ['label' => 'WhatsApp', 'id' => 'wa', 'icon' => 'chat'],
                        ['label' => 'Mesin AI', 'id' => 'quota', 'icon' => 'psychology'],
                        ['label' => 'Aktivitas Terakhir', 'id' => 'last-activity', 'icon' => 'history']
                    ];
                @endphp
                
                @foreach($cards as $card)
                <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 transition-all duration-300 hover:shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-10 h-10 rounded-full bg-[#f1f5f9] flex items-center justify-center text-[#3755c3]">
                            <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                        </div>
                        <span id="{{ $card['id'] }}-badge" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#e2e8f0] text-[#475569]">
                            Memeriksa
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-[#64748b] uppercase mb-1">{{ $card['label'] }}</p>
                        <p id="{{ $card['id'] }}-text" class="text-lg font-bold text-[#0b1c30] truncate">--</p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- QR / Pairing Code Section (Hidden by default) -->
            <div id="qr-section" class="hidden bg-white rounded-xl border border-[#e2e8f0] p-6 shadow-sm">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <div id="qr-container" class="w-64 h-64 bg-white border border-[#e2e8f0] rounded-xl flex items-center justify-center p-4">
                        <div class="animate-pulse flex flex-col items-center gap-2">
                            <span class="material-symbols-outlined text-4xl text-[#cbd5e1]">qr_code_2</span>
                            <p class="text-sm text-[#64748b]">Menunggu QR...</p>
                        </div>
                    </div>
                    <div id="pairing-container" class="w-64 h-64 bg-white border border-[#e2e8f0] rounded-xl flex items-center justify-center p-4 hidden">
                        <div class="flex flex-col items-center gap-3">
                            <span class="material-symbols-outlined text-4xl text-[#3755c3]">pin</span>
                            <p class="text-sm text-[#64748b]">Kode Pairing</p>
                            <p id="pairing-code-display" class="text-3xl font-bold text-[#3755c3] tracking-[0.3em]">------</p>
                            <p class="text-xs text-[#64748b] text-center">Buka WhatsApp > Perangkat Tertaut > Tautkan Perangkat > <br/><strong>Gunakan kode</strong></p>
                        </div>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h3 class="text-xl font-bold text-[#3755c3] mb-2">Tautkan WhatsApp</h3>
                        <p id="qr-instructions" class="text-md text-[#444653] mb-6">Pindai kode QR ini dengan WhatsApp Anda untuk menghubungkan bot. Pastikan ponsel Anda tetap daring.</p>
                        <p id="pairing-instructions" class="text-md text-[#444653] mb-6 hidden">Masukkan kode pairing di WhatsApp Anda. Pastikan ponsel Anda tetap daring.</p>
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2 text-[#0b1c30]">
                                <span class="material-symbols-outlined text-[#3755c3]">check_circle</span>
                                <span class="text-sm font-semibold">Buka WhatsApp di ponsel Anda</span>
                            </div>
                            <div class="flex items-center gap-2 text-[#0b1c30]">
                                <span class="material-symbols-outlined text-[#3755c3]">check_circle</span>
                                <span class="text-sm font-semibold">Ketuk Menu atau Pengaturan dan pilih Perangkat Tertaut</span>
                            </div>
                            <div id="qr-step-3" class="flex items-center gap-2 text-[#0b1c30]">
                                <span class="material-symbols-outlined text-[#3755c3]">check_circle</span>
                                <span class="text-sm font-semibold">Arahkan ponsel Anda ke layar ini untuk memindai kode</span>
                            </div>
                            <div id="pairing-step-3" class="hidden flex items-center gap-2 text-[#0b1c30]">
                                <span class="material-symbols-outlined text-[#3755c3]">check_circle</span>
                                <span class="text-sm font-semibold">Pilih "Tautkan Perangkat" lalu masukkan kode di atas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="xl:col-span-4">
            <div class="bg-white rounded-xl border border-[#e2e8f0] p-6 sticky top-24 shadow-sm">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-xl bg-[#e5eeff] flex items-center justify-center text-[#3755c3]">
                        <span class="material-symbols-outlined text-2xl">bolt</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-[#0b1c30]">Kendali Sistem</h2>
                        <p class="text-sm text-[#64748b]">Tindakan bot instan</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <button onclick="sendCommand('restart')"
                        class="w-full flex items-center justify-center gap-2 bg-[#3755c3] text-white py-3 rounded-lg font-bold hover:bg-[#2d46a3] transition-all duration-200 shadow-sm">
                        <span class="material-symbols-outlined">refresh</span>
                        Mulai Ulang Bot
                    </button>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="sendCommand('start')" id="start-btn"
                            class="flex flex-col items-center justify-center gap-1 border border-[#e2e8f0] text-[#006c49] p-4 rounded-lg font-bold hover:bg-[#ecfdf5] transition-all duration-200">
                            <span class="material-symbols-outlined text-xl">play_arrow</span>
                            <span class="text-[10px] uppercase">Mulai</span>
                        </button>
                        <button onclick="sendCommand('stop')" id="stop-btn"
                            class="flex flex-col items-center justify-center gap-1 border border-[#e2e8f0] text-[#ba1a1a] p-4 rounded-lg font-bold hover:bg-[#fef2f2] transition-all duration-200">
                            <span class="material-symbols-outlined text-xl">stop</span>
                            <span class="text-[10px] uppercase">Berhenti</span>
                        </button>
                    </div>

                    <button onclick="sendCommand('logout')"
                        class="w-full flex items-center justify-center gap-2 border border-[#94a3b8] text-[#444653] py-3 rounded-lg font-bold hover:bg-[#f1f5f9] transition-all duration-200">
                        <span class="material-symbols-outlined">logout</span>
                        Keluar WhatsApp
                    </button>
                </div>

                <div class="mt-8 pt-6 border-t border-[#e2e8f0]">
                    <h3 class="text-xs text-[#64748b] uppercase mb-4 tracking-wider">Info Sistem</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-[#64748b]">Versi</span>
                            <span class="font-bold text-[#0b1c30]">v1.0.0</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-[#64748b]">Port Node</span>
                            <span id="system-port" class="font-bold text-[#0b1c30]">--</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-[#64748b]">ID Proses</span>
                            <span id="system-pid" class="font-bold text-[#0b1c30]">--</span>
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
        const pairingContainer = document.getElementById('pairing-container');
        const pairingDisplay = document.getElementById('pairing-code-display');
        const qrInstructions = document.getElementById('qr-instructions');
        const pairingInstructions = document.getElementById('pairing-instructions');
        const qrStep3 = document.getElementById('qr-step-3');
        const pairingStep3 = document.getElementById('pairing-step-3');

        // Bot running status
        if (data.is_running) {
            statusBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#dcfce7] text-[#166534]';
            statusBadge.textContent = 'BERJALAN';
            statusText.textContent = 'Aktif';
            document.getElementById('start-btn').disabled = true;
            document.getElementById('start-btn').classList.add('opacity-50');
            document.getElementById('stop-btn').disabled = false;
            document.getElementById('stop-btn').classList.remove('opacity-50');
        } else {
            statusBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#f1f5f9] text-[#475569]';
            statusBadge.textContent = 'OFFLINE';
            statusText.textContent = 'Berhenti';
            document.getElementById('start-btn').disabled = false;
            document.getElementById('start-btn').classList.remove('opacity-50');
            document.getElementById('stop-btn').disabled = true;
            document.getElementById('stop-btn').classList.add('opacity-50');
        }

        // WhatsApp login status
        if (data.is_logged_in) {
            waBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#dcfce7] text-[#166534]';
            waBadge.textContent = 'ONLINE';
            waText.textContent = 'Terhubung';
            qrSection.classList.add('hidden');
            currentQr = null;
        } else {
            waBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#fef3c7] text-[#92400e]';
            waBadge.textContent = 'TERPUTUS';
            waText.textContent = 'Tidak Terhubung';
            
            if (data.is_running && (data.qr_code || data.pairing_code)) {
                qrSection.classList.remove('hidden');
                if (data.login_method === 'pairing_code' && data.pairing_code) {
                    qrContainer.classList.add('hidden');
                    pairingContainer.classList.remove('hidden');
                    qrInstructions.classList.add('hidden');
                    pairingInstructions.classList.remove('hidden');
                    qrStep3.classList.add('hidden');
                    pairingStep3.classList.remove('hidden');
                    pairingDisplay.textContent = data.pairing_code;
                } else if (data.qr_code) {
                    qrContainer.classList.remove('hidden');
                    pairingContainer.classList.add('hidden');
                    qrInstructions.classList.remove('hidden');
                    pairingInstructions.classList.add('hidden');
                    qrStep3.classList.remove('hidden');
                    pairingStep3.classList.add('hidden');
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
                }
            } else {
                qrSection.classList.add('hidden');
            }
        }

        // AI Quota status
        if (data.quota_exhausted) {
            quotaBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#fee2e2] text-[#991b1b]';
            quotaBadge.textContent = 'Habis';
            quotaText.textContent = 'Tidak Tersedia';
            quotaAlert.classList.remove('hidden');
        } else {
            quotaBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#dcfce7] text-[#166534]';
            quotaBadge.textContent = 'Tersedia';
            quotaText.textContent = 'Siap';
            quotaAlert.classList.add('hidden');
        }

        // Last activity
        lastActivityBadge.className = 'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#e5eeff] text-[#3755c3]';
        lastActivityBadge.textContent = 'Detak';
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
        if (!confirm(`Apakah Anda yakin ingin ${command} bot?`)) return;

        let url = `/bot/${command}`;
        if (command === 'restart') url = '/bot/restart';

        fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(response => response.json())
            .then(data => {
                if (data.error) alert(data.error);
                else alert(data.message);
                pollStatus();
            })
            .catch(() => alert('Gagal mengirim perintah'));
    }

    document.addEventListener('DOMContentLoaded', () => {
        pollStatus();
        setInterval(pollStatus, 5000);
    });
</script>
