<div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Add Single Contact</h1>
        <p class="text-muted mb-0">Use this form for one-off additions. If the email, phone, or employee number already exists, the save will be blocked so another employee is not overwritten by mistake.</p>
    </div>
    <div class="d-flex flex-column flex-sm-row gap-2">
        <a href="<?= e(path_url('/admin/import')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Bulk Import CSV</a>
        <a href="<?= e(path_url('/admin/results')) ?>" class="btn btn-primary dashboard-btn">View Results</a>
    </div>
</div>

<div class="card border-0 shadow-sm admin-panel-card">
    <div class="card-body p-4 p-lg-5">
        <form method="POST" action="<?= e(path_url('/admin/contacts/create')) ?>" class="row g-4">
            <?= csrf_field() ?>

            <?php if ($supportsEmployeeNumber): ?>
                <div class="col-md-6">
                    <label for="employee_number" class="form-label fw-semibold">Employee Number</label>
                    <input
                        type="text"
                        id="employee_number"
                        name="employee_number"
                        class="form-control<?= validation_error('employee_number') ? ' is-invalid' : '' ?>"
                        value="<?= e(old('employee_number', $employee['employee_number'] ?? '')) ?>"
                    >
                    <div class="form-text">Optional internal identifier. It is not included in the QR code when scanned.</div>
                    <?php if (validation_error('employee_number')): ?>
                        <div class="invalid-feedback"><?= e(validation_error('employee_number')) ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="col-md-6">
                <label for="first_name" class="form-label fw-semibold">First Name</label>
                <input
                    type="text"
                    id="first_name"
                    name="first_name"
                    class="form-control<?= validation_error('first_name') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('first_name', $employee['first_name'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('first_name')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('first_name')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="last_name" class="form-label fw-semibold">Last Name</label>
                <input
                    type="text"
                    id="last_name"
                    name="last_name"
                    class="form-control<?= validation_error('last_name') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('last_name', $employee['last_name'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('last_name')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('last_name')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="organization" class="form-label fw-semibold">Organization</label>
                <input
                    type="text"
                    id="organization"
                    name="organization"
                    class="form-control<?= validation_error('organization') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('organization', $employee['organization'] ?? config('app.organisation_name'))) ?>"
                    required
                >
                <?php if (validation_error('organization')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('organization')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="title" class="form-label fw-semibold">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control<?= validation_error('title') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('title', $employee['title'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('title')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('title')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label fw-semibold">Phone</label>
                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control<?= validation_error('phone') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('phone', $employee['phone'] ?? '')) ?>"
                    required
                >
                <div class="form-text">Must be unique. If it already exists, the save will be rejected.</div>
                <?php if (validation_error('phone')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('phone')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control<?= validation_error('email') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('email', $employee['email'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('email')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('email')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <label for="street" class="form-label fw-semibold">Street</label>
                <input
                    type="text"
                    id="street"
                    name="street"
                    class="form-control<?= validation_error('street') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('street', $employee['street'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('street')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('street')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <label for="city" class="form-label fw-semibold">City</label>
                <input
                    type="text"
                    id="city"
                    name="city"
                    class="form-control<?= validation_error('city') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('city', $employee['city'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('city')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('city')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <label for="region" class="form-label fw-semibold">Region</label>
                <input
                    type="text"
                    id="region"
                    name="region"
                    class="form-control<?= validation_error('region') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('region', $employee['region'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('region')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('region')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <label for="postal_code" class="form-label fw-semibold">Postal Code</label>
                <input
                    type="text"
                    id="postal_code"
                    name="postal_code"
                    class="form-control<?= validation_error('postal_code') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('postal_code', $employee['postal_code'] ?? '')) ?>"
                    required
                >
                <?php if (validation_error('postal_code')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('postal_code')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="country" class="form-label fw-semibold">Country</label>
                <input
                    type="text"
                    id="country"
                    name="country"
                    class="form-control<?= validation_error('country') ? ' is-invalid' : '' ?>"
                    value="<?= e(old('country', $employee['country'] ?? 'Zambia')) ?>"
                    required
                >
                <?php if (validation_error('country')): ?>
                    <div class="invalid-feedback"><?= e(validation_error('country')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <button type="submit" class="btn btn-primary dashboard-btn">Save Contact and Generate QR</button>
                    <a href="<?= e(path_url('/admin/results')) ?>" class="btn btn-primary dashboard-btn dashboard-btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
