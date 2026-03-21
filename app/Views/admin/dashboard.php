<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <p class="text-uppercase text-muted small fw-semibold mb-2">Overview</p>
        <h1 class="h2 mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-0">Manage employee contact pages, QR codes, and public vCards.</p>
    </div>
    <a href="<?= e(path_url('/admin/employees/create')) ?>" class="btn btn-primary">Add Employee</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-2">Total Employees</p>
                <h2 class="display-6 mb-0"><?= e((string) $totalEmployees) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-2">Active</p>
                <h2 class="display-6 mb-0 text-success"><?= e((string) $activeEmployees) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-stat h-100 border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-2">Inactive</p>
                <h2 class="display-6 mb-0 text-danger"><?= e((string) $inactiveEmployees) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0">Recently Added Employees</h2>
            <a href="<?= e(path_url('/admin/employees')) ?>" class="btn btn-sm btn-outline-primary">View All</a>
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
