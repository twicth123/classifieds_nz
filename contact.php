<?php
/**
 * Contact Us page
 * Assumes the same include structure as about.php:
 * - includes/header.php  -> navbar + opens the main content container
 * - includes/footer.php  -> closes the page
 * - Constants SITE_NAME and BASE_URL already defined
 *
 * Update ADMIN_EMAIL below to your real inbox, and swap mail() for
 * PHPMailer/SMTP if your host blocks the built-in mail() function.
 */
require_once __DIR__ . '/includes/header.php';

define('ADMIN_EMAIL', 'support@example.com'); // <-- change this

$success = false;
$errors  = [];
$old     = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Honeypot field (hidden from real users via CSS) — bots tend to fill it in
    if (!empty($_POST['website'])) {
        $success = true; // silently pretend it worked, drop it
    } else {

        $old['name']    = trim($_POST['name']    ?? '');
        $old['email']   = trim($_POST['email']   ?? '');
        $old['subject'] = trim($_POST['subject'] ?? '');
        $old['message'] = trim($_POST['message'] ?? '');

        if ($old['name'] === '') {
            $errors[] = 'Please enter your name.';
        }
        if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($old['subject'] === '') {
            $errors[] = 'Please enter a subject.';
        }
        if ($old['message'] === '' || strlen($old['message']) < 10) {
            $errors[] = 'Please enter a message of at least 10 characters.';
        }

        if (empty($errors)) {
            $to      = ADMIN_EMAIL;
            $subject = '[' . SITE_NAME . ' Contact] ' . $old['subject'];
            $body    = "New contact form submission:\n\n"
                     . "Name: {$old['name']}\n"
                     . "Email: {$old['email']}\n\n"
                     . "Message:\n{$old['message']}\n";
            $headers = 'From: ' . SITE_NAME . ' <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'example.com') . ">\r\n"
                     . 'Reply-To: ' . $old['email'] . "\r\n";

            // NOTE: mail() requires a configured mail server on your host.
            // Replace this with PHPMailer/SMTP or a service like SendGrid/Mailgun
            // if messages aren't arriving.
            $sent = @mail($to, $subject, $body, $headers);

            if ($sent) {
                $success = true;
                $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
            } else {
                $errors[] = 'Sorry, something went wrong sending your message. Please try again or email us directly.';
            }
        }
    }
}
?>

<!-- ================= HERO ================= -->
<section class="about-hero py-5 mb-5 rounded-4">
  <div class="container py-4 text-center">
    <span class="badge rounded-pill bg-indigo-soft text-indigo-600 fw-semibold mb-3 px-3 py-2">
      <i class="bi bi-envelope-fill me-1"></i>Contact <?= SITE_NAME ?>
    </span>
    <h1 class="fw-bold display-6 mb-3">We'd love to hear from you.</h1>
    <p class="text-secondary col-lg-7 mx-auto mb-0">
      Questions about buying, selling, or your account? Send us a message and
      our team will get back to you as soon as possible.
    </p>
  </div>
</section>

<section class="container mb-5">
  <div class="row g-4">

    <!-- ================= CONTACT FORM ================= -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 h-100">
        <h5 class="fw-bold mb-4">Send Us a Message</h5>

        <?php if ($success): ?>
          <div class="alert alert-success d-flex align-items-center gap-2 rounded-3" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>Thanks for reaching out! We'll get back to you shortly.</div>
          </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger rounded-3" role="alert">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/contact.php" novalidate>

          <!-- Honeypot: hidden from real users, bots often fill this in -->
          <div class="d-none" aria-hidden="true">
            <label for="website">Website</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label for="name" class="form-label small fw-semibold">Your Name</label>
              <input type="text" class="form-control rounded-3" id="name" name="name"
                     value="<?= htmlspecialchars($old['name']) ?>" placeholder="e.g. Priya Sharma" required>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label small fw-semibold">Email Address</label>
              <input type="email" class="form-control rounded-3" id="email" name="email"
                     value="<?= htmlspecialchars($old['email']) ?>" placeholder="you@example.com" required>
            </div>
            <div class="col-12">
              <label for="subject" class="form-label small fw-semibold">Subject</label>
              <input type="text" class="form-control rounded-3" id="subject" name="subject"
                     value="<?= htmlspecialchars($old['subject']) ?>" placeholder="What's this about?" required>
            </div>
            <div class="col-12">
              <label for="message" class="form-label small fw-semibold">Message</label>
              <textarea class="form-control rounded-3" id="message" name="message" rows="5"
                        placeholder="Tell us how we can help..." required><?= htmlspecialchars($old['message']) ?></textarea>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-indigo rounded-3 px-4 py-2">
                <i class="bi bi-send-fill me-1"></i>Send Message
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- ================= CONTACT INFO ================= -->
    <div class="col-lg-5">
      <div class="d-grid gap-4 h-100">

        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h6 class="fw-semibold text-uppercase small tracking-wider text-indigo-600 mb-3">Get In Touch</h6>
          <ul class="list-unstyled d-grid gap-3 mb-0">
            <li class="d-flex align-items-start gap-3">
              <span class="step-icon"><i class="bi bi-geo-alt"></i></span>
              <span class="small text-secondary">123 Market Street, Suite 400<br>Hyderabad, Telangana 500001</span>
            </li>
            <li class="d-flex align-items-start gap-3">
              <span class="step-icon"><i class="bi bi-envelope-at"></i></span>
              <a href="mailto:<?= ADMIN_EMAIL ?>" class="small text-secondary text-decoration-none hover-indigo-link"><?= ADMIN_EMAIL ?></a>
            </li>
            <li class="d-flex align-items-start gap-3">
              <span class="step-icon"><i class="bi bi-telephone"></i></span>
              <a href="tel:+911234567890" class="small text-secondary text-decoration-none hover-indigo-link">+91 123 456 7890</a>
            </li>
            <li class="d-flex align-items-start gap-3">
              <span class="step-icon"><i class="bi bi-clock"></i></span>
              <span class="small text-secondary">Mon &ndash; Sat, 9:00 AM &ndash; 6:00 PM IST</span>
            </li>
          </ul>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 bg-indigo-soft">
          <h6 class="fw-bold mb-2"><i class="bi bi-shield-check text-indigo-600 me-1"></i>Before You Trade</h6>
          <p class="small text-secondary mb-0">
            Always trade locally, meet in public places, and verify products
            before paying. Never wire money to sellers online.
          </p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 flex-grow-1">
          <h6 class="fw-bold mb-2">Looking for quick answers?</h6>
          <p class="small text-secondary mb-3">Check our FAQs before reaching out &mdash; you might find what you need right away.</p>
          <a href="<?= BASE_URL ?>/faq.php" class="btn btn-outline-indigo rounded-3 align-self-start">
            <i class="bi bi-question-circle me-1"></i>Visit FAQs
          </a>
        </div>

      </div>
    </div>

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
    flex: 0 0 auto;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background-color: rgba(99, 102, 241, 0.1);
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
  }
  .hover-indigo-link:hover { color: #4f46e5 !important; }
  .form-control:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15);
  }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
