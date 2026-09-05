<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/mailer.php';

$content = pf_load_content($pdo);
$admin = is_admin();
$csrf = $admin ? csrf_token() : '';

$phone = $content['contact.phone'] ?? '';
$email = $content['contact.email'] ?? '';
$telHref = pf_tel_href($phone);
$mailHref = pf_mailto_href($email);

pf_start_session();

$formToken = csrf_token();
if (empty($_SESSION['felanmalan_rendered_at'])) {
    $_SESSION['felanmalan_rendered_at'] = time();
}

const PF_FEL_TYP = [
    'vvs' => 'VVS / Rör',
    'el' => 'El',
    'las_dorr' => 'Lås / Dörr',
    'fonster' => 'Fönster',
    'tak_fasad' => 'Tak / Fasad',
    'ovrigt' => 'Övrigt',
];

const PF_FEL_AKUT = [
    'akut' => 'Akut – fara för person eller egendom',
    'snart' => 'Bör åtgärdas snart',
    'kan_vanta' => 'Kan vänta',
];

function pf_throttle_state_path(): string
{
    return __DIR__ . '/storage/felanmalan_throttle.json';
}

function pf_throttle_state_read(): array
{
    $file = pf_throttle_state_path();
    if (!is_file($file)) {
        return ['ips' => [], 'global' => ['count' => 0, 'windowStart' => time()]];
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        return ['ips' => [], 'global' => ['count' => 0, 'windowStart' => time()]];
    }
    $data['ips'] = is_array($data['ips'] ?? null) ? $data['ips'] : [];
    $data['global'] = is_array($data['global'] ?? null) ? $data['global'] : ['count' => 0, 'windowStart' => time()];
    return $data;
}

function pf_throttle_state_write(array $data): void
{
    $file = pf_throttle_state_path();
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($file, json_encode($data), LOCK_EX);
}

/** Returns null if allowed, or an error message if blocked. Records the attempt as a side effect. */
function pf_throttle_check_and_record(string $ip): ?string
{
    $now = time();
    $state = pf_throttle_state_read();

    foreach ($state['ips'] as $key => $entry) {
        if (($now - ($entry['last'] ?? 0)) > 3600) {
            unset($state['ips'][$key]);
        }
    }

    if (($now - ($state['global']['windowStart'] ?? 0)) > 86400) {
        $state['global'] = ['count' => 0, 'windowStart' => $now];
    }

    if (($state['global']['count'] ?? 0) >= 100) {
        pf_throttle_state_write($state);
        return 'För många förfrågningar just nu. Försök igen senare eller ring oss istället.';
    }

    $entry = $state['ips'][$ip] ?? ['count' => 0, 'first' => $now, 'last' => $now];
    if (($now - $entry['first']) > 3600) {
        $entry = ['count' => 0, 'first' => $now, 'last' => $now];
    }

    if ($entry['count'] >= 5) {
        $state['ips'][$ip] = $entry;
        pf_throttle_state_write($state);
        return 'För många förfrågningar från din uppkoppling. Vänta en stund och försök igen.';
    }

    $entry['count']++;
    $entry['last'] = $now;
    $state['ips'][$ip] = $entry;
    $state['global']['count'] = ($state['global']['count'] ?? 0) + 1;
    pf_throttle_state_write($state);
    return null;
}

