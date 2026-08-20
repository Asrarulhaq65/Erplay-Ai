@extends('layouts.enterprise')
@section('title', 'Pengaturan AI & Vision Integrasi — ' . (auth()->user()?->toko?->nama_toko ?? 'ERPlay AI'))

@section('content')
    <!-- Breadcrumb Navigation -->
    <nav class="erp-breadcrumb d-none d-md-block" aria-label="Breadcrumb">
        <a href="{{ url('/dashboard') }}" aria-label="Beranda Dashboard"><i class="bi bi-house-door"></i></a>
        <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
        <span>Pengaturan</span>
        <i class="bi bi-chevron-right mx-1" style="font-size:9px;" aria-hidden="true"></i>
        <span aria-current="page">Integrasi AI & Vision</span>
    </nav>

    <!-- Page Header & Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h1 class="h4 mb-0 fw-bold"
                style="font-family:'Plus Jakarta Sans',sans-serif;color:var(--pb-text);letter-spacing:-0.02em;">
                <i class="bi bi-robot me-2 text-primary" aria-hidden="true"></i>Pengaturan Integrasi AI & Vision Scan
            </h1>
            <p class="mb-0 mt-1" style="color:var(--text-secondary);font-size:13px;">
                Atur provider BYOK, AI Copilot Assistant, pemrosesan Vision OCR POS, dan model yang digunakan toko Anda.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pengaturan.ai.assistant') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-person-heart me-1"></i>Profil Asisten
            </a>
            <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer"
                class="btn btn-sm btn-outline-primary">
                <i class="bi bi-box-arrow-up-right me-1"></i>Ambil Gemini API Key Gratis
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Configuration Form -->
        <div class="col-12 col-lg-7">
            <div class="card card-erp shadow-sm">
                <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
                    <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                        <i class="bi bi-gear-fill me-2 text-primary"></i>Konfigurasi Integrasi AI & Vision Scan
                    </h2>
                    @if ($hasByokKey || $hasEnvKey)
                        <span
                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill"
                            style="font-size:11px;font-weight:600;">
                            <i class="bi bi-check-circle-fill me-1"></i>API Key Terpasang
                        </span>
                    @else
                        <span
                            class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill"
                            style="font-size:11px;font-weight:600;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>API Key Belum Ada
                        </span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('pengaturan.ai.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Input API Key (paling atas) -->
                        <div class="p-4 mb-4 rounded border"
                            style="background:var(--bg-input);border-color:var(--border-color);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold" style="font-size:13px;color:var(--pb-text);">Provider dan kredensial
                                    per toko</span>
                                <span class="badge bg-secondary-subtle text-secondary" style="font-size:10px;">BYOK</span>
                            </div>

                            <div class="input-group mb-2">
                                <span class="input-group-text"><i class="bi bi-key-fill text-warning"></i></span>
                                <input type="text" class="form-control text-muted" readonly
                                    value="{{ $hasByokKey ? 'API key BYOK tersimpan terenkripsi' : ($hasEnvKey ? 'Fallback GEMINI_API_KEY tersedia di server' : 'Belum ada API key') }}"
                                    style="font-family:monospace;font-size:12px;">
                            </div>

                            {{-- Badge: Provider aktif & Model yang digunakan --}}
                            @if ($hasByokKey || $hasEnvKey)
                                @php
                                    $activeProvider = $toko?->ai_provider ?? 'gemini';
                                    $activeModel    = $toko?->ai_model ?: $toko?->gemini_model ?: config('services.gemini.model', 'gemini-2.0-flash');
                                    $providerLabel  = match($activeProvider) {
                                        'gemini'            => 'Google Gemini',
                                        'openai-compatible' => 'OpenAI-compatible',
                                        default             => ucfirst($activeProvider),
                                    };
                                @endphp
                                <div class="d-flex align-items-center gap-2 flex-wrap mt-2 mb-2">
                                    <span class="badge border d-inline-flex align-items-center gap-1"
                                        style="font-size:10.5px;font-weight:500;background:var(--bg-card);border-color:var(--border-color)!important;color:var(--text-secondary);">
                                        <i class="bi bi-plug-fill text-primary" style="font-size:9px;"></i>
                                        <span class="text-muted" style="font-size:10px;">Provider:</span>
                                        <span style="color:var(--pb-text);">{{ $providerLabel }}</span>
                                    </span>
                                    <span class="badge border d-inline-flex align-items-center gap-1"
                                        style="font-size:10.5px;font-weight:500;background:var(--bg-card);border-color:var(--border-color)!important;color:var(--text-secondary);">
                                        <i class="bi bi-cpu-fill text-success" style="font-size:9px;"></i>
                                        <span class="text-muted" style="font-size:10px;">Model:</span>
                                        <span style="color:var(--pb-text);font-family:monospace;">{{ $activeModel }}</span>
                                    </span>
                                    @if ($toko?->ai_vision_enabled)
                                        <span class="badge border d-inline-flex align-items-center gap-1"
                                            style="font-size:10.5px;font-weight:500;background:var(--bg-card);border-color:var(--border-color)!important;">
                                            <i class="bi bi-eye-fill text-info" style="font-size:9px;"></i>
                                            <span style="color:var(--text-secondary);font-size:10px;">Vision aktif</span>
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <div class="text-secondary" style="font-size:11.5px;line-height:1.5;">
                                <i class="bi bi-shield-check text-success me-1"></i>
                                <strong>Aman:</strong> API key BYOK disimpan terenkripsi per toko dan tidak pernah
                                ditampilkan kembali atau dikirim ke frontend. Kosongkan field key saat menyimpan jika ingin
                                mempertahankan key lama.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="ai_api_key" class="form-label fw-bold">API Key BYOK</label>
                            <input type="password" name="ai_api_key" id="ai_api_key" class="form-control"
                                autocomplete="new-password" placeholder="Masukkan API key provider">
                            <div class="form-text">Key disimpan terenkripsi. Tidak di-log dan tidak dikirim kembali ke
                                browser.</div>
                        </div>

                        <!-- Provider AI -->
                        <div class="mb-3">
                            <label for="ai_provider" class="form-label fw-bold">Provider AI</label>
                            <select name="ai_provider" id="ai_provider" class="form-select">
                                <option value="gemini" @selected(($toko?->ai_provider ?? 'gemini') === 'gemini')>Google Gemini</option>
                                <option value="openai-compatible" @selected(($toko?->ai_provider ?? '') === 'openai-compatible')>OpenAI-compatible (OpenAI,
                                    OpenRouter, Ollama, LM Studio, proxy)</option>
                            </select>
                        </div>

                        <!-- Base URL OpenAI-compatible -->
                        <div class="mb-3" id="baseUrlGroup">
                            <label for="ai_base_url" class="form-label fw-bold">Base URL OpenAI-compatible</label>
                            <input type="url" name="ai_base_url" id="ai_base_url" class="form-control"
                                value="{{ old('ai_base_url', $toko?->ai_base_url ?? 'https://api.openai.com/v1') }}"
                                placeholder="https://api.openai.com/v1">
                            <div class="form-text">Untuk OpenAI gunakan <code>https://api.openai.com/v1</code>; untuk Ollama
                                gunakan <code>http://localhost:11434/v1</code>.</div>
                        </div>

                        <!-- Model Selection -->
                        <div class="mb-4">
                            <label for="ai_model" class="form-label fw-bold">Model</label>
                            <select name="ai_model" id="ai_model" class="form-select" required>
                                <option value="" disabled selected>Memuat model...</option>
                            </select>
                            <div class="form-text d-flex align-items-center gap-2">
                                <span>Pilih model dari daftar tersedia di provider Anda.</span>
                                <button type="button" id="btnRefreshModels"
                                    class="btn btn-sm btn-outline-secondary py-0 px-2" title="Refresh daftar model">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Test Connection Button -->
                        <div class="d-flex align-items-center justify-content-between pt-1 pb-2 border-bottom mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" id="btnTestConnection"
                                    class="btn btn-outline-info btn-sm px-4 fw-bold">
                                    <i class="bi bi-lightning-charge me-1"></i>Cek Koneksi Provider
                                </button>
                                <span class="text-muted" style="font-size:11px;">Uji provider, base URL, model, dan key
                                    yang dipilih.</span>
                            </div>
                        </div>
                        <div id="testResultBox" class="mt-3" style="display:none;"></div>

                        <!-- Feature Toggles -->
                        <div class="p-3 mb-4 rounded border" style="background:var(--bg-input);">
                            <div class="fw-bold mb-2" style="font-size:13px;color:var(--pb-text);">
                                <i class="bi bi-toggle-on me-1 text-success"></i>Aktifkan Fitur Kecerdasan AI
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="ai_enabled"
                                    name="ai_enabled" value="1" {{ $toko?->ai_enabled ?? true ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="ai_enabled" style="font-size:12.5px;">
                                    Aktifkan Floating AI Copilot Assistant (Draggable Widget)
                                </label>
                                <div class="text-muted" style="font-size:11px;">Menampilkan widget melayang ERPlay AI
                                    Copilot yang dapat digeser di seluruh halaman ERP.</div>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="ai_vision_enabled"
                                    name="ai_vision_enabled" value="1"
                                    {{ $toko?->ai_vision_enabled ?? true ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="ai_vision_enabled"
                                    style="font-size:12.5px;">
                                    Aktifkan AI Vision OCR & Camera Scan POS
                                </label>
                                <div class="text-muted" style="font-size:11px;">Mengizinkan kasir memindai gambar produk /
                                    struk menggunakan kamera & kecerdasan buatan.</div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-bold">
                                <i class="bi bi-check-lg me-1"></i>Simpan Pengaturan AI</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info & Guidance Side Column -->
        <div class="col-12 col-lg-5">
            <!-- AI Usage & Token Metrics Card -->
            <div class="card card-erp shadow-sm mb-4">
                <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
                    <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                        <i class="bi bi-cpu me-2 text-primary" aria-hidden="true"></i>Statistik Penggunaan Model & Token
                        AI
                    </h2>
                    <span
                        class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill"
                        style="font-size:10px;">
                        Real-time Metrics
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- Stat Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded border text-center" style="background:var(--bg-input);">
                                <div class="text-secondary mb-1" style="font-size:11px;font-weight:600;">
                                    <i class="bi bi-send-fill text-primary me-1"></i>Total Request
                                </div>
                                <div class="h5 mb-0 fw-bold text-primary" id="statTotalRequests">
                                    {{ number_format($toko?->ai_total_requests ?? 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded border text-center" style="background:var(--bg-input);">
                                <div class="text-secondary mb-1" style="font-size:11px;font-weight:600;">
                                    <i class="bi bi-arrow-down-left-circle-fill text-info me-1"></i>Prompt Tokens
                                </div>
                                <div class="h5 mb-0 fw-bold text-info" id="statPromptTokens">
                                    {{ number_format($toko?->ai_prompt_tokens ?? 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded border text-center" style="background:var(--bg-input);">
                                <div class="text-secondary mb-1" style="font-size:11px;font-weight:600;">
                                    <i class="bi bi-arrow-up-right-circle-fill text-success me-1"></i>Completion Tokens
                                </div>
                                <div class="h5 mb-0 fw-bold text-success" id="statCompletionTokens">
                                    {{ number_format($toko?->ai_completion_tokens ?? 0) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 rounded border text-center" style="background:var(--bg-input);">
                                <div class="text-secondary mb-1" style="font-size:11px;font-weight:600;">
                                    <i class="bi bi-hash text-warning me-1"></i>Total Tokens Used
                                </div>
                                <div class="h5 mb-0 fw-bold text-warning-emphasis" id="statTotalTokens">
                                    {{ number_format($toko?->ai_total_tokens ?? 0) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Provider information -->
                    <div class="p-3 rounded border" style="background:var(--bg-input);font-size:12px;">
                        <div class="fw-bold mb-2 text-primary d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-info-circle-fill me-1"></i>Catatan penggunaan provider AI:</span>
                            <span class="badge bg-success-subtle text-success" style="font-size:10px;">Tenant BYOK</span>
                        </div>
                        <div class="row g-2 text-secondary" style="font-size:11.5px;">
                            <div class="col-12 col-md-4">
                                <i class="bi bi-shield-check me-1 text-primary"></i><strong>BYOK:</strong> key milik toko
                            </div>
                            <div class="col-12 col-md-4">
                                <i class="bi bi-link-45deg me-1 text-success"></i><strong>Compatible:</strong> OpenAI,
                                OpenRouter, Ollama, LM Studio
                            </div>
                            <div class="col-12 col-md-4">
                                <i class="bi bi-lock me-1 text-warning"></i><strong>Privat:</strong> key terenkripsi per
                                toko
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-erp shadow-sm mb-4">
                <div class="card-header py-3 px-4">
                    <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                        <i class="bi bi-file-earmark-code me-2 text-primary" aria-hidden="true"></i>Fallback Server
                        (Opsional)
                    </h2>
                </div>
                <div class="card-body p-4" style="font-size:12.5px;color:var(--text-secondary);line-height:1.6;">
                    <p class="mb-2">
                        BYOK per toko adalah metode utama. Konfigurasi environment berikut hanya digunakan sebagai fallback
                        jika toko belum memiliki key tersimpan:
                    </p>
                    <ol class="ps-3 mb-3">
                        <li class="mb-1.5">Buka berkas <code>.env</code> di root folder project ERP Anda.</li>
                        <li class="mb-1.5">Tambahkan/perbarui baris berikut:
                            <div class="p-2 mt-1 rounded bg-dark text-white font-monospace"
                                style="font-size:11px;user-select:all;">
                                GEMINI_API_KEY=AIzaSy...kunci_anda_di_sini<br>
                                GEMINI_MODEL=gemini-1.5-flash
                            </div>
                        </li>
                        <li>Simpan file <code>.env</code> dan jalankan tombol <strong>"Tes Koneksi API Key (.env)"</strong>
                            di samping.</li>
                    </ol>
                </div>
            </div>

            <div class="card card-erp shadow-sm">
                <div class="card-header py-3 px-4">
                    <h2 class="h6 card-title mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;">
                        <i class="bi bi-shield-check me-2 text-success" aria-hidden="true"></i>Keuntungan Penyimpanan .env
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="stat-icon stat-icon-green"
                            style="width:36px;height:36px;border-radius:10px;flex-shrink:0;">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-1" style="font-size:13px;color:var(--pb-text);">Perlindungan Tingkat
                                Server</div>
                            <div class="text-secondary" style="font-size:12px;line-height:1.5;">
                                Kunci API hanya dibaca oleh environment server PHP dan tidak pernah terpapar ke tabel
                                database maupun response frontend.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon stat-icon-teal"
                            style="width:36px;height:36px;border-radius:10px;flex-shrink:0;">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-1" style="font-size:13px;color:var(--pb-text);">Performa & Cache Tinggi
                            </div>
                            <div class="text-secondary" style="font-size:12px;line-height:1.5;">
                                Konfigurasi environment dapat dicache oleh Laravel secara instan sehingga pemanggilan API AI
                                menjadi ultra-cepat.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const testBtn = document.getElementById('btnTestConnection');
            const resultBox = document.getElementById('testResultBox');
            const providerSelect = document.getElementById('ai_provider');
            const baseUrlGroup = document.getElementById('baseUrlGroup');
            const baseUrlInput = document.getElementById('ai_base_url');
            const modelSelect = document.getElementById('ai_model');
            const refreshBtn = document.getElementById('btnRefreshModels');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const updateProviderFields = () => {
                const compatible = providerSelect.value === 'openai-compatible';
                baseUrlGroup.style.display = compatible ? '' : 'none';
                baseUrlInput.required = compatible;
            };

            const loadModels = async (refresh = false) => {
                const provider = providerSelect.value;
                const apiKey = document.getElementById('ai_api_key').value;
                const baseUrl = baseUrlInput.value;
                const visionEnabled = document.querySelector('#ai_vision_enabled')?.checked ?? (@json($toko?->ai_vision_enabled ?? true));
                const currentModel = @json($toko?->ai_model ?? ($toko?->gemini_model ?? ''));

                modelSelect.innerHTML = '<option value="" disabled selected>Memuat model...</option>';

                try {
                    const res = await fetch('{{ route('pengaturan.ai.models') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            provider: provider,
                            api_key: apiKey,
                            base_url: baseUrl,
                            ai_vision_enabled: visionEnabled,
                            refresh: refresh
                        })
                    });

                    const data = await res.json();

                    modelSelect.innerHTML = '';

                    if (data.fallback && data.default_vision_model) {
                        modelSelect.innerHTML =
                            `<option value="${data.default_vision_model}" selected>${data.default_vision_model}</option>`;
                    }

                    if (data.models && data.models.length > 0) {
                        data.models.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.name;
                            opt.textContent =
                                `${m.display_name}${m.vision_capable ? ' [Vision]' : ''}`;
                            opt.selected = (m.name === currentModel) || (m.name === data
                                .selected_model);
                            modelSelect.appendChild(opt);
                        });
                    } else {
                        const opt = document.createElement('option');
                        opt.value = data.selected_model || '';
                        opt.textContent =
                            `${data.selected_model || 'No model available'} ${data.fallback ? '(default)' : ''}`;
                        opt.selected = true;
                        modelSelect.appendChild(opt);
                    }
                } catch (err) {
                    const fallbackModel = @json($toko?->ai_model ?? 'gemini-1.5-flash');
                    modelSelect.innerHTML =
                        `<option value="${fallbackModel}">Error loading models — using current model</option>`;
                }
            };

            providerSelect.addEventListener('change', updateProviderFields);
            providerSelect.addEventListener('change', () => loadModels(true));
            if (refreshBtn) refreshBtn.addEventListener('click', () => loadModels(true));
            const visionToggle = document.querySelector('#ai_vision_enabled');
            if (visionToggle) {
                visionToggle.addEventListener('change', () => loadModels(true));
            }
            updateProviderFields();
            loadModels(true);

            // Test the selected BYOK provider without persisting the submitted key.
            testBtn.addEventListener('click', async function() {
                const provider = providerSelect.value;
                const model = modelSelect.value;
                const apiKey = document.getElementById('ai_api_key').value;
                const baseUrl = baseUrlInput.value;
                const visionEnabled = document.querySelector('#ai_vision_enabled')?.checked ?? false;

                testBtn.disabled = true;
                testBtn.innerHTML = '<i class="bi bi-arrow-repeat spin me-1"></i>Menguji koneksi...';

                resultBox.style.display = 'block';
                resultBox.className = 'alert alert-info py-2 px-3';
                resultBox.innerHTML =
                    '<i class="bi bi-info-circle me-1"></i>Menghubungkan ke provider dengan kredensial BYOK...';

                try {
                    const res = await fetch('{{ route('pengaturan.ai.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            provider: provider,
                            model: model,
                            api_key: apiKey,
                            base_url: baseUrl,
                            ai_vision_enabled: visionEnabled
                        })
                    });

                    const data = await res.json();

                    if (data.success) {
                        let usageInfo = '';
                        if (data.usage) {
                            usageInfo =
                                `<br><small class="text-muted"><i class="bi bi-cpu me-1"></i>Token Uji: Prompt (${data.usage.promptTokenCount ?? 0}) + Completion (${data.usage.candidatesTokenCount ?? 0}) = <strong>Total ${data.usage.totalTokenCount ?? 0} Token</strong></small>`;
                        }
                        let modelInfo = '';
                        if (data.model_changed) {
                            modelInfo =
                                `<br><small class="text-info"><i class="bi bi-arrow-right-circle me-1"></i>Model otomatis diganti dari <code>${data.requested_model}</code> ke <code>${data.working_model}</code> untuk dukungan vision.</small>`;
                            modelSelect.value = data.working_model;
                            modelSelect.dispatchEvent(new Event('change'));
                        }
                        resultBox.className = 'alert alert-success py-2 px-3';
                        resultBox.innerHTML =
                            `<i class="bi bi-check-circle-fill me-1"></i><strong>Berhasil!</strong> ${data.message}${modelInfo}${usageInfo}`;

                        if (data.toko_usage) {
                            document.getElementById('statTotalRequests').textContent = data.toko_usage
                                .total_requests;
                            document.getElementById('statPromptTokens').textContent = data.toko_usage
                                .prompt_tokens;
                            document.getElementById('statCompletionTokens').textContent = data
                                .toko_usage.completion_tokens;
                            document.getElementById('statTotalTokens').textContent = data.toko_usage
                                .total_tokens;
                        }

                        if (data.model_changed) {
                            loadModels(true);
                        }
                    } else {
                        resultBox.className = 'alert alert-danger py-2 px-3';
                        resultBox.innerHTML =
                            `<i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Gagal:</strong> ${data.message}`;
                    }
                } catch (err) {
                    resultBox.className = 'alert alert-danger py-2 px-3';
                    resultBox.innerHTML =
                        `<i class="bi bi-exclamation-triangle-fill me-1"></i>Gagal melakukan tes koneksi: ${err.message}`;
                } finally {
                    testBtn.disabled = false;
                    testBtn.innerHTML =
                        '<i class="bi bi-lightning-charge me-1"></i>Cek Koneksi Provider';
                }
            });
        });
    </script>
@endpush
