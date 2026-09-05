<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

pf_start_session();
$_SESSION = [];
session_destroy();
header('Location: /');
exit;
