<?= view('_components/page_header', [
    'title' => 'Chat History',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Conversations', 'url' => '/conversations'],
        ['label' => esc($waNumber)],
    ],
]) ?>

<div class="px-4 pb-5">
    <div class="row">
        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Customer Info</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-person text-secondary" style="font-size: 2.5rem;"></i>
                        </div>
                        <h4 class="fw-bold mb-0"><?= esc($waNumber) ?></h4>
                        <span class="badge bg-light text-dark border mt-1">WA Customer</span>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Channel</label>
                        <div class="fw-bold"><i class="bi bi-phone me-1"></i> <?= esc($channel['name']) ?></div>
                        <div class="small text-muted"><?= esc($channel['wa_number']) ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block mb-1">Total Pesan</label>
                        <div class="fw-bold"><?= count($history) ?> Pesan</div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3">
                    <a href="/conversations" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke List
                    </a>
                </div>
            </div>
        </div>

        <!-- Chat History -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 sticky-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Timeline Chat</h5>
                        <div class="d-flex gap-2">
                            <span class="badge bg-white text-dark border"><i class="bi bi-circle-fill text-primary me-1" style="font-size: 0.5rem;"></i> Customer</span>
                            <span class="badge bg-white text-dark border"><i class="bi bi-circle-fill text-success me-1" style="font-size: 0.5rem;"></i> AI Assistant</span>
                            <span class="badge bg-white text-dark border"><i class="bi bi-circle-fill text-info me-1" style="font-size: 0.5rem;"></i> Agent</span>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-light" style="min-height: 400px;">
                    <?php if (empty($history)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-left-dots text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">Belum ada percakapan.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($history as $msg): ?>
                                <?php if ($msg['role'] == 'user'): ?>
                                    <!-- Customer Message -->
                                    <div class="align-self-start" style="max-width: 80%;">
                                        <div class="bg-white p-3 rounded-3 shadow-sm" style="border-bottom-left-radius: 0 !important; border-left: 4px solid var(--color-primary);">
                                            <div class="small fw-bold text-primary mb-1">Customer</div>
                                            <div class="text-dark">
                                                <?= nl2br(esc($msg['message'])) ?>
                                            </div>
                                            <div class="text-end small text-muted mt-1" style="font-size: 0.7rem;">
                                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Assistant/Agent Message -->
                                    <div class="align-self-end" style="max-width: 80%;">
                                        <?php 
                                            $isAgent = !empty($msg['agent_name']);
                                            $bgColor = $isAgent ? 'bg-info' : 'bg-success';
                                            $headerLabel = $isAgent ? 'Agent: ' . esc($msg['agent_name']) : 'AI Assistant';
                                            $icon = $isAgent ? 'bi-person-badge' : 'bi-robot';
                                        ?>
                                        <div class="<?= $bgColor ?> text-white p-3 rounded-3 shadow-sm" style="border-bottom-right-radius: 0 !important;">
                                            <div class="small fw-bold mb-1 d-flex align-items-center">
                                                <i class="bi <?= $icon ?> me-1"></i> <?= $headerLabel ?>
                                            </div>
                                            <div>
                                                <?= nl2br(esc($msg['message'])) ?>
                                            </div>
                                            <div class="text-end small text-white-50 mt-1" style="font-size: 0.7rem;">
                                                <?= date('H:i', strtotime($msg['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
