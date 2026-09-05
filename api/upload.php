<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/content.php';

require_login();
require_csrf();

$key = $_POST['key'] ?? null;
if (!is_string($key) || pf_key_type($key) !== 'image') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Okänt fält.']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ingen fil vald.']);
    exit;
}

$file = $_FILES['file'];

$uploadErrors = [
    UPLOAD_ERR_INI_SIZE => 'Filen är för stor för servern att ta emot.',
    UPLOAD_ERR_FORM_SIZE => 'Filen är för stor.',
    UPLOAD_ERR_PARTIAL => 'Uppladdningen avbröts, försök igen.',
    UPLOAD_ERR_NO_FILE => 'Ingen fil vald.',
];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $uploadErrors[$file['error']] ?? 'Uppladdningen misslyckades.']);
    exit;
}

$maxBytes = 4 * 1024 * 1024;
if ($file['size'] > $maxBytes) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Bilden är för stor (max 4 MB).']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Filtypen stöds inte. Använd JPG, PNG eller WEBP.']);
    exit;
}

if (@getimagesize($file['tmp_name']) === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Filen verkar inte vara en giltig bild.']);
    exit;
}

$image = match ($mime) {
    'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
    'image/png' => @imagecreatefrompng($file['tmp_name']),
    'image/webp' => @imagecreatefromwebp($file['tmp_name']),
    default => false,
};

if ($image === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Kunde inte läsa bilden.']);
    exit;
}

$maxWidth = 1920;
$width = imagesx($image);
$height = imagesy($image);
if ($width > $maxWidth) {
    $newHeight = (int) round($height * ($maxWidth / $width));
    $resized = imagecreatetruecolor($maxWidth, $newHeight);
    if ($mime === 'image/png') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
    }
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
    imagedestroy($image);
    $image = $resized;
}

$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$extension = $allowed[$mime];
$filename = bin2hex(random_bytes(16)) . '.' . $extension;
$targetPath = $uploadsDir . '/' . $filename;

$saved = match ($mime) {
    'image/jpeg' => imagejpeg($image, $targetPath, 85),
    'image/png' => imagepng($image, $targetPath, 6),
    'image/webp' => imagewebp($image, $targetPath, 85),
    default => false,
};
imagedestroy($image);

if (!$saved) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Kunde inte spara bilden.']);
    exit;
}

$publicUrl = '/uploads/' . $filename;

$stmt = $pdo->prepare('SELECT value FROM content WHERE `key` = ?');
$stmt->execute([$key]);
$old = $stmt->fetchColumn();

if ($old === false) {
    @unlink($targetPath);
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Okänt fält.']);
    exit;
}

$update = $pdo->prepare('UPDATE content SET value = ?, updated_at = NOW() WHERE `key` = ?');
$update->execute([$publicUrl, $key]);

if (is_string($old) && str_starts_with($old, '/uploads/')) {
    $oldPath = __DIR__ . '/../' . ltrim($old, '/');
    if (is_file($oldPath)) {
        @unlink($oldPath);
    }
}

echo json_encode(['ok' => true, 'url' => $publicUrl]);
