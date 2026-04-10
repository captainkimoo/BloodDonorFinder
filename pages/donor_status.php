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
