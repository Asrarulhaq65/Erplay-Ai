{{-- ══════════════════════════════════════════════════════════════════════════
     AI COPILOT FLOATING WIDGET (DRAGGABLE & RESIZABLE CHATBOT)
     ──────────────────────────────────────────────────────────────────────────
     Floating Chatbot Widget with Google Gemini AI Integration.
     Includes Draggable FAB & Panel, Quick Prompts, Markdown Parsing,
     Typing Animation, & Position Memory via localStorage.
     ══════════════════════════════════════════════════════════════════════════ --}}

<style>
    /* ── AI Copilot Trigger FAB (Draggable) ── */
    #aiCopilotFab {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 1045;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-accent) 100%);
        color: #FFFFFF;
        border: none;
        box-shadow: 0 6px 20px rgba(13, 78, 86, 0.35);
        cursor: grab;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
        user-select: none;
        touch-action: none;
    }
    #aiCopilotFab:active {
        cursor: grabbing;
        transform: scale(0.95);
    }
    #aiCopilotFab:hover {
        box-shadow: 0 8px 25px rgba(91, 160, 173, 0.5);
    }
    #aiCopilotFab .ai-badge-dot {
        position: absolute;
        top: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        background: #10B981;
        border: 2px solid #FFFFFF;
        border-radius: 50%;
    }

    /* ── AI Copilot Panel Window (Draggable) ── */
    #aiCopilotPanel {
        position: fixed;
        bottom: 86px;
        right: 24px;
        width: 385px;
        max-width: calc(100vw - 32px);
        height: 540px;
        max-height: calc(100vh - 110px);
        background: var(--bg-card);
        border: 1px solid var(--border-light);
        border-radius: 16px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
        z-index: 1050;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px) scale(0.95);
        transition: opacity 0.25s ease, transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.25s;
        touch-action: none;
    }
    #aiCopilotPanel.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    /* Header Drag Handle */
    .ai-panel-header {
        background: linear-gradient(135deg, var(--pb-dark) 0%, var(--pb-darker) 100%);
        color: #FFFFFF;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
        cursor: move;
        user-select: none;
    }
    .ai-panel-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ai-status-indicator {
        font-size: 10px;
        background: rgba(255, 255, 255, 0.15);
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }

    /* Messages List */
    .ai-panel-messages {
        flex: 1;
        padding: 14px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: var(--bg-body);
    }

    /* Chat Bubbles */
    .ai-msg {
        max-width: 86%;
        font-size: 12.5px;
        line-height: 1.5;
        border-radius: 14px;
        padding: 10px 14px;
        word-break: break-word;
    }
    .ai-msg-user {
        align-self: flex-end;
        background: var(--pb-dark);
        color: #FFFFFF;
        border-bottom-right-radius: 3px;
    }
    [data-theme="dark"] .ai-msg-user {
        background: var(--pb-accent);
        color: #0F172A;
    }
    .ai-msg-assistant {
        align-self: flex-start;
        background: var(--bg-card);
        color: var(--text-primary);
        border: 1px solid var(--border-light);
        border-bottom-left-radius: 3px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }

    /* Quick Prompts Container */
    .ai-quick-prompts {
        padding: 8px 12px;
        background: var(--bg-card);
        border-top: 1px solid var(--border-light);
        display: flex;
        gap: 6px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }
    .ai-quick-prompts::-webkit-scrollbar { display: none; }
    .ai-prompt-chip {
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 14px;
        background: var(--bg-input);
        color: var(--pb-text);
        border: 1px solid var(--border-light);
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .ai-prompt-chip:hover {
        background: var(--pb-lightest);
        border-color: var(--pb-mid);
    }

    /* Footer Input */
    .ai-panel-footer {
        padding: 10px 12px;
        background: var(--bg-card);
        border-top: 1px solid var(--border-light);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ai-input-field {
        flex: 1;
        border: 1px solid var(--border-light);
        border-radius: 20px;
        padding: 8px 14px;
        font-size: 12px;
        background: var(--bg-input);
        color: var(--text-primary);
        outline: none;
        transition: border-color 0.15s;
    }
    .ai-input-field:focus {
        border-color: var(--pb-accent);
    }
    .ai-send-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--pb-dark);
        color: #FFFFFF;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        cursor: pointer;
        transition: background 0.15s, transform 0.15s;
        flex-shrink: 0;
    }
    .ai-send-btn:hover {
        background: var(--pb-darker);
        transform: scale(1.05);
    }
    .ai-send-btn:disabled {
        background: var(--text-muted);
        cursor: not-allowed;
        transform: none;
    }

    /* Typing Dots Animation */
    .ai-typing-dots {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
    }
    .ai-typing-dots span {
        width: 6px;
        height: 6px;
        background: var(--pb-accent);
        border-radius: 50%;
        animation: aiBlink 1.4s infinite ease-in-out both;
    }
    .ai-typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .ai-typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    @keyframes aiBlink {
        0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
        40% { transform: scale(1); opacity: 1; }
    }
