<div class="topbar"><div class="container">
  <span class="topbar-badge"<?= pf_edit_attrs($content, 'topbar.badge_1') ?>><?= pf_text($content, 'topbar.badge_1') ?></span>
  <span class="topbar-badge"<?= pf_edit_attrs($content, 'topbar.badge_2') ?>><?= pf_text($content, 'topbar.badge_2') ?></span>
  <span class="topbar-badge"<?= pf_edit_attrs($content, 'topbar.badge_3') ?>><?= pf_text($content, 'topbar.badge_3') ?></span>
  <span class="topbar-badge"<?= pf_edit_attrs($content, 'topbar.badge_4') ?>><?= pf_text($content, 'topbar.badge_4') ?></span>
</div></div>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php#hem" aria-label="Pifast startsida">PI<span>FAST</span><small>AB</small></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#pfNav" aria-controls="pfNav" aria-expanded="false" aria-label="Öppna meny">
      <span class="navbar-toggler-icon-custom"></span>
    </button>
    <div class="collapse navbar-collapse" id="pfNav">
      <ul class="navbar-nav mx-lg-auto my-3 my-lg-0 gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="index.php#hem" data-bs-toggle="collapse" data-bs-target="#pfNav">Hem</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#tjanster" data-bs-toggle="collapse" data-bs-target="#pfNav">Tjänster</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#om-oss" data-bs-toggle="collapse" data-bs-target="#pfNav">Om oss</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#referenser" data-bs-toggle="collapse" data-bs-target="#pfNav">Referenser</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#kontakt" data-bs-toggle="collapse" data-bs-target="#pfNav">Kontakt</a></li>
      </ul>
      <a class="navbar-cta" href="<?= htmlspecialchars($telHref, ENT_QUOTES, 'UTF-8') ?>"<?= pf_edit_attrs($content, 'contact.phone') ?>><?= pf_text($content, 'contact.phone') ?></a>
    </div>
  </div>
</nav>
