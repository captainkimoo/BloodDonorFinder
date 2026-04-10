<?php
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
