<x-app-layout>
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-gutter">
        <!-- Left: Admin Profile -->
        <div class="xl:col-span-4 space-y-gutter">
            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm">
                <div class="p-lg bg-surface-container-low border-b border-outline-variant">
                    <h2 class="font-title-md text-title-md font-bold">Profil Admin</h2>
                </div>
                <div class="p-lg space-y-lg">
                    <div class="flex items-center gap-md">
                        <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-on-primary font-bold text-xl shadow-sm shrink-0">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-on-surface truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-on-surface-variant truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="pt-lg border-t border-surface-container-low">
                        <a href="{{ route('profile.edit') }}" class="text-primary font-bold text-sm hover:underline flex items-center gap-xs">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            Edit Profil & Password
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <!-- Right: Upload -->
        <div class="xl:col-span-8 space-y-gutter">
            <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm">
                <div class="p-lg border-b border-surface-container-low">
                    <div class="flex items-center gap-md">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                            <span class="material-symbols-outlined text-xl">upload_file</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-on-surface">Upload Dokumen</h2>
                            <p class="text-xs text-on-surface-variant">Tambah pengetahuan untuk bot</p>
                        </div>
                    </div>
                </div>

                <div class="p-lg space-y-lg">
                    <div id="drop-zone" class="relative group cursor-pointer" onclick="document.getElementById('context-file').click()">
                        <div class="border-2 border-dashed border-outline-variant/50 rounded-2xl p-xl text-center hover:border-primary/50 hover:bg-primary/[0.02] transition-all">
                            <div class="w-16 h-16 rounded-2xl bg-surface-container-low flex items-center justify-center mx-auto mb-md group-hover:scale-110 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-3xl text-outline group-hover:text-primary">cloud_upload</span>
                            </div>
                            <p class="text-sm font-bold text-on-surface">Klik atau tarik file</p>
                            <p class="text-[10px] text-on-surface-variant mt-sm uppercase tracking-wider">DOCX, PDF, TXT, XLSX, JSON — Maks 50MB</p>
                            <input id="context-file" type="file" class="hidden" accept=".docx,.pdf,.txt,.xlsx,.json" onchange="document.getElementById('file-name').textContent = this.files[0]?.name || ''" />
                            <p id="file-name" class="text-xs font-bold text-primary mt-lg truncate max-w-full px-sm"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-lg">
                        <div class="space-y-sm">
                            <label class="font-bold text-xs text-on-surface uppercase tracking-wider" for="context-category">Kategori</label>
                            <input id="context-category" type="text" placeholder="Contoh: Kebijakan, Tarif"
                                class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        </div>
                        <div class="space-y-sm">
                            <label class="font-bold text-xs text-on-surface uppercase tracking-wider" for="context-tags">Tags</label>
                            <input id="context-tags" type="text" placeholder="Contoh: bpjs, rawat-inap"
                                class="w-full px-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/30" />
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center gap-md">
                        <button id="btn-upload" onclick="uploadContext()"
                            class="bg-primary text-white px-xl py-sm rounded-xl font-bold text-sm hover:bg-primary-container transition-all flex items-center gap-sm shadow-md">
                            <span class="material-symbols-outlined text-[20px]">upload</span>
                            Unggah Pengetahuan
                        </button>
                        <p id="upload-status" class="text-xs hidden"></p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Context List -->
    <div class="mt-xl">
        <section class="bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm">
            <div class="p-lg border-b border-surface-container-low">
                <div class="flex items-center justify-between gap-lg">
                    <div class="flex items-center gap-md">
                        <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                            <span class="material-symbols-outlined text-xl">menu_book</span>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-on-surface">Daftar Pengetahuan</h2>
                            <p class="text-xs text-on-surface-variant">Dokumen yang tersimpan dalam sistem</p>
                        </div>
                    </div>
                    <button onclick="loadContextList()" class="w-10 h-10 flex items-center justify-center rounded-xl border border-outline-variant/50 hover:bg-surface-container-low transition-all shrink-0">
                        <span class="material-symbols-outlined text-[20px]">refresh</span>
                    </button>
                </div>

                <!-- Search & Filter -->
                <div class="flex flex-col sm:flex-row gap-md mt-lg">
                    <div class="relative flex-1">
                        <span class="material-symbols-outlined absolute left-sm top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
                        <input type="text" id="search-context" oninput="filterContexts()" placeholder="Cari dokumen..."
                            class="w-full pl-xl pr-md py-sm bg-white border border-outline-variant/50 rounded-xl text-sm text-on-surface focus:ring-2 focus:ring-primary/30" />
                    </div>
                    <div class="flex gap-md">
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

            <div class="p-lg">
                <div id="context-grid" class="grid grid-cols-1 md:grid-cols-2 gap-lg">
                    <div class="col-span-full text-center py-xl text-on-surface-variant text-sm">Memuat...</div>
                </div>
            </div>
        </section>
    </div>

