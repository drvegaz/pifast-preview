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
?>
<!doctype html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Pifast AB – badrumsrenovering, fastighetsservice och byggarbeten i Nättraby och Karlskrona.">
<title>Pifast AB | Bygg &amp; Fastigheter i Nättraby</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="style.css">
<?php if ($admin): ?>
<link rel="stylesheet" href="assets/admin/edit.css">
<?php endif; ?>
</head>
<body class="<?= $admin ? 'admin-mode' : '' ?>"<?= $admin ? ' data-csrf="' . htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
<?php require __DIR__ . '/includes/partials/header.php'; ?>
<main>

<section class="hero" id="hem" data-edit-image="hero.image" style="--hero-image:url('<?= pf_image_url($content, 'hero.image') ?>')">
  <div class="container">
    <div class="row">
      <div class="col-lg-7">
        <div class="hero-text">
          <p class="overline"<?= pf_edit_attrs($content, 'hero.overline') ?>><?= pf_text($content, 'hero.overline') ?></p>
          <h1<?= pf_edit_attrs($content, 'hero.title') ?>><?= pf_text($content, 'hero.title') ?></h1>
          <p<?= pf_edit_attrs($content, 'hero.subtitle') ?>><?= pf_text($content, 'hero.subtitle') ?></p>
          <div class="hero-buttons">
            <a class="btn btn-brand" href="<?= htmlspecialchars($telHref, ENT_QUOTES, 'UTF-8') ?>">Ring <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?></a>
            <a class="btn btn-outline-light-custom" href="#tjanster"<?= pf_edit_attrs($content, 'hero.button_secondary_label') ?>><?= pf_text($content, 'hero.button_secondary_label') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section-py">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-6">
        <p class="overline"<?= pf_edit_attrs($content, 'intro.overline') ?>><?= pf_text($content, 'intro.overline') ?></p>
        <h2<?= pf_edit_attrs($content, 'intro.heading') ?>><?= pf_text($content, 'intro.heading') ?></h2>
      </div>
      <div class="col-lg-6">
        <p class="intro-text"<?= pf_edit_attrs($content, 'intro.paragraph') ?>><?= pf_text($content, 'intro.paragraph') ?></p>
      </div>
    </div>
  </div>
</section>

<section class="section-py bg-offwhite" id="tjanster">
  <div class="container">
    <div class="section-heading text-center">
      <p class="overline"<?= pf_edit_attrs($content, 'services.overline') ?>><?= pf_text($content, 'services.overline') ?></p>
      <h2<?= pf_edit_attrs($content, 'services.heading') ?>><?= pf_text($content, 'services.heading') ?></h2>
    </div>
    <div class="row g-4">
      <?php
      $serviceImages = [
          1 => 'https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?auto=format&fit=crop&w=700&q=80',
          2 => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=700&q=80',
          3 => 'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=700&q=80',
      ];
      for ($i = 1; $i <= 3; $i++):
      ?>
      <div class="col-md-4">
        <article class="service-card">
          <div class="service-card-img"><img src="<?= htmlspecialchars($serviceImages[$i], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"></div>
          <div class="service-card-body">
            <h3<?= pf_edit_attrs($content, "services.$i.title") ?>><?= pf_text($content, "services.$i.title") ?></h3>
            <p<?= pf_edit_attrs($content, "services.$i.body") ?>><?= pf_text($content, "services.$i.body") ?></p>
            <a class="service-card-link" href="#kontakt">Läs mer &rarr;</a>
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
      <div class="col-lg-6">
        <p class="overline"<?= pf_edit_attrs($content, 'about.overline') ?>><?= pf_text($content, 'about.overline') ?></p>
        <h2<?= pf_edit_attrs($content, 'about.heading') ?>><?= pf_text($content, 'about.heading') ?></h2>
        <p<?= pf_edit_attrs($content, 'about.paragraph') ?>><?= pf_text($content, 'about.paragraph') ?></p>
        <a class="btn btn-outline-light-custom mb-4" href="#kontakt"<?= pf_edit_attrs($content, 'about.button_label') ?>><?= pf_text($content, 'about.button_label') ?></a>
        <ul class="usp-list">
          <li class="usp-item">
            <span class="usp-icon">01</span>
            <strong<?= pf_edit_attrs($content, 'about.list.1') ?>><?= pf_text($content, 'about.list.1') ?></strong>
          </li>
          <li class="usp-item">
            <span class="usp-icon">02</span>
            <strong<?= pf_edit_attrs($content, 'about.list.2') ?>><?= pf_text($content, 'about.list.2') ?></strong>
          </li>
          <li class="usp-item">
            <span class="usp-icon">03</span>
            <strong<?= pf_edit_attrs($content, 'about.list.3') ?>><?= pf_text($content, 'about.list.3') ?></strong>
          </li>
        </ul>
      </div>
      <div class="col-lg-6">
        <div class="about-image-frame" data-edit-image="about.image" style="--about-image:url('<?= pf_image_url($content, 'about.image') ?>')"></div>
      </div>
    </div>
  </div>
</section>

<section class="section-py" id="referenser">
  <div class="container">
    <div class="section-heading text-center">
      <p class="overline">Referenser</p>
      <h2>Några av våra arbeten</h2>
    </div>
    <div class="row g-3">
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

<section class="contact-cta section-py" id="kontakt">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-6">
        <p class="overline"<?= pf_edit_attrs($content, 'contact.overline') ?>><?= pf_text($content, 'contact.overline') ?></p>
        <h2<?= pf_edit_attrs($content, 'contact.heading') ?>><?= pf_text($content, 'contact.heading') ?></h2>
        <p<?= pf_edit_attrs($content, 'contact.paragraph') ?>><?= pf_text($content, 'contact.paragraph') ?></p>
        <a class="btn btn-brand" href="<?= htmlspecialchars($mailHref, ENT_QUOTES, 'UTF-8') ?>"<?= pf_edit_attrs($content, 'contact.button_label') ?>><?= pf_text($content, 'contact.button_label') ?></a>
      </div>
      <div class="col-lg-6">
        <div class="contact-info">
          <div class="contact-info-row">
            <span class="contact-info-icon">&#9742;</span>
            <a href="<?= htmlspecialchars($telHref, ENT_QUOTES, 'UTF-8') ?>"<?= pf_edit_attrs($content, 'contact.phone') ?>><?= pf_text($content, 'contact.phone') ?></a>
          </div>
          <div class="contact-info-row">
            <span class="contact-info-icon">&#9993;</span>
            <a href="<?= htmlspecialchars($mailHref, ENT_QUOTES, 'UTF-8') ?>"<?= pf_edit_attrs($content, 'contact.email') ?>><?= pf_text($content, 'contact.email') ?></a>
          </div>
          <div class="contact-info-row">
            <span class="contact-info-icon">&#8962;</span>
            <p<?= pf_edit_attrs($content, 'contact.address') ?>><?= pf_text($content, 'contact.address') ?></p>
          </div>
          <div class="contact-info-row">
            <span class="contact-info-icon">&#9636;</span>
            <p>Bankgiro <span<?= pf_edit_attrs($content, 'contact.bankgiro') ?>><?= pf_text($content, 'contact.bankgiro') ?></span></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

</main>
<?php require __DIR__ . '/includes/partials/footer.php'; ?>
</body>
</html>
