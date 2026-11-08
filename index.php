<?php
require __DIR__ . '/includes/functions.php';
$pageTitle = 'Containment database';
$q = input($_GET, 'q');
if (text_length($q) > 150) { error_page('Search is too long', 'Please use a search of 150 characters or fewer.', 400); }
try {
    $counts = db()->query("SELECT COUNT(*) AS total, SUM(object_class = 'Safe') AS safe, SUM(object_class = 'Euclid') AS euclid, SUM(object_class = 'Keter') AS keter FROM scp_subjects")->fetch();
    // Each LIKE placeholder is distinct for native PDO prepared statements.
    $sql = 'SELECT * FROM scp_subjects';
    $params = [];
    if ($q !== '') {
        $sql .= " WHERE scp_number LIKE :number ESCAPE '!' OR title LIKE :title ESCAPE '!' OR object_class LIKE :class ESCAPE '!'";
        $term = '%' . strtr($q, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $params = ['number' => $term, 'title' => $term, 'class' => $term];
    }
    $stmt = db()->prepare($sql . ' ORDER BY CAST(SUBSTRING(scp_number, 5) AS UNSIGNED), scp_number');
    $stmt->execute($params);
    $records = $stmt->fetchAll();
} catch (Throwable $error) {
    log_error($error);
    error_page('Archive temporarily unavailable', 'Unable to connect to the database. Check the database configuration and SQL import, then try again.', 503);
}
require __DIR__ . '/includes/header.php';
?>
<section class="hero"><div><span class="eyebrow"><span class="red-line"></span> FOUNDATION INTERNAL ARCHIVE</span><h1>Containment database<span class="accent">.</span></h1><p>Research. Document. Maintain containment.<br>Manage the anomalies entrusted to the Foundation.</p></div><a href="create.php" class="btn btn-accent">＋ Add SCP record</a></section>
<section class="stats-grid" aria-label="Archive statistics">
<?php foreach (['total' => ['Total records', 'All documented anomalies'], 'safe' => ['Safe', 'Reliably contained'], 'euclid' => ['Euclid', 'Containment requires attention'], 'keter' => ['Keter', 'Complex containment']] as $key => [$label, $detail]): ?>
<div class="stat-card <?= e($key) ?>"><span><?= e($label) ?></span><strong><?= (int) $counts[$key] ?></strong><small><?= e($detail) ?></small></div>
<?php endforeach; ?>
</section>
<section aria-labelledby="records-heading">
<div class="archive-toolbar"><div><h2 id="records-heading">SCP records <span class="count-pill"><?= count($records) ?></span></h2><p><?= $q !== '' ? 'Search results for “' . e($q) . '”' : 'Browse the Foundation’s documented anomalies.' ?></p></div><form action="index.php" method="get" class="search-form" role="search"><label for="search" class="visually-hidden">Search number, title or object class</label><input id="search" class="form-control" type="search" name="q" maxlength="150" value="<?= e($q) ?>" placeholder="Search number, title, class…"><button class="btn btn-secondary" type="submit">Search</button><?php if ($q !== ''): ?><a class="clear-search" href="index.php">Clear</a><?php endif; ?></form></div>
<?php if (!$records): ?>
<div class="empty-state"><h3><?= $q !== '' ? 'No SCP records matched your search.' : 'The archive is ready for its first record.' ?></h3><p><?= $q !== '' ? 'Try another number, title or object class.' : 'Add an SCP record to begin documenting anomalies.' ?></p><a class="btn btn-accent" href="<?= $q !== '' ? 'index.php' : 'create.php' ?>"><?= $q !== '' ? 'Clear search' : 'Add SCP' ?></a></div>
<?php else: ?>
<div class="row g-4">
<?php foreach ($records as $record): ?>
<div class="col-12 col-md-6 col-lg-4"><article class="scp-card h-100"><div class="card-topline"><span class="record-label">ANOMALY FILE / <?= str_pad((string) $record['id'], 3, '0', STR_PAD_LEFT) ?></span><span class="class-badge <?= e(badge_class($record['object_class'])) ?>"><?= e($record['object_class']) ?></span></div><h3><?= e($record['scp_number']) ?></h3><h4><?= e($record['title']) ?></h4><p><?= e(excerpt($record['description'])) ?></p><div class="card-actions"><a class="btn btn-view" href="view.php?id=<?= (int) $record['id'] ?>">View record <span aria-hidden="true">↗</span></a><a class="btn btn-quiet" aria-label="Edit <?= e($record['scp_number']) ?>" href="edit.php?id=<?= (int) $record['id'] ?>">Edit</a><a class="btn btn-delete" aria-label="Delete <?= e($record['scp_number']) ?>" href="delete.php?id=<?= (int) $record['id'] ?>" data-delete-id="<?= (int) $record['id'] ?>" data-delete-number="<?= e($record['scp_number']) ?>">Delete</a></div></article></div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<p class="archive-note"><span aria-hidden="true">⌖</span> Secure. Contain. Protect. <span>End of archive · <?= count($records) ?> records shown</span></p>
</section>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="deleteModalTitle">Delete SCP record</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><p id="deleteRecordLabel" class="fw-bold"></p><p>Are you sure you want to delete this SCP record? This action cannot be undone.</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><form method="post" action="delete.php"><?php csrf_field(); ?><input type="hidden" name="id" id="deleteRecordId"><button type="submit" class="btn btn-danger">Delete record</button></form></div></div></div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
