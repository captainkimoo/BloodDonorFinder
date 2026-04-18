<?php // File: includes/navbar.php ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container">
    <a class="navbar-brand" href="<?= isLoggedIn() ? '../pages/dashboard.php' : '../index.php' ?>">
      🩸 Blood<span>Donor</span>
    </a>
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <?php if (!isLoggedIn()): ?>
          <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="../pages/login.php">Login</a></li>
          <li class="nav-item">
            <a class="nav-link btn btn-light text-danger ms-2 px-3"
               href="../pages/register.php">Register</a></li>
        <?php elseif (getUserType() === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php">
            <i class="bi bi-speedometer2"></i> Admin Panel</a></li>
          <li class="nav-item"><a class="nav-link" href="../admin/users.php">
            <i class="bi bi-people"></i> Users</a></li>
          <li class="nav-item"><a class="nav-link" href="../pages/logout.php">Logout</a></li>
        <?php elseif (getUserType() === 'donor'): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="search.php">Find Donors</a></li>
          <li class="nav-item"><a class="nav-link" href="donor_status.php">My Status</a></li>
          <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">
            <i class="bi bi-person-circle"></i> Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php elseif (getUserType() === 'requester'): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="search.php">Find Donors</a></li>
          <li class="nav-item">
            <a class="nav-link btn btn-light text-danger ms-2 px-3"
               href="request.php">+ New Request</a></li>
          <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">
            <i class="bi bi-person-circle"></i> Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
