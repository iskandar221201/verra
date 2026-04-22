<style>
    .chat-container {
        height: calc(100vh - 120px);
        display: flex;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }

    .chat-sidebar {
        width: 320px;
        border-right: 1px solid #f0f0f0;
        display: flex;
        flex-direction: column;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fdfdfd;
    }

    .chat-list {
        flex: 1;
        overflow-y: auto;
    }

    .chat-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f8f8f8;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chat-item:hover {
        background: #f9f9ff;
    }

    .chat-item.active {
        background: #f0f0ff;
        border-left: 4px solid var(--color-primary);
    }

    .chat-history {
        flex: 1;
        overflow-y: auto;
        padding: 25px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        background-image: radial-gradient(#6366f111 0.5px, transparent 0.5px);
        background-size: 20px 20px;
    }

    .bubble {
        padding: 12px 16px;
        border-radius: 18px;
        max-width: 75%;
        font-size: 0.95rem;
        line-height: 1.5;
        position: relative;
    }

    .bubble-customer {
        align-self: flex-start;
        background: #ffffff;
        color: #333;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        border-bottom-left-radius: 2px;
        border: 1px solid #eee;
    }

    .bubble-ai {
        align-self: flex-end;
        background: #eef2ff;
        color: #312e81;
        border-bottom-right-radius: 2px;
        border: 1px solid #e0e7ff;
    }

    .bubble-agent {
        align-self: flex-end;
        background: #8b5cf6;
        color: #fff;
        border-bottom-right-radius: 2px;
        box-shadow: 0 4px 10px rgba(139, 92, 246, 0.2);
    }

    .chat-footer {
        padding: 20px;
        background: #fff;
        border-top: 1px solid #f0f0f0;
    }

    .empty-chat {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #999;
    }
</style>

<div class="px-4 pb-4">
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="p-3 border-bottom bg-light">
                <h6 class="fw-bold mb-0">Handover Aktif</h6>
            </div>
            <div class="chat-list" id="activeHandoversList">
                <?php if (empty($activeHandovers)): ?>
                    <div class="text-center py-4 text-muted small">Tidak ada handover aktif.</div>
                <?php else: ?>
                    <?php foreach ($activeHandovers as $h): ?>
                        <div class="chat-item"
                            onclick="loadChat(<?= $h['id'] ?>, '<?= $h['wa_number'] ?>', <?= $h['channel_id'] ?>)"
                            id="handover-item-<?= $h['id'] ?>">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold small text-dark">
                                    <?= esc($h['wa_number']) ?>
                                </span>
                                <span class="badge bg-light text-dark border p-1" style="font-size: 0.65rem;">
                                    <?= esc($h['trigger_type']) ?>
                                </span>
                            </div>
                            <div class="text-truncate small text-muted">
                                <?= esc($h['trigger_message']) ?>
                            </div>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <?= view('_components/badge_status', ['status' => $h['status']]) ?>
                                <span class="text-muted" style="font-size: 0.7rem;">
                                    <?= date('H:i', strtotime($h['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Chat -->
        <div class="chat-main" id="chatMainArea">
            <div class="empty-chat" id="noChatSelected">
                <i class="bi bi-chat-left-text fs-1 mb-3 opacity-25"></i>
                <p>Pilih percakapan untuk memulai live chat</p>
            </div>

            <!-- Chat content will be loaded here via JS -->
            <div id="chatActiveArea" style="display: none; flex-direction: column; height: 100%;">
                <div class="chat-header p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <h6 class="fw-bold mb-0" id="currentWaNumber"></h6>
                        <span class="small text-muted" id="currentChannelInfo"></span>
                        <span id="currentModeBadge" class="ms-2"></span>
                    </div>
                    <div id="headerActions">
                        <!-- Buttons will be rendered by JS -->
                    </div>
                </div>

                <div class="chat-history" id="chatHistoryBox">
                    <!-- Messages will be appended here -->
                </div>

                <div class="chat-footer" id="chatFooter">
                    <form id="sendMessageForm" class="d-flex gap-2">
                        <input type="text" id="messageInput" class="form-control" placeholder="Ketik pesan..." required
                            autocomplete="off">
                        <button type="submit" class="btn btn-primary px-4"
                            style="background-color: var(--color-primary);">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                    <div id="chatDisabledMessage" class="text-center text-muted small py-2" style="display: none;">
                        Ambil alih percakapan untuk membalas pesan.
                    </div>
                    <div id="chatReadOnlyMessage" class="text-center text-muted small py-2" style="display: none;">
                        Mode AI aktif. Anda hanya dapat memantau percakapan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentHandoverId = null;
    let currentWaNumber = null;
    let currentChannelId = null;
    let pollingInterval = null;
    let renderedMessageCount = 0;
    let isSending = false;
    const myAgentId = <?= auth()->id() ?>;

    // Auto-open from query param
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');
        if (id) {
            const item = document.getElementById('handover-item-' + id);
            if (item) item.click();
        }
    });

    function loadChat(id, waNumber, channelId) {
        if (currentHandoverId === id) return;

        // UI Cleanup
        document.getElementById('noChatSelected').style.display = 'none';
        document.getElementById('chatActiveArea').style.display = 'flex';
        document.getElementById('chatHistoryBox').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary spinner-border-sm" role="status"></div></div>';

        // Highlight sidebar
        document.querySelectorAll('.chat-item').forEach(el => el.classList.remove('active'));
        const item = document.getElementById('handover-item-' + id);
        if (item) item.classList.add('active');

        currentHandoverId = id;
        currentWaNumber = waNumber;
        currentChannelId = channelId;
        renderedMessageCount = 0;

        document.getElementById('currentWaNumber').innerText = waNumber;

        // Fetch state + history in parallel via JSON APIs
        Promise.all([
            fetch(`/handover/api/state/${id}`).then(r => r.json()),
            fetch(`/handover/api/history/${id}`).then(r => r.json())
        ]).then(([stateRes, historyRes]) => {
            // Render history
            const box = document.getElementById('chatHistoryBox');
            box.innerHTML = '';

            if (historyRes.status === 'success' && historyRes.data.length > 0) {
                historyRes.data.forEach(msg => {
                    appendBubble(msg.message, msg.sender, msg.timestamp);
                });
                renderedMessageCount = historyRes.data.length;
            } else {
                box.innerHTML = '<div class="text-center py-5 text-muted">Belum ada percakapan.</div>';
            }

            // Render header actions from state
            if (stateRes.status === 'success') {
                const s = stateRes.data;
                renderHeaderActions(s.handover_status, s.mode, s.claimed_by);
            }
        }).catch(err => {
            console.error('Failed to load chat:', err);
            document.getElementById('chatHistoryBox').innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat percakapan.</div>';
        });

        // Start polling for new messages
        startPolling();
    }

    async function refreshState() {
        if (!currentHandoverId) return;
        try {
            const res = await fetch(`/handover/api/state/${currentHandoverId}`);
            const json = await res.json();
            if (json.status === 'success') {
                const s = json.data;
                renderHeaderActions(s.handover_status, s.mode, s.claimed_by);
            }
        } catch (e) {
            console.error('Failed to refresh state', e);
        }
    }

    function renderHeaderActions(status, mode, claimedBy) {
        const actionsArea = document.getElementById('headerActions');
        const footerInput = document.getElementById('sendMessageForm');
        const disabledMsg = document.getElementById('chatDisabledMessage');
        const readOnlyMsg = document.getElementById('chatReadOnlyMessage');
        const modeBadge = document.getElementById('currentModeBadge');

        actionsArea.innerHTML = '';
        footerInput.style.display = 'none';
        disabledMsg.style.display = 'none';
        readOnlyMsg.style.display = 'none';

        // Render Badge
        if (mode === 'agent') {
            modeBadge.className = 'badge bg-info text-white ms-2';
            modeBadge.innerHTML = '<i class="bi bi-person-fill"></i> Mode Agen';
        } else {
            modeBadge.className = 'badge bg-secondary text-white ms-2';
            modeBadge.innerHTML = '<i class="bi bi-robot"></i> Mode AI';
        }

        if (status === 'handled') {
            actionsArea.innerHTML = '<span class="badge bg-success">Selesai</span>';
            readOnlyMsg.style.display = 'block';
            stopPolling(); // Stop polling when handover is done
            return;
        }

        if (mode === 'ai') {
            actionsArea.innerHTML = `<button onclick="handleAction('claim')" class="btn btn-sm btn-primary">Ambil Alih</button>`;
            readOnlyMsg.style.display = 'block';
        } else if (mode === 'agent') {
            if (claimedBy === myAgentId) {
                actionsArea.innerHTML = `
                <button onclick="handleAction('return-to-ai')" class="btn btn-sm btn-warning me-2">Kembalikan ke AI</button>
                <button onclick="handleAction('close')" class="btn btn-sm btn-success">Selesai</button>
            `;
                footerInput.style.display = 'flex';
            } else {
                actionsArea.innerHTML = `<span class="badge bg-light text-dark border">Diambil oleh Agent #${claimedBy}</span>`;
                disabledMsg.style.display = 'block';
            }
        }
    }

    async function handleAction(type) {
        if (!currentHandoverId) return;

        const url = `/agent-chat/${currentHandoverId}/${type}`;
        try {
            const response = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const result = await response.json();

            if (result.status === 'success') {
                refreshState();
            } else {
                alert(result.message);
            }
        } catch (e) {
            alert("Terjadi kesalahan sistem.");
        }
    }

    function startPolling() {
        stopPolling();
        pollingInterval = setInterval(pollMessages, 2500);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    async function pollMessages() {
        if (!currentHandoverId) return;
        try {
            // Fetch history + state in parallel
            const [historyRes, stateRes] = await Promise.all([
                fetch(`/handover/api/history/${currentHandoverId}`).then(r => r.json()),
                fetch(`/handover/api/state/${currentHandoverId}`).then(r => r.json())
            ]);

            // Append only NEW messages
            if (historyRes.status === 'success') {
                const allMessages = historyRes.data;
                if (allMessages.length > renderedMessageCount) {
                    const newMessages = allMessages.slice(renderedMessageCount);
                    newMessages.forEach(msg => {
                        appendBubble(msg.message, msg.sender, msg.timestamp);
                    });
                }
            }

            // Update header state (mode, claimed_by, status)
            if (stateRes.status === 'success') {
                const s = stateRes.data;
                renderHeaderActions(s.handover_status, s.mode, s.claimed_by);
            }
        } catch (e) {
            console.error('Polling error:', e);
        }
    }

    // Cleanup on page leave
    window.addEventListener('beforeunload', stopPolling);

    function appendBubble(message, sender, timestamp) {
        const box = document.getElementById('chatHistoryBox');
        // Remove "no messages" placeholder if present
        const placeholder = box.querySelector('.text-muted');
        if (placeholder && box.children.length === 1) box.innerHTML = '';

        const timeStr = timestamp ? timestamp.split(' ')[1]?.substring(0, 5) || '' : '';

        const div = document.createElement('div');
        if (sender === 'customer') {
            div.className = 'bubble bubble-customer align-self-start';
            div.innerHTML = `<div class="small fw-bold text-primary mb-1">Customer</div><div>${escapeHtml(message)}</div><div class="text-end small text-muted mt-1" style="font-size: 0.7rem;">${timeStr}</div>`;
        } else if (sender === 'agent') {
            div.className = 'bubble bubble-agent align-self-end';
            div.innerHTML = `<div class="small fw-bold mb-1">Agent</div><div>${escapeHtml(message)}</div><div class="text-end small text-white-50 mt-1" style="font-size: 0.7rem;">${timeStr}</div>`;
        } else {
            div.className = 'bubble bubble-ai align-self-end';
            div.innerHTML = `<div class="small fw-bold mb-1">AI</div><div>${escapeHtml(message)}</div><div class="text-end small text-muted mt-1" style="font-size: 0.7rem;">${timeStr}</div>`;
        }

        box.appendChild(div);
        renderedMessageCount++;
        scrollToBottom();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function scrollToBottom() {
        const box = document.getElementById('chatHistoryBox');
        box.scrollTop = box.scrollHeight;
    }

    document.getElementById('sendMessageForm').onsubmit = function (e) {
        e.preventDefault();
        if (isSending) return; // Prevent double submit

        const input = document.getElementById('messageInput');
        const btn = this.querySelector('button[type="submit"]');
        const message = input.value.trim();
        if (!message || !currentHandoverId) return;

        // Disable button + show loading
        isSending = true;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        const formData = new FormData();
        formData.append('message', message);

        fetch(`/agent-chat/${currentHandoverId}/send`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(res => res.json()).then(data => {
            if (data.status === 'success') {
                input.value = '';
            } else {
                alert(data.message);
            }
        }).catch(() => {
            alert('Gagal mengirim pesan.');
        }).finally(() => {
            // Re-enable button
            isSending = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send"></i>';
        });
    };
</script>