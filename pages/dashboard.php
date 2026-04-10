<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
$uid = getUserId();
$userType = getUserType();
$db = new Database(); $conn = $db->getConnection();
 
// Fetch profile for all roles
  $stmt = $conn->prepare('SELECT p.*, u.email FROM profiles p'
                       . ' JOIN users u ON u.user_id = p.user_id WHERE p.user_id = :uid');
  $stmt->execute([':uid' => $uid]);
  $profile = $stmt->fetch();
 
// Role-specific statistics
if ($userType === 'donor') {
  $donorStmt = $conn->prepare('SELECT * FROM donors WHERE user_id = :uid');
  $donorStmt->execute([':uid' => $uid]);
  $donorInfo = $donorStmt->fetch();
  $countStmt = $conn->prepare('SELECT COUNT(*) FROM request_responses WHERE donor_id = :did');
  $countStmt->execute([':did' => $donorInfo['donor_id']]);
  $responseCount = $countStmt->fetchColumn();
} elseif ($userType === 'requester') {
  $reqStmt = $conn->prepare('SELECT COUNT(*) FROM blood_requests WHERE requester_user_id = :uid');
  $reqStmt->execute([':uid' => $uid]);
  $requestCount = $reqStmt->fetchColumn();
}
// HTML below: show donor availability toggle if donor,
// show "New Request" button if requester
?>
