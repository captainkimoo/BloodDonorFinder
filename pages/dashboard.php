<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
$uid = getUserId();
$userType = getUserType();
$db = new Database(); $conn = $db->getConnection();
 
// Fetch profile for all roles
  $stmt = $conn->prepare('SELECT p.*, u.email FROM profiles p'
                       . ' JOIN users u ON u.user_id = p.user_id WHERE p.user_id = :uid');
  $stmt->execute([':uid' => $uid]);
  $profile = $stmt->fetch();
 
// Role-specific statistics
if ($userType === 'donor') {
  $donorStmt = $conn->prepare('SELECT * FROM donors WHERE user_id = :uid');
  $donorStmt->execute([':uid' => $uid]);
  $donorInfo = $donorStmt->fetch();
  $countStmt = $conn->prepare('SELECT COUNT(*) FROM request_responses WHERE donor_id = :did');
  $countStmt->execute([':did' => $donorInfo['donor_id']]);
  $responseCount = $countStmt->fetchColumn();
} elseif ($userType === 'requester') {
  $reqStmt = $conn->prepare('SELECT COUNT(*) FROM blood_requests WHERE requester_user_id = :uid');
  $reqStmt->execute([':uid' => $uid]);
  $requestCount = $reqStmt->fetchColumn();
}
// HTML below: show donor availability toggle if donor,
// show "New Request" button if requester
?>
<?php
/* ... PHP logic block from Backend Phase 4 Task 12 stays here unchanged ... */
?>

<?php
$pageTitle = 'Dashboard | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">

  <!-- Flash Messages -->
  <?php $success=getSuccess(); $error=getError(); ?>
  <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <!-- Welcome Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="page-title mb-1">
        Welcome, <?= htmlspecialchars($profile['first_name']) ?>! 👋
      </h2>
      <p class="text-muted mb-0">
        <?= htmlspecialchars($profile['city']) ?> &bull;
        <?= ucfirst($userType) ?> Account
      </p>
    </div>
    <a href="profile.php" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-person-gear"></i> Edit Profile</a>
  </div>

  <!-- ============================================================ -->
  <!-- DONOR DASHBOARD                                               -->
  <!-- ============================================================ -->
  <?php if ($userType === 'donor'): ?>
    <div class="row g-4 mb-4">

      <!-- Availability Card -->
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body text-center p-4">
            <h5 class="fw-bold mb-3">Your Availability Status</h5>
            <?php if ($donorInfo['is_available']): ?>
              <span class="availability-badge available">
                ✅ Available to Donate
              </span>
            <?php else: ?>
              <span class="availability-badge unavailable">
                ⏸ Currently Unavailable
              </span>
            <?php endif; ?>
            <p class="text-muted mt-2 mb-3">
              Blood Type:
              <span class="badge-blood"><?= $donorInfo['blood_type'] ?></span>
            </p>
            <a href="donor_status.php?toggle=1"
               class="btn btn-outline-secondary btn-sm">
              Toggle Availability</a>
          </div>
        </div>
      </div>

      <!-- Response Count Card -->
      <div class="col-md-6">
        <div class="stat-card stat-blue">
          <div class="stat-number"><?= $responseCount ?></div>
          <div class="stat-label">Requests Responded To</div>
        </div>
      </div>
    </div>

    <!-- Quick Links for Donors -->
    <div class="row g-3">
      <div class="col-md-4">
        <a href="search.php" class="card text-decoration-none">
          <div class="card-body text-center p-3">
            <div style="font-size:2rem">🔍</div>
            <div class="fw-bold mt-1">Find Donors</div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="donor_status.php" class="card text-decoration-none">
          <div class="card-body text-center p-3">
            <div style="font-size:2rem">❤️</div>
            <div class="fw-bold mt-1">My Status</div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="history.php" class="card text-decoration-none">
          <div class="card-body text-center p-3">
            <div style="font-size:2rem">📋</div>
            <div class="fw-bold mt-1">History</div>
          </div>
        </a>
      </div>
    </div>

  <!-- ============================================================ -->
  <!-- REQUESTER DASHBOARD                                           -->
  <!-- ============================================================ -->
  <?php elseif ($userType === 'requester'): ?>
    <div class="row g-4 mb-4">

      <!-- Request Count Card -->
      <div class="col-md-6">
        <div class="stat-card stat-red">
          <div class="stat-number"><?= $requestCount ?></div>
          <div class="stat-label">Blood Requests Submitted</div>
        </div>
      </div>

      <!-- New Request CTA -->
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body text-center p-4 d-flex flex-column
                      justify-content-center">
            <div style="font-size:2.5rem">🏥</div>
            <h5 class="fw-bold mt-2">Need Blood Urgently?</h5>
            <a href="request.php"
               class="btn btn-primary btn-lg mt-2">+ Submit New Request</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Links for Requesters -->
    <div class="row g-3">
      <div class="col-md-4">
        <a href="search.php" class="card text-decoration-none">
          <div class="card-body text-center p-3">
            <div style="font-size:2rem">🔍</div>
            <div class="fw-bold mt-1">Find Donors</div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="request.php" class="card text-decoration-none">
          <div class="card-body text-center p-3">
            <div style="font-size:2rem">📋</div>
            <div class="fw-bold mt-1">New Request</div>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="history.php" class="card text-decoration-none">
          <div class="card-body text-center p-3">
            <div style="font-size:2rem">🗂️</div>
            <div class="fw-bold mt-1">My Requests</div>
          </div>
        </a>
      </div>
    </div>

  <?php endif; ?>

</div><!-- /container -->

<?php require_once '../includes/footer.php'; ?>