{{-- ══════════════════════════════════════════════════════════════════════════
     POS VOICE TRANSACTION MODAL & ASSISTANT (WEB SPEECH + MANUAL TEXT FALLBACK)
     ──────────────────────────────────────────────────────────────────────────
     Features:
     - Real-time speech recognition (id-ID) with microphone permission handling
     - Manual text input fallback for typed/pasted commands
     - Pulsing audio visualizer wave animation
     - AI natural language parsing via VoiceTransactionController
     - Speech Synthesis Text-to-Speech feedback (id-ID)
     - Automatic injection into POS cart & transaction workflow
     ══════════════════════════════════════════════════════════════════════════ --}}

<style>
    /* ── Voice Modal Audio Wave Visualizer ── */
    .voice-pulse-ring {
        width: 86px;
        height: 86px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-accent) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 34px;
        margin: 0 auto;
        position: relative;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        border: none;
        box-shadow: 0 8px 25px rgba(13, 78, 86, 0.3);
    }
    .voice-pulse-ring:hover {
        transform: scale(1.06);
    }
    .voice-pulse-ring.recording {
        background: linear-gradient(135deg, #EF4444 0%, #F59E0B 100%);
        animation: voicePulse 1.5s infinite;
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5);
    }
    @keyframes voicePulse {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
        70% { box-shadow: 0 0 0 24px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    .voice-bars {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        height: 24px;
        margin-top: 10px;
    }
    .voice-bar {
        width: 4px;
        height: 8px;
        background: var(--pb-accent);
        border-radius: 2px;
        transition: height 0.15s ease;
    }
    .voice-pulse-ring.recording + .voice-bars .voice-bar {
        animation: voiceWave 1s infinite ease-in-out alternate;
    }
    .voice-bars .voice-bar:nth-child(1) { animation-delay: 0.1s; }
    .voice-bars .voice-bar:nth-child(2) { animation-delay: 0.3s; }
    .voice-bars .voice-bar:nth-child(3) { animation-delay: 0.2s; }
    .voice-bars .voice-bar:nth-child(4) { animation-delay: 0.4s; }
    .voice-bars .voice-bar:nth-child(5) { animation-delay: 0.15s; }

    @keyframes voiceWave {
        0% { height: 6px; }
        100% { height: 26px; }
    }

    .voice-transcript-box {
        background: var(--bg-input);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 12px 14px;
        min-height: 60px;
        font-size: 13px;
        color: var(--text-primary);
        word-break: break-word;
    }
</style>

<!-- Voice Input Modal -->
<div class="modal fade" id="voiceTransactionModal" tabindex="-1" aria-labelledby="voiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-erp shadow-lg border-0" style="border-radius:20px;overflow:hidden;">
            <div class="modal-header py-3 px-4" style="background:linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-darker) 100%);color:#FFF;">
                <h5 class="modal-title h6 fw-bold mb-0 d-flex align-items-center gap-2" id="voiceModalLabel">
                    <i class="bi bi-mic-fill text-warning fs-5"></i>Input Transaksi Suara & Teks AI
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-4 text-center">
                <!-- Mic Pulsing Button -->
                <div class="mb-3">
                    <button type="button" id="voiceMicBtn" class="voice-pulse-ring" title="Klik untuk mulai merekam suara">
                        <i class="bi bi-mic-fill" id="voiceMicIcon"></i>
                    </button>
                    <div class="voice-bars">
                        <div class="voice-bar"></div>
                        <div class="voice-bar"></div>
                        <div class="voice-bar"></div>
                        <div class="voice-bar"></div>
                        <div class="voice-bar"></div>
                    </div>
                </div>

                <div class="fw-bold mb-1" id="voiceStatusTitle" style="font-size:14px;color:var(--pb-text);">
                    Klik Mikrofon & Bicara Perintah Transaksi
                </div>
                <div class="text-secondary mb-3" style="font-size:12px;">
                    Contoh: <em>"Tambah 2 Kopi Susu dan 3 Roti Cokelat, diskon 5000"</em>
                </div>

                <!-- Live Transcript Area -->
                <div class="voice-transcript-box text-start mb-3" id="voiceTranscriptText">
                    <span class="text-muted italic"><i class="bi bi-chat-quote me-1"></i>Suara Anda akan muncul di sini...</span>
                </div>

                <!-- Permission / Error Troubleshooting Alert Box -->
                <div id="voicePermissionHelp" class="alert alert-warning py-2 px-3 text-start mb-3" style="display:none;font-size:11.5px;">
                    <i class="bi bi-shield-lock-fill me-1"></i>
                    <strong>Akses Mikrofon Diblokir Browser.</strong><br>
                    Silakan klik ikon gembok/mikrofon di sebelah kiri address bar URL browser Anda (<code>http://...</code>), lalu pilih <strong>"Allow / Izinkan Microphone"</strong> dan muat ulang halaman. Atau gunakan kolom teks di bawah ini.
                </div>

                <!-- Manual Text Input Fallback -->
                <div class="input-group mb-3">
                    <input type="text" id="voiceManualInput" class="form-control form-control-sm" placeholder="Atau ketik perintah di sini (contoh: Tambah 2 Kopi Susu)..." autocomplete="off">
                    <button class="btn btn-sm btn-primary fw-bold" type="button" id="voiceSubmitManualBtn">
                        <i class="bi bi-stars me-1"></i>Proses AI
                    </button>
                </div>

                <!-- AI Processing Result Card -->
                <div id="voiceAiResultCard" class="text-start p-3 rounded border" style="display:none;background:var(--bg-card);border-color:var(--border-medium)!important;">
                    <div class="fw-bold mb-2 text-primary d-flex align-items-center justify-content-between" style="font-size:13px;">
                        <span><i class="bi bi-stars text-warning me-1"></i>Hasil Analisis AI:</span>
                        <span id="voiceIntentBadge" class="badge bg-primary-subtle text-primary" style="font-size:10px;">Input Keranjang</span>
                    </div>
                    <div id="voiceParsedItems" class="mb-2" style="font-size:12.5px;"></div>
                    <div id="voiceParsedNotes" class="text-muted" style="font-size:11px;"></div>
                </div>
            </div>

            <div class="modal-footer py-2 px-4 bg-light-subtle d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="voiceApplyBtn" class="btn btn-sm btn-primary fw-bold px-4" style="display:none;">
                    <i class="bi bi-cart-plus me-1"></i>Terapkan ke Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('voiceTransactionModal');
    const micBtn = document.getElementById('voiceMicBtn');
    const micIcon = document.getElementById('voiceMicIcon');
    const statusTitle = document.getElementById('voiceStatusTitle');
    const transcriptText = document.getElementById('voiceTranscriptText');
    const permissionHelp = document.getElementById('voicePermissionHelp');
    const manualInput = document.getElementById('voiceManualInput');
    const submitManualBtn = document.getElementById('voiceSubmitManualBtn');
    const aiResultCard = document.getElementById('voiceAiResultCard');
    const parsedItemsDiv = document.getElementById('voiceParsedItems');
    const parsedNotesDiv = document.getElementById('voiceParsedNotes');
    const intentBadge = document.getElementById('voiceIntentBadge');
    const applyBtn = document.getElementById('voiceApplyBtn');

    let recognition = null;
    let isRecording = false;
    let currentParsedData = null;

    // Fallback Manual Trigger Handler for POS Standar / Custom
    document.addEventListener('click', function(e) {
        const triggerBtn = e.target.closest('[data-bs-target="#voiceTransactionModal"]') || e.target.closest('#openVoiceModalBtn');
        if (triggerBtn) {
            e.preventDefault();
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                modalInstance.show();
            }
        }
    });

    // Check SpeechRecognition Support
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition) {
        recognition = new SpeechRecognition();
        recognition.lang = 'id-ID';
        recognition.continuous = false;
        recognition.interimResults = true;

        recognition.onstart = function() {
            isRecording = true;
            micBtn.classList.add('recording');
            micIcon.className = 'bi bi-stop-fill';
            statusTitle.textContent = 'Mendengarkan... Silakan Bicara!';
            transcriptText.innerHTML = '<span class="text-primary"><i class="bi bi-record-fill me-1 text-danger spin"></i>Merekam suara...</span>';
            permissionHelp.style.display = 'none';
            aiResultCard.style.display = 'none';
            applyBtn.style.display = 'none';
        };

        recognition.onresult = function(event) {
            let transcript = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
            }
            transcriptText.innerHTML = `<strong>"${transcript}"</strong>`;

            if (event.results[0].isFinal) {
                stopRecording();
                processTranscriptWithAi(transcript);
            }
        };

        recognition.onerror = function(event) {
            stopRecording();
            statusTitle.textContent = 'Akses Suara Dibatasi Browser';

            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                permissionHelp.style.display = 'block';
                transcriptText.innerHTML = `<span class="text-danger"><i class="bi bi-shield-slash me-1"></i>Izin mikrofon tidak diberikan. Gunakan kolom ketik di bawah.</span>`;
            } else {
                transcriptText.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Error: ${event.error}. Silakan coba lagi atau ketik manual di bawah.</span>`;
            }
        };

        recognition.onend = function() {
            if (isRecording) {
                stopRecording();
            }
        };
    } else {
        statusTitle.textContent = 'Gunakan Input Teks Perintah AI';
        transcriptText.innerHTML = '<span class="text-muted">Browser Anda belum mendukung rekaman mikrofon langsung. Silakan ketik perintah di bawah ini.</span>';
    }

    function toggleRecording() {
        if (!recognition) {
            permissionHelp.style.display = 'block';
            return;
        }
        if (isRecording) {
            recognition.stop();
            stopRecording();
        } else {
            try {
                recognition.start();
            } catch (err) {
                console.error(err);
                permissionHelp.style.display = 'block';
            }
        }
    }

    function stopRecording() {
        isRecording = false;
        micBtn.classList.remove('recording');
        micIcon.className = 'bi bi-mic-fill';
    }

    micBtn.addEventListener('click', toggleRecording);

    // Rolling Feedback Phrases for TTS
    const rollingFeedbackPhrases = [
        'terus',
        'ada lagi yang di tambah',
        'siap',
        'lanjut, beli apalagi'
    ];
    let feedbackPhraseIndex = 0;

    function getNextFeedbackPhrase() {
        const phrase = rollingFeedbackPhrases[feedbackPhraseIndex % rollingFeedbackPhrases.length];
        feedbackPhraseIndex++;
        return phrase;
    }

    // Text-to-Speech Feedback (id-ID)
    function speakResponse(text) {
        if ('speechSynthesis' in window && text) {
            window.speechSynthesis.cancel(); // stop previous
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.rate = 1.0;
            window.speechSynthesis.speak(utterance);
        }
    }

    // Process Voice / Text Transcript with AI
    async function processTranscriptWithAi(transcript) {
        if (!transcript || !transcript.trim()) return;

        statusTitle.textContent = 'Memproses Perintah dengan AI...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('{{ route("pos.voice.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ transcript: transcript })
            });

            const data = await res.json();

            if (data.ok && data.result) {
                const newResult = data.result;

                // Initialize or accumulate into currentParsedData
                if (!currentParsedData) {
                    currentParsedData = {
                        intent: newResult.intent || 'add_to_cart',
                        items: [],
                        diskon: newResult.diskon || 0,
                        nominal_bayar: newResult.nominal_bayar || 0,
                        metode_pembayaran: newResult.metode_pembayaran || 'Tunai',
                        voice_response: newResult.voice_response || ''
                    };
                }

                // Merge items (accumulate qty for existing products or append new products)
                if (newResult.items && newResult.items.length > 0) {
                    newResult.items.forEach(newItem => {
                        if (!newItem.produk_id) return;
                        const pId = parseInt(newItem.produk_id, 10);
                        const qty = parseInt(newItem.qty, 10) || 1;

                        const existing = currentParsedData.items.find(i => parseInt(i.produk_id, 10) === pId);
                        if (existing) {
                            existing.qty = (parseInt(existing.qty, 10) || 0) + qty;
                        } else {
                            currentParsedData.items.push({
                                produk_id: pId,
                                nama_produk: newItem.nama_produk,
                                qty: qty,
                                harga_satuan: parseFloat(newItem.harga_satuan) || 0
                            });
                        }
                    });
                }

                if (newResult.diskon) currentParsedData.diskon = newResult.diskon;
                if (newResult.nominal_bayar) currentParsedData.nominal_bayar = newResult.nominal_bayar;
                if (newResult.metode_pembayaran) currentParsedData.metode_pembayaran = newResult.metode_pembayaran;

                renderAiResult(currentParsedData);
                statusTitle.textContent = 'Perintah Berhasil Diproses!';

                // Speak rolling feedback phrase instead of full product list
                const feedbackText = getNextFeedbackPhrase();
                speakResponse(feedbackText);

            } else {
                statusTitle.textContent = 'Gagal Memproses Perintah';
                transcriptText.innerHTML += `<div class="text-danger mt-1" style="font-size:11.5px;">${data.message || 'Gagal memproses suara.'}</div>`;
            }
        } catch (err) {
            console.error('Voice Processing Error:', err);
            statusTitle.textContent = 'Terjadi Kesalahan Koneksi';
        }
    }

    // Manual Submit Button Handler
    submitManualBtn.addEventListener('click', function() {
        const text = manualInput.value.trim();
        if (text) {
            transcriptText.innerHTML = `<strong>"${text}"</strong> (Input Teks)`;
            processTranscriptWithAi(text);
            manualInput.value = '';
        }
    });

    manualInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitManualBtn.click();
        }
    });

    // Render Parsed Result Card
    function renderAiResult(result) {
        aiResultCard.style.display = 'block';
        intentBadge.textContent = result.intent ? result.intent.replace('_', ' ').toUpperCase() : 'TRANSAKSI';

        let html = '';
        if (result.items && result.items.length > 0) {
            html += '<ul class="ps-3 mb-1">';
            result.items.forEach(item => {
                html += `<li><strong>${item.qty}x</strong> ${item.nama_produk} @ Rp${Number(item.harga_satuan).toLocaleString('id-ID')}</li>`;
            });
            html += '</ul>';
        } else {
            html = '<div class="text-muted">Tidak ada item produk yang terdeteksi.</div>';
        }

        parsedItemsDiv.innerHTML = html;

        let notes = '';
        if (result.diskon > 0) notes += `Diskon: Rp${Number(result.diskon).toLocaleString('id-ID')} | `;
        if (result.nominal_bayar > 0) notes += `Bayar: Rp${Number(result.nominal_bayar).toLocaleString('id-ID')} (${result.metode_pembayaran || 'Tunai'})`;
        parsedNotesDiv.textContent = notes || '';

        applyBtn.style.display = 'inline-block';
    }

    // Apply Parsed Items into POS Cart
    applyBtn.addEventListener('click', function() {
        if (!currentParsedData) {
            console.warn('[Voice Modal] No parsed data to apply.');
            return;
        }

        console.log('[Voice Modal] Dispatching pos-voice-apply event with data:', JSON.stringify(currentParsedData));

        // Dispatch custom global event for POS views
        window.dispatchEvent(new CustomEvent('pos-voice-apply', {
            detail: currentParsedData
        }));

        // Delay modal close slightly to ensure cart rendering completes
        setTimeout(function() {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();
        }, 300);
    });

    // Reset when modal closes
    modalEl.addEventListener('hidden.bs.modal', function() {
        stopRecording();
        if (window.speechSynthesis) window.speechSynthesis.cancel();
        statusTitle.textContent = 'Klik Mikrofon & Bicara Perintah Transaksi';
        transcriptText.innerHTML = '<span class="text-muted italic"><i class="bi bi-chat-quote me-1"></i>Suara Anda akan muncul di sini...</span>';
        permissionHelp.style.display = 'none';
        manualInput.value = '';
        aiResultCard.style.display = 'none';
        applyBtn.style.display = 'none';
        currentParsedData = null;
        feedbackPhraseIndex = 0;
    });
});

</script>
