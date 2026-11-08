<?php
// Shared create/edit controller and form. The wrapper supplies $editing.
require __DIR__ . '/functions.php';
$id = null;
$data = array_fill_keys(['scp_number', 'title', 'object_class', 'description', 'containment_procedures', 'image'], '');
$errors = [];
try {
    if ($editing) {
        $id = record_id($_GET['id'] ?? null);
        if (!$id || !($record = find_record($id))) { error_page('Record not found', 'The requested SCP record does not exist.'); }
        foreach ($data as $key => $_) { $data[$key] = $record[$key] ?? ''; }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($data as $key => $_) { $data[$key] = input($_POST, $key); }
        $data['scp_number'] = strtoupper($data['scp_number']);
        if (!valid_csrf()) {
            http_response_code(403);
            $errors['_form'] = 'Your session token is invalid or expired. Please submit the form again.';
        } else {
            $errors = validate_record($data, $id);
            if (!$errors) {
                $params = $data;
                $params['image'] = $data['image'] === '' ? null : $data['image'];
                // Prepared statements keep all user-supplied values separate from SQL.
                if ($editing) {
                    $params['id'] = $id;
                    $stmt = db()->prepare('UPDATE scp_subjects SET scp_number=:scp_number, title=:title, object_class=:object_class, description=:description, containment_procedures=:containment_procedures, image=:image WHERE id=:id');
                } else {
                    $stmt = db()->prepare('INSERT INTO scp_subjects (scp_number,title,object_class,description,containment_procedures,image) VALUES (:scp_number,:title,:object_class,:description,:containment_procedures,:image)');
                }
                $stmt->execute($params);
                if ($editing && $stmt->rowCount() === 0 && !find_record($id)) { error_page('Record not found', 'This record was removed before your changes could be saved.'); }
                flash('success', $data['scp_number'] . ($editing ? ' was updated successfully.' : ' was created successfully.'));
                redirect('index.php'); // PRG prevents refresh from repeating the write.
            }
        }
    }
} catch (Throwable $error) {
    log_error($error);
    if ($error instanceof PDOException && ($error->errorInfo[1] ?? null) === 1062) {
        $errors['scp_number'] = 'This SCP number already exists.';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(503);
        $errors['_form'] = 'Unable to save the record. Please check the database connection and try again.';
    } else { error_page('Archive temporarily unavailable', 'Unable to load the record. Please check the database connection and try again.', 503); }
}
$pageTitle = $editing ? 'Edit SCP' : 'Add SCP';
require __DIR__ . '/header.php';
?>
<a class="back-link" href="index.php">← Back to database</a>
<div class="page-heading"><span class="eyebrow">ANOMALY DOCUMENTATION</span><h1><?= $editing ? 'Edit SCP record' : 'Register an anomaly' ?><span class="accent">.</span></h1><p><?= $editing ? 'Update the information held in the Foundation archive.' : 'Create a new containment file. All fields marked * are required.' ?></p></div>
<div class="row g-4"><div class="col-lg-8">
<?php if ($errors): ?><div class="alert alert-warning" role="alert"><strong>Please correct the highlighted fields.</strong><ul class="mb-0 mt-2"><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="<?= $editing ? 'edit.php?id=' . $id : 'create.php' ?>" class="form-panel" novalidate>
<?php csrf_field(); ?>
<h2 class="section-title">Record details</h2><p class="text-muted mb-4">Provide an accurate, concise account of the anomaly.</p>
<div class="row g-4">
<?php foreach (['scp_number' => 'SCP number', 'title' => 'Title', 'object_class' => 'Object class', 'description' => 'Description', 'containment_procedures' => 'Special containment procedures', 'image' => 'Image URL or local path'] as $key => $label): ?>
<div class="<?= in_array($key, ['scp_number', 'object_class'], true) ? 'col-md-6' : 'col-12' ?>">
<label class="form-label" for="<?= e($key) ?>"><?= e($label) ?><?= $key !== 'image' ? ' <span class="accent">*</span>' : ' <span class="text-muted">(optional)</span>' ?></label>
<?php $invalid = isset($errors[$key]); $attributes = ' id="' . $key . '" name="' . $key . '" class="form-control' . ($invalid ? ' is-invalid' : '') . '"' . ($key !== 'image' ? ' required' : '') . ($invalid ? ' aria-invalid="true" aria-describedby="' . $key . '-error"' : ($key === 'image' ? ' aria-describedby="image-help"' : '')); ?>
<?php if ($key === 'object_class'): ?>
<select <?= $attributes ?>><option value="">Select object class</option><?php foreach (OBJECT_CLASSES as $class): ?><option value="<?= e($class) ?>" <?= $data[$key] === $class ? 'selected' : '' ?>><?= e($class) ?></option><?php endforeach; ?></select>
<?php elseif (in_array($key, ['description', 'containment_procedures'], true)): ?>
<textarea <?= $attributes ?> rows="6"><?= e($data[$key]) ?></textarea>
<?php else: ?>
<input <?= $attributes ?> type="text" maxlength="<?= ['scp_number'=>20,'title'=>150,'image'=>255][$key] ?>" value="<?= e($data[$key]) ?>" <?= $key === 'scp_number' ? 'placeholder="SCP-173"' : '' ?>>
<?php endif; ?>
<?php if ($invalid): ?><div class="invalid-feedback" id="<?= e($key) ?>-error"><?= e($errors[$key]) ?></div><?php endif; ?>
<?php if ($key === 'image'): ?><div id="image-help" class="form-text">Use an HTTPS URL or assets/images/filename.jpg. Leave blank to use the local placeholder.</div><?php endif; ?>
</div>
<?php endforeach; ?>
</div><div class="form-actions"><a href="<?= $editing ? 'view.php?id=' . $id : 'index.php' ?>" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-accent"><?= $editing ? 'Save changes' : 'Create SCP record' ?></button></div>
</form></div><aside class="col-lg-4"><div class="aside-panel"><span class="eyebrow">DOCUMENTATION STANDARD</span><h2>Every detail matters.</h2><p>Use a unique SCP number, select the appropriate object class, and describe the procedures needed to maintain containment.</p><hr><p class="small mb-0">This is a fictional educational database. Do not enter personal or sensitive information.</p></div></aside></div>
<?php require __DIR__ . '/footer.php'; ?>
