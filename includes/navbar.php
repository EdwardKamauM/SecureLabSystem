<?php
// Note: session_start() should already be called in pages before including this.
?>
<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <i class="fas fa-laptop-code"></i> Secure Lab
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navMenu" aria-controls="navMenu"
            aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
               data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-user-circle"></i> 
              <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><a class="dropdown-item" href="../public/update_profile.php">
                <i class="fas fa-user-edit"></i> Update Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="../public/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link position-relative" href="#" id="notificationsDropdown">
              <i class="fas fa-bell"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                2 <span class="visually-hidden">notifications</span>
              </span>
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="index.php">
            <i class="fas fa-sign-in-alt"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">
            <i class="fas fa-user-plus"></i> Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php if (isset($_SESSION['user_id'])): ?>
<!-- Sidebar Navigation -->
<div class="sidebar-nav bg-dark">
  <div class="sidebar-header pt-4 pb-2 text-center">
    <div class="user-avatar mb-2">
      <i class="fas fa-user-circle fa-3x text-light"></i>
    </div>
    <div class="user-info">
      <h6 class="text-light mb-0"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></h6>
      <span class="badge bg-success"><?= htmlspecialchars($_SESSION['role'] ?? 'user') ?></span>
    </div>
  </div>
  <ul class="nav flex-column">
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/dashboard_admin.php">
          <i class="fas fa-user-shield"></i> Admin Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/manage_users.php">
          <i class="fas fa-users-cog"></i> Manage Users
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/manage_computers.php">
          <i class="fas fa-desktop"></i> Manage Computers
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/manage_bookings.php">
          <i class="fas fa-calendar-check"></i> Manage Bookings
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/maintenance.php">
          <i class="fas fa-tools"></i> Maintenance
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/predictive_maintenance.php">
          <i class="fas fa-chart-line"></i> Predictive Analytics
        </a>
      </li>
    <?php else: ?>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/dashboard_user.php">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/book_computer.php">
          <i class="fas fa-calendar-plus"></i> Book Computer
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/view_bookings.php">
          <i class="fas fa-history"></i> My Bookings
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link sidebar-link" href="../public/computer_status.php">
          <i class="fas fa-desktop"></i> Computer Status
        </a>
      </li>
    <?php endif; ?>
  </ul>
</div>

<!-- Main content container with padding for sidebar -->
<div class="content-container">
<?php endif; ?>