<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
$db = new Database(); $conn = $db->getConnection();
$uid = getUserId();
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first_name  = sanitise($_POST['first_name']  ?? '');
  $last_name   = sanitise($_POST['last_name']   ?? '');
  $phone       = sanitise($_POST['phone']       ?? '');
  $city        = sanitise($_POST['city']        ?? '');
  $governorate = sanitise($_POST['governorate'] ?? '');
  $conn->prepare('UPDATE profiles SET first_name=:fn, last_name=:ln, phone=:ph,'
    . ' city=:ci, governorate=:go WHERE user_id=:uid')
    ->execute([':fn'=>$first_name, ':ln'=>$last_name, ':ph'=>$phone,
               ':ci'=>$city,       ':go'=>$governorate, ':uid'=>$uid]);
  if (getUserType() === 'donor' && !empty($_POST['blood_type'])) {
    $conn->prepare('UPDATE donors SET blood_type=:bt WHERE user_id=:uid')
          ->execute([':bt'=>sanitise($_POST['blood_type']), ':uid'=>$uid]);
  }
  redirect('profile.php', 'Profile updated successfully!');
}
 
// Fetch current profile
  $stmt = $conn->prepare('SELECT p.*, u.email, u.user_type FROM profiles p'
                       . ' JOIN users u ON u.user_id = p.user_id WHERE p.user_id = :uid');
  $stmt->execute([':uid'=>$uid]);
  $profile = $stmt->fetch();
 
  if (getUserType() === 'donor') {
    $d = $conn->prepare('SELECT * FROM donors WHERE user_id=:uid');
    $d->execute([':uid'=>$uid]);
    $donorInfo = $d->fetch();
  }
?>
<?php
/* ... PHP logic block from Backend Phase 5 Feature 5 stays here unchanged ... */
?>

<?php
$pageTitle = 'My Profile | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
  <div class="row justify-content-center">
    <div class="col-md-7">
      <h2 class="page-title mb-4">👤 My Profile</h2>

      <!-- Flash messages -->
      <?php $success=getSuccess(); if($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header fw-bold">Personal Information</div>
        <div class="card-body p-4">
          <form method="POST" action="">

            <!-- Email (read-only) -->
            <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control bg-light"
                     value="<?= htmlspecialchars($profile['email']) ?>"
                     readonly>
              <div class="form-text">Email cannot be changed.</div>
            </div>

            <!-- Name -->
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label for="first_name" class="form-label required-star">
                  First Name</label>
                <input type="text" name="first_name" id="first_name"
                       class="form-control"
                       value="<?= htmlspecialchars($profile['first_name']) ?>">
              </div>
              <div class="col-6">
                <label for="last_name" class="form-label required-star">
                  Last Name</label>
                <input type="text" name="last_name" id="last_name"
                       class="form-control"
                       value="<?= htmlspecialchars($profile['last_name']) ?>">
              </div>
            </div>

            <!-- Phone -->
            <div class="mb-3">
              <label for="phone" class="form-label">Phone Number</label>
              <input type="tel" name="phone" id="phone" class="form-control"
                     value="<?= htmlspecialchars($profile['phone']??'') ?>">
            </div>

            <!-- City / Governorate -->
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label for="city" class="form-label required-star">City</label>
                <input type="text" name="city" id="city" class="form-control"
                       value="<?= htmlspecialchars($profile['city']) ?>">
              </div>
              <div class="col-6">
                <label for="governorate" class="form-label">Governorate</label>
                <input type="text" name="governorate" id="governorate"
                       class="form-control"
                       value="<?= htmlspecialchars($profile['governorate']??'') ?>">
              </div>
            </div>

            <!-- Blood Type (donors only) -->
            <?php if (getUserType() === 'donor'): ?>
              <div class="mb-3">
                <label for="blood_type" class="form-label">Blood Type</label>
                <select name="blood_type" id="blood_type" class="form-select">
                  <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                    <option value="<?=$bt?>"
                      <?= (($donorInfo['blood_type']??'')===$bt)?'selected':'' ?>>
                      <?=$bt?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check-lg"></i> Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>