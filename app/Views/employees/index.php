<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h1 class="h2 mb-1">Employees</h1>
        <p class="text-muted mb-0">Each employee gets a random public token, a mobile contact page, and a downloadable QR code.</p>
    </div>
    <a href="<?= e(path_url('/admin/employees/create')) ?>" class="btn btn-primary">Add Employee</a>
</div>

<div class="card border-0 shadow-sm admin-panel-card" id="employeeDirectory">
    <div class="card-body">
        <div class="employee-list-toolbar mb-4">
            <form method="GET" action="<?= e(path_url('/admin/employees')) ?>" class="employee-search-form" id="employeeSearchForm" autocomplete="off">
                <span class="employee-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="M20 20l-3.5-3.5"></path>
                    </svg>
                </span>
                <input
                    type="search"
                    name="search"
                    value="<?= e($search ?? '') ?>"
                    class="form-control employee-search-input"
                    id="employeeSearchInput"
                    placeholder="Search employees by name, role, phone, or department"
                >
            </form>
        </div>

        <?php if ($employees === []): ?>
            <p class="text-muted mb-0">
                <?= ($search ?? '') !== ''
                    ? 'No employees matched your search. Try a different name, role, or contact detail.'
                    : 'No employees found. Add the first employee to generate a public contact page.' ?>
            </p>
        <?php else: ?>
            <div class="table-responsive employee-table-wrap">
                <table class="table table-hover align-middle mb-0 employee-directory-table">
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
                                <a href="<?= e(path_url('/c/' . $employee['public_token'])) ?>" target="_blank" class="small">
                                    /c/<?= e($employee['public_token']) ?>
                                </a>
                            </td>
                            <td class="text-end">
                                <details class="employee-action-menu">
                                    <summary class="employee-action-toggle">Actions</summary>
                                    <div class="employee-action-dropdown">
                                        <a href="<?= e(path_url('/admin/employees/' . $employee['id'] . '/edit')) ?>" class="employee-action-item employee-action-edit">Edit</a>

                                        <?php if ($employee['status'] === 'active'): ?>
                                            <a href="<?= e(path_url('/admin/employees/' . $employee['id'] . '/qr')) ?>" class="employee-action-item employee-action-qr">Download QR</a>
                                            <form method="POST" action="<?= e(path_url('/admin/employees/' . $employee['id'] . '/deactivate')) ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="employee-action-item employee-action-deactivate" onclick="return confirm('Mark this employee as inactive?');">Deactivate</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= e(path_url('/admin/employees/' . $employee['id'] . '/activate')) ?>">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="employee-action-item employee-action-activate" onclick="return confirm('Activate this employee again?');">Activate</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $queryBase = [];
            if (($search ?? '') !== '') {
                $queryBase['search'] = $search;
            }
            ?>

            <?php
            $previousQuery = $queryBase;
            $previousQuery['page'] = max(1, $page - 1);
            $nextQuery = $queryBase;
            $nextQuery['page'] = min($totalPages, $page + 1);
            $previousUrl = path_url('/admin/employees') . '?' . http_build_query($previousQuery) . '#employeeDirectory';
            $nextUrl = path_url('/admin/employees') . '?' . http_build_query($nextQuery) . '#employeeDirectory';
            ?>

            <nav class="entries-pagination" aria-label="Employee pagination">
                <div class="entries-pagination-meta">
                    <?php if (($search ?? '') !== ''): ?>
                        Showing <?= e((string) (($totalEmployees ?? 0) > 0 ? (($page - 1) * $perPage) + 1 : 0)) ?>
                        -
                        <?= e((string) (($totalEmployees ?? 0) > 0 ? min((($page - 1) * $perPage) + count($employees), $totalEmployees) : 0)) ?>
                        of <?= e((string) $totalEmployees) ?> matching employees
                    <?php else: ?>
                        Showing <?= e((string) (($totalEmployees ?? 0) > 0 ? (($page - 1) * $perPage) + 1 : 0)) ?>
                        -
                        <?= e((string) (($totalEmployees ?? 0) > 0 ? min((($page - 1) * $perPage) + count($employees), $totalEmployees) : 0)) ?>
                        of <?= e((string) $totalEmployees) ?> employees
                    <?php endif; ?>
                </div>

                <?php if (($totalPages ?? 1) > 1): ?>
                    <div class="entries-pagination-controls">
                        <?php if ($page > 1): ?>
                            <a class="entries-pagination-link" href="<?= e($previousUrl) ?>">Back</a>
                        <?php else: ?>
                            <span class="entries-pagination-link is-disabled" aria-disabled="true">Back</span>
                        <?php endif; ?>

                        <span class="entries-pagination-status">
                            Page <?= e((string) $page) ?> of <?= e((string) $totalPages) ?>
                        </span>

                        <?php if ($page < $totalPages): ?>
                            <a class="entries-pagination-link" href="<?= e($nextUrl) ?>">Next</a>
                        <?php else: ?>
                            <span class="entries-pagination-link is-disabled" aria-disabled="true">Next</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
    (() => {
        const searchForm = document.getElementById('employeeSearchForm');
        const searchInput = document.getElementById('employeeSearchInput');
        const actionMenus = document.querySelectorAll('.employee-action-menu');

        if (searchForm && searchInput) {
            let debounceId = null;
            let previousValue = searchInput.value;

            searchInput.addEventListener('input', () => {
                window.clearTimeout(debounceId);

                debounceId = window.setTimeout(() => {
                    const nextValue = searchInput.value.trim();

                    if (nextValue === previousValue.trim()) {
                        return;
                    }

                    searchForm.submit();
                }, 280);
            });
        }

        actionMenus.forEach((menu) => {
            menu.addEventListener('toggle', () => {
                if (!menu.open) {
                    return;
                }

                actionMenus.forEach((otherMenu) => {
                    if (otherMenu !== menu) {
                        otherMenu.removeAttribute('open');
                    }
                });
            });
        });

        document.addEventListener('click', (event) => {
            actionMenus.forEach((menu) => {
                if (menu.open && !menu.contains(event.target)) {
                    menu.removeAttribute('open');
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            actionMenus.forEach((menu) => menu.removeAttribute('open'));
        });
    })();
</script>
