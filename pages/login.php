<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
if (isLoggedIn()) { redirect('dashboard.php'); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email    = sanitise($_POST['email']    ?? '');
  $password = $_POST['password']          ?? '';
  if (empty($email) || empty($password)) {
    $errors[] = 'Both fields are required.';
  } else {
    $db = new Database(); $conn = $db->getConnection();
    $stmt = $conn->prepare('SELECT user_id, email, password, user_type, status'
                         . ' FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
      if ($user['status'] !== 'active') {
        $errors[] = 'Your account is not active. Contact support.';
      } else {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        if ($user['user_type'] === 'admin')
          redirect('../admin/dashboard.php', 'Welcome back, Admin!');
        else
          redirect('dashboard.php', 'Welcome back!');
      }
    } else { $errors[] = 'Incorrect email or password.'; }
  }
}
?>
<?php
/* ... PHP logic block from Backend Phase 4 Task 11 stays here unchanged ... */
?>

<?php
$pageTitle = 'Login | Blood Donor Finder';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="auth-wrapper">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">
        <div class="card auth-card shadow-lg">
          <div class="card-body p-4">

            <!-- Logo & Title -->
            <div class="text-center mb-4">
              <div class="auth-logo">🩸</div>
              <h2 class="fw-bold">Welcome Back</h2>
              <p class="text-muted">Login to Blood Donor Finder</p>
            </div>

            <!-- Success message (e.g. after registration) -->
            <?php $success = getSuccess(); if ($success): ?>
              <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?= htmlspecialchars($success) ?>
              </div>
            <?php endif; ?>

            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php foreach($errors as $e): ?>
                  <p class="mb-0"><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
              <div class="mb-3">
                <label for="email" class="form-label required-star">Email Address</label>
                <input type="email" name="email" id="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email']??'') ?>"
                       placeholder="your@email.com">
              </div>
              <div class="mb-4">
                <label for="password" class="form-label required-star">Password</label>
                <input type="password" name="password" id="password"
                       class="form-control" placeholder="Your password">
              </div>
              <button type="submit" class="btn btn-primary w-100 btn-lg">
                Login</button>
            </form>

            <hr>
            <p class="text-center mb-0">Don't have an account?
              <a href="register.php" class="text-danger fw-bold">Register</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
