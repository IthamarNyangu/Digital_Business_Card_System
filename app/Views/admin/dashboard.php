<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">QR Contact Generator</h1>
        
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2">
        <a href="<?= e(path_url('/admin/import')) ?>" class="btn btn-primary dashboard-btn">Import CSV</a>
        <a href="<?= e(path_url('/admin/contacts/create')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Add Single Contact</a>
        <a href="<?= e(path_url('/admin/results')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">View Results</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body dashboard-stat-body">
                <div>
                    <p class="dashboard-stat-label mb-1">Stored Contacts</p>
                    <h2 class="dashboard-stat-value mb-0"><?= e((string) $totalEmployees) ?></h2>
                </div>
                <span class="dashboard-stat-chip dashboard-stat-chip-total">Directory</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body dashboard-stat-body">
                <div>
                    <p class="dashboard-stat-label mb-1">QR Images Ready</p>
                    <h2 class="dashboard-stat-value dashboard-stat-value-active mb-0"><?= e((string) $readyQrs) ?></h2>
                </div>
                <span class="dashboard-stat-chip dashboard-stat-chip-active">PNG</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body dashboard-stat-body">
                <div>
                    <p class="dashboard-stat-label mb-1">Organizations In Data</p>
                    <h2 class="dashboard-stat-value mb-0"><?= e((string) $organizationCount) ?></h2>
                </div>
                <span class="dashboard-stat-chip dashboard-stat-chip-total">CSV</span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm admin-panel-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 gap-3 flex-wrap">
            <div>
                <h2 class="recent-employees-title mb-1">Recent QR Contacts</h2>
                <p class="text-muted mb-0 small">The newest created or refreshed contacts appear here after each import.</p>
            </div>
            <a href="<?= e(path_url('/admin/results')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Open Results</a>
        </div>

        <?php if ($recentEmployees === []): ?>
            <p class="text-muted mb-0">No contacts have been imported yet. Start with a CSV file to generate your first batch of QR codes.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0 employee-directory-table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Organization</th>
                        <th>Updated</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentEmployees as $employee): ?>
                        <tr>
                            <td><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                            <td><?= e($employee['title']) ?></td>
                            <td><?= e($employee['organization']) ?></td>
                            <td><?= e(date('d M Y H:i', strtotime($employee['updated_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
