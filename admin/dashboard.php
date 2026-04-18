<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin', '../pages/dashboard.php'); // 2nd arg: where to redirect if denied

$db = new Database(); $conn = $db->getConnection();

// Platform-wide statistics
$totalUsers    = $conn->query('SELECT COUNT(*) FROM users WHERE status="active"')->fetchColumn();
$totalDonors   = $conn->query('SELECT COUNT(*) FROM donors WHERE is_available=TRUE')->fetchColumn();
$totalRequests = $conn->query('SELECT COUNT(*) FROM blood_requests')->fetchColumn();
$openRequests  = $conn->query('SELECT COUNT(*) FROM blood_requests WHERE status="open"')->fetchColumn();
$emailsSent    = $conn->query('SELECT COUNT(*) FROM donor_notifications WHERE email_sent=TRUE')->fetchColumn();

// Blood type distribution
$byType = $conn->query(
    'SELECT blood_type, COUNT(*) AS cnt FROM blood_requests GROUP BY blood_type ORDER BY cnt DESC'
)->fetchAll();

$donorsByType = $conn->query(
    'SELECT blood_type, COUNT(*) AS cnt FROM donors WHERE is_available=TRUE GROUP BY blood_type'
)->fetchAll();

?>

<!-- HTML: display stat cards and distribution tables -->
<?php
/* ... PHP logic block from Backend Phase 6 Task 14 stays here unchanged ... */
?>

<?php
$pageTitle = 'Admin Dashboard | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">

  <!-- Admin Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">👑 Admin Dashboard</h2>
    <span class="badge bg-danger fs-6">Administrator</span>
  </div>

  <!-- Stat Cards Row -->
  <div class="row g-4 mb-5">
    <div class="col-md-3">
      <div class="stat-card stat-blue">
        <div class="stat-number"><?= $totalUsers ?></div>
        <div class="stat-label">Active Users</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card stat-green">
        <div class="stat-number"><?= $totalDonors ?></div>
        <div class="stat-label">Available Donors</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card stat-red">
        <div class="stat-number"><?= $openRequests ?></div>
        <div class="stat-label">Open Requests</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card stat-orange">
        <div class="stat-number"><?= $emailsSent ?></div>
        <div class="stat-label">Emails Sent</div>
      </div>
    </div>
  </div>

  <!-- Blood Type Distribution Tables -->
  <div class="row g-4 mb-4">

    <!-- Requests by Blood Type -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header fw-bold">
          🩸 Blood Requests by Type
        </div>
        <div class="card-body p-0">
          <table class="table mb-0">
            <thead>
              <tr><th>Blood Type</th><th>Total Requests</th></tr>
            </thead>
            <tbody>
              <?php foreach($byType as $row): ?>
                <tr>
                  <td><span class="badge-blood"><?= $row['blood_type'] ?></span></td>
                  <td class="fw-bold"><?= $row['cnt'] ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($byType)): ?>
                <tr><td colspan="2" class="text-muted text-center">
                  No requests yet</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Available Donors by Blood Type -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header fw-bold">
          ❤️ Available Donors by Type
        </div>
        <div class="card-body p-0">
          <table class="table mb-0">
            <thead>
              <tr><th>Blood Type</th><th>Available Donors</th></tr>
            </thead>
            <tbody>
              <?php foreach($donorsByType as $row): ?>
                <tr>
                  <td><span class="badge-blood"><?= $row['blood_type'] ?></span></td>
                  <td class="fw-bold"><?= $row['cnt'] ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($donorsByType)): ?>
                <tr><td colspan="2" class="text-muted text-center">
                  No available donors</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Admin Links -->
  <div class="row g-3">
    <div class="col-md-4">
      <a href="users.php" class="card text-decoration-none">
        <div class="card-body text-center p-3">
          <div style="font-size:2rem">👥</div>
          <div class="fw-bold mt-1">Manage Users</div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="../pages/history.php" class="card text-decoration-none">
        <div class="card-body text-center p-3">
          <div style="font-size:2rem">🗂️</div>
          <div class="fw-bold mt-1">All Requests</div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="../pages/search.php" class="card text-decoration-none">
        <div class="card-body text-center p-3">
          <div style="font-size:2rem">🔍</div>
          <div class="fw-bold mt-1">Search Donors</div>
        </div>
      </a>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>