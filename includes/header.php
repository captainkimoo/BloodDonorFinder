<?php
// File: includes/header.php
// USAGE: set $pageTitle before including, e.g.:
//   $pageTitle = 'Login | Blood Donor Finder';
//   require_once '../includes/header.php';
$pageTitle = $pageTitle ?? 'Blood Donor Finder';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Project Styles (works from /pages/ and /admin/) -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>