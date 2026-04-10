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
