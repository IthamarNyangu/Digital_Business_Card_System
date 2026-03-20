<div class="card border-0 shadow-lg public-card">
    <div class="card-body p-4 p-md-5 text-center">
        <p class="text-uppercase small fw-semibold text-muted mb-2">Contact Status</p>
        <h1 class="h2 mb-3">This contact is no longer active</h1>
        <p class="text-muted mb-0">
            The staff member record for
            <strong><?= e($employee['first_name'] . ' ' . $employee['last_name']) ?></strong>
            is no longer active at <?= e(config('app.organisation_name')) ?>.
        </p>
    </div>
</div>
