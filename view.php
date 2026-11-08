<?php
require __DIR__ . '/includes/functions.php';
$id = record_id($_GET['id'] ?? null);
if (!$id) { error_page('Record not found', 'Please select a valid SCP record from the database.'); }
try { $record = find_record($id); }
catch (Throwable $error) { log_error($error); error_page('Archive temporarily unavailable', 'Unable to load this record. Please try again later.', 503); }
if (!$record) { error_page('Record not found', 'The requested SCP record does not exist.'); }
$pageTitle = $record['scp_number'];
require __DIR__ . '/includes/header.php';
?>
<a class="back-link" href="index.php">← Back to database</a>
<section class="record-heading"><div><span class="eyebrow">FOUNDATION ANOMALY FILE</span><h1><?= e($record['scp_number']) ?><span class="accent">.</span></h1><p><?= e($record['title']) ?></p></div><span class="class-badge <?= e(badge_class($record['object_class'])) ?>"><?= e($record['object_class']) ?></span></section>
<div class="row g-4"><div class="col-lg-8"><section class="detail-panel"><span class="eyebrow">01 / CONTAINMENT</span><h2>Special containment procedures</h2><p class="long-text"><?= e($record['containment_procedures']) ?></p></section><section class="detail-panel mt-4"><span class="eyebrow">02 / OBSERVATION</span><h2>Description</h2><p class="long-text"><?= e($record['description']) ?></p></section></div><aside class="col-lg-4"><div class="aside-panel"><img class="record-image" src="<?= e(image_url($record['image'])) ?>" alt="<?= $record['image'] ? e('Reference image for ' . $record['scp_number']) : 'Foundation archive placeholder; no reference image available' ?>" data-fallback="assets/images/placeholder-scp.svg"><dl class="record-meta"><dt>Object class</dt><dd><?= e($record['object_class']) ?></dd><dt>Created</dt><dd><?= e($record['created_at']) ?></dd><dt>Last updated</dt><dd><?= e($record['updated_at']) ?></dd></dl><a class="btn btn-accent w-100" href="edit.php?id=<?= $id ?>">Edit SCP record</a><a class="btn btn-delete w-100 mt-2" href="delete.php?id=<?= $id ?>">Delete record</a></div></aside></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
