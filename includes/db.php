<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$pf_db_port = defined('DB_PORT') ? ';port=' . DB_PORT : '';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . $pf_db_port . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Kunde inte ansluta till databasen.');
}
