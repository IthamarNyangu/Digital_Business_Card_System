<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? 'Contact') . ' | ' . config('app.organisation_name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(path_url('/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="public-body">
<main class="container py-4 py-md-5">
    <div class="mx-auto" style="max-width: 760px;">
        <?= $content ?>
    </div>
</main>
</body>
</html>
