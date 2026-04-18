<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
$db = new Database(); $conn = $db->getConnection();
$uid = getUserId(); $userType = getUserType();
 
if ($userType === 'requester' || $userType === 'admin') {
  $sql = 'SELECT br.*, p.first_name, p.last_name,'
       . ' (SELECT COUNT(*) FROM donor_notifications dn'
       . '  WHERE dn.request_id=br.request_id) AS notified_count'
       . ' FROM blood_requests br'
       . ' JOIN profiles p ON p.user_id = br.requester_user_id';
  if ($userType !== 'admin') { $sql .= ' WHERE br.requester_user_id = :uid'; }
  $sql .= ' ORDER BY br.created_at DESC';
  $stmt = $conn->prepare($sql);
  if ($userType !== 'admin') $stmt->execute([':uid' => $uid]);
  else $stmt->execute();
  $requests = $stmt->fetchAll();
} elseif ($userType === 'donor') {
  $stmt = $conn->prepare('SELECT br.*, dn.email_sent, dn.sent_at'
    . ' FROM donor_notifications dn'
    . ' JOIN blood_requests br ON br.request_id = dn.request_id'
    . ' JOIN donors d ON d.donor_id = dn.donor_id'
    . ' WHERE d.user_id = :uid'
    . ' ORDER BY br.created_at DESC');
  $stmt->execute([':uid' => $uid]);
  $requests = $stmt->fetchAll();
}
// HTML: render $requests in a table with status badges
?>
<?php
/* ... PHP logic block from Backend Phase 5 Feature 4 stays here unchanged ... */
?>

<?php
$pageTitle = 'Request History | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
  <h2 class="page-title mb-4">🗂️ Request History</h2>

  <?php if (empty($requests)): ?>
    <div class="text-center py-5">
      <div style="font-size:3rem">📂</div>
      <h4 class="mt-2">No history yet</h4>
      <p class="text-muted">
        <?= ($userType === 'requester') ?
          'Submit your first blood request to see it here.' :
          'You haven\'t been matched to any requests yet.' ?>
      </p>
      <?php if ($userType === 'requester'): ?>
        <a href="request.php" class="btn btn-primary">Submit a Request</a>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Blood Type</th>
            <th>Hospital</th>
            <th>City</th>
            <th>Urgency</th>
            <th>Status</th>
            <?php if ($userType !== 'donor'): ?>
              <th>Notified</th>
            <?php endif; ?>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($requests as $req): ?>
            <tr>
              <td class="fw-bold">
                <?= htmlspecialchars($req['patient_name']) ?></td>
              <td><span class="badge-blood"><?= $req['blood_type'] ?></span></td>
              <td><?= htmlspecialchars($req['hospital_name']) ?></td>
              <td><?= htmlspecialchars($req['city']) ?></td>
              <td>
                <?php
                  $urgClass = ['normal'=>'secondary','urgent'=>'warning','critical'=>'danger'];
                  $urg = $req['urgency'] ?? 'normal';
                ?>
                <span class="badge bg-<?= $urgClass[$urg] ?? 'secondary' ?>">
                  <?= ucfirst($urg) ?></span>
              </td>
              <td>
                <span class="badge badge-<?= $req['status'] ?? 'open' ?>">
                  <?= ucfirst($req['status'] ?? 'open') ?></span>
              </td>
              <?php if ($userType !== 'donor'): ?>
                <td><?= $req['notified_count'] ?? '0' ?> donors</td>
              <?php endif; ?>
              <td class="text-muted" style="font-size:0.85rem">
                <?= date('M j, Y', strtotime($req['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>