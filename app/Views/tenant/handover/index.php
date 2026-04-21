<?= view('_components/page_header', [
    'title' => 'Handover List',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Handover']],
]) ?>

<div class="px-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="" method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="pending" <?= $current_status == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= $current_status == 'in_progress' ? 'selected' : '' ?>>In Progress
                        </option>
                        <option value="handled" <?= $current_status == 'handled' ? 'selected' : '' ?>>Handled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Channel</label>
                    <select name="channel_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Channel</option>
                        <?php foreach ($channels as $channel): ?>
                            <option value="<?= $channel['id'] ?>" <?= $current_channel == $channel['id'] ? 'selected' : '' ?>>
                                <?= esc($channel['name']) ?> (
                                <?= esc($channel['wa_number']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 text-end d-flex align-items-end justify-content-end">
                    <a href="/handover/chat" class="btn btn-primary" style="background-color: var(--color-primary);">
                        <i class="bi bi-chat-dots me-1"></i> Buka Live Chat
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Customer</th>
                            <th>Trigger</th>
                            <th>Status</th>
                            <th>Mode</th>
                            <th>Claimed By</th>
                            <th>Waktu</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($handovers)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox d-block mb-2 fs-2"></i>
                                    Tidak ada data handover.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($handovers as $h): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">
                                            <?= esc($h['wa_number']) ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= esc($h['channel_id']) // Better show channel name if joined ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= esc($h['trigger_type']) ?>
                                        </span>
                                        <div class="small text-truncate" style="max-width: 150px;"
                                            title="<?= esc($h['trigger_message']) ?>">
                                            <?= esc($h['trigger_message']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?= view('_components/badge_status', ['status' => $h['status']]) ?>
                                    </td>
                                    <td>
                                        <?php if ($h['mode'] == 'agent'): ?>
                                            <span class="badge bg-info text-white"><i class="bi bi-person-fill"></i> Agent</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary text-white"><i class="bi bi-robot"></i> AI</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?= $h['claimed_by'] ? 'User ID: ' . $h['claimed_by'] : '-' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?= date('d/m H:i', strtotime($h['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="/handover/detail/<?= $h['id'] ?>" class="btn btn-sm btn-outline-info"
                                            title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/handover/chat?id=<?= $h['id'] ?>" class="btn btn-sm btn-primary ms-1"
                                            style="background-color: var(--color-primary);" title="Chat">
                                            <i class="bi bi-chat-text"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>