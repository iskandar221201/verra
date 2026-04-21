<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-bold">
                    <tr>
                        <?php foreach ($headers as $header): ?>
                            <th class="px-4 py-3 border-0">
                                <?= $header ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?= count($headers) ?>" class="text-center py-5">
                                <p class="text-muted mb-0">
                                    <?= $empty_message ?? 'No data found' ?>
                                </p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td class="px-4 py-3">
                                        <?= $cell ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>