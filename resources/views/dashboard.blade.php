<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <!-- Quota Alert Banner -->
    <div id="quota-alert" class="hidden mb-6 p-4 bg-error-container border border-error rounded-xl flex items-center gap-3 shadow-sm">
        <span class="material-symbols-outlined text-error text-2xl">warning</span>
        <div class="flex-1">
            <p class="font-semibold text-error">Kuota Gemini API Habis</p>
            <p class="text-sm text-on-surface-variant">Bot AI tidak bisa merespons pesan natural. Buat API key baru di <a href="https://aistudio.google.com" target="_blank" class="text-error underline">aistudio.google.com</a> atau upgrade ke paid tier.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <!-- Main Content Area -->
        <div class="xl:col-span-8 space-y-6">
            
            <!-- Bot Status Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                @php
                    $cards = [
                        ['label' => 'Bot Status', 'id' => 'status', 'default' => 'Checking...'],
                        ['label' => 'WhatsApp', 'id' => 'wa', 'default' => 'Checking...'],
                        ['label' => 'AI Quota', 'id' => 'quota', 'default' => 'Checking...'],
                        ['label' => 'Last Activity', 'id' => 'last-activity', 'default' => '--']
                    ];
                @endphp
                
                @foreach($cards as $card)
                <div class="bg-white rounded-xl border border-outline-variant p-5 transition-all duration-300 hover:shadow-md">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[12px] font-bold text-on-surface-variant uppercase tracking-wider">{{ $card['label'] }}</span>
                        <span id="{{ $card['id'] }}-indicator" class="w-2.5 h-2.5 rounded-full bg-outline-variant"></span>
                    </div>
                    <p id="{{ $card['id'] }}-text" class="text-xl font-semibold text-on-surface">{{ $card['default'] }}</p>
                </div>
                @endforeach
            </div>

            <!-- Recent Commands Section -->
            <div class="bg-white rounded-xl border border-outline-variant p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">history</span>
                        Recent Commands
                    </h2>
                    <span class="text-xs text-on-surface-variant opacity-70">Auto-refreshing</span>
                </div>
                <div class="space-y-3">
                    <div class="p-3 bg-surface-container-low rounded-lg border border-outline-variant/50">
                        <p class="text-sm text-on-surface-variant italic">No recent command logs available.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="xl:col-span-4">
            <div class="bg-white rounded-xl border border-outline-variant p-6 sticky top-24 shadow-sm">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-full bg-primary-container/20 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">bolt</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-on-surface">Quick Actions</h2>
                        <p class="text-xs text-on-surface-variant">Immediate system controls</p>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <button onclick="sendCommand('restart')"
                        class="w-full bg-primary text-on-primary py-3 rounded-lg font-semibold hover:bg-primary/90 transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined">refresh</span>
                        Restart Bot Engine
                    </button>
                    <button onclick="sendCommand('logout')"
                        class="w-full border border-error text-error py-3 rounded-lg font-semibold hover:bg-error-container/10 transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined">logout</span>
                        Logout WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



<script>
    function updateStatus(data) {
        const statusIndicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        const waIndicator = document.getElementById('wa-indicator');
        const waText = document.getElementById('wa-text');
        const quotaIndicator = document.getElementById('quota-indicator');
        const quotaText = document.getElementById('quota-text');
        const lastActivity = document.getElementById('last-activity');
        const quotaAlert = document.getElementById('quota-alert');

        // Bot running status
        if (data.is_running) {
            statusIndicator.className = 'w-3 h-3 rounded-full bg-secondary';
            statusText.textContent = 'Running';
            statusText.className = 'font-display-lg text-display-lg text-secondary';
        } else {
            statusIndicator.className = 'w-3 h-3 rounded-full bg-error';
            statusText.textContent = 'Stopped';
            statusText.className = 'font-display-lg text-display-lg text-error';
        }

        // WhatsApp login status
        if (data.is_logged_in) {
            waIndicator.className = 'w-3 h-3 rounded-full bg-secondary';
            waText.textContent = 'Connected';
            waText.className = 'font-display-lg text-display-lg text-secondary';
        } else {
            waIndicator.className = 'w-3 h-3 rounded-full bg-error';
            waText.textContent = 'Disconnected';
            waText.className = 'font-display-lg text-display-lg text-error';
        }

        // AI Quota status
        if (data.quota_exhausted) {
            quotaIndicator.className = 'w-3 h-3 rounded-full bg-error';
            quotaText.textContent = 'Exhausted';
            quotaText.className = 'font-display-lg text-display-lg text-error';
            quotaAlert.classList.remove('hidden');
        } else {
            quotaIndicator.className = 'w-3 h-3 rounded-full bg-secondary';
            quotaText.textContent = 'Available';
            quotaText.className = 'font-display-lg text-display-lg text-secondary';
            quotaAlert.classList.add('hidden');
        }

        // Last activity
        if (data.last_activity) {
            lastActivity.textContent = new Date(data.last_activity).toLocaleTimeString();
        } else {
            lastActivity.textContent = '--';
        }
    }

    function pollStatus() {
        fetch('/bot/status')
            .then(response => response.json())
            .then(data => updateStatus(data))
            .catch(() => {});
    }

    function sendCommand(command) {
        if (!confirm(`Are you sure you want to ${command} the bot?`)) return;

        fetch(`/bot/${command}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(() => alert('Failed to send command'));
    }

    // Poll every 10 seconds
    document.addEventListener('DOMContentLoaded', () => {
        pollStatus();
        setInterval(pollStatus, 10000);
    });
</script>
