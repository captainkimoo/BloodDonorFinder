<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = new Database(); $conn = $db->getConnection();

// Toggle user status (admin cannot deactivate themselves)
if (isset($_GET['toggle']) && isset($_GET['uid'])) {
    $targetId = (int)$_GET['uid'];
    if ($targetId !== getUserId()) {
        $curr = $conn->prepare('SELECT status FROM users WHERE user_id=:id');
        $curr->execute([':id'=>$targetId]);
        $newStatus = ($curr->fetchColumn() === 'active') ? 'inactive' : 'active';
        $conn->prepare('UPDATE users SET status=:s WHERE user_id=:id')
             ->execute([':s'=>$newStatus, ':id'=>$targetId]);
        redirect('users.php', 'User status updated.');
    }
}

// Search users (optional query string ?q=name)
$search = sanitise($_GET['q'] ?? '');

$sql = 'SELECT u.user_id, u.email, u.user_type, u.status, u.registration_date,'
     . ' p.first_name, p.last_name, p.city, d.blood_type, d.is_available'
     . ' FROM users u'
     . ' JOIN profiles p ON p.user_id = u.user_id'
     . ' LEFT JOIN donors d ON d.user_id = u.user_id';

if ($search) {
    $sql .= ' WHERE u.email LIKE :q OR p.first_name LIKE :q OR p.last_name LIKE :q';
}
$sql .= ' ORDER BY u.registration_date DESC';

$stmt = $conn->prepare($sql);
if ($search) $stmt->execute([':q' => '%'.$search.'%']);
else         $stmt->execute();

$users = $stmt->fetchAll();

?>
<?php
/* ... PHP logic block from Backend Phase 6 Task 15 stays here unchanged ... */
?>

<?php
$pageTitle = 'Manage Users | Admin';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title">👥 User Management</h2>
    <span class="text-muted"><?= count($users) ?> users found</span>
  </div>

  <!-- Flash Messages -->
  <?php $success=getSuccess(); if($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <!-- Search Bar -->
  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" action="users.php" class="d-flex gap-2">
        <input type="text" name="q" class="form-control"
               placeholder="Search by name or email..."
               value="<?= htmlspecialchars($_GET['q']??'') ?>">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-search"></i> Search</button>
        <?php if (!empty($_GET['q'])): ?>
          <a href="users.php" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- Users Table -->
  <?php if (empty($users)): ?>
    <div class="text-center py-5">
      <div style="font-size:3rem">🔍</div>
      <h4 class="mt-2">No users found</h4>
      <p class="text-muted">Try a different search term.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Blood Type</th>
            <th>City</th>
            <th>Status</th>
            <th>Registered</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($users as $u): ?>
            <tr>
              <td class="fw-bold">
                <?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?>
              </td>
              <td class="text-muted">
                <?= htmlspecialchars($u['email']) ?></td>
              <td>
                <?php
                  $roleColors = ['donor'=>'success','requester'=>'info','admin'=>'secondary'];
                  $rc = $roleColors[$u['user_type']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $rc ?>">
                  <?= ucfirst($u['user_type']) ?></span>
              </td>
              <td>
                <?php if ($u['blood_type']): ?>
                  <span class="badge-blood"><?= $u['blood_type'] ?></span>
                <?php else: echo '<span class="text-muted">N/A</span>'; endif; ?>
              </td>
              <td><?= htmlspecialchars($u['city']??'') ?></td>
              <td>
                <span class="badge badge-<?= $u['status'] ?>">
                  <?= ucfirst($u['status']) ?></span>
              </td>
              <td class="text-muted" style="font-size:0.85rem">
                <?= date('M j, Y', strtotime($u['registration_date'])) ?>
              </td>
              <td>
                <?php if ($u['user_id'] == getUserId()): ?>
                  <span class="badge bg-secondary">You</span>
                <?php else: ?>
                  <a href="users.php?toggle=1&uid=<?= $u['user_id'] ?>"
                     class="btn btn-sm btn-outline-<?=
                       $u['status']==='active' ? 'danger' : 'success' ?>"
                     onclick="return confirm('Toggle status for this user?')">
                    <?= $u['status']==='active' ? 'Deactivate' : 'Activate' ?>
                  </a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
