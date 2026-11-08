<?php
declare(strict_types=1);

ini_set('display_errors', '0');
if (session_status() === PHP_SESSION_NONE) {
    session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
}
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

const OBJECT_CLASSES = ['Safe', 'Euclid', 'Keter', 'Thaumiel', 'Neutralized', 'Explained', 'Pending'];

function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function input(array $source, string $key): string { return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : ''; }
function redirect(string $url): void { header('Location: ' . $url, true, 303); exit; }
function flash(string $type, string $message): void { $_SESSION['flashes'][] = ['type' => $type, 'message' => $message]; }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): void { echo '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function valid_csrf(): bool { return hash_equals(csrf_token(), input($_POST, 'csrf')); }
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $file = __DIR__ . '/../config/database.php';
        if (!is_file($file)) { throw new RuntimeException('Database configuration missing.'); }
        $pdo = require $file;
    }
    return $pdo;
}
function log_error(Throwable $error): void { error_log('[SCP database] ' . $error->getMessage()); }
function record_id(mixed $value): ?int {
    if (!is_string($value)) { return null; }
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}
function find_record(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM scp_subjects WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
}
function badge_class(string $class): string { return in_array($class, OBJECT_CLASSES, true) ? strtolower($class) : 'pending'; }
function excerpt(string $text, int $limit = 145): string {
    $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    return count($characters) > $limit ? implode('', array_slice($characters, 0, $limit)) . '…' : $text;
}
function text_length(string $value): int { return preg_match_all('/./us', $value); }
function image_url(?string $value): string {
    $value = trim($value ?? '');
    if ($value === '') { return 'assets/images/placeholder-scp.svg'; }
    if (filter_var($value, FILTER_VALIDATE_URL) && strtolower((string) parse_url($value, PHP_URL_SCHEME)) === 'https') { return $value; }
    // Local images are deliberately limited to this folder; no traversal or script URLs.
    if (preg_match('~^assets/images/[a-zA-Z0-9_-]+\.(?:png|jpe?g|webp|gif|svg)$~D', $value)) { return $value; }
    return 'assets/images/placeholder-scp.svg';
}
function validate_record(array $data, ?int $id): array {
    $errors = [];
    $labels = ['scp_number' => 'SCP number', 'title' => 'Title', 'object_class' => 'Object class', 'description' => 'Description', 'containment_procedures' => 'Containment procedures'];
    foreach ($labels as $key => $label) { if ($data[$key] === '') { $errors[$key] = $label . ' is required.'; } }
    if ($data['scp_number'] !== '' && !preg_match('/^SCP-[0-9]{1,10}(?:-[A-Z0-9]{1,5})?$/D', $data['scp_number'])) { $errors['scp_number'] = 'Use an SCP number such as SCP-173 or SCP-001-A.'; }
    foreach (['scp_number' => 20, 'title' => 150, 'image' => 255] as $key => $max) {
        if (text_length($data[$key]) > $max) { $errors[$key] = 'Use no more than ' . $max . ' characters.'; }
    }
    foreach (['description', 'containment_procedures'] as $key) {
        if (strlen($data[$key]) > 60000) { $errors[$key] = 'This field must be less than 60,000 bytes.'; }
    }
    if ($data['object_class'] !== '' && !in_array($data['object_class'], OBJECT_CLASSES, true)) { $errors['object_class'] = 'Select a valid object class.'; }
    if ($data['image'] !== '' && image_url($data['image']) !== $data['image']) { $errors['image'] = 'Enter an HTTPS image URL or a filename in assets/images/ (png, jpg, webp, gif or svg).'; }
    if (!isset($errors['scp_number'])) {
        // Exclude the current record when checking uniqueness during an edit.
        $stmt = db()->prepare('SELECT id FROM scp_subjects WHERE scp_number = :number AND id <> :id');
        $stmt->execute(['number' => $data['scp_number'], 'id' => $id ?? 0]);
        if ($stmt->fetch()) { $errors['scp_number'] = 'This SCP number already exists.'; }
    }
    return $errors;
}
function error_page(string $title, string $message, int $status = 404): void {
    http_response_code($status);
    $pageTitle = $title;
    require __DIR__ . '/header.php';
    echo '<section class="empty-state"><span class="eyebrow">ACCESS NOTICE</span><h1>' . e($title) . '</h1><p>' . e($message) . '</p><a class="btn btn-accent" href="index.php">Back to database</a></section>';
    require __DIR__ . '/footer.php';
    exit;
}
