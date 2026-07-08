<?php
/**
 * About Us page
 * Assumes the same include structure as the rest of the site:
 * - includes/header.php  -> opens <html>, <head>, navbar, and the
 *                            main content container div
 * - includes/footer.php  -> the footer you already have, closes </html>
 * - Constants SITE_NAME and BASE_URL are already defined (as in your footer)
 *
 * If your header/footer live at different paths, just update the two
 * include lines below.
 */
require_once __DIR__ . '/includes/header.php';
?>

<!-- ================= HERO ================= -->
<section class="about-hero py-5 mb-5 rounded-4">
  <div class="container py-4 text-center">
    <span class="badge rounded-pill bg-indigo-soft text-indigo-600 fw-semibold mb-3 px-3 py-2">
      <i class="bi bi-tag-fill me-1"></i>About <?= SITE_NAME ?>
    </span>
    <h1 class="fw-bold display-6 mb-3">Buying and selling, made simple.</h1>
    <p class="text-secondary col-lg-7 mx-auto mb-0">
      <?= SITE_NAME ?> is a local classifieds platform built to help people in your
      city buy, sell, and discover things they actually need &mdash; cars, gadgets,
      homes, services and more &mdash; without the hassle of big, impersonal marketplaces.
    </p>
  </div>
</section>

<!-- ================= STATS ================= -->
<section class="container mb-5">
  <div class="row g-4 text-center">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 py-4">
        <i class="bi bi-megaphone-fill text-indigo-600 fs-2 mb-2"></i>
        <h3 class="fw-bold mb-0">25K+</h3>
        <p class="text-secondary small mb-0">Active Listings</p>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 py-4">
        <i class="bi bi-people-fill text-indigo-600 fs-2 mb-2"></i>
        <h3 class="fw-bold mb-0">40K+</h3>
        <p class="text-secondary small mb-0">Registered Users</p>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 py-4">
        <i class="bi bi-geo-alt-fill text-indigo-600 fs-2 mb-2"></i>
        <h3 class="fw-bold mb-0">50+</h3>
        <p class="text-secondary small mb-0">Cities Covered</p>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 h-100 py-4">
        <i class="bi bi-shield-check text-indigo-600 fs-2 mb-2"></i>
        <h3 class="fw-bold mb-0">99%</h3>
        <p class="text-secondary small mb-0">Verified Safe Trades</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= MISSION / STORY ================= -->
<section class="container mb-5">
  <div class="row g-4 align-items-center">
    <div class="col-lg-6">
      <h6 class="fw-semibold text-uppercase small tracking-wider text-indigo-600 mb-2">Our Story</h6>
      <h2 class="fw-bold mb-3">Built for local communities, not algorithms.</h2>
      <p class="text-secondary">
        We started <?= SITE_NAME ?> because we were tired of noisy, ad-cluttered
        marketplaces that made it hard to find genuine local deals. Our goal is
        simple: give buyers and sellers a clean, fast, and trustworthy place to
        connect &mdash; with the people who need it, in the city they live in.
      </p>
      <p class="text-secondary mb-0">
        Every feature we build, from filters to seller dashboards, is designed
        around one question: does this make local trade easier and safer?
      </p>
    </div>
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-light">
        <h6 class="fw-semibold text-uppercase small tracking-wider text-muted mb-3">What We Value</h6>
        <ul class="list-unstyled d-grid gap-3 mb-0">
          <li class="d-flex align-items-start gap-2">
            <i class="bi bi-check-circle-fill text-indigo-400 mt-1"></i>
            <span class="text-secondary small"><span class="text-light fw-semibold">Trust first</span> &mdash; every ad and account is built around safety, not just speed.</span>
          </li>
          <li class="d-flex align-items-start gap-2">
            <i class="bi bi-check-circle-fill text-indigo-400 mt-1"></i>
            <span class="text-secondary small"><span class="text-light fw-semibold">Local focus</span> &mdash; real connections in your city, not a faceless global feed.</span>
          </li>
          <li class="d-flex align-items-start gap-2">
            <i class="bi bi-check-circle-fill text-indigo-400 mt-1"></i>
            <span class="text-secondary small"><span class="text-light fw-semibold">Simplicity</span> &mdash; no clutter, no distractions, just what you're looking for.</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ================= HOW IT WORKS ================= -->
<section class="container mb-5">
  <div class="text-center mb-4">
    <h6 class="fw-semibold text-uppercase small tracking-wider text-indigo-600 mb-2">How It Works</h6>
    <h2 class="fw-bold">Three steps to your next great deal</h2>
  </div>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
        <div class="step-icon mb-3">
          <i class="bi bi-plus-lg"></i>
        </div>
        <h5 class="fw-bold">1. Post Your Ad</h5>
        <p class="text-secondary small mb-0">
          Create a listing in minutes with photos, price, and a short description.
          It's free to get started.
        </p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
        <div class="step-icon mb-3">
          <i class="bi bi-chat-dots"></i>
        </div>
        <h5 class="fw-bold">2. Connect Locally</h5>
        <p class="text-secondary small mb-0">
          Interested buyers reach out directly. Filter by category, city, and
          price to find exactly what you need.
        </p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
        <div class="step-icon mb-3">
          <i class="bi bi-handshake"></i>
        </div>
        <h5 class="fw-bold">3. Trade Safely</h5>
        <p class="text-secondary small mb-0">
          Meet in public, verify the item, and complete the trade &mdash; guided
          by our trust &amp; safety recommendations.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ================= TRUST & SAFETY ================= -->
<section class="container mb-5">
  <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-indigo-soft">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <h6 class="fw-semibold text-uppercase small tracking-wider text-indigo-600 mb-2">Trust &amp; Safety</h6>
        <h3 class="fw-bold mb-2">Your safety is part of the product.</h3>
        <p class="text-secondary mb-0">
          Always trade locally, meet in public places, and verify products before
          paying. Never wire money to sellers online. We continuously review
          listings and accounts to keep <?= SITE_NAME ?> a safe place to buy and sell.
        </p>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a href="<?= BASE_URL ?>/post_ad.php" class="btn btn-indigo btn-lg rounded-3 px-4">
          <i class="bi bi-plus-lg me-1"></i>Post Your First Ad
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ================= CONTACT CTA ================= -->
<section class="container mb-5">
  <div class="text-center">
    <h5 class="fw-bold mb-2">Still have questions?</h5>
    <p class="text-secondary mb-3">Our team is happy to help &mdash; reach out any time.</p>
    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-indigo rounded-3 px-4">
      <i class="bi bi-envelope me-1"></i>Contact Us
    </a>
  </div>
</section>

<style>
  .text-indigo-600 { color: #4f46e5 !important; }
  .text-indigo-400 { color: #818cf8 !important; }
  .bg-indigo-soft   { background-color: rgba(99, 102, 241, 0.08); }
  .btn-indigo {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
  }
  .btn-indigo:hover {
    background-color: #4338ca;
    border-color: #4338ca;
    color: #fff;
  }
  .btn-outline-indigo {
    border: 1px solid #4f46e5;
    color: #4f46e5;
    background: transparent;
  }
  .btn-outline-indigo:hover {
    background-color: #4f46e5;
    color: #fff;
  }
  .about-hero {
    background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(99,102,241,0.02));
  }
  .tracking-wider { letter-spacing: 0.05em; }
  .step-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background-color: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
  }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
