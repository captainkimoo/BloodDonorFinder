

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';
require_once '../includes/functions.php';
if (isLoggedIn()) { redirect('dashboard.php'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1. Collect and sanitize inputs
  $email      = sanitise($_POST['email']      ?? '');
  $password   = $_POST['password']            ?? '';
  $confirm    = $_POST['confirm']             ?? '';
  $first_name = sanitise($_POST['first_name'] ?? '');
  $last_name  = sanitise($_POST['last_name']  ?? '');
  $city       = sanitise($_POST['city']       ?? '');
  $governorate= sanitise($_POST['governorate']?? '');
  $phone      = sanitise($_POST['phone']      ?? '');
  $user_type  = sanitise($_POST['user_type']  ?? '');
  $blood_type = sanitise($_POST['blood_type'] ?? '');
 
  // 2. Validate
  if (!isValidEmail($email))              $errors[] = 'A valid email is required.';
  if (empty($first_name))                 $errors[] = 'First name is required.';
  if (empty($last_name))                  $errors[] = 'Last name is required.';
  if (empty($city))                       $errors[] = 'City is required.';
  if ($password !== $confirm)             $errors[] = 'Passwords do not match.';
  if (strlen($password) < 8)              $errors[] = 'Password must be at least 8 characters.';
  if (!in_array($user_type,['donor','requester'])) $errors[] = 'Invalid role.';
  if ($user_type==='donor' && empty($blood_type))  $errors[] = 'Blood type required for donors.';
 
  if (empty($errors)) {
    $db = new Database(); $conn = $db->getConnection();
    // 3. Check duplicate email
    $check = $conn->prepare('SELECT user_id FROM users WHERE email=:e');
    $check->execute([':e' => $email]);
    if ($check->rowCount() > 0) {
      $errors[] = 'An account with this email already exists.';
    } else {
      $hashed = password_hash($password, PASSWORD_DEFAULT);
      $conn->beginTransaction();
      try {
        $conn->prepare('INSERT INTO users (email,password,user_type) VALUES (:e,:p,:t)')
              ->execute([':e'=>$email, ':p'=>$hashed, ':t'=>$user_type]);
        $newId = $conn->lastInsertId();
        $conn->prepare('INSERT INTO profiles (user_id,first_name,last_name,phone,city,governorate)'
              . ' VALUES (:uid,:fn,:ln,:ph,:ci,:go)')
              ->execute([':uid'=>$newId, ':fn'=>$first_name, ':ln'=>$last_name,
                         ':ph'=>$phone,  ':ci'=>$city,       ':go'=>$governorate]);
        if ($user_type === 'donor') {
          $conn->prepare('INSERT INTO donors (user_id, blood_type) VALUES (:uid, :bt)')
                ->execute([':uid'=>$newId, ':bt'=>$blood_type]);
        }
        $conn->commit();
        redirect('login.php', 'Account created! Please log in.', 'success');
      } catch (PDOException $e) {
        $conn->rollBack();
        $errors[] = 'Registration failed. Please try again.';
      }
    }
  }
}
?>
<?php
$pageTitle = 'Register | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="auth-wrapper">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">
        <div class="card auth-card shadow-lg">
          <div class="card-body p-4">

            <!-- Logo & Title -->
            <div class="text-center mb-4">
              <div class="auth-logo">🩸</div>
              <h2 class="fw-bold">Create Account</h2>
              <p class="text-muted">Join Blood Donor Finder today</p>
            </div>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <ul class="mb-0">
                  <?php foreach($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="POST" action="">

              <!-- Account Type -->
              <div class="mb-3">
                <label class="form-label required-star">I want to</label>
                <div class="d-flex gap-3">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="user_type"
                           id="typeDonor" value="donor"
                           <?= (($_POST['user_type']??'') === 'donor') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="typeDonor">🙋 Donate Blood</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="user_type"
                           id="typeRequester" value="requester"
                           <?= (($_POST['user_type']??'') === 'requester') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="typeRequester">🏥 Request Blood</label>
                  </div>
                </div>
              </div>

              <!-- Blood Type (donors only) -->
              <div class="mb-3" id="bloodTypeField" style="display:none;">
                <label for="blood_type" class="form-label required-star">Blood Type</label>
                <select name="blood_type" id="blood_type" class="form-select">
                  <option value="">-- Select --</option>
                  <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                    <option value="<?=$bt?>"
                      <?= (($_POST['blood_type']??'')===$bt)?'selected':'' ?>>
                      <?=$bt?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <!-- Name Row -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label for="first_name" class="form-label required-star">First Name</label>
                  <input type="text" name="first_name" id="first_name"
                         class="form-control"
                         value="<?= htmlspecialchars($_POST['first_name']??'') ?>">
                </div>
                <div class="col-6">
                  <label for="last_name" class="form-label required-star">Last Name</label>
                  <input type="text" name="last_name" id="last_name"
                         class="form-control"
                         value="<?= htmlspecialchars($_POST['last_name']??'') ?>">
                </div>
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label required-star">Email Address</label>
                <input type="email" name="email" id="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email']??'') ?>">
              </div>

              <!-- Phone -->
              <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" name="phone" id="phone" class="form-control"
                       value="<?= htmlspecialchars($_POST['phone']??'') ?>">
              </div>

              <!-- City / Governorate -->
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label for="city" class="form-label required-star">City</label>
                  <input type="text" name="city" id="city" class="form-control"
                         value="<?= htmlspecialchars($_POST['city']??'') ?>">
                </div>
                <div class="col-6">
                  <label for="governorate" class="form-label">Governorate</label>
                  <input type="text" name="governorate" id="governorate"
                         class="form-control"
                         value="<?= htmlspecialchars($_POST['governorate']??'') ?>">
                </div>
              </div>

              <!-- Password -->
              <div class="mb-3">
                <label for="password" class="form-label required-star">Password</label>
                <input type="password" name="password" id="password"
                       class="form-control" placeholder="Min. 8 characters">
              </div>
              <div class="mb-4">
                <label for="confirm" class="form-label required-star">Confirm Password</label>
                <input type="password" name="confirm" id="confirm" class="form-control">
              </div>

              <!-- Submit -->
              <button type="submit" class="btn btn-primary w-100 btn-lg">
                Create Account</button>
            </form>

            <hr>
            <p class="text-center mb-0">Already have an account?
              <a href="login.php" class="text-danger fw-bold">Login</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Show blood type field only for donors -->
<script>
  document.querySelectorAll('input[name="user_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
      document.getElementById('bloodTypeField').style.display =
        (this.value === 'donor') ? 'block' : 'none';
    });
  });
  // Re-show on page reload if donor was previously selected
  var saved = document.querySelector('input[name="user_type"]:checked');
  if (saved && saved.value === 'donor') {
    document.getElementById('bloodTypeField').style.display = 'block';
  }
</script>

<?php require_once '../includes/footer.php'; ?>

