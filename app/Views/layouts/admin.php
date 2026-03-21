<?php

use App\Core\Auth;
use App\Core\Csrf;

$isGuest = !Auth::check();
$sessionTimeoutSeconds = max(60, (int) config('app.session_timeout_seconds', 900));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? 'Admin') . ' | ' . config('app.app_name')) ?></title>
    <link rel="icon" type="image/png" href="<?= e(path_url('/media/logo1.png')) ?>">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(path_url('/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="admin-body<?= $isGuest ? ' admin-guest-body' : '' ?>">
<?php if (!$isGuest): ?>
    <nav class="navbar navbar-expand-lg app-navbar mb-4">
        <div class="container">
            <a class="navbar-brand fw-semibold d-inline-flex align-items-center gap-2" href="<?= e(path_url('/admin/dashboard')) ?>">
                <img src="<?= e(path_url('/media/logo1.png')) ?>" alt="Right to Care Zambia" class="brand-logo">
                <span class="brand-lockup">
                    <span class="brand-kicker">RTCZ</span>
                    <span class="brand-label">Digital Business Card System</span>
                </span>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a class="nav-link text-white-50" href="<?= e(path_url('/admin/dashboard')) ?>">Dashboard</a>
                <a class="nav-link text-white-50" href="<?= e(path_url('/admin/employees')) ?>">Employees</a>
                <form method="POST" action="<?= e(path_url('/admin/logout')) ?>" class="d-inline" id="adminLogoutForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="logout_reason" value="" id="adminLogoutReason">
                    <button type="submit" class="btn btn-sm btn-outline-light navbar-btn">Logout</button>
                </form>
            </div>
        </div>
    </nav>
<?php endif; ?>

<main class="<?= $isGuest ? 'login-main' : 'container pb-5' ?>">
    <?php if ($successMessage): ?>
        <div class="alert alert-success shadow-sm"><?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger shadow-sm"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <?php if ($warningMessage): ?>
        <div class="alert alert-warning shadow-sm"><?= e($warningMessage) ?></div>
    <?php endif; ?>

    <?= $content ?>
</main>
<?php if (!$isGuest && $sessionTimeoutSeconds > 0): ?>
    <script>
        (() => {
            const timeoutSeconds = <?= (int) $sessionTimeoutSeconds ?>;
            const timeoutMs = timeoutSeconds * 1000;
            const heartbeatIntervalMs = Math.max(60000, Math.floor(timeoutMs / 3));
            const logoutForm = document.getElementById('adminLogoutForm');
            const logoutReason = document.getElementById('adminLogoutReason');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const heartbeatUrl = <?= json_encode(path_url('/admin/session/ping')) ?>;
            const loginUrl = <?= json_encode(path_url('/admin/login')) ?>;

            if (!logoutForm || !logoutReason || !csrfToken) {
                return;
            }

            let idleTimerId = 0;
            let heartbeatTimerId = 0;
            let lastActivityAt = Date.now();
            let heartbeatInFlight = false;

            const scheduleIdleLogout = () => {
                window.clearTimeout(idleTimerId);
                idleTimerId = window.setTimeout(() => {
                    logoutReason.value = 'timeout';
                    logoutForm.submit();
                }, timeoutMs);
            };

            const scheduleHeartbeat = () => {
                window.clearTimeout(heartbeatTimerId);

                heartbeatTimerId = window.setTimeout(() => {
                    if ((Date.now() - lastActivityAt) >= timeoutMs) {
                        return;
                    }

                    if (heartbeatInFlight || document.hidden) {
                        scheduleHeartbeat();
                        return;
                    }

                    heartbeatInFlight = true;

                    const body = new URLSearchParams();
                    body.set('_token', csrfToken);

                    fetch(heartbeatUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: body.toString(),
                        keepalive: true
                    })
                        .then((response) => {
                            if (response.status === 401 || response.redirected) {
                                window.location.assign(loginUrl);
                            }
                        })
                        .catch(() => {
                        })
                        .finally(() => {
                            heartbeatInFlight = false;
                            scheduleHeartbeat();
                        });
                }, heartbeatIntervalMs);
            };

            const recordActivity = () => {
                const now = Date.now();

                if ((now - lastActivityAt) < 1000) {
                    lastActivityAt = now;
                    scheduleIdleLogout();
                    return;
                }

                lastActivityAt = now;
                scheduleIdleLogout();
                scheduleHeartbeat();
            };

            ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach((eventName) => {
                document.addEventListener(eventName, recordActivity, true);
            });

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    recordActivity();
                }
            });

            scheduleIdleLogout();
            scheduleHeartbeat();
        })();
    </script>
<?php endif; ?>
</body>
</html>
