<?php $callNumber = preg_replace('/[^0-9+]/', '', $employee['phone']) ?: $employee['phone']; ?>

<div class="public-card card border-0 shadow-lg overflow-hidden">
    <div class="card-body p-4 p-md-5">
        <p class="text-uppercase small fw-semibold text-muted mb-2">Work Contact</p>
        <h1 class="display-6 mb-2"><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></h1>
        <p class="lead mb-1"><?= e($employee['position']) ?></p>
        <p class="text-muted mb-4"><?= e(config('app.organisation_name')) ?></p>

        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <div class="detail-tile h-100">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Phone</div>
                    <div class="fw-semibold"><?= e($employee['phone']) ?></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="detail-tile h-100">
                    <div class="small text-muted text-uppercase fw-semibold mb-1">Email</div>
                    <div class="fw-semibold"><?= e($employee['email']) ?></div>
                </div>
            </div>
            <?php if (!empty($employee['department'])): ?>
                <div class="col-sm-6">
                    <div class="detail-tile h-100">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Department</div>
                        <div class="fw-semibold"><?= e($employee['department']) ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($employee['location'])): ?>
                <div class="col-sm-6">
                    <div class="detail-tile h-100">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Location</div>
                        <div class="fw-semibold"><?= e($employee['location']) ?></div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-grid gap-2">
            <a href="<?= e(path_url('/c/' . $employee['public_token'] . '/vcf')) ?>" class="btn btn-primary btn-lg">Save Contact</a>
            <a href="tel:<?= e($callNumber) ?>" class="btn btn-outline-dark btn-lg">Call</a>
            <a href="mailto:<?= e($employee['email']) ?>" class="btn btn-outline-dark btn-lg">Email</a>
        </div>
    </div>
</div>
