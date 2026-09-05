<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/content.php';

require_login();
require_csrf();

$input = json_decode((string) file_get_contents('php://input'), true);
$key = is_array($input) ? ($input['key'] ?? null) : null;
$value = is_array($input) ? ($input['value'] ?? null) : null;

if (!is_string($key) || !is_string($value)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ogiltig förfrågan.']);
    exit;
}

if (pf_key_type($key) !== 'text') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Okänt fält.']);
    exit;
}

$value = trim($value);
$maxLength = pf_key_multiline($key) ? 2000 : 200;
if (mb_strlen($value) > $maxLength) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Texten är för lång (max ' . $maxLength . ' tecken).']);
    exit;
}

$stmt = $pdo->prepare('SELECT value FROM content WHERE `key` = ?');
$stmt->execute([$key]);
$old = $stmt->fetchColumn();

if ($old === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Okänt fält.']);
    exit;
}

$pdo->beginTransaction();
try {
    $update = $pdo->prepare('UPDATE content SET value = ?, updated_at = NOW() WHERE `key` = ?');
    $update->execute([$value, $key]);

    if ($old !== $value) {
        $history = $pdo->prepare('INSERT INTO content_history (`key`, old_value) VALUES (?, ?)');
        $history->execute([$key, $old]);
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Kunde inte spara.']);
    exit;
}

echo json_encode(['ok' => true, 'value' => $value]);