</x-app-layout>

<script>
    let contextPollTimers = {};
    let allContexts = [];

    document.addEventListener('DOMContentLoaded', () => {
        loadContextList();
    });

    function formatBytes(bytes, decimals = 1) {
        if (!bytes) return '0 B';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        try {
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        } catch (e) {
            return dateStr;
        }
    }

    function loadContextList() {
        const grid = document.getElementById('context-grid');
        if (!allContexts.length) {
            grid.innerHTML = '<div class="col-span-full text-center py-xl text-on-surface-variant text-sm">Memuat...</div>';
        }

        fetch('/api/context')
            .then(r => r.json())
            .then(data => {
                allContexts = data;
                populateCategoryFilter(data);
                filterContexts();
                data.forEach(ctx => {
                    if (ctx.status === 'processing') startContextPoll(ctx.id);
                });
            })
            .catch(() => {
                grid.innerHTML = '<div class="col-span-full text-center py-xl text-error text-sm">Gagal memuat data.</div>';
            });
    }

    function populateCategoryFilter(data) {
        const select = document.getElementById('filter-category');
        const currentVal = select.value;
        const categories = [...new Set(data.map(item => item.category).filter(Boolean))].sort();
        select.innerHTML = '<option value="">Semua Kategori</option>';
        categories.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat;
            opt.textContent = cat;
            if (cat === currentVal) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function filterContexts() {
        const query = document.getElementById('search-context').value.toLowerCase().trim();
        const category = document.getElementById('filter-category').value;
        const status = document.getElementById('filter-status').value;
        const grid = document.getElementById('context-grid');

        const filtered = allContexts.filter(ctx => {
            const matchesQuery = !query || ctx.title.toLowerCase().includes(query);
            const matchesCategory = !category || ctx.category === category;
            let matchesStatus = true;
            if (status) {
                if (status === 'active') matchesStatus = ctx.status === 'completed' && ctx.active;
                else if (status === 'inactive') matchesStatus = ctx.status === 'completed' && !ctx.active;
                else matchesStatus = ctx.status === status;
            }
            return matchesQuery && matchesCategory && matchesStatus;
        });

        if (!filtered.length) {
            grid.innerHTML = `
                <div class="col-span-full py-xl text-center flex flex-col items-center justify-center bg-surface-container-low/30 border border-dashed border-outline-variant/50 rounded-xl p-lg">
                    <span class="material-symbols-outlined text-outline text-[48px] mb-sm">folder_open</span>
                    <p class="text-sm text-on-surface-variant font-medium">Tidak ada dokumen yang ditemukan</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = '';
        filtered.forEach(ctx => {
            grid.innerHTML += cardHTML(ctx);
        });
    }

    function cardHTML(ctx) {
        let icon = 'draft';
        let bgClass = 'bg-surface-container-high text-on-surface-variant';
        const type = ctx.type.toLowerCase();
        if (type === 'pdf') { icon = 'picture_as_pdf'; bgClass = 'bg-red-500/10 text-red-600'; }
        else if (type === 'docx') { icon = 'description'; bgClass = 'bg-blue-500/10 text-blue-600'; }
        else if (type === 'xlsx') { icon = 'table_chart'; bgClass = 'bg-green-500/10 text-green-600'; }
        else if (type === 'json') { icon = 'code'; bgClass = 'bg-purple-500/10 text-purple-600'; }
        else if (type === 'txt') { icon = 'text_snippet'; bgClass = 'bg-amber-500/10 text-amber-600'; }

        const statusMap = {
            pending: ['bg-surface-container-high text-on-surface-variant', 'Menunggu'],
            processing: ['bg-tertiary-container text-tertiary', 'Diproses'],
            completed: ctx.active
                ? ['bg-secondary-container text-on-secondary-container', 'Aktif']
                : ['bg-surface-container-high text-outline border border-outline-variant/30', 'Nonaktif'],
            failed: ['bg-error-container text-on-error-container', 'Gagal'],
        };
        const [statusClass, statusLabel] = statusMap[ctx.status] || ['bg-surface-container-high text-on-surface-variant', ctx.status];

        let actions = '';
        if (ctx.status === 'completed') {
            const toggleIcon = ctx.active ? 'toggle_on' : 'toggle_off';
            const toggleColor = ctx.active ? 'text-secondary' : 'text-outline';
            actions += `
                <button onclick="toggleContext(${ctx.id})" class="p-sm rounded-lg hover:bg-surface-container-low transition-all shrink-0 border border-outline-variant/30 flex items-center justify-center bg-white shadow-sm" title="${ctx.active ? 'Nonaktifkan' : 'Aktifkan'}">
                    <span class="material-symbols-outlined text-[20px] ${toggleColor}">${toggleIcon}</span>
                </button>`;
        } else if (ctx.status === 'failed') {
            actions += `
                <button onclick="retryContext(${ctx.id})" class="p-sm rounded-lg hover:bg-surface-container-low transition-all text-tertiary shrink-0 border border-outline-variant/30 flex items-center justify-center bg-white shadow-sm" title="Coba Lagi">
                    <span class="material-symbols-outlined text-[20px]">refresh</span>
                </button>`;
        }
        actions += `
            <button onclick="deleteContext(${ctx.id}, '${escapeHtml(ctx.title)}')" class="p-sm rounded-lg hover:bg-surface-container-low transition-all text-error shrink-0 border border-outline-variant/30 flex items-center justify-center bg-white shadow-sm" title="Hapus">
                <span class="material-symbols-outlined text-[20px]">delete</span>
            </button>`;

        let progressHTML = '';
        if (ctx.status === 'processing') {
            progressHTML = `
                <div class="mt-md">
                    <div class="w-full bg-surface-container-high rounded-full h-1.5 overflow-hidden">
                        <div class="bg-secondary rounded-full h-1.5 transition-all duration-300 animate-pulse" style="width:${ctx.progress}%"></div>
                    </div>
                    <div class="flex justify-between items-center mt-xs">
                        <span class="text-[10px] text-secondary font-bold">Memproses...</span>
                        <span class="text-[10px] text-secondary font-bold">${ctx.progress}%</span>
                    </div>
                </div>`;
        }

        let errorHTML = '';
        if (ctx.status === 'failed' && ctx.error_message) {
            errorHTML = `
                <div class="mt-md p-sm bg-error-container/20 border border-error-container/30 rounded-xl text-xs text-on-error-container max-h-[80px] overflow-y-auto leading-relaxed shadow-inner">
                    <div class="font-bold flex items-center gap-xs mb-xs">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        Detail Error:
                    </div>
                    <div class="font-mono text-[10px] whitespace-pre-wrap">${escapeHtml(ctx.error_message)}</div>
                </div>`;
        }

        let categoryHTML = '';
        if (ctx.category) {
            categoryHTML = `
                <span class="bg-primary/5 text-primary border border-primary/10 px-sm py-[2px] rounded-full text-[10px] font-semibold flex items-center gap-xs">
                    <span class="material-symbols-outlined text-[12px]">folder</span>
                    ${escapeHtml(ctx.category)}
                </span>`;
        }

        let tagsHTML = '';
        if (ctx.tags) {
            const tagList = ctx.tags.split(',').map(t => t.trim()).filter(Boolean);
            tagList.forEach(tag => {
                tagsHTML += `
                    <span class="bg-surface-container text-on-surface-variant px-sm py-[2px] rounded-full text-[10px] font-medium border border-outline-variant/10">
                        #${escapeHtml(tag)}
                    </span>`;
            });
        }

        return `
            <div class="bg-surface-container-lowest border border-outline-variant/40 hover:border-primary/40 rounded-xl p-lg shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between" id="context-card-${ctx.id}">
                <div>
                    <div class="flex items-start justify-between gap-sm mb-sm">
                        <div class="flex items-center gap-md min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 ${bgClass} shadow-inner">
                                <span class="material-symbols-outlined text-[24px]">${icon}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-xs">
                                    <span class="bg-surface-container-high text-on-surface-variant px-sm py-[2px] rounded text-[9px] font-bold uppercase tracking-wider">${ctx.type}</span>
                                    <span class="text-[11px] text-on-surface-variant/70 font-semibold">${formatBytes(ctx.file_size)}</span>
                                </div>
                                <span class="text-[11px] text-on-surface-variant/50 mt-[2px] block">${formatDate(ctx.created_at)}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-xs">
                            ${actions}
                        </div>
                    </div>

                    <div class="mb-md mt-sm">
                        <h3 class="font-bold text-sm text-on-surface leading-snug line-clamp-2 hover:line-clamp-none transition-all cursor-default" title="${escapeHtml(ctx.title)}">
                            ${escapeHtml(ctx.title)}
                        </h3>
                        <div class="flex flex-wrap gap-xs mt-sm">
                            ${categoryHTML}
                            ${tagsHTML}
                        </div>
                    </div>
                </div>

                <div class="mt-xs">
                    <div class="pt-sm border-t border-outline-variant/20 flex items-center justify-between">
                        <span class="text-[11px] text-on-surface-variant/60 font-medium">Status Bot</span>
                        <span class="${statusClass} px-sm py-[2px] rounded text-[10px] font-bold uppercase tracking-wider">${statusLabel}</span>
                    </div>
                    ${progressHTML}
                    ${errorHTML}
                </div>
            </div>`;
    }

    function startContextPoll(id) {
        if (contextPollTimers[id]) clearTimeout(contextPollTimers[id]);
        const poll = () => {
            fetch(`/api/context/${id}`)
                .then(r => r.json())
                .then(data => {
                    const idx = allContexts.findIndex(c => c.id === id);
                    if (idx !== -1) allContexts[idx] = data;
                    if (data.status === 'processing') {
                        contextPollTimers[id] = setTimeout(poll, 3000);
                        filterContexts();
                    } else {
                        delete contextPollTimers[id];
                        loadContextList();
                    }
                })
                .catch(() => { contextPollTimers[id] = setTimeout(poll, 5000); });
        };
        contextPollTimers[id] = setTimeout(poll, 3000);
    }

    function uploadContext() {
        const fileInput = document.getElementById('context-file');
        const category = document.getElementById('context-category').value.trim();
        const tags = document.getElementById('context-tags').value.trim();
        const status = document.getElementById('upload-status');
        const btn = document.getElementById('btn-upload');

        if (!fileInput.files.length) { status.textContent = 'Pilih file dulu.'; status.className = 'text-tertiary text-sm font-semibold'; status.classList.remove('hidden'); return; }
        const file = fileInput.files[0];
        if (file.size > 50 * 1024 * 1024) { status.textContent = 'File maksimal 50MB.'; status.className = 'text-error text-sm font-semibold'; status.classList.remove('hidden'); return; }
        const allowed = ['docx', 'pdf', 'txt', 'xlsx', 'json'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) { status.textContent = 'Tipe: ' + allowed.join(', '); status.className = 'text-error text-sm font-semibold'; status.classList.remove('hidden'); return; }

        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">sync</span> Mengunggah...';
        status.textContent = 'Mengunggah...';
        status.className = 'text-on-surface-variant text-sm';
        status.classList.remove('hidden');

        const fd = new FormData();
        fd.append('file', file);
        if (category) fd.append('category', category);
        if (tags) fd.append('tags', tags);

        fetch('/api/context', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.error) { status.textContent = '✗ ' + data.error; status.className = 'text-error text-sm font-semibold'; }
            else {
                status.textContent = '✓ Terunggah, memproses...';
                status.className = 'text-secondary text-sm font-semibold';
                fileInput.value = '';
                document.getElementById('file-name').textContent = '';
                document.getElementById('context-category').value = '';
                document.getElementById('context-tags').value = '';
                loadContextList();
            }
        })
        .catch(err => { status.textContent = '✗ ' + err.message; status.className = 'text-error text-sm font-semibold'; })
        .finally(() => { btn.disabled = false; btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">upload</span> Unggah'; });
    }

    function toggleContext(id) { fetch(`/api/context/${id}/toggle`, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => loadContextList()); }
    function retryContext(id) { fetch(`/api/context/${id}/retry`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => loadContextList()); }
    function deleteContext(id, title) { if (!confirm(`Hapus "${title}"?`)) return; fetch(`/api/context/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } }).then(() => loadContextList()); }
    function escapeHtml(str) { if (!str) return ''; return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;'); }
</script>
