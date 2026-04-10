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
