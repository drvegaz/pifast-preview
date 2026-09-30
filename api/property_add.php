<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/content.php';
require_once __DIR__ . '/../includes/properties.php';

require_login();
require_csrf();

$count = (int) $pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn();
if ($count >= PF_PROPERTY_MAX_COUNT) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Max antal fastigheter (' . PF_PROPERTY_MAX_COUNT . ') är uppnått.']);
    exit;
}

$name = 'Ny fastighet – fyll i adress';
$description = 'Kort beskrivning av fastigheten – redigera i adminläget.';

$nextOrder = (int) $pdo->query('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM properties')->fetchColumn();

$insert = $pdo->prepare('INSERT INTO properties (image, name, description, sort_order) VALUES (?, ?, ?, ?)');
$insert->execute(['', $name, $description, $nextOrder]);

echo json_encode([
    'ok' => true,
    'id' => (int) $pdo->lastInsertId(),
    'image' => '',
    'name' => $name,
    'description' => $description,
]);
