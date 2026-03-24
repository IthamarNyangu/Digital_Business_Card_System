<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">QR Results</h1>
    
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2">
        <a href="<?= e(path_url('/admin/import')) ?>" class="btn btn-primary dashboard-btn">Upload New CSV</a>
        <a href="<?= e(path_url('/admin/contacts/create')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Add Single Contact</a>
        <a href="<?= e(path_url('/admin/qrcodes/download-all')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Download ZIP Package</a>
        <a href="<?= e(path_url('/admin/qrcodes/print-report')) ?>" target="_blank" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Print / Save PDF</a>
    </div>
</div>

<?php if (!empty($importSummary)): ?>
    <div class="card border-0 shadow-sm admin-panel-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                <div>
                    <h2 class="h5 mb-1">Latest Import Summary</h2>
                    <p class="text-muted mb-0">Created <?= e((string) $importSummary['created_count']) ?> new contact records from the uploaded CSV. Duplicate identifiers are now reported as failures instead of updating an existing person.</p>
                </div>
                <span class="summary-chip">
                    <?= e((string) $importSummary['success_count']) ?> success / <?= e((string) $importSummary['failure_count']) ?> failed
                </span>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="import-summary-card">
                        <span class="import-summary-label">Processed Columns</span>
                        <strong class="import-summary-value"><?= e((string) $importSummary['processed_columns']) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="import-summary-card">
                        <span class="import-summary-label">Generated</span>
                        <strong class="import-summary-value"><?= e((string) $importSummary['success_count']) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="import-summary-card">
                        <span class="import-summary-label">Skipped Empty</span>
                        <strong class="import-summary-value"><?= e((string) $importSummary['skipped_empty_columns']) ?></strong>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="import-summary-card">
                        <span class="import-summary-label">Failures</span>
                        <strong class="import-summary-value"><?= e((string) $importSummary['failure_count']) ?></strong>
                    </div>
                </div>
            </div>

            <?php if ($importSummary['results'] !== []): ?>
                <div class="table-responsive mb-4">
                    <table class="table align-middle employee-directory-table mb-0">
                        <thead>
                        <tr>
                            <th>Column</th>
                            <th>Name</th>
                            <th>Title</th>
                            <th>Contact</th>
                            <th>Action</th>
                            <th class="text-end">QR</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($importSummary['results'] as $result): ?>
                            <tr>
                                <td><?= e($result['column_label']) ?></td>
                                <td><?= e($result['name']) ?></td>
                                <td>
                                    <div><?= e($result['title']) ?></div>
                                    <div class="small text-muted"><?= e($result['organization']) ?></div>
                                </td>
                                <td>
                                    <div><?= e($result['phone']) ?></div>
                                    <div class="small text-muted"><?= e($result['email']) ?></div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?= $result['action'] === 'created' ? 'text-bg-success' : 'text-bg-primary' ?>">
                                        <?= e(ucfirst($result['action'])) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <a href="<?= e(path_url('/admin/qrcodes/' . $result['id'] . '/preview')) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Preview</a>
                                        <a href="<?= e(path_url('/admin/qrcodes/' . $result['id'] . '/download')) ?>" class="btn btn-sm btn-primary">Download</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($importSummary['failures'] !== []): ?>
                <div class="table-responsive">
                    <table class="table align-middle employee-directory-table mb-0">
                        <thead>
                        <tr>
                            <th>Column</th>
                            <th>Employee</th>
                            <th>Reason</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($importSummary['failures'] as $failure): ?>
                            <tr>
                                <td><?= e($failure['column_label']) ?></td>
                                <td><?= e($failure['name']) ?></td>
                                <td><?= e($failure['message']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm admin-panel-card" id="qrResultsDirectory">
    <div class="card-body">
        <div class="employee-list-toolbar mb-4">
            <form method="GET" action="<?= e(path_url('/admin/results')) ?>" class="employee-search-form" id="employeeSearchForm" autocomplete="off">
                <span class="employee-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="M20 20l-3.5-3.5"></path>
                    </svg>
                </span>
                <input
                    type="search"
                    name="search"
                    value="<?= e($search ?? '') ?>"
                    class="form-control employee-search-input"
                    id="employeeSearchInput"
                    placeholder="Search by name, title, email, phone or city"
                >
            </form>
        </div>

        <?php if ($employees === []): ?>
            <p class="text-muted mb-0">
                <?= ($search ?? '') !== ''
                    ? 'No QR contacts matched your search. Try a different name, title, or location.'
                    : 'No QR contacts are stored yet. Upload a CSV file to generate your first batch.' ?>
            </p>
        <?php else: ?>
            <div class="table-responsive employee-table-wrap">
                <table class="table table-hover align-middle mb-0 employee-directory-table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>QR Preview</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e(trim(implode(' ', array_filter([$employee['honorific'] ?? '', $employee['first_name'], $employee['last_name']])))) ?></div>
                                <div class="small text-muted"><?= e($employee['organization'] ?? config('app.organisation_name')) ?></div>
                            </td>
                            <td><?= e($employee['title'] ?? $employee['position'] ?? '') ?></td>
                            <td>
                                <div><?= e($employee['phone'] ?? '') ?></div>
                                <div class="small text-muted"><?= e($employee['email'] ?? '') ?></div>
                            </td>
                            <td>
                                <div><?= e($employee['city'] ?? $employee['location'] ?? '') ?><?= !empty($employee['country']) ? ', ' . e($employee['country']) : '' ?></div>
                                <div class="small text-muted"><?= e($employee['street'] ?? '') ?></div>
                            </td>
                            <td>
                                <a href="<?= e(path_url('/admin/qrcodes/' . $employee['id'] . '/preview')) ?>" target="_blank" class="qr-thumb-link" aria-label="Preview QR for <?= e(trim(implode(' ', array_filter([$employee['honorific'] ?? '', $employee['first_name'], $employee['last_name']])))) ?>">
                                    <img
                                        src="<?= e(path_url('/admin/qrcodes/' . $employee['id'] . '/preview')) ?>"
                                        alt="QR code for <?= e(trim(implode(' ', array_filter([$employee['honorific'] ?? '', $employee['first_name'], $employee['last_name']])))) ?>"
                                        class="qr-thumb-image"
                                        loading="lazy"
                                    >
                                </a>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <a href="<?= e(path_url('/admin/qrcodes/' . $employee['id'] . '/preview')) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Preview</a>
                                    <a href="<?= e(path_url('/admin/qrcodes/' . $employee['id'] . '/download')) ?>" class="btn btn-sm btn-primary">Download</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $queryBase = [];
            if (($search ?? '') !== '') {
                $queryBase['search'] = $search;
            }

            $previousQuery = $queryBase;
            $previousQuery['page'] = max(1, $page - 1);
            $nextQuery = $queryBase;
            $nextQuery['page'] = min($totalPages, $page + 1);
            $previousUrl = path_url('/admin/results') . '?' . http_build_query($previousQuery) . '#qrResultsDirectory';
            $nextUrl = path_url('/admin/results') . '?' . http_build_query($nextQuery) . '#qrResultsDirectory';
            ?>

            <nav class="entries-pagination" aria-label="QR results pagination">
                <div class="entries-pagination-meta">
                    Showing <?= e((string) (($totalEmployees ?? 0) > 0 ? (($page - 1) * $perPage) + 1 : 0)) ?>
                    -
                    <?= e((string) (($totalEmployees ?? 0) > 0 ? min((($page - 1) * $perPage) + count($employees), $totalEmployees) : 0)) ?>
                    of <?= e((string) $totalEmployees) ?> stored contacts
                </div>

                <?php if (($totalPages ?? 1) > 1): ?>
                    <div class="entries-pagination-controls">
                        <?php if ($page > 1): ?>
                            <a class="entries-pagination-link" href="<?= e($previousUrl) ?>">Back</a>
                        <?php else: ?>
                            <span class="entries-pagination-link is-disabled" aria-disabled="true">Back</span>
                        <?php endif; ?>

                        <span class="entries-pagination-status">
                            Page <?= e((string) $page) ?> of <?= e((string) $totalPages) ?>
                        </span>

                        <?php if ($page < $totalPages): ?>
                            <a class="entries-pagination-link" href="<?= e($nextUrl) ?>">Next</a>
                        <?php else: ?>
                            <span class="entries-pagination-link is-disabled" aria-disabled="true">Next</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
    (() => {
        const searchForm = document.getElementById('employeeSearchForm');
        const searchInput = document.getElementById('employeeSearchInput');

        if (!searchForm || !searchInput) {
            return;
        }

        let debounceId = null;
        let previousValue = searchInput.value;

        searchInput.addEventListener('input', () => {
            window.clearTimeout(debounceId);

            debounceId = window.setTimeout(() => {
                const nextValue = searchInput.value.trim();

                if (nextValue === previousValue.trim()) {
                    return;
                }

                searchForm.submit();
            }, 280);
        });
    })();
</script>
