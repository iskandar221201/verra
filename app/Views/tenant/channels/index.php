<?= view('_components/page_header', [
    'title' => 'WA Channels',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'WA Channels']],
    'action' => ['label' => '+ Tambah Channel', 'url' => '/channels/create'],
]) ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Channel Name</th>
                        <th>WA Number</th>
                        <th>Webhook URL</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($channels)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <?= view('_components/empty_state', ['message' => 'Belum ada WA channel.']) ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($channels as $channel): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold">
                                        <?= esc($channel['name']) ?>
                                    </div>
                                    <small class="text-muted">UID:
                                        <?= esc($channel['uuid']) ?>
                                    </small>
                                </td>
                                <td><code><?= esc($channel['wa_number']) ?></code></td>
                                <td>
                                    <div class="input-group input-group-sm" style="max-width: 300px;">
                                        <input type="text" class="form-control"
                                            value="<?= base_url('webhook/' . $channel['uuid']) ?>" readonly
                                            id="webhook-<?= $channel['uuid'] ?>">
                                        <button class="btn btn-outline-secondary btn-copy" type="button"
                                            data-target="webhook-<?= $channel['uuid'] ?>" title="Copy URL">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <?= view('_components/badge_status', [
                                        'status' => $channel['is_active'] ? 'active' : 'inactive',
                                        'label' => $channel['is_active'] ? 'Active' : 'Inactive'
                                    ]) ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="/channels/edit/<?= $channel['id'] ?>" class="btn btn-sm btn-outline-primary"
                                            title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/channels/delete/<?= $channel['id'] ?>" class="btn btn-sm btn-outline-danger"
                                            title="Hapus"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus channel ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-copy').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            input.select();
            document.execCommand('copy');

            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check-lg"></i>';
            this.classList.replace('btn-outline-secondary', 'btn-success');

            setTimeout(() => {
                this.innerHTML = originalHtml;
                this.classList.replace('btn-success', 'btn-outline-secondary');
            }, 2000);
        });
    });
</script>