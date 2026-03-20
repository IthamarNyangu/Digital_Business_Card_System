<?php
$employeeData = [
    'first_name' => old('first_name', $employee['first_name'] ?? ''),
    'last_name' => old('last_name', $employee['last_name'] ?? ''),
    'position' => old('position', $employee['position'] ?? ''),
    'department' => old('department', $employee['department'] ?? ''),
    'phone' => old('phone', $employee['phone'] ?? ''),
    'email' => old('email', $employee['email'] ?? ''),
    'location' => old('location', $employee['location'] ?? ''),
];
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="mb-4">
                    <p class="text-uppercase text-muted small fw-semibold mb-2">Employee Details</p>
                    <h1 class="h3 mb-1"><?= e($formTitle) ?></h1>
                    <p class="text-muted mb-0">The public contact page and vCard are generated from this one employee record.</p>
                </div>

                <form method="POST" action="<?= e(url($action)) ?>" novalidate>
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control<?= validation_error('first_name') ? ' is-invalid' : '' ?>" value="<?= e($employeeData['first_name']) ?>">
                            <?php if (validation_error('first_name')): ?>
                                <div class="invalid-feedback"><?= e(validation_error('first_name')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control<?= validation_error('last_name') ? ' is-invalid' : '' ?>" value="<?= e($employeeData['last_name']) ?>">
                            <?php if (validation_error('last_name')): ?>
                                <div class="invalid-feedback"><?= e(validation_error('last_name')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="position" class="form-label">Position</label>
                            <input type="text" id="position" name="position" class="form-control<?= validation_error('position') ? ' is-invalid' : '' ?>" value="<?= e($employeeData['position']) ?>">
                            <?php if (validation_error('position')): ?>
                                <div class="invalid-feedback"><?= e(validation_error('position')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="department" class="form-label">Department</label>
                            <input type="text" id="department" name="department" class="form-control<?= validation_error('department') ? ' is-invalid' : '' ?>" value="<?= e($employeeData['department']) ?>">
                            <?php if (validation_error('department')): ?>
                                <div class="invalid-feedback"><?= e(validation_error('department')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control<?= validation_error('phone') ? ' is-invalid' : '' ?>" value="<?= e($employeeData['phone']) ?>">
                            <?php if (validation_error('phone')): ?>
                                <div class="invalid-feedback"><?= e(validation_error('phone')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control<?= validation_error('email') ? ' is-invalid' : '' ?>" value="<?= e($employeeData['email']) ?>">
                            <?php if (validation_error('email')): ?>
                                <div class="invalid-feedback"><?= e(validation_error('email')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" id="location" name="location" class="form-control<?= validation_error('location') ? ' is-invalid' : '' ?>" value="<?= e($employeeData['location']) ?>">
                            <?php if (validation_error('location')): ?>
                                <div class="invalid-feedback"><?= e(validation_error('location')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
                        <a href="<?= e(url('/admin/employees')) ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Public Card Info</h2>
                <?php if ($isEdit): ?>
                    <dl class="mb-0 small">
                        <dt class="text-muted mb-1">Public Token</dt>
                        <dd class="mb-3 font-monospace"><?= e($employee['public_token']) ?></dd>

                        <dt class="text-muted mb-1">Status</dt>
                        <dd class="mb-3">
                            <span class="badge rounded-pill <?= $employee['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                <?= e(ucfirst($employee['status'])) ?>
                            </span>
                        </dd>

                        <dt class="text-muted mb-1">Public URL</dt>
                        <dd class="mb-3">
                            <a href="<?= e(url('/c/' . $employee['public_token'])) ?>" target="_blank">
                                <?= e(url('/c/' . $employee['public_token'])) ?>
                            </a>
                        </dd>
                    </dl>

                    <?php if ($employee['status'] === 'active'): ?>
                        <a href="<?= e(url('/admin/employees/' . $employee['id'] . '/qr')) ?>" class="btn btn-outline-dark w-100">Download QR Code</a>
                    <?php else: ?>
                        <p class="small text-muted mb-0">Inactive employees keep their record, but the public contact page shows an inactive message.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-0">A random public token and QR code will be created automatically after you save this employee.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
