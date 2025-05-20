<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

if ($_SESSION['role'] !== 'admin') {
  header("Location: dashboard_user.php");
  exit();
}

// Fetch counts
$pendingBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'approved'")->fetchColumn();
$totalComputers = $pdo->query("SELECT COUNT(*) FROM computers")->fetchColumn();
$atRisk = $pdo->query("SELECT COUNT(*) FROM computers WHERE health_score < 40")->fetchColumn();
$availableComputers = $pdo->query("SELECT COUNT(*) FROM computers WHERE status = 'available'")->fetchColumn();

// Get recent bookings
$recentBookings = $pdo->query("
    SELECT b.*, u.name AS user_name, c.label AS computer_label
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN computers c ON b.computer_id = c.id
    ORDER BY b.created_at DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once "../includes/header.php"; ?>

<body class="d-flex flex-column min-vh-100 bg-light">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container-fluid">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <div class="row">
        <div class="col-md-8">
          <h2><i class="fas fa-user-shield me-2"></i>Admin Dashboard</h2>
          <p class="text-muted">Manage your secure lab system</p>
        </div>
        <div class="col-md-4 text-end">
          <p class="current-time mb-0 fw-bold"><?= date('l, F j, Y') ?></p>
          <p class="text-muted"><?= date('h:i A') ?></p>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
      <!-- Pending Bookings Card -->
      <div class="col-md-3">
        <div class="card stat-card card-maintenance mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <h5 class="card-title">Pending Bookings</h5>
                <h2 class="mb-0"><?= $pendingBookings ?></h2>
              </div>
              <div class="col-4 text-end">
                <div class="card-icon bg-light text-warning">
                  <i class="fas fa-hourglass-half"></i>
                </div>
              </div>
            </div>
            <p class="card-text mt-3">
              <a href="manage_bookings.php" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-tasks"></i> Review
              </a>
            </p>
          </div>
        </div>
      </div>

      <!-- Total Users Card -->
      <div class="col-md-3">
        <div class="card stat-card card-booked mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <h5 class="card-title">Active Users</h5>
                <h2 class="mb-0"><?= $totalUsers ?></h2>
              </div>
              <div class="col-4 text-end">
                <div class="card-icon bg-light text-primary">
                  <i class="fas fa-users"></i>
                </div>
              </div>
            </div>
            <p class="card-text mt-3">
              <a href="manage_users.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user-cog"></i> Manage
              </a>
            </p>
          </div>
        </div>
      </div>

      <!-- Available Computers Card -->
      <div class="col-md-3">
        <div class="card stat-card card-available mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <h5 class="card-title">Available PCs</h5>
                <h2 class="mb-0"><?= $availableComputers ?></h2>
              </div>
              <div class="col-4 text-end">
                <div class="card-icon bg-light text-success">
                  <i class="fas fa-desktop"></i>
                </div>
              </div>
            </div>
            <p class="card-text mt-3">
              <a href="computer_status.php" class="btn btn-sm btn-outline-success">
                <i class="fas fa-search"></i> View
              </a>
            </p>
          </div>
        </div>
      </div>

      <!-- At Risk Computers Card -->
      <div class="col-md-3">
        <div class="card stat-card card-unavailable mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <h5 class="card-title">At Risk PCs</h5>
                <h2 class="mb-0"><?= $atRisk ?></h2>
              </div>
              <div class="col-4 text-end">
                <div class="card-icon bg-light text-danger">
                  <i class="fas fa-exclamation-triangle"></i>
                </div>
              </div>
            </div>
            <p class="card-text mt-3">
              <a href="predictive_maintenance.php" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-chart-line"></i> Analytics
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Actions -->
    <h4 class="mb-3">Quick Management</h4>
    <div class="row g-4 mb-4">
      <!-- Pending Bookings -->
      <div class="col-md-6 col-lg-4">
        <a href="manage_bookings.php" class="text-decoration-none">
          <div class="card border-start border-warning border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-hourglass-half me-2 text-warning"></i>Pending Bookings</h5>
              <p class="card-text">You have <strong><?= $pendingBookings ?></strong> bookings awaiting approval.</p>
            </div>
          </div>
        </a>
      </div>

      <!-- Manage Users -->
      <div class="col-md-6 col-lg-4">
        <a href="manage_users.php" class="text-decoration-none">
          <div class="card border-start border-primary border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-users me-2 text-primary"></i>Manage Users</h5>
              <p class="card-text">Currently <strong><?= $totalUsers ?></strong> approved users in the system.</p>
            </div>
          </div>
        </a>
      </div>

      <!-- View Computers -->
      <div class="col-md-6 col-lg-4">
        <a href="computer_status.php" class="text-decoration-none">
          <div class="card border-start border-info border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-desktop me-2 text-info"></i>Computer Inventory</h5>
              <p class="card-text">You have <strong><?= $totalComputers ?></strong> computers in the lab.</p>
            </div>
          </div>
        </a>
      </div>
      
      <!-- Manage Computers -->
      <div class="col-md-6 col-lg-4">
        <a href="manage_computers.php" class="text-decoration-none">
          <div class="card border-start border-secondary border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-cogs me-2 text-secondary"></i>Manage Computers</h5>
              <p class="card-text">Add or archive lab PCs from inventory.</p>
            </div>
          </div>
        </a>
      </div>

      <!-- Predictive Maintenance -->
      <div class="col-md-6 col-lg-4">
        <a href="predictive_maintenance.php" class="text-decoration-none">
          <div class="card border-start border-danger border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-brain me-2 text-danger"></i>Predictive Analysis</h5>
              <p class="card-text"><?= $atRisk ?> computers are flagged for possible failure. View details.</p>
            </div>
          </div>
        </a>
      </div>
    </div>
    
    

    <!-- System Stats -->
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>System Statistics</h5>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h6>Lab Utilization</h6>
                <div class="progress mb-4" style="height: 25px;">
                  <?php 
                    $utilizationRate = ($totalComputers > 0) ? round((($totalComputers - $availableComputers) / $totalComputers) * 100) : 0;
                  ?>
                  <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $utilizationRate ?>%;" 
                       aria-valuenow="<?= $utilizationRate ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= $utilizationRate ?>% Utilization
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <h6>System Health</h6>
                <div class="progress mb-4" style="height: 25px;">
                  <?php 
                    $healthRate = ($totalComputers > 0) ? round((($totalComputers - $atRisk) / $totalComputers) * 100) : 0;
                    $healthClass = ($healthRate >= 80) ? 'bg-success' : (($healthRate >= 50) ? 'bg-warning' : 'bg-danger');
                  ?>
                  <div class="progress-bar <?= $healthClass ?>" role="progressbar" style="width: <?= $healthRate ?>%;" 
                       aria-valuenow="<?= $healthRate ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= $healthRate ?>% Healthy
                  </div>
                </div>
              </div>
            </div>
            <div class="text-center mt-3">
              <a href="reports.php" class="btn btn-outline-primary"><i class="fas fa-file-alt me-1"></i>Generate Reports</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<!-- Feedback Management Section -->
<div class="card mb-4">
  <div class="card-header bg-danger text-white">
    <strong>User Feedback Management</strong>
  </div>
  <div class="card-body">
    <?php
    // Handle delete action
    if (isset($_GET['delete_feedback'])) {
        $fid = (int)$_GET['delete_feedback'];
        $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$fid]);
        echo '<div class="alert alert-success mb-3">Feedback deleted.</div>';
    }
    // Fetch feedback from DB
    $feedbacks = $pdo->query("SELECT f.*, u.name AS user_name 
                              FROM feedback f 
                              LEFT JOIN users u ON f.user_id = u.id 
                              ORDER BY f.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <?php if (count($feedbacks)): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Name</th>
              <th>Email</th>
              <th>Message</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($feedbacks as $i => $fb): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($fb['user_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($fb['name']) ?></td>
                <td><?= htmlspecialchars($fb['email']) ?></td>
                <td><?= nl2br(htmlspecialchars($fb['message'])) ?></td>
                <td><?= date('M d, Y H:i', strtotime($fb['created_at'])) ?></td>
                <td>
                  <a href="?archive_feedback=<?= $fb['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('archive this feedback?')">archive</a>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">No feedback submitted yet.</div>
    <?php endif ?>
  </div>
</div>

  
  <?php include_once "../includes/footer.php"; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Update current time
    function updateTime() {
      const now = new Date();
      const timeElement = document.querySelector('.text-muted');
      if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
      }
    }
    
    // Update time every minute
    setInterval(updateTime, 60000);
  </script>
</body>
</html>