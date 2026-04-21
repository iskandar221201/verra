<?= view('_components/page_header', [
    'title' => 'Tambah Knowledge Base',
    'breadcrumb' => [
        ['label' => 'Dashboard', 'url' => '/dashboard'],
        ['label' => 'Knowledge Base', 'url' => '/kb'],
        ['label' => 'Tambah'],
    ],
]) ?>

<div class="px-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="/kb/store" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="category" class="form-label fw-bold">Kategori</label>
                            <input type="text" class="form-control" id="category" name="category"
                                value="<?= old('category') ?>" placeholder="Contoh: Produk, Layanan, Harga" required>
                            <div class="form-text">Gunakan nama kategori yang sama untuk mengelompokkan data.</div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Judul</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= old('title') ?>"
                                placeholder="Contoh: Jam Operasional" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label fw-bold">Konten / Jawaban</label>
                            <textarea class="form-control" id="content" name="content" rows="6"
                                placeholder="Tuliskan informasi lengkap di sini..."
                                required><?= old('content') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label fw-bold">Urutan Tampil</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                        value="<?= old('sort_order', 0) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <a href="/kb" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4"
                                style="background-color: var(--color-primary);">Simpan Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Tips Knowledge Base</h5>
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Gunakan bahasa yang natural seperti menjawab pertanyaan customer.</li>
                        <li class="mb-2">Kategorikan informasi dengan konsisten agar AI mudah memprosesnya.</li>
                        <li class="mb-2">Informasi di sini akan dikirim sebagai context ke AI (Gemini/Grok) untuk
                            menjawab pesan WhatsApp.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>