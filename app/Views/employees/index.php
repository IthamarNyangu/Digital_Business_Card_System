<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <p class="text-uppercase text-muted small fw-semibold mb-2">Directory</p>
        <h1 class="h2 mb-1">Employees</h1>
        <p class="text-muted mb-0">Each employee gets a random public token, a mobile contact page, and a downloadable QR code.</p>
    </div>
    <a href="<?= e(url('/admin/employees/create')) ?>" class="btn btn-primary">Add Employee</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php if ($employees === []): ?>
            <p class="text-muted mb-0">No employees found. Add the first employee to generate a public contact page.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Public Link</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></div>
                                <?php if (!empty($employee['department'])): ?>
                                    <div class="small text-muted"><?= e($employee['department']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= e($employee['position']) ?></td>
                            <td>
                                <div><?= e($employee['phone']) ?></div>
                                <div class="small text-muted"><?= e($employee['email']) ?></div>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?= $employee['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                    <?= e(ucfirst($employee['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= e(url('/c/' . $employee['public_token'])) ?>" target="_blank" class="small">
                                    /c/<?= e($employee['public_token']) ?>
                                </a>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <a href="<?= e(url('/admin/employees/' . $employee['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="<?= e(url('/admin/employees/' . $employee['id'] . '/qr')) ?>" class="btn btn-sm btn-outline-dark">Download QR</a>
                                    <?php if ($employee['status'] === 'active'): ?>
                                        <form method="POST" action="<?= e(url('/admin/employees/' . $employee['id'] . '/deactivate')) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Mark this employee as inactive?');">Deactivate</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
