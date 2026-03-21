<section class="login-panel">
    <div class="logo-shell">
        <img src="<?= e(path_url('/media/logo.png')) ?>" alt="Right to Care Zambia" class="logo-image">
    </div>

    <p class="login-eyebrow">Admin Portal</p>
    <h1 class="title">Digital Business Cards</h1>
    <p class="login-copy">Sign in to manage employee contact pages, vCards, and QR codes.</p>

    <form method="POST" action="<?= e(path_url('/admin/login')) ?>" novalidate>
        <?= csrf_field() ?>

        <div class="field">
            <label for="username">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                class="<?= validation_error('username') ? 'is-invalid' : '' ?>"
                value="<?= e(old('username')) ?>"
                autocomplete="username"
            >
            <?php if (validation_error('username')): ?>
                <div class="invalid-feedback"><?= e(validation_error('username')) ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                class="<?= validation_error('password') ? 'is-invalid' : '' ?>"
                autocomplete="current-password"
            >
            <?php if (validation_error('password')): ?>
                <div class="invalid-feedback"><?= e(validation_error('password')) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary btn-login">Login</button>
    </form>
</section>
