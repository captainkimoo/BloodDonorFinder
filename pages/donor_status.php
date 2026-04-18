<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireRole('donor');
$db = new Database(); $conn = $db->getConnection();
$uid = getUserId();
 
if (isset($_GET['toggle'])) {
  $curr = $conn->prepare('SELECT is_available FROM donors WHERE user_id=:uid');
  $curr->execute([':uid' => $uid]);
  $current = $curr->fetchColumn();
  $newStatus = $current ? 0 : 1;
  $conn->prepare('UPDATE donors SET is_available=:s WHERE user_id=:uid')
        ->execute([':s'=>$newStatus, ':uid'=>$uid]);
  $msg = $newStatus ? 'You are now marked as available.' : 'You are now marked as unavailable.';
  redirect('dashboard.php', $msg);
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $last_donation = sanitise($_POST['last_donation'] ?? '');
  $notes         = sanitise($_POST['notes']         ?? '');
  $conn->prepare('UPDATE donors SET last_donation=:ld, notes=:no WHERE user_id=:uid')
        ->execute([':ld'=>$last_donation, ':no'=>$notes, ':uid'=>$uid]);
  redirect('dashboard.php', 'Donor profile updated.');
}
?>
<?php
/* ... PHP logic block from Backend Phase 5 Feature 3 stays here unchanged ... */
// Also fetch current donor info for display:
$donorStmt = $conn->prepare('SELECT * FROM donors WHERE user_id = :uid');
$donorStmt->execute([':uid' => $uid]);
$donor = $donorStmt->fetch();
?>

<?php
$pageTitle = 'My Availability | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <h2 class="page-title mb-4">❤️ Donor Availability</h2>

      <!-- Flash messages -->
      <?php $success=getSuccess(); $error=getError(); ?>
      <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <!-- Status Card -->
      <div class="card mb-4">
        <div class="card-body text-center p-5">
          <h4 class="fw-bold mb-3">Current Status</h4>
          <?php if ($donor['is_available']): ?>
            <div class="availability-badge available mb-3">
              ✅ You are Available to Donate
            </div>
          <?php else: ?>
            <div class="availability-badge unavailable mb-3">
              ⏸ You are Currently Unavailable
            </div>
          <?php endif; ?>
          <p class="text-muted">
            Blood Type: <span class="badge-blood"><?= $donor['blood_type'] ?></span>
          </p>
          <a href="donor_status.php?toggle=1"
             class="btn btn-outline-secondary btn-lg mt-2">
            🔄 Toggle My Availability</a>
        </div>
      </div>

      <!-- Update Donation Info -->
      <div class="card">
        <div class="card-header fw-bold">Update Donation Details</div>
        <div class="card-body">
          <form method="POST" action="">
            <div class="mb-3">
              <label for="last_donation" class="form-label">
                Last Donation Date</label>
              <input type="date" name="last_donation" id="last_donation"
                     class="form-control"
                     value="<?= $donor['last_donation']??'' ?>">
            </div>
            <div class="mb-3">
              <label for="notes" class="form-label">Personal Notes</label>
              <textarea name="notes" id="notes" class="form-control" rows="3"
                        placeholder="Any notes for requesters..."><?=
                htmlspecialchars($donor['notes']??'') ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">
              Save Details</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>