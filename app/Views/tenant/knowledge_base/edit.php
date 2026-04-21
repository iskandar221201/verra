<?= view('_components/page_header', [
    'title' => 'Edit Knowledge Base',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Knowledge Base', 'url' => '/kb'],
        ['label' => 'Edit'],
    ],
]) ?>

<div class="px-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="/kb/update/<?= $item['id'] ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="category" class="form-label fw-bold">Kategori</label>
                            <input type="text" class="form-control" id="category" name="category"
                                value="<?= old('category', $item['category']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Judul</label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="<?= old('title', $item['title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label fw-bold">Konten / Jawaban</label>
                            <textarea class="form-control" id="content" name="content" rows="6"
                                required><?= old('content', $item['content']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label fw-bold">Urutan Tampil</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                        value="<?= old('sort_order', $item['sort_order']) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label fw-bold">Status</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1" <?= $item['is_active'] == 1 ? 'selected' : '' ?>>Aktif</option>
                                        <option value="0" <?= $item['is_active'] == 0 ? 'selected' : '' ?>>Non-aktif
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="/kb" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4"
                                style="background-color: var(--color-primary);">Perbarui Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>