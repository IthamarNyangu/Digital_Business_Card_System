<section class="print-report-shell">
    <div class="print-report-header">
        <div>
            <p class="print-report-kicker">RTCZ Export</p>
            <h1 class="print-report-title">Bulk QR Contact Report</h1>
            <p class="print-report-meta mb-0">Generated on <?= e($generatedAt) ?>. Use your browser's print dialog to save this page as a PDF.</p>
        </div>
        <div class="print-report-actions">
            <button type="button" class="btn btn-primary" onclick="window.print()">Print This Report</button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="print-report-grid">
        <?php foreach ($employees as $employee): ?>
            <article class="print-card">
                <div class="print-card-top">
                    <div>
                        <h2 class="print-card-name"><?= e(trim(implode(' ', array_filter([$employee['honorific'] ?? '', $employee['first_name'], $employee['last_name']])))) ?></h2>
                        <p class="print-card-role"><?= e($employee['title']) ?></p>
                        <p class="print-card-org"><?= e($employee['organization']) ?></p>
                    </div>
                    <img
                        src="<?= e(path_url('/admin/qrcodes/' . $employee['id'] . '/preview')) ?>"
                        alt="QR code for <?= e($employee['first_name'] . ' ' . $employee['last_name']) ?>"
                        class="print-card-qr"
                    >
                </div>

                <div class="print-card-details">
                    <?php if (!empty($employee['employee_number'])): ?>
                        <div><strong>Employee Number:</strong> <?= e($employee['employee_number']) ?></div>
                    <?php endif; ?>
                    <div><strong>Phone:</strong> <?= e($employee['phone']) ?></div>
                    <div><strong>Email:</strong> <?= e($employee['email']) ?></div>
                    <div><strong>Address:</strong> <?= e($employee['street']) ?>, <?= e($employee['city']) ?>, <?= e($employee['region']) ?>, <?= e($employee['postal_code']) ?>, <?= e($employee['country']) ?></div>
                    <div><strong>QR File:</strong> <?= e(basename((string) $employee['qr_code_path'])) ?></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
