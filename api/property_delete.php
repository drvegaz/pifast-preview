<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/properties.php';

require_login();
require_csrf();

$input = json_decode((string) file_get_contents('php://input'), true);
$id = is_array($input) ? ($input['id'] ?? null) : null;

if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ogiltig förfrågan.']);
    exit;
}
$id = (int) $id;

$stmt = $pdo->prepare('SELECT image FROM properties WHERE id = ?');
$stmt->execute([$id]);
$image = $stmt->fetchColumn();

if ($image === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Fastigheten finns inte.']);
    exit;
}

$delete = $pdo->prepare('DELETE FROM properties WHERE id = ?');
$delete->execute([$id]);

if (is_string($image) && str_starts_with($image, '/uploads/')) {
    $path = __DIR__ . '/../' . ltrim($image, '/');
    if (is_file($path)) {
        @unlink($path);
    }
}

echo json_encode(['ok' => true]);
