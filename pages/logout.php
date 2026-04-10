<?php
require_once '../includes/functions.php';
session_unset();    // Clear all session variables
session_destroy();  // Destroy the session completely
header('Location: login.php');
exit();
?>
