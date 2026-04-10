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
