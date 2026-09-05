<footer>
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4">
        <a class="navbar-brand d-inline-block mb-3" href="index.php#hem">PI<span>FAST</span><small>AB</small></a>
        <p>Familjeföretag inom bygg och fastighetsservice i Nättraby, sedan 2012.</p>
      </div>
      <div class="col-6 col-lg-4">
        <h4>Kontakt</h4>
        <p><a href="<?= htmlspecialchars($telHref, ENT_QUOTES, 'UTF-8') ?>"<?= pf_edit_attrs($content, 'contact.phone') ?>><?= pf_text($content, 'contact.phone') ?></a></p>
        <p><a href="<?= htmlspecialchars($mailHref, ENT_QUOTES, 'UTF-8') ?>"<?= pf_edit_attrs($content, 'contact.email') ?>><?= pf_text($content, 'contact.email') ?></a></p>
        <p<?= pf_edit_attrs($content, 'contact.address') ?>><?= pf_text($content, 'contact.address') ?></p>
      </div>
      <div class="col-6 col-lg-4">
        <h4>Snabblänkar</h4>
        <ul class="footer-links">
          <li><a href="index.php#hem">Hem</a></li>
          <li><a href="index.php#om-oss">Om oss</a></li>
          <li><a href="index.php#tjanster">Tjänster</a></li>
          <li><a href="index.php#referenser">Referenser</a></li>
          <li><a href="index.php#kontakt">Kontakt</a></li>
          <li><a href="felanmalan.php">Felanmälan</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> Pifast AB. Alla rättigheter förbehållna.</span>
      <span>Org.nr <span<?= pf_edit_attrs($content, 'footer.org_number') ?>><?= pf_text($content, 'footer.org_number') ?></span> &middot; Bankgiro <span<?= pf_edit_attrs($content, 'contact.bankgiro') ?>><?= pf_text($content, 'contact.bankgiro') ?></span> &middot; <span<?= pf_edit_attrs($content, 'footer.tax_status') ?>><?= pf_text($content, 'footer.tax_status') ?></span></span>
    </div>
  </div>
</footer>
<script src="assets/vendor/bootstrap/js/bootstrap.min.js"></script>
<?php if ($admin): ?>
<script src="assets/admin/edit.js" defer></script>
<?php endif; ?>