$errors = [];
$sent = false;
$values = [
    'namn' => '',
    'telefon' => '',
    'epost' => '',
    'adress' => '',
    'typ' => '',
    'akut' => '',
    'beskrivning' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) {
        $errors[] = 'Sessionen gick ut. Ladda om sidan och försök igen.';
    } elseif (!empty($_POST['hemsida'] ?? '')) {
        // Honeypot ifylld - låtsas att allt gick bra, skicka inget.
        $sent = true;
    } else {
        $renderedAt = (int) ($_SESSION['felanmalan_rendered_at'] ?? 0);
        if (time() - $renderedAt < 3) {
            $errors[] = 'Formuläret skickades för snabbt. Vänta någon sekund och försök igen.';
        }

        $values['namn'] = trim((string) ($_POST['namn'] ?? ''));
        $values['telefon'] = trim((string) ($_POST['telefon'] ?? ''));
        $values['epost'] = trim((string) ($_POST['epost'] ?? ''));
        $values['adress'] = trim((string) ($_POST['adress'] ?? ''));
        $values['typ'] = (string) ($_POST['typ'] ?? '');
        $values['akut'] = (string) ($_POST['akut'] ?? '');
        $values['beskrivning'] = trim((string) ($_POST['beskrivning'] ?? ''));

        if ($values['namn'] === '' || mb_strlen($values['namn']) < 2 || mb_strlen($values['namn']) > 100 || pf_header_unsafe($values['namn'])) {
            $errors[] = 'Ange ditt namn (2–100 tecken).';
        }
        if (!preg_match('/^[0-9+\-() ]{5,30}$/', $values['telefon'])) {
            $errors[] = 'Ange ett giltigt telefonnummer.';
        }
        if ($values['epost'] !== '' && filter_var($values['epost'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Ange en giltig e-postadress, eller lämna fältet tomt.';
        }
        if ($values['adress'] === '' || mb_strlen($values['adress']) < 3 || mb_strlen($values['adress']) > 200) {
            $errors[] = 'Ange fastigheten/adressen felet gäller (3–200 tecken).';
        }
        if (!array_key_exists($values['typ'], PF_FEL_TYP)) {
            $errors[] = 'Välj en typ av fel.';
        }
        if (!array_key_exists($values['akut'], PF_FEL_AKUT)) {
            $errors[] = 'Ange hur akut felet är.';
        }
        if (mb_strlen($values['beskrivning']) < 10 || mb_strlen($values['beskrivning']) > 2000) {
            $errors[] = 'Beskriv felet lite mer utförligt (10–2000 tecken).';
        }

        $attachment = null;
        if (!empty($_FILES['foto']['name'] ?? '')) {
            $file = $_FILES['foto'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Bilden kunde inte laddas upp. Prova en mindre fil.';
            } elseif ($file['size'] > 4 * 1024 * 1024) {
                $errors[] = 'Bilden är för stor (max 4 MB).';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
                    $errors[] = 'Filen verkar inte vara en giltig bild (JPG, PNG eller WEBP).';
                } else {
                    $image = match ($mime) {
                        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
                        'image/png' => @imagecreatefrompng($file['tmp_name']),
                        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
                        default => false,
                    };
                    if ($image === false) {
                        $errors[] = 'Kunde inte läsa bilden.';
                    } else {
                        $maxWidth = 1920;
                        $width = imagesx($image);
                        $height = imagesy($image);
                        if ($width > $maxWidth) {
                            $newHeight = (int) round($height * ($maxWidth / $width));
                            $resized = imagecreatetruecolor($maxWidth, $newHeight);
                            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                            imagedestroy($image);
                            $image = $resized;
                        }
                        ob_start();
                        if ($mime === 'image/png') {
                            imagepng($image, null, 6);
                            $outMime = 'image/png';
                            $ext = 'png';
                        } elseif ($mime === 'image/webp') {
                            imagewebp($image, null, 85);
                            $outMime = 'image/webp';
                            $ext = 'webp';
                        } else {
                            imagejpeg($image, null, 85);
                            $outMime = 'image/jpeg';
                            $ext = 'jpg';
                        }
                        $data = ob_get_clean();
                        imagedestroy($image);
                        $attachment = ['data' => $data, 'mime' => $outMime, 'filename' => 'felanmalan-bild.' . $ext];
                    }
                }
            }
        }

        if (empty($errors)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $throttleError = pf_throttle_check_and_record($ip);
            if ($throttleError !== null) {
                $errors[] = $throttleError;
            }
        }

        if (empty($errors)) {
            $subject = 'Felanmälan: ' . PF_FEL_TYP[$values['typ']] . ' (' . PF_FEL_AKUT[$values['akut']] . ')';
            $bodyLines = [
                'Ny felanmälan från webbplatsen',
                '',
                'Namn: ' . $values['namn'],
                'Telefon: ' . $values['telefon'],
                'E-post: ' . ($values['epost'] !== '' ? $values['epost'] : '(ej angiven)'),
                'Fastighet/adress: ' . $values['adress'],
                'Typ av fel: ' . PF_FEL_TYP[$values['typ']],
                'Hur akut: ' . PF_FEL_AKUT[$values['akut']],
                '',
                'Beskrivning:',
                $values['beskrivning'],
            ];
            $bodyText = implode("\n", $bodyLines);

            $sendTo = $email !== '' ? $email : 'patrik@pifastab.se';
            $replyTo = $values['epost'] !== '' ? $values['epost'] : null;

            $sent = pf_send_felanmalan_mail($sendTo, $sendTo, $replyTo, $subject, $bodyText, $attachment);
            if (!$sent) {
                $errors[] = 'Kunde inte skicka anmälan just nu. Ring oss gärna istället.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Anmäl ett fel på en fastighet Pifast AB sköter.">
<title>Felanmälan | Pifast AB</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
<?php if ($admin): ?>
<link rel="stylesheet" href="assets/admin/edit.css">
<?php endif; ?>
<style>
.fel-form>.container>p{color:var(--muted);max-width:620px;margin:0 0 36px}
.fel-form form{max-width:620px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);padding:32px}
.fel-form label{display:block;font-weight:700;font-size:13px;margin:0 0 6px}
.fel-form .field{margin-bottom:20px}
.fel-form textarea{min-height:120px}
.fel-form .hp{position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden}
.fel-form .errors{background:#fbeaea;border:1px solid #e2b4b0;color:#a4342a;padding:14px 16px;margin:0 0 20px;font-size:14px;border-radius:var(--radius-sm)}
.fel-form .errors ul{margin:0;padding-left:18px}
.fel-form .success{background:#eaf6ec;border:1px solid #b7dcbe;color:#2c6a3a;padding:20px;font-size:15px;border-radius:var(--radius-sm)}
</style>
</head>
<body class="<?= $admin ? 'admin-mode' : '' ?>"<?= $admin ? ' data-csrf="' . htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
<?php require __DIR__ . '/includes/partials/header.php'; ?>
<main>
<section class="fel-form section-py"><div class="container">
  <h1>Felanmälan</h1>
  <p>Anmäl ett fel på en fastighet vi sköter, så hör vi av oss. Vid akuta fel som innebär fara för person eller egendom, ring oss direkt på <a href="<?= htmlspecialchars($telHref, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></a>.</p>

  <?php if ($sent): ?>
  <div class="success">Tack! Din felanmälan har skickats. Vi hör av oss så snart vi kan.</div>
  <?php else: ?>

  <?php if (!empty($errors)): ?>
  <div class="errors"><ul>
    <?php foreach ($errors as $error): ?>
    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul></div>
  <?php endif; ?>

  <form method="post" action="felanmalan.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($formToken, ENT_QUOTES, 'UTF-8') ?>">
    <div class="field hp" aria-hidden="true">
      <label for="hemsida">Hemsida (lämna tomt)</label>
      <input type="text" id="hemsida" name="hemsida" tabindex="-1" autocomplete="off" value="">
    </div>
    <div class="field">
      <label for="namn">Namn</label>
      <input type="text" id="namn" name="namn" required maxlength="100" value="<?= htmlspecialchars($values['namn'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
      <label for="telefon">Telefon</label>
      <input type="tel" id="telefon" name="telefon" required maxlength="30" value="<?= htmlspecialchars($values['telefon'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
      <label for="epost">E-post (frivilligt)</label>
      <input type="email" id="epost" name="epost" maxlength="200" value="<?= htmlspecialchars($values['epost'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
      <label for="adress">Fastighet / adress</label>
      <input type="text" id="adress" name="adress" required maxlength="200" value="<?= htmlspecialchars($values['adress'], ENT_QUOTES, 'UTF-8') ?>">
    </div>
    <div class="field">
      <label for="typ">Typ av fel</label>
      <select id="typ" name="typ" required>
        <option value="">Välj typ av fel</option>
        <?php foreach (PF_FEL_TYP as $key => $label): ?>
        <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"<?= $values['typ'] === $key ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="akut">Hur akut är felet?</label>
      <select id="akut" name="akut" required>
        <option value="">Välj hur akut felet är</option>
        <?php foreach (PF_FEL_AKUT as $key => $label): ?>
        <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"<?= $values['akut'] === $key ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="beskrivning">Beskrivning av felet</label>
      <textarea id="beskrivning" name="beskrivning" required maxlength="2000"><?= htmlspecialchars($values['beskrivning'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
    <div class="field">
      <label for="foto">Bifoga foto (frivilligt, max 4 MB)</label>
      <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
    </div>
    <button type="submit" class="btn btn-brand">Skicka felanmälan</button>
  </form>

  <?php endif; ?>
</div></section>
</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>
