<?php $activePage = basename($_SERVER['SCRIPT_NAME']); ?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SCP Foundation research and containment records. COMP.5210 assessment project.">
    <title><?= e($pageTitle ?? 'Database') ?> · SCP Foundation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="assets/js/script.js" defer></script>
</head>
<body>
<a href="#main" class="skip-link">Skip to content</a>
<header class="site-header">
    <nav class="navbar navbar-expand-md container" aria-label="Main navigation">
        <a class="navbar-brand brand" href="index.php"><span class="brand-mark" aria-hidden="true">⌖</span><span>SCP FOUNDATION<small>SECURE. CONTAIN. PROTECT.</small></span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navigation">
            <div class="navbar-nav ms-auto gap-md-4">
                <?php foreach (['index.php' => 'Database', 'create.php' => 'Add SCP', 'about.php' => 'About'] as $url => $label): ?>
                <a class="nav-link <?= $activePage === $url ? 'active' : '' ?>" <?= $activePage === $url ? 'aria-current="page"' : '' ?> href="<?= e($url) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
            <span class="nav-status"><i></i> ARCHIVE TERMINAL</span>
        </div>
    </nav>
</header>
<main id="main" class="container main-content">
<?php // Flash messages survive a redirect and are displayed once.
$flashes = $_SESSION['flashes'] ?? []; unset($_SESSION['flashes']);
foreach ($flashes as $notice): ?>
    <div class="alert alert-<?= e(in_array($notice['type'], ['success','danger','warning','info'], true) ? $notice['type'] : 'info') ?> alert-dismissible fade show" role="alert"><?= e($notice['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss message"></button></div>
<?php endforeach; ?>
