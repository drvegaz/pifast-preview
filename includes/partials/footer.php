<footer>
  <div class="container">
    <div class="footer-top">
      <a class="footer-logo" href="index.php#hem" aria-label="Pifast startsida"><img src="assets/img/frontlogga-red-invert.png" alt="PIFAST AB – Bygg &amp; Fastighetsservice"></a>
      <ul class="footer-links">
        <li><a href="index.php#hem">Hem</a></li>
        <li><a href="index.php#om-oss">Om oss</a></li>
        <li><a href="index.php#tjanster">Tjänster</a></li>
        <li><a href="index.php#referenser">Referenser</a></li>
        <li><a href="fastigheter.php">Våra fastigheter</a></li>
        <li><a href="index.php#kontakt">Kontakt</a></li>
        <li><a href="felanmalan.php">Felanmälan</a></li>
      </ul>
    </div>
    <div class="footer-bottom">
      <span>PIFAST AB &ndash; Bygg &amp; Fastighetsservice &middot; <span<?= pf_edit_attrs($content, 'topbar.badge_1') ?>><?= pf_text($content, 'topbar.badge_1') ?></span></span>
      <span>&copy; <?= date('Y') ?> PIFAST AB. Org.nr <span<?= pf_edit_attrs($content, 'footer.org_number') ?>><?= pf_text($content, 'footer.org_number') ?></span></span>
      <a class="footer-admin-link" href="admin/login.php">Logga in</a>
    </div>
  </div>
</footer>
<button type="button" class="scroll-top-btn" id="pfScrollTop" aria-label="Till toppen"><?= pf_icon('chevron-up') ?></button>
<script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
<script>
(function () {
  var navEl = document.getElementById('pfNav');
  if (navEl) {
    navEl.querySelectorAll('.nav-link').forEach(function (link) {
      link.addEventListener('click', function () {
        if (navEl.classList.contains('show')) {
          bootstrap.Collapse.getOrCreateInstance(navEl, { toggle: false }).hide();
        }
      });
    });
  }

  var scrollBtn = document.getElementById('pfScrollTop');
  if (scrollBtn) {
    window.addEventListener('scroll', function () {
      scrollBtn.classList.toggle('visible', window.scrollY > 480);
    });
    scrollBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
})();
</script>
<?php if ($admin): ?>
<script src="assets/admin/edit.js" defer></script>
<?php endif; ?>
