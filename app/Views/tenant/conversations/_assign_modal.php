<!-- Lead Assignment Modal -->
<div class="modal fade" id="assignLeadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Assign Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">Customer</label>
                    <div class="fw-bold" id="assignCustomerWa"></div>
                </div>
                <input type="hidden" id="assignChannelId">
                <input type="hidden" id="assignWaNumber">
                <div class="mb-3">
                    <label class="form-label fw-bold">Assign ke Salesperson</label>
                    <select class="form-select" id="assignSalesperson">
                        <option value="">Memuat...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnAssignLead">
                    <i class="bi bi-check-lg me-1"></i> Assign
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('assignLeadModal');
        const selectEl = document.getElementById('assignSalesperson');
        const btnAssign = document.getElementById('btnAssignLead');
        let salespersonsLoaded = false;

        // Populate modal on open
        modal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('assignChannelId').value = btn.getAttribute('data-channel-id');
            document.getElementById('assignWaNumber').value = btn.getAttribute('data-wa-number');
            document.getElementById('assignCustomerWa').textContent = btn.getAttribute('data-wa-number');

            // Load salespersons if not cached
            if (!salespersonsLoaded) {
                fetch('<?= base_url('lead-assign/salespersons') ?>')
                    .then(r => r.json())
                    .then(data => {
                        selectEl.innerHTML = '<option value="">-- Pilih Salesperson --</option>';
                        if (data.salespersons) {
                            data.salespersons.forEach(sp => {
                                selectEl.innerHTML += `<option value="${sp.id}">${sp.name} (${sp.wa_number})</option>`;
                            });
                        }
                        salespersonsLoaded = true;
                    })
                    .catch(() => {
                        selectEl.innerHTML = '<option value="">Gagal memuat</option>';
                    });
            }
        });

        // Submit assignment
        btnAssign.addEventListener('click', function () {
            const channelId = document.getElementById('assignChannelId').value;
            const waNumber = document.getElementById('assignWaNumber').value;
            const salespersonId = selectEl.value;

            if (!salespersonId) {
                alert('Pilih salesperson terlebih dahulu.');
                return;
            }

            btnAssign.disabled = true;
            btnAssign.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Assigning...';

            const formData = new FormData();
            formData.append('channel_id', channelId);
            formData.append('wa_number', waNumber);
            formData.append('salesperson_id', salespersonId);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('<?= base_url('lead-assign/assign') ?>', {
                method: 'POST',
                body: formData,
            })
                .then(r => r.json())
                .then(data => {
                    btnAssign.disabled = false;
                    btnAssign.innerHTML = '<i class="bi bi-check-lg me-1"></i> Assign';

                    if (data.success) {
                        const modalInstance = bootstrap.Modal.getInstance(modal);
                        modalInstance.hide();

                        // Show success toast
                        const toast = document.createElement('div');
                        toast.className = 'position-fixed bottom-0 end-0 p-3';
                        toast.style.zIndex = '11';
                        toast.innerHTML = `
                    <div class="toast show align-items-center text-white bg-success border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body"><i class="bi bi-check-circle me-1"></i> ${data.message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>`;
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 4000);
                    } else {
                        alert(data.message || 'Gagal assign lead.');
                    }
                })
                .catch(() => {
                    btnAssign.disabled = false;
                    btnAssign.innerHTML = '<i class="bi bi-check-lg me-1"></i> Assign';
                    alert('Terjadi kesalahan.');
                });
        });
    })();
</script>