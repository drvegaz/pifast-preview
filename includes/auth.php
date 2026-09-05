<?php
declare(strict_types=1);

function pf_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_admin(): bool
{
    pf_start_session();
    return !empty($_SESSION['is_admin']);
}

function require_login(): void
{
    if (is_admin()) {
        return;
    }
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (str_starts_with($uri, '/api/')) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Ej inloggad.']);
        exit;
    }
    header('Location: /admin/login.php');
    exit;
}
