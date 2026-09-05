<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/config.php';

pf_start_session();

if (is_admin()) {
    header('Location: /');
    exit;
}

function pf_throttle_path(): string
{
    return __DIR__ . '/../storage/login_throttle.json';
}

function pf_read_throttle(): array
{
    $file = pf_throttle_path();
    if (!is_file($file)) {
        return ['count' => 0, 'last' => 0];
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : ['count' => 0, 'last' => 0];
}

function pf_write_throttle(array $data): void
{
    $file = pf_throttle_path();
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($file, json_encode($data), LOCK_EX);
}

function pf_clear_throttle(): void
{
    $file = pf_throttle_path();
    if (is_file($file)) {
        unlink($file);
    }
}

$throttle = pf_read_throttle();
$waitSeconds = 0;
if ($throttle['count'] >= 5) {
    $elapsed = time() - $throttle['last'];
    $required = min(60, (int) (2 ** ($throttle['count'] - 4)));
    if ($elapsed < $required) {
        $waitSeconds = $required - $elapsed;
    }
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($waitSeconds > 0) {
        $error = 'För många felaktiga försök. Vänta ' . $waitSeconds . ' sekunder och försök igen.';
    } elseif (!csrf_verify($_POST['csrf'] ?? null)) {
        $error = 'Sessionen gick ut. Ladda om sidan och försök igen.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        if ($password !== '' && password_verify($password, ADMIN_PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['is_admin'] = true;
            unset($_SESSION['csrf']);
            pf_clear_throttle();
            header('Location: /');
            exit;
        }
        $throttle['count'] = ($throttle['count'] ?? 0) + 1;
        $throttle['last'] = time();
        pf_write_throttle($throttle);
        sleep(1);
        $error = 'Fel lösenord.';
    }
}

$csrf = csrf_token();
?>
<!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Logga in | Pifast AB</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/style.css">
<style>
.pf-login{min-height:100vh;display:grid;place-items:center;background:var(--pale)}
.pf-login form{background:white;padding:40px 36px;border-radius:var(--radius);box-shadow:var(--shadow-md);width:min(340px,90vw)}
.pf-login h1{font-size:22px;margin:0 0 20px;font-weight:800}
.pf-login input[type=password]{margin-bottom:16px}
.pf-login button{width:100%;padding:12px;border:0;background:var(--blue);color:white;font-weight:700;font-size:14px;border-radius:var(--radius-sm);cursor:pointer;transition:background-color .15s ease}
.pf-login button:hover{background:var(--accent-dark, #274d61)}
.pf-login .error{color:#a4342a;font-size:13px;margin:0 0 14px}
</style>
</head>
<body>
<div class="pf-login">
<form method="post" action="/admin/login.php">
<h1>Logga in</h1>
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
<input type="password" name="password" placeholder="Lösenord" autofocus required>
<button type="submit">Logga in</button>
</form>
</div>
</body>
</html>