</style>

@php
    $aiAssistantConfig = auth()->user()?->toko?->aiAssistantConfig;
    $aiAssistantName = $aiAssistantConfig?->assistant_name ?? 'ERPlay AI Assistant';
    $aiGreeting = $aiAssistantConfig?->greeting_message ?: 'Ada yang bisa saya bantu terkait data stok, ringkasan penjualan, atau saran bisnis hari ini?';
@endphp

<!-- Floating Action Button Trigger (Draggable) -->
<button id="aiCopilotFab" title="Geser untuk memindahkan. Klik untuk membuka AI Copilot" aria-label="Buka AI Copilot Assistant">
    <i class="bi bi-robot"></i>
    <span class="ai-badge-dot"></span>
</button>

<!-- Floating Chat Panel Window (Draggable by Header) -->
<div id="aiCopilotPanel" role="dialog" aria-label="{{ $aiAssistantName }} Window">
    <div class="ai-panel-header" id="aiPanelDragHeader" title="Tarik header untuk menggeser jendela">
        <div class="ai-panel-title">
            <i class="bi bi-grip-vertical text-white-50 me-1" style="font-size:16px;"></i>
            <i class="bi bi-robot text-warning fs-5"></i>
            <div>
                <div>{{ $aiAssistantName }}</div>
                <div class="ai-status-indicator"><i class="bi bi-circle-fill text-success me-1" style="font-size:7px;"></i>AI siap membantu</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1" onclick="event.stopPropagation();">
            <a href="{{ route('pengaturan.ai.index') }}" class="btn btn-sm text-white-50 p-1" title="Pengaturan AI" aria-label="Pengaturan AI">
                <i class="bi bi-gear" style="font-size:14px;"></i>
            </a>
            <button class="btn btn-sm text-white-50 p-1" id="aiClearHistory" title="Bersihkan Obrolan" aria-label="Bersihkan Obrolan">
                <i class="bi bi-trash3" style="font-size:14px;"></i>
            </button>
            <button class="btn btn-sm text-white-50 p-1" id="aiClosePanel" title="Tutup Copilot" aria-label="Tutup Copilot">
                <i class="bi bi-x-lg" style="font-size:14px;"></i>
            </button>
        </div>
    </div>

    <!-- Chat Messages Container -->
    <div class="ai-panel-messages" id="aiMessagesContainer">
        <!-- Initial Welcome Message -->
        <div class="ai-msg ai-msg-assistant">
            <strong>Halo, {{ auth()->user()?->nama_lengkap ?? 'Rekan Toko' }}! 👋</strong><br>
            Saya **{{ $aiAssistantName }}** untuk toko <em>{{ auth()->user()?->toko?->nama_toko ?? 'ERPlay AI' }}</em>. {{ str_replace('{nama_user}', auth()->user()?->nama_lengkap ?? 'Rekan Toko', $aiGreeting) }}
        </div>
    </div>

    <!-- Quick Prompts Toolbar -->
    <div class="ai-quick-prompts">
        <button class="ai-prompt-chip" data-prompt="Ringkas penjualan dan statistik toko hari ini">📊 Penjualan Hari Ini</button>
        <button class="ai-prompt-chip" data-prompt="Produk mana saja yang stoknya sudah menipis?">⚠️ Stok Menipis</button>
        <button class="ai-prompt-chip" data-prompt="Berikan saran strategi bisnis dan restok untuk toko saya">💡 Saran Restok</button>
    </div>

    <!-- Chat Input Footer -->
    <div class="ai-panel-footer">
        <input type="text" id="aiInputField" class="ai-input-field" placeholder="Tanyakan seputar data toko Anda..." autocomplete="off">
        <button id="aiSendBtn" class="ai-send-btn" title="Kirim Pesan" aria-label="Kirim Pesan">
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fab = document.getElementById('aiCopilotFab');
    const panel = document.getElementById('aiCopilotPanel');
    const panelHeader = document.getElementById('aiPanelDragHeader');
    const closeBtn = document.getElementById('aiClosePanel');
    const clearBtn = document.getElementById('aiClearHistory');
    const sendBtn = document.getElementById('aiSendBtn');
    const inputField = document.getElementById('aiInputField');
    const messagesContainer = document.getElementById('aiMessagesContainer');
    const promptChips = document.querySelectorAll('.ai-prompt-chip');

    let chatHistory = [];

    // ── 1. RESTORE FAB POSITION FROM LOCALSTORAGE ──
    const savedFabLeft = localStorage.getItem('ai_fab_left');
    const savedFabTop = localStorage.getItem('ai_fab_top');
    if (savedFabLeft && savedFabTop) {
        fab.style.left = savedFabLeft + 'px';
        fab.style.top = savedFabTop + 'px';
        fab.style.bottom = 'auto';
        fab.style.right = 'auto';
    }

    const savedPanelLeft = localStorage.getItem('ai_panel_left');
    const savedPanelTop = localStorage.getItem('ai_panel_top');
    if (savedPanelLeft && savedPanelTop) {
        panel.style.left = savedPanelLeft + 'px';
        panel.style.top = savedPanelTop + 'px';
        panel.style.bottom = 'auto';
        panel.style.right = 'auto';
    }

    // Toggle Panel Visibility
    function togglePanel() {
        panel.classList.toggle('active');
        if (panel.classList.contains('active')) {
            inputField.focus();
        }
    }

    closeBtn.addEventListener('click', togglePanel);

    // ── 2. DRAGGABLE FAB LOGIC (DISAMBIGUATE DRAG VS CLICK) ──
    let isFabDragging = false;
    let fabStartX = 0, fabStartY = 0;
    let fabInitLeft = 0, fabInitTop = 0;
    let fabDragDistance = 0;

    function onFabDragStart(e) {
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        isFabDragging = true;
        fabDragDistance = 0;
        fabStartX = clientX;
        fabStartY = clientY;

        const rect = fab.getBoundingClientRect();
        fabInitLeft = rect.left;
        fabInitTop = rect.top;

        document.addEventListener('mousemove', onFabDragMove);
        document.addEventListener('touchmove', onFabDragMove, { passive: false });
        document.addEventListener('mouseup', onFabDragEnd);
        document.addEventListener('touchend', onFabDragEnd);
    }

    function onFabDragMove(e) {
        if (!isFabDragging) return;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        const dx = clientX - fabStartX;
        const dy = clientY - fabStartY;
        fabDragDistance = Math.hypot(dx, dy);

        if (fabDragDistance > 4) {
            e.preventDefault();
            let newLeft = fabInitLeft + dx;
            let newTop = fabInitTop + dy;

            // Clamp inside viewport boundaries
            const maxLeft = window.innerWidth - fab.offsetWidth - 8;
            const maxTop = window.innerHeight - fab.offsetHeight - 8;
            newLeft = Math.max(8, Math.min(newLeft, maxLeft));
            newTop = Math.max(8, Math.min(newTop, maxTop));

            fab.style.left = newLeft + 'px';
            fab.style.top = newTop + 'px';
            fab.style.bottom = 'auto';
            fab.style.right = 'auto';
        }
    }

    function onFabDragEnd() {
        if (!isFabDragging) return;
        isFabDragging = false;

        document.removeEventListener('mousemove', onFabDragMove);
        document.removeEventListener('touchmove', onFabDragMove);
        document.removeEventListener('mouseup', onFabDragEnd);
        document.removeEventListener('touchend', onFabDragEnd);

        if (fabDragDistance > 6) {
            // Save position to localStorage
            const rect = fab.getBoundingClientRect();
            localStorage.setItem('ai_fab_left', rect.left);
            localStorage.setItem('ai_fab_top', rect.top);
        } else {
            // Click action
            togglePanel();
        }
    }

    fab.addEventListener('mousedown', onFabDragStart);
    fab.addEventListener('touchstart', onFabDragStart, { passive: true });

    // ── 3. DRAGGABLE PANEL LOGIC (VIA HEADER) ──
    let isPanelDragging = false;
    let panelStartX = 0, panelStartY = 0;
    let panelInitLeft = 0, panelInitTop = 0;

    function onPanelDragStart(e) {
        if (e.target.closest('button') || e.target.closest('a')) return;

        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        isPanelDragging = true;
        panelStartX = clientX;
        panelStartY = clientY;

        const rect = panel.getBoundingClientRect();
        panelInitLeft = rect.left;
        panelInitTop = rect.top;

        document.addEventListener('mousemove', onPanelDragMove);
        document.addEventListener('touchmove', onPanelDragMove, { passive: false });
        document.addEventListener('mouseup', onPanelDragEnd);
        document.addEventListener('touchend', onPanelDragEnd);
    }

    function onPanelDragMove(e) {
        if (!isPanelDragging) return;
        e.preventDefault();

        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;

        const dx = clientX - panelStartX;
        const dy = clientY - panelStartY;

        let newLeft = panelInitLeft + dx;
        let newTop = panelInitTop + dy;

        const maxLeft = window.innerWidth - panel.offsetWidth - 8;
        const maxTop = window.innerHeight - panel.offsetHeight - 8;
        newLeft = Math.max(8, Math.min(newLeft, maxLeft));
        newTop = Math.max(8, Math.min(newTop, maxTop));

        panel.style.left = newLeft + 'px';
        panel.style.top = newTop + 'px';
        panel.style.bottom = 'auto';
        panel.style.right = 'auto';
    }

    function onPanelDragEnd() {
        if (!isPanelDragging) return;
        isPanelDragging = false;

        document.removeEventListener('mousemove', onPanelDragMove);
        document.removeEventListener('touchmove', onPanelDragMove);
        document.removeEventListener('mouseup', onPanelDragEnd);
        document.removeEventListener('touchend', onPanelDragEnd);

        const rect = panel.getBoundingClientRect();
        localStorage.setItem('ai_panel_left', rect.left);
        localStorage.setItem('ai_panel_top', rect.top);
    }

    panelHeader.addEventListener('mousedown', onPanelDragStart);
    panelHeader.addEventListener('touchstart', onPanelDragStart, { passive: true });

    // Clear History
    clearBtn.addEventListener('click', function() {
        chatHistory = [];
        messagesContainer.innerHTML = `
            <div class="ai-msg ai-msg-assistant">
                <strong>Obrolan dibersihkan. ✨</strong><br>Silakan ajukan pertanyaan baru seputar inventori, penjualan, atau operasional toko Anda.
            </div>
        `;
    });

    // Handle Quick Prompts
    promptChips.forEach(chip => {
        chip.addEventListener('click', function() {
            const prompt = this.getAttribute('data-prompt');
            if (prompt) {
                inputField.value = prompt;
                sendMessage();
            }
        });
    });

    // Simple Markdown Formatter (Bold, List, Code, Linebreak, Links)
    function parseMarkdown(text) {
        if (!text) return '';
        let html = text
            .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
            .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" style="color:var(--pb-accent);text-decoration:underline;">$1</a>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`([^`]+)`/g, '<code style="background:var(--bg-input);padding:2px 5px;border-radius:4px;font-size:11px;">$1</code>')
            .replace(/^\s*-\s+(.*)$/gim, '• $1')
            .replace(/\n/g, '<br>');
        return html;
    }

    // Append Message to UI
    function appendMessage(role, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-msg ${role === 'user' ? 'ai-msg-user' : 'ai-msg-assistant'}`;
        msgDiv.innerHTML = parseMarkdown(text);
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Show Typing Indicator
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'aiTypingIndicator';
        typingDiv.className = 'ai-msg ai-msg-assistant';
        typingDiv.innerHTML = `
            <div class="ai-typing-dots">
                <span></span><span></span><span></span>
            </div>
            <span style="font-size:11px;color:var(--text-muted);margin-left:4px;">Memproses data toko...</span>
        `;
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function removeTypingIndicator() {
        const typingDiv = document.getElementById('aiTypingIndicator');
        if (typingDiv) typingDiv.remove();
    }

    // Send Message AJAX
    async function sendMessage() {
        const text = inputField.value.trim();
        if (!text) return;

        // Render User Message
        appendMessage('user', text);
        inputField.value = '';
        sendBtn.disabled = true;

        showTypingIndicator();

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const response = await fetch('{{ route("ai.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: text,
                    history: chatHistory
                })
            });

            const data = await response.json();
            removeTypingIndicator();

            if (data.success) {
                appendMessage('assistant', data.reply);
                chatHistory.push({ role: 'user', content: text });
                chatHistory.push({ role: 'assistant', content: data.reply });
                if (chatHistory.length > 10) chatHistory = chatHistory.slice(-10);
            } else {
                appendMessage('assistant', data.reply || 'Maaf, terjadi kesalahan pada server AI.');
            }
        } catch (err) {
            removeTypingIndicator();
            appendMessage('assistant', 'Gagal terhubung ke server. Silakan coba beberapa saat lagi.');
            console.error('AI Chat Error:', err);
        } finally {
            sendBtn.disabled = false;
        }
    }

    sendBtn.addEventListener('click', sendMessage);
    inputField.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
});
</script>
