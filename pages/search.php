<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();
$db = new Database(); $conn = $db->getConnection();
$donors = [];
if ($_SERVER['REQUEST_METHOD']=== 'POST' || isset($_GET['blood_type'])) {
  $blood_type  = sanitise($_POST['blood_type']  ?? $_GET['blood_type']  ?? '');
  $city        = sanitise($_POST['city']        ?? $_GET['city']        ?? '');
  $governorate = sanitise($_POST['governorate'] ?? $_GET['governorate'] ?? '');
 
  $compatible = getCompatibleTypes($blood_type);
  if (empty($compatible)) $compatible = [$blood_type];
 
  $placeholders = implode(',', array_fill(0, count($compatible), '?'));
  $params = array_merge($compatible, [$city]);
 
  $sql = "SELECT d.*, p.first_name, p.last_name, p.phone, p.city,
          p.governorate, u.email
          FROM donors d
          JOIN profiles p ON p.user_id = d.user_id
          JOIN users u ON u.user_id = d.user_id
          WHERE d.blood_type IN ($placeholders)
          AND d.is_available = TRUE
          AND u.status = 'active'
          AND p.city = ?";
 
  $stmt = $conn->prepare($sql);
  $stmt->execute($params);
  $donors = $stmt->fetchAll();
}
?>
