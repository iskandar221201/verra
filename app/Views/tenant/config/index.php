<?= view('_components/page_header', [
    'title' => 'Konfigurasi AI',
    'breadcrumb' => [['label' => 'Dashboard', 'url' => '/dashboard'], ['label' => 'Konfigurasi AI']],
]) ?>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Pengaturan AI Provider</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('config/update') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Active AI Provider</label>
                        <div class="d-flex gap-3">
                            <div class="form-check card-select p-3 border rounded <?= ($config['ai_provider'] == 'gemini') ? 'border-primary bg-light' : '' ?>"
                                style="flex: 1; cursor: pointer;">
                                <input class="form-check-input d-none" type="radio" name="ai_provider"
                                    id="provider_gemini" value="gemini" <?= ($config['ai_provider'] == 'gemini') ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="provider_gemini">
                                    <div class="d-flex align-items-center">
                                        <div class="provider-icon bg-primary text-white rounded p-2 me-3">
                                            <i class="bi bi-robot"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">Google Gemini</div>
                                            <small class="text-muted">Fast & reliable</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check card-select p-3 border rounded <?= ($config['ai_provider'] == 'grok') ? 'border-primary bg-light' : '' ?>"
                                style="flex: 1; cursor: pointer;">
                                <input class="form-check-input d-none" type="radio" name="ai_provider"
                                    id="provider_grok" value="grok" <?= ($config['ai_provider'] == 'grok') ? 'checked' : '' ?>>
                                <label class="form-check-label w-100" for="provider_grok">
                                    <div class="d-flex align-items-center">
                                        <div class="provider-icon bg-dark text-white rounded p-2 me-3">
                                            <i class="bi bi-stars"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">xAI Grok</div>
                                            <small class="text-muted">Smart & conversational</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="gemini_model" class="form-label fw-bold">Gemini Model</label>
                            <?php if (!empty($gemini_models)): ?>
                                <select class="form-select" name="gemini_model" id="gemini_model">
                                    <?php foreach ($gemini_models as $model): ?>
                                        <option value="<?= esc($model) ?>" <?= ($config['gemini_model'] == $model) ? 'selected' : '' ?>>
                                            <?= esc($model) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control" name="gemini_model" id="gemini_model"
                                    value="<?= esc($config['gemini_model']) ?>" placeholder="e.g. gemini-1.5-flash">
                                <small class="text-danger small">Gagal mengambil daftar model atau API Key belum diset.</small>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-1">Model ID dari Google AI Studio</small>
                        </div>
                        <div class="col-md-6">
                            <label for="grok_model" class="form-label fw-bold">Grok Model</label>
                            <?php if (!empty($grok_models)): ?>
                                <select class="form-select" name="grok_model" id="grok_model">
                                    <?php foreach ($grok_models as $model): ?>
                                        <option value="<?= esc($model) ?>" <?= ($config['grok_model'] == $model) ? 'selected' : '' ?>>
                                            <?= esc($model) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control" name="grok_model" id="grok_model"
                                    value="<?= esc($config['grok_model']) ?>" placeholder="e.g. grok-beta">
                                <small class="text-danger small">Gagal mengambil daftar model atau API Key belum diset.</small>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-1">Model ID dari x.ai Console</small>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label for="system_prompt" class="form-label fw-bold">System Prompt</label>
                        <textarea class="form-control" name="system_prompt" id="system_prompt" rows="6"
                            placeholder="Instruksi dasar untuk AI..."><?= esc($config['system_prompt']) ?></textarea>
                        <small class="text-muted">Akan digabungkan dengan Knowledge Base saat memproses pesan.</small>
                    </div>

                    <div class="mb-4">
                        <label for="max_history" class="form-label fw-bold">Max Conversation History</label>
                        <div class="input-group" style="max-width: 200px;">
                            <input type="number" class="form-control" name="max_history" id="max_history"
                                value="<?= esc($config['max_history']) ?>" min="1" max="50">
                            <span class="input-group-text">turns</span>
                        </div>
                        <small class="text-muted">Jumlah pesan terakhir yang dikirim ke AI untuk konteks.</small>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i> Simpan Konfigurasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-info"></i>Tips Prompting</h6>
                <p class="small text-muted mb-0">
                    Berikan instruksi yang jelas seperti:
                    "Anda adalah customer service dari Toko ABC. Gunakan bahasa yang sopan. Jika tidak tahu, arahkan ke
                    admin."
                </p>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-key me-2 text-warning"></i>API Keys</h6>
                <p class="small text-muted mb-3">
                    Pastikan Anda sudah menambahkan API Key untuk provider yang aktif agar AI bisa menjawab pesan.
                </p>
                <a href="<?= base_url('api-keys') ?>" class="btn btn-outline-primary btn-sm w-100">
                    Kelola API Keys <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .card-select:hover {
        border-color: var(--bs-primary) !important;
    }

    .provider-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
</style>

<script>
    document.querySelectorAll('.card-select').forEach(card => {
        card.addEventListener('click', function () {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;

            // Remove selection from others
            document.querySelectorAll('.card-select').forEach(c => {
                c.classList.remove('border-primary', 'bg-light');
            });

            // Add to this
            this.classList.add('border-primary', 'bg-light');
        });
    });
</script>