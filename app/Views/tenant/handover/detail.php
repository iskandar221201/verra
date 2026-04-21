<?= view('_components/page_header', [
    'title' => 'Handover Detail',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Handover', 'url' => '/handover'],
        ['label' => 'Detail'],
    ],
]) ?>

<div class="px-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="fw-bold mb-0">Informasi Handover</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width: 120px;">Nomor WA</td>
                            <td class="fw-bold">
                                <?= esc($handover['wa_number']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                <?= view('_components/badge_status', ['status' => $handover['status']]) ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Mode Saat Ini</td>
                            <td>
                                <?php if ($handover['mode'] == 'agent'): ?>
                                    <span class="badge bg-info text-white">Agent</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white">AI</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Trigger</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= esc($handover['trigger_type']) ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pesan Pemicu</td>
                            <td class="small italic">"
                                <?= esc($handover['trigger_message']) ?>"
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat Pada</td>
                            <td class="small">
                                <?= date('d M Y H:i', strtotime($handover['created_at'])) ?>
                            </td>
                        </tr>
                        <?php if ($handover['claimed_by']): ?>
                            <tr>
                                <td class="text-muted">Claimed By</td>
                                <td class="small">User ID:
                                    <?= $handover['claimed_by'] ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Claimed At</td>
                                <td class="small">
                                    <?= date('d M Y H:i', strtotime($handover['claimed_at'])) ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="/handover/chat?id=<?= $handover['id'] ?>" class="btn btn-primary"
                            style="background-color: var(--color-primary);">
                            <i class="bi bi-chat-dots me-1"></i> Buka Chat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">History Percakapan (Read-only)</h5>
                    <span class="badge bg-light text-dark">
                        <?= count($history) ?> Pesan Terakhir
                    </span>
                </div>
                <div class="card-body bg-light" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($history)): ?>
                        <div class="text-center py-5 text-muted">Belum ada percakapan.</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($history as $msg): ?>
                                <?php if ($msg['role'] == 'user'): ?>
                                    <div class="align-self-start bg-white p-3 rounded-3 shadow-sm"
                                        style="max-width: 80%; border-bottom-left-radius: 0 !important;">
                                        <div class="small fw-bold text-primary mb-1">Customer</div>
                                        <div>
                                            <?= esc($msg['message']) ?>
                                        </div>
                                        <div class="text-end small text-muted mt-1" style="font-size: 0.7rem;">
                                            <?= date('H:i', strtotime($msg['created_at'])) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="align-self-end bg-success text-white p-3 rounded-3 shadow-sm"
                                        style="max-width: 80%; background-color: var(--color-primary) !important; border-bottom-right-radius: 0 !important;">
                                        <div class="small fw-bold mb-1">AI / Agent</div>
                                        <div>
                                            <?= esc($msg['message']) ?>
                                        </div>
                                        <div class="text-end small text-white-50 mt-1" style="font-size: 0.7rem;">
                                            <?= date('H:i', strtotime($msg['created_at'])) ?>
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