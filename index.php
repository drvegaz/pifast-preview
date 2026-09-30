<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';
require_once __DIR__ . '/includes/content.php';

$content = pf_load_content($pdo);
$admin = is_admin();
$csrf = $admin ? csrf_token() : '';

$phone = $content['contact.phone'] ?? '';
$email = $content['contact.email'] ?? '';
$telHref = pf_tel_href($phone);
$mailHref = pf_mailto_href($email);
$pfFacebookUrl = trim($content['footer.facebook_url'] ?? '');
?>
<!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="PIFAST AB är ett familjeföretag i Nättraby som hjälper till med badrumsrenovering, byggarbeten och fastighetsservice i Nättraby och Karlskrona-området.">
<title>PIFAST AB – Bygg &amp; fastigheter i Nättraby</title>
<link rel="canonical" href="https://www.pifastab.se/">
<meta property="og:type" content="website">
<meta property="og:site_name" content="PIFAST AB">
<meta property="og:title" content="PIFAST AB – Bygg &amp; fastigheter i Nättraby">
<meta property="og:description" content="Familjeföretag i Nättraby som hjälper till med badrumsrenovering, byggarbeten och fastighetsservice.">
<meta property="og:url" content="https://www.pifastab.se/">
<meta property="og:image" content="<?= pf_image_url($content, 'hero.image') ?>">
<meta property="og:locale" content="sv_SE">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
<?php if ($admin): ?>
<link rel="stylesheet" href="assets/admin/edit.css">
<?php endif; ?>
</head>
<body class="<?= $admin ? 'admin-mode' : '' ?>"<?= $admin ? ' data-csrf="' . htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
<?php require __DIR__ . '/includes/partials/header.php'; ?>
<main>

<section class="hero" id="hem">
  <div class="hero-image-frame" data-edit-image="hero.image" style="--hero-image:url('<?= pf_image_url($content, 'hero.image') ?>')">
    <span class="hero-caption"<?= pf_edit_attrs($content, 'hero.caption') ?>><?= pf_text($content, 'hero.caption') ?></span>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <div class="hero-text">
          <p class="overline"<?= pf_edit_attrs($content, 'hero.overline') ?>><?= pf_text($content, 'hero.overline') ?></p>
          <h1 class="hero-title">
            <span<?= pf_edit_attrs($content, 'hero.title') ?>><?= pf_text($content, 'hero.title') ?></span>
            <span<?= pf_edit_attrs($content, 'hero.title_accent') ?>><?= pf_text($content, 'hero.title_accent') ?></span>
          </h1>
          <p<?= pf_edit_attrs($content, 'hero.subtitle') ?>><?= pf_text($content, 'hero.subtitle') ?></p>
          <div class="hero-buttons">
            <a class="btn btn-brand" href="#kontakt">Kontakta oss <?= pf_icon('arrow-right') ?></a>
            <a class="btn btn-white-custom" href="#tjanster"<?= pf_edit_attrs($content, 'hero.button_secondary_label') ?>><?= pf_text($content, 'hero.button_secondary_label') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-py bg-offwhite" id="tjanster">
  <div class="container">
    <div class="section-heading">
      <p class="overline"<?= pf_edit_attrs($content, 'services.overline') ?>><?= pf_text($content, 'services.overline') ?></p>
      <h2 class="heading-underline"<?= pf_edit_attrs($content, 'services.heading') ?>><?= pf_text($content, 'services.heading') ?></h2>
    </div>
    <div class="row g-4">
      <?php for ($i = 1; $i <= 4; $i++): ?>
      <div class="col-md-6 col-lg-3">
        <article class="service-card">
          <div class="service-card-img" data-edit-image="services.<?= $i ?>.image">
            <img src="<?= pf_image_url($content, "services.$i.image") ?>" alt="" loading="lazy">
          </div>
          <div class="service-card-body">
            <h3<?= pf_edit_attrs($content, "services.$i.title") ?>><?= pf_text($content, "services.$i.title") ?></h3>
            <p<?= pf_edit_attrs($content, "services.$i.body") ?>><?= pf_text($content, "services.$i.body") ?></p>
            <a class="service-card-link" href="#kontakt">Läs mer <?= pf_icon('arrow-right') ?></a>
          </div>
        </article>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="about-section section-py" id="om-oss">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5">
        <p class="overline"<?= pf_edit_attrs($content, 'about.overline') ?>><?= pf_text($content, 'about.overline') ?></p>
        <h2 class="heading-underline"<?= pf_edit_attrs($content, 'about.heading') ?>><?= pf_text($content, 'about.heading') ?></h2>
        <p<?= pf_edit_attrs($content, 'about.paragraph') ?>><?= pf_text($content, 'about.paragraph') ?></p>
        <a class="btn btn-brand" href="#kontakt"><span<?= pf_edit_attrs($content, 'about.button_label') ?>><?= pf_text($content, 'about.button_label') ?></span> <?= pf_icon('arrow-right') ?></a>
      </div>
      <div class="col-md-6 col-lg-4">
        <ul class="about-features">
          <li class="about-feature">
            <span class="about-feature-icon"><?= pf_icon('people') ?></span>
            <span>
              <span class="about-feature-label"<?= pf_edit_attrs($content, 'about.feature_1.label') ?>><?= pf_text($content, 'about.feature_1.label') ?></span>
              <span class="about-feature-text"<?= pf_edit_attrs($content, 'about.feature_1.text') ?>><?= pf_text($content, 'about.feature_1.text') ?></span>
            </span>
          </li>
          <li class="about-feature">
            <span class="about-feature-icon"><?= pf_icon('diamond') ?></span>
            <span>
              <span class="about-feature-label"<?= pf_edit_attrs($content, 'about.feature_2.label') ?>><?= pf_text($content, 'about.feature_2.label') ?></span>
              <span class="about-feature-text"<?= pf_edit_attrs($content, 'about.feature_2.text') ?>><?= pf_text($content, 'about.feature_2.text') ?></span>
            </span>
          </li>
          <li class="about-feature">
            <span class="about-feature-icon"><?= pf_icon('house') ?></span>
            <span>
              <span class="about-feature-label"<?= pf_edit_attrs($content, 'about.feature_3.label') ?>><?= pf_text($content, 'about.feature_3.label') ?></span>
              <span class="about-feature-text"<?= pf_edit_attrs($content, 'about.feature_3.text') ?>><?= pf_text($content, 'about.feature_3.text') ?></span>
            </span>
          </li>
          <li class="about-feature">
            <span class="about-feature-icon"><?= pf_icon('pin') ?></span>
            <span>
              <span class="about-feature-label"<?= pf_edit_attrs($content, 'about.feature_4.label') ?>><?= pf_text($content, 'about.feature_4.label') ?></span>
              <span class="about-feature-text"<?= pf_edit_attrs($content, 'about.feature_4.text') ?>><?= pf_text($content, 'about.feature_4.text') ?></span>
            </span>
          </li>
        </ul>
      </div>
      <div class="col-md-6 col-lg-3">
        <div class="about-side-photo" data-edit-image="about.image" style="--about-image:url('<?= pf_image_url($content, 'about.image') ?>')"></div>
      </div>
    </div>
  </div>
</section>

<section class="section-py" id="referenser">
  <div class="container">
    <div class="section-header-row">
      <div>
        <p class="overline">Referenser</p>
        <h2 class="heading-underline">Några av våra arbeten</h2>
      </div>
      <a class="section-link-btn" href="#referenser">Se fler referenser <?= pf_icon('arrow-right') ?></a>
    </div>
    <div class="row g-4">
      <?php for ($i = 1; $i <= 4; $i++): ?>
      <div class="col-6 col-lg-3">
        <figure class="gallery-item" data-edit-image="gallery.<?= $i ?>.image">
          <img src="<?= pf_image_url($content, "gallery.$i.image") ?>" alt="Genomfört projekt, Pifast AB" loading="lazy">
        </figure>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="contact-cta section-py" id="kontakt" data-edit-image="contact.image" style="--contact-image:url('<?= pf_image_url($content, 'contact.image') ?>')">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-7">
        <p class="overline"<?= pf_edit_attrs($content, 'contact.overline') ?>><?= pf_text($content, 'contact.overline') ?></p>
        <h2<?= pf_edit_attrs($content, 'contact.heading') ?>><?= pf_text($content, 'contact.heading') ?></h2>
        <p<?= pf_edit_attrs($content, 'contact.paragraph') ?>><?= pf_text($content, 'contact.paragraph') ?></p>
        <div class="contact-cta-buttons">
          <a class="btn btn-brand" href="#kontakt"><span<?= pf_edit_attrs($content, 'contact.button_label') ?>><?= pf_text($content, 'contact.button_label') ?></span> <?= pf_icon('arrow-right') ?></a>
          <a class="contact-cta-phone" href="<?= htmlspecialchars($telHref, ENT_QUOTES, 'UTF-8') ?>"><?= pf_icon('phone') ?><span<?= pf_edit_attrs($content, 'contact.phone') ?>><?= pf_text($content, 'contact.phone') ?></span></a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="contact-info">
          <div class="contact-info-row">
            <span class="contact-info-icon"><?= pf_icon('pin') ?></span>
            <?php /* OBS: adressen nedan (contact.address) är ett innehållsfält hämtat från databasen. Verifiera med företaget att den stämmer innan den ändras. */ ?>
            <span<?= pf_edit_attrs($content, 'contact.address') ?>><?= pf_text($content, 'contact.address') ?></span>
          </div>
          <div class="contact-info-row">
            <span class="contact-info-icon"><?= pf_icon('mail') ?></span>
            <a href="<?= htmlspecialchars($mailHref, ENT_QUOTES, 'UTF-8') ?>"<?= pf_edit_attrs($content, 'contact.email') ?>><?= pf_text($content, 'contact.email') ?></a>
          </div>
          <?php
          /* OBS: footer.facebook_url går INTE att redigera med pennan på sidan (fältets värde är en
             länk-URL, inte synlig text). Sätt den riktiga adressen direkt i databasen tills vidare,
             t.ex. UPDATE content SET value='https://facebook.com/...' WHERE key='footer.facebook_url'. */
          ?>
          <div class="contact-info-row">
            <span class="contact-info-icon"><?= pf_icon_facebook('pf-icon') ?></span>
            <?php if ($pfFacebookUrl !== ''): ?>
            <a href="<?= htmlspecialchars($pfFacebookUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">Följ oss på Facebook</a>
            <?php else: ?>
            <span style="opacity:.5">Följ oss på Facebook</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <span class="contact-cta-caption"<?= pf_edit_attrs($content, 'contact.caption') ?>><?= pf_text($content, 'contact.caption') ?></span>
</section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>
