<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Bulk Import CSV</h1>
        <p class="text-muted mb-0">Upload a transposed CSV and the system will generate one MECARD QR code per employee column while blocking duplicate phone numbers, emails, and employee numbers.</p>
    </div>
    <a href="<?= e(path_url('/admin/results')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">View Results</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm admin-panel-card h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                    <div>
                        <h2 class="h5 mb-1">Upload CSV File</h2>
                        <p class="text-muted mb-0">Column A must contain the field names. Every column after A becomes one employee contact and one QR code image, but duplicate identifiers are rejected instead of replacing an existing person.</p>
                    </div>
                    <a href="<?= e(path_url('/admin/contacts/create')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Add One Contact</a>
                </div>

                <form method="POST" action="<?= e(path_url('/admin/import')) ?>" enctype="multipart/form-data" class="import-form">
                    <?= csrf_field() ?>
                    <label for="csvFile" class="form-label fw-semibold">Transposed CSV file</label>
                    <input
                        type="file"
                        name="csv_file"
                        id="csvFile"
                        class="form-control import-file-input<?= validation_error('csv_file') ? ' is-invalid' : '' ?>"
                        accept=".csv,text/csv"
                        required
                    >
                    <?php if (validation_error('csv_file')): ?>
                        <div class="invalid-feedback d-block"><?= e(validation_error('csv_file')) ?></div>
                    <?php endif; ?>

                    <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                        <button type="submit" class="btn btn-primary dashboard-btn">Generate QR Codes</button>
                        <a href="<?= e(path_url('/downloads/sample_contacts_transposed.csv')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Download Sample CSV</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm admin-panel-card h-100">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Expected CSV Structure</h2>
                <p class="text-muted small mb-3">Save the sheet as <strong>CSV UTF-8 (Comma delimited)</strong> in Excel when possible.</p>

                <?php if (!empty($optionalFields)): ?>
                    <p class="text-muted small mb-3">Optional row supported: <?= e(implode(', ', $optionalFields)) ?>.</p>
                <?php endif; ?>

                <ul class="list-unstyled import-field-list mb-4">
                    <?php foreach ($requiredFields as $field): ?>
                        <li><?= e($field) ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="sample-csv-shell">
<pre class="sample-csv-preview mb-0">EmployeeNumber,RTCZ001,RTCZ002
LastName,Bwalya,Malama
FirstName,Mary,Beatrice
Organization,Right to Care Zambia,Right to Care Zambia
Title,Program Manager,Admin Officer
Phone,+260 977 123 100,0972448338
Email,mary.bwalya@righttocare.org.zm,beatrice@righttocare-zambia.org
Street,Plot 12 Addis Ababa Drive,Plot 12 Addis Ababa Drive
City,Lusaka,Lusaka
Region,Lusaka Province,Lusaka Province
PostalCode,10101,10101
Country,Zambia,Zambia</pre>
                </div>
            </div>
        </div>
    </div>
</div>
