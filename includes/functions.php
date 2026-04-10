<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
 
// ── AUTH HELPERS ─────────────────────────────────────────────────
function isLoggedIn() { return isset($_SESSION['user_id']); }
function getUserId() { return $_SESSION['user_id'] ?? null; }
function getUserType() { return $_SESSION['user_type'] ?? null; }
 
function requireLogin() {
  if (!isLoggedIn()) {
    $_SESSION['error'] = 'Please log in to access this page.';
    header('Location: login.php'); exit();
  }
}
 
function requireRole($role, $redirectTo = 'dashboard.php') {
  requireLogin();
  if (getUserType() !== $role) {
    $_SESSION['error'] = 'Access denied.';
    header('Location: ' . $redirectTo); exit();
  }
}
 
// ── BLOOD TYPE COMPATIBILITY ──────────────────────────────────────
function getCompatibleTypes($requested) {
  $compat = [
    'A+'  => ['A+','A-','O+','O-'],
    'A-'  => ['A-','O-'],
    'B+'  => ['B+','B-','O+','O-'],
    'B-'  => ['B-','O-'],
    'AB+' => ['A+','A-','B+','B-','AB+','AB-','O+','O-'],
    'AB-' => ['A-','B-','AB-','O-'],
    'O+'  => ['O+','O-'],
    'O-'  => ['O-'],
  ];
  return $compat[$requested] ?? [];
}
 
// ── INPUT SANITISATION ───────────────────────────────────────────
function sanitise($data) {
  return htmlspecialchars(stripslashes(trim($data)));
}
function isValidEmail($email) {
  return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
 
// ── FLASH MESSAGES ───────────────────────────────────────────────
function setSuccess($msg) { $_SESSION['success'] = $msg; }
function setError($msg)   { $_SESSION['error']   = $msg; }
function getSuccess() { $m=$_SESSION['success']??null; unset($_SESSION['success']); return $m; }
function getError()   { $m=$_SESSION['error']  ??null; unset($_SESSION['error']);   return $m; }
function redirect($url, $msg=null, $type='success') {
  if ($msg) { $type==='success' ? setSuccess($msg) : setError($msg); }
  header('Location:'.$url); exit();
}
 
// ── DATE FORMATTING ─────────────────────────────────────────────
function formatDate($d)     { return date('F j, Y', strtotime($d)); }
function formatDateTime($d) { return date('F j, Y g:i A', strtotime($d)); }
?>
