<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg border-0 admin-card">
            <div class="card-body p-4 p-lg-5">
                <p class="text-uppercase text-muted small fw-semibold mb-2">Admin Portal</p>
                <h1 class="h3 mb-3">Digital Business Cards</h1>
                <p class="text-muted mb-4">Sign in to manage employee contact cards and QR codes.</p>

                <form method="POST" action="<?= e(url('/admin/login')) ?>" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control<?= validation_error('username') ? ' is-invalid' : '' ?>"
                            value="<?= e(old('username')) ?>"
                            autocomplete="username"
                        >
                        <?php if (validation_error('username')): ?>
                            <div class="invalid-feedback"><?= e(validation_error('username')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control<?= validation_error('password') ? ' is-invalid' : '' ?>"
                            autocomplete="current-password"
                        >
                        <?php if (validation_error('password')): ?>
                            <div class="invalid-feedback"><?= e(validation_error('password')) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
