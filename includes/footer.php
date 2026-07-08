</div> <!-- Close main content container -->
<footer class="bg-dark text-light mt-auto py-5 border-top border-secondary">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <h5 class="fw-bold mb-3 text-indigo-300"><i class="bi bi-tag-fill me-2"></i><?= SITE_NAME ?></h5>
        <p class="text-secondary small mb-4">
          The ultimate classifieds platform for buying and selling anything locally. Find cars, electronics, houses, services, and more, hassle-free.
        </p>
        <div class="d-flex gap-3">
          <a href="#" class="text-secondary fs-5 hover-indigo"><i class="bi bi-facebook"></i></a>
          <a href="#" class="text-secondary fs-5 hover-indigo"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="text-secondary fs-5 hover-indigo"><i class="bi bi-instagram"></i></a>
          <a href="#" class="text-secondary fs-5 hover-indigo"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="fw-semibold text-uppercase mb-3 small tracking-wider text-muted">Quick Links</h6>
        <ul class="list-unstyled d-grid gap-2 small">
          <li><a href="<?= BASE_URL ?>/index.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-chevron-right me-1"></i>Browse Ads</a></li>
          <li><a href="<?= BASE_URL ?>/post_ad.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-plus-lg me-1"></i>Post a New Ad</a></li>
          <li><a href="<?= BASE_URL ?>/dashboard.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-speedometer2 me-1"></i>Seller Dashboard</a></li>
          <li><a href="<?= BASE_URL ?>/favorites.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-heart me-1"></i>My Saved Items</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="fw-semibold text-uppercase mb-3 small tracking-wider text-muted">Company</h6>
        <ul class="list-unstyled d-grid gap-2 small">
          <li><a href="<?= BASE_URL ?>/about.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-info-circle me-1"></i>About Us</a></li>
          <li><a href="<?= BASE_URL ?>/contact.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-envelope me-1"></i>Contact Us</a></li>
          <li><a href="<?= BASE_URL ?>/faq.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-question-circle me-1"></i>FAQs</a></li>
          <li><a href="<?= BASE_URL ?>/privacy.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-file-earmark-text me-1"></i>Privacy Policy</a></li>
          <li><a href="<?= BASE_URL ?>/terms.php" class="text-secondary text-decoration-none hover-white"><i class="bi bi-file-earmark-text me-1"></i>Terms of Service</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="fw-semibold text-uppercase mb-3 small tracking-wider text-muted">Get In Touch</h6>
        <ul class="list-unstyled d-grid gap-2 small mb-3">
          <li class="text-secondary"><i class="bi bi-geo-alt me-2"></i>123 Market Street, Suite 400, Hyderabad, TG 500001</li>
          <li><a href="mailto:support@example.com" class="text-secondary text-decoration-none hover-white"><i class="bi bi-envelope-at me-2"></i>support@example.com</a></li>
          <li><a href="tel:+911234567890" class="text-secondary text-decoration-none hover-white"><i class="bi bi-telephone me-2"></i>+91 123 456 7890</a></li>
        </ul>
      </div>
    </div>

    <div class="row g-4 mt-1">
      <div class="col-12">
        <h6 class="fw-semibold text-uppercase mb-3 small tracking-wider text-muted">Trust & Safety</h6>
        <p class="text-secondary small">
          Always trade locally, meet in public places, and verify products before paying. Never wire money to sellers online.
        </p>
        <div class="bg-secondary bg-opacity-10 p-3 rounded border border-secondary border-opacity-20 d-flex align-items-center gap-2">
          <i class="bi bi-shield-check text-indigo-400 fs-3"></i>
          <span class="small text-secondary">Verified buyer protections and secure local ads platform.</span>
        </div>
      </div>
    </div>

    <hr class="my-4 border-secondary opacity-20">
    <div class="d-flex flex-wrap justify-content-between align-items-center small text-secondary">
      <div>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</div>
      <div>Designed with <i class="bi bi-heart-fill text-danger mx-1"></i> for local trade.</div>
    </div>
  </div>
</footer>
<style>
  .hover-indigo:hover { color: #818cf8 !important; }
  .hover-white:hover { color: #ffffff !important; transform: translateX(3px); }
  .hover-white { transition: 0.2s ease; display: inline-block; }
  .tracking-wider { letter-spacing: 0.05em; }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>