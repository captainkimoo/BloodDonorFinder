<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

requireRole('admin', '../pages/dashboard.php'); // 2nd arg: where to redirect if denied

$db = new Database(); $conn = $db->getConnection();

// Platform-wide statistics
$totalUsers    = $conn->query('SELECT COUNT(*) FROM users WHERE status="active"')->fetchColumn();
$totalDonors   = $conn->query('SELECT COUNT(*) FROM donors WHERE is_available=TRUE')->fetchColumn();
$totalRequests = $conn->query('SELECT COUNT(*) FROM blood_requests')->fetchColumn();
$openRequests  = $conn->query('SELECT COUNT(*) FROM blood_requests WHERE status="open"')->fetchColumn();
$emailsSent    = $conn->query('SELECT COUNT(*) FROM donor_notifications WHERE email_sent=TRUE')->fetchColumn();

// Blood type distribution
$byType = $conn->query(
    'SELECT blood_type, COUNT(*) AS cnt FROM blood_requests GROUP BY blood_type ORDER BY cnt DESC'
)->fetchAll();

$donorsByType = $conn->query(
    'SELECT blood_type, COUNT(*) AS cnt FROM donors WHERE is_available=TRUE GROUP BY blood_type'
)->fetchAll();

?>

<!-- HTML: display stat cards and distribution tables -->
