<?php
// File: index.php  (project root — note: no ../ prefix on requires)
require_once 'config/database.php';
require_once 'includes/functions.php';
if (isLoggedIn()) { redirect('pages/dashboard.php'); }
$pageTitle = 'Blood Donor Finder — Save Lives';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>
<main>
  <!-- Hero Section -->
  <section class="py-5 text-white"
           style="background:linear-gradient(135deg,#922B21,#C0392B);">
    <div class="container text-center py-4">
      <div style="font-size:4rem;">🩸</div>
      <h1 class="display-4 fw-bold">Blood Donor Finder</h1>
      <p class="lead mb-4">Connecting patients with life-saving donors across Egypt</p>
      <a href="pages/register.php"
         class="btn btn-light text-danger fw-bold btn-lg me-3">Register Now</a>
      <a href="pages/login.php"
         class="btn btn-outline-light btn-lg">Login</a>
    </div>
  </section>

  <!-- Feature Cards -->
  <section class="py-5">
    <div class="container">
      <h2 class="text-center fw-bold mb-4">How It Works</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <div style="font-size:3rem">🔍</div>
            <h4 class="fw-bold mt-3">Search Donors</h4>
            <p class="text-muted">Find compatible donors by blood type and city instantly.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <div style="font-size:3rem">📋</div>
            <h4 class="fw-bold mt-3">Submit Request</h4>
            <p class="text-muted">Post an urgent request and alert matching donors by email.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 text-center p-4">
            <div style="font-size:3rem">✅</div>
            <h4 class="fw-bold mt-3">Stay Available</h4>
            <p class="text-muted">Toggle your availability so patients can find you in time.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<?php require_once 'includes/footer.php'; ?>
