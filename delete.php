<?php
require __DIR__ . '/includes/functions.php';
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET', 'POST'], true)) { header('Allow: GET, POST'); error_page('Method not allowed', 'Use the confirmation form to delete a record.', 405); }
$id = record_id(($method === 'POST' ? $_POST : $_GET)['id'] ?? null);
if (!$id) { error_page('Record not found', 'Please select a valid SCP record to delete.'); }
if ($method === 'POST' && !valid_csrf()) { error_page('Session expired', 'The deletion was not performed. Return to the database and try again.', 403); }
try {
    if ($method === 'POST') {
        // A GET only displays confirmation. Actual deletion always requires POST + CSRF.
        $stmt = db()->prepare('DELETE FROM scp_subjects WHERE id = :id');
        $stmt->execute(['id' => $id]);
        flash($stmt->rowCount() ? 'success' : 'warning', $stmt->rowCount() ? 'SCP record deleted successfully.' : 'Record not found. It may already have been deleted.');
        redirect('index.php');
    }
    $record = find_record($id);
    if (!$record) { error_page('Record not found', 'The requested SCP record does not exist.'); }
} catch (Throwable $error) { log_error($error); error_page('Unable to delete record', 'The database is unavailable. Please try again later.', 503); }
$pageTitle = 'Confirm deletion';
require __DIR__ . '/includes/header.php';
?>
<section class="confirmation-panel"><span class="eyebrow">PERMANENT ACTION</span><h1>Delete <?= e($record['scp_number']) ?>?</h1><p><?= e($record['title']) ?></p><div class="alert alert-warning">Are you sure you want to delete this SCP record? This action cannot be undone.</div><form method="post" action="delete.php"><?php csrf_field(); ?><input type="hidden" name="id" value="<?= $id ?>"><div class="form-actions"><a class="btn btn-secondary" href="index.php">Cancel</a><button type="submit" class="btn btn-danger">Delete record</button></div></form></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
