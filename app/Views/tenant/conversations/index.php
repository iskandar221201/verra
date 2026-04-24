<?= view('_components/page_header', [
    'title' => 'Conversations',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Conversations'],
    ],
]) ?>

<div class="px-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="/conversations" method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Cari Nomor WA</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Contoh: 62812..." value="<?= esc($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Filter Channel</label>
                    <select name="channel_id" class="form-select">
                        <option value="">Semua Channel</option>
                        <?php foreach ($channels as $channel): ?>
                            <option value="<?= $channel['id'] ?>" <?= $channelFilter == $channel['id'] ? 'selected' : '' ?>>
                                <?= esc($channel['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"
                        style="background-color: var(--color-primary);">Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="/conversations" class="btn btn-light w-100 border text-muted">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <?php
    $headers = ['Nomor WA', 'Channel', 'Pesan Terakhir', 'Total Pesan', 'Last Activity', 'Aksi'];
    $rows = [];
    foreach ($conversations as $conv) {
        $rows[] = [
            '<div class="fw-bold text-dark">' . esc($conv['wa_number']) . '</div>',
            '<span class="badge bg-light text-dark border">' . esc($conv['channel_name'] ?? 'N/A') . '</span>',
            '<div class="text-muted small text-truncate" style="max-width: 250px;">' . esc($conv['message']) . '</div>',
            '<span class="badge rounded-pill bg-info text-white">' . $conv['message_count'] . '</span>',
            '<div class="small text-muted">' . date('d M H:i', strtotime($conv['created_at'])) . '</div>',
            '<a href="/conversations/show/' . $conv['channel_id'] . '/' . $conv['wa_number'] . '" class="btn btn-sm btn-outline-primary px-3 rounded-pill shadow-sm me-1"><i class="bi bi-eye-fill me-1"></i> Lihat</a>'
            . '<button class="btn btn-sm btn-outline-success px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#assignLeadModal" data-channel-id="' . $conv['channel_id'] . '" data-wa-number="' . esc($conv['wa_number']) . '"><i class="bi bi-person-plus me-1"></i> Assign</button>'
        ];
    }
    ?>

    <?= view('_components/data_table', [
        'headers' => $headers,
        'rows' => $rows,
        'empty_message' => 'Belum ada percakapan ditemukan.'
    ]) ?>

    <?php if ($pager): ?>
        <div class="mt-4 d-flex justify-content-center">
            <?= $pager->links('default', 'bootstrap_full') ?>
        </div>
    <?php endif; ?>
</div>

<?= view('tenant/conversations/_assign_modal') ?>