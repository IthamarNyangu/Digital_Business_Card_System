<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-0">Manage employee contact pages, QR codes, and public vCards.</p>
    </div>
    <a href="<?= e(path_url('/admin/employees/create')) ?>" class="btn btn-primary dashboard-btn">Add Employee</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body dashboard-stat-body">
                <div>
                    <p class="dashboard-stat-label mb-1">Total Employees</p>
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
                    <p class="dashboard-stat-label mb-1">Active</p>
                    <h2 class="dashboard-stat-value dashboard-stat-value-active mb-0"><?= e((string) $activeEmployees) ?></h2>
                </div>
                <span class="dashboard-stat-chip dashboard-stat-chip-active">Live</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body dashboard-stat-body">
                <div>
                    <p class="dashboard-stat-label mb-1">Inactive</p>
                    <h2 class="dashboard-stat-value dashboard-stat-value-inactive mb-0"><?= e((string) $inactiveEmployees) ?></h2>
                </div>
                <span class="dashboard-stat-chip dashboard-stat-chip-inactive">Archived</span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="recent-employees-title mb-0">Recently Added Employees</h2>
            <a href="<?= e(path_url('/admin/employees')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Edit Records</a>
        </div>

        <?php if ($recentEmployees === []): ?>
            <p class="text-muted mb-0">No employees have been added yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentEmployees as $employee): ?>
                        <tr>
                            <td><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></td>
                            <td><?= e($employee['position']) ?></td>
                            <td>
                                <span class="badge rounded-pill <?= $employee['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                    <?= e(ucfirst($employee['status'])) ?>
                                </span>
                            </td>
                            <td><?= e(date('d M Y', strtotime($employee['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
