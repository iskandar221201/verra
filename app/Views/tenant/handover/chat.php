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
    let sseSource = null;
    let lastEventId = 0;
    const myAgentId = <?= auth()->id() ?>;

    // Get query param for direct open
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
        document.getElementById('handover-item-' + id).classList.add('active');

        currentHandoverId = id;
        currentWaNumber = waNumber;
        currentChannelId = channelId;

        document.getElementById('currentWaNumber').innerText = waNumber;

        // Load detail & history
        fetch(`/handover/detail/${id}`)
            .then(res => res.text())
            .then(html => {
                // We need a better way to get data, but for now we extract from detail view or use another endpoint
                // Actually, let's create a dedicated JSON endpoint if needed, but per spec I'll just use what I have.
                // Wait, I didn't create a JSON history endpoint. I'll just fetch history directly here for now.
                refreshChatState();
            });

        // Subscribe to SSE
        subscribeSSE(channelId, waNumber);
    }

    function refreshChatState() {
        // This is a quick workaround: we need the current handover state (mode, claimed_by)
        // I'll fetch it via detail endpoint (returning partial or we can add a small API method)
        // Let's assume we have a way to get state. I'll just call an async function.
        updateUIState();
    }

    async function updateUIState() {
        try {
            const response = await fetch(`/handover/detail/${currentHandoverId}`);
            const html = await response.text();

            // Use DOM Parser to extract specific info from detail view
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Extract messages
            const historyBox = doc.getElementById('chatHistoryBox');
            if (historyBox) {
                document.getElementById('chatHistoryBox').innerHTML = historyBox.innerHTML;
                scrollToBottom();
            }

            // Extract state from the info table in detail view
            const rows = doc.querySelectorAll('table tr');
            let mode = 'ai';
            let claimedBy = null;
            let status = 'pending';

            rows.forEach(row => {
                const label = row.querySelector('td:first-child')?.innerText.trim();
                const value = row.querySelector('td:last-child')?.innerText.trim();

                if (label === 'Mode Saat Ini') mode = value.toLowerCase();
                if (label === 'Claimed By' && value.includes('User ID:')) claimedBy = parseInt(value.split(':')[1].trim());
                if (label === 'Status') status = row.querySelector('.badge')?.innerText.toLowerCase().trim();
            });

            renderHeaderActions(status, mode, claimedBy);
        } catch (e) {
            console.error("Failed to update UI state", e);
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
                updateUIState();
            } else {
                alert(result.message);
            }
        } catch (e) {
            alert("Terjadi kesalahan sistem.");
        }
    }

    function subscribeSSE(channelId, waNumber) {
        if (sseSource) sseSource.close();

        sseSource = new EventSource(`/sse/${channelId}/${waNumber}?lastEventId=${lastEventId}`);

        sseSource.onmessage = function (e) {
            // SSE standard data handling
        };

        // Custom events
        sseSource.addEventListener('new_message', function (e) {
            const data = JSON.parse(e.data);
            appendBubble(data.message, data.sender, data.timestamp);
        });

        sseSource.addEventListener('handover_claimed', function (e) {
            updateUIState();
        });

        sseSource.addEventListener('returned_to_ai', function (e) {
            updateUIState();
        });
    }

    function appendBubble(message, sender, timestamp) {
        const box = document.getElementById('chatHistoryBox');
        const timeStr = timestamp.split(' ')[1].substring(0, 5);

        const div = document.createElement('div');
        if (sender === 'customer') {
            div.className = 'bubble bubble-customer align-self-start';
            div.innerHTML = `<div class="small fw-bold text-primary mb-1">Customer</div><div>${message}</div><div class="text-end small text-muted mt-1" style="font-size: 0.7rem;">${timeStr}</div>`;
        } else if (sender === 'agent') {
            div.className = 'bubble bubble-agent align-self-end';
            div.innerHTML = `<div class="small fw-bold mb-1">Agent</div><div>${message}</div><div class="text-end small text-white-50 mt-1" style="font-size: 0.7rem;">${timeStr}</div>`;
        } else {
            div.className = 'bubble bubble-ai align-self-end';
            div.innerHTML = `<div class="small fw-bold mb-1">AI</div><div>${message}</div><div class="text-end small text-muted mt-1" style="font-size: 0.7rem;">${timeStr}</div>`;
        }

        box.appendChild(div);
        scrollToBottom();
    }

    function scrollToBottom() {
        const box = document.getElementById('chatHistoryBox');
        box.scrollTop = box.scrollHeight;
    }

    document.getElementById('sendMessageForm').onsubmit = function (e) {
        e.preventDefault();
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        if (!message || !currentHandoverId) return;

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
        });
    };
</script>