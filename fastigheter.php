<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/content.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/throttle.php';

$content = pf_load_content($pdo);
$admin = is_admin();
$csrf = $admin ? csrf_token() : '';

$phone = $content['contact.phone'] ?? '';
$email = $content['contact.email'] ?? '';
$telHref = pf_tel_href($phone);
$mailHref = pf_mailto_href($email);

pf_start_session();

$formToken = csrf_token();
if (empty($_SESSION['fastigheter_rendered_at'])) {
    $_SESSION['fastigheter_rendered_at'] = time();
}

$properties = [];
for ($i = 1; $i <= 4; $i++) {
    $properties[$i] = [
        'image' => $content["properties.$i.image"] ?? '',
        'name' => $content["properties.$i.name"] ?? '',
        'description' => $content["properties.$i.description"] ?? '',
    ];
}

$errors = [];
$sent = false;
$values = [
    'namn' => '',
    'telefon' => '',
    'epost' => '',
    'fastighet' => '',
    'meddelande' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) {
        $errors[] = 'Sessionen gick ut. Ladda om sidan och försök igen.';
    } elseif (!empty($_POST['hemsida'] ?? '')) {
        // Honeypot ifylld - låtsas att allt gick bra, skicka inget.
        $sent = true;
    } else {
        $renderedAt = (int) ($_SESSION['fastigheter_rendered_at'] ?? 0);
        if (time() - $renderedAt < 3) {
            $errors[] = 'Formuläret skickades för snabbt. Vänta någon sekund och försök igen.';
        }

        $values['namn'] = trim((string) ($_POST['namn'] ?? ''));
        $values['telefon'] = trim((string) ($_POST['telefon'] ?? ''));
        $values['epost'] = trim((string) ($_POST['epost'] ?? ''));
        $values['fastighet'] = (string) ($_POST['fastighet'] ?? '');
        $values['meddelande'] = trim((string) ($_POST['meddelande'] ?? ''));

        $fastighetOptions = ['' => 'Oavsett fastighet'];
        foreach ($properties as $i => $property) {
            if ($property['name'] !== '') {
                $fastighetOptions['p' . $i] = $property['name'];
            }
        }

        if ($values['namn'] === '' || mb_strlen($values['namn']) < 2 || mb_strlen($values['namn']) > 100 || pf_header_unsafe($values['namn'])) {
            $errors[] = 'Ange ditt namn (2–100 tecken).';
        }
        if (!preg_match('/^[0-9+\-() ]{5,30}$/', $values['telefon'])) {
            $errors[] = 'Ange ett giltigt telefonnummer.';
        }
        if ($values['epost'] !== '' && filter_var($values['epost'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Ange en giltig e-postadress, eller lämna fältet tomt.';
        }
        if (!array_key_exists($values['fastighet'], $fastighetOptions)) {
            $errors[] = 'Välj en fastighet, eller "Oavsett fastighet".';
        }
        if (mb_strlen($values['meddelande']) > 2000) {
            $errors[] = 'Meddelandet är för långt (max 2000 tecken).';
        }

        if (empty($errors)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $throttleError = pf_throttle_check_and_record('fastigheter', $ip);
            if ($throttleError !== null) {
                $errors[] = $throttleError;
            }
        }

        if (empty($errors)) {
            $subject = 'Intresseanmälan fastighet: ' . $fastighetOptions[$values['fastighet']];
            $bodyLines = [
                'Ny intresseanmälan/kö-förfrågan från webbplatsen',
                '',
                'Namn: ' . $values['namn'],
                'Telefon: ' . $values['telefon'],
                'E-post: ' . ($values['epost'] !== '' ? $values['epost'] : '(ej angiven)'),
                'Fastighet/område: ' . $fastighetOptions[$values['fastighet']],
                '',
                'Meddelande:',
                $values['meddelande'] !== '' ? $values['meddelande'] : '(inget meddelande)',
            ];
            $bodyText = implode("\n", $bodyLines);

            $sendTo = $email !== '' ? $email : 'patrik@pifastab.se';
            $replyTo = $values['epost'] !== '' ? $values['epost'] : null;

            $sent = pf_send_form_mail($sendTo, $sendTo, $replyTo, $subject, $bodyText, null);
            if (!$sent) {
                $errors[] = 'Kunde inte skicka anmälan just nu. Ring oss gärna istället.';
            }
        }
    }
}

$fastighetOptionsForView = ['' => 'Oavsett fastighet'];
foreach ($properties as $i => $property) {
    if ($property['name'] !== '') {
        $fastighetOptionsForView['p' . $i] = $property['name'];
    }
}
?>
<!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Fastigheter som Pifast AB äger och hyr ut, samt intresseanmälan/kö.">
<title>Våra fastigheter | Pifast AB</title>
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

<section class="section-py">
  <div class="container">
    <div class="section-heading text-center">
      <p class="overline">Våra fastigheter</p>
      <h2>Fastigheter vi äger och hyr ut</h2>
    </div>
    <div class="row g-4">
      <?php for ($i = 1; $i <= 4; $i++): ?>
      <div class="col-md-6 col-lg-3">
        <article class="service-card">
          <div class="service-card-img" data-edit-image="properties.<?= $i ?>.image">
            <img src="<?= pf_image_url($content, "properties.$i.image") ?>" alt="Fastighet, Pifast AB" loading="lazy">
          </div>
          <div class="service-card-body">
            <h3<?= pf_edit_attrs($content, "properties.$i.name") ?>><?= pf_text($content, "properties.$i.name") ?></h3>
            <p<?= pf_edit_attrs($content, "properties.$i.description") ?>><?= pf_text($content, "properties.$i.description") ?></p>
          </div>
        </article>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="fel-form section-py bg-offwhite"><div class="container">
  <h1>Intresseanmälan</h1>
  <p>Ser du inget ledigt just nu, eller vill du bara ställa dig i kö för någon av våra fastigheter? Fyll i formuläret så hör vi av oss så snart något blir tillgängligt.</p>

  <?php if ($sent): ?>
  <div class="success">Tack! Din intresseanmälan har skickats. Vi hör av oss om något blir ledigt.</div>
  <?php else: ?>

  <?php if (!empty($errors)): ?>
  <div class="errors"><ul>
    <?php foreach ($errors as $error): ?>
    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul></div>
  <?php endif; ?>

  <form method="post" action="fastigheter.php">
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
      <label for="fastighet">Fastighet/område</label>
      <select id="fastighet" name="fastighet" required>
        <?php foreach ($fastighetOptionsForView as $key => $label): ?>
        <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"<?= $values['fastighet'] === $key ? ' selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="meddelande">Meddelande (frivilligt)</label>
      <textarea id="meddelande" name="meddelande" maxlength="2000" placeholder="T.ex. typ av lägenhet, antal rum eller önskat inflyttningsdatum"><?= htmlspecialchars($values['meddelande'], ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>
    <button type="submit" class="btn btn-brand">Skicka intresseanmälan</button>
  </form>

  <?php endif; ?>
</div></section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>
