<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Get user info
$role = $_SESSION['role'];

// Fetch all active computers
$all = $pdo->query("SELECT * FROM computers WHERE archived = 0 ORDER BY label")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Computer Status</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-desktop me-2"></i>Real-Time Computer Status</h2>
    <div class="row g-4">
      <?php foreach ($all as $c):
        // Check if currently booked
        $inUse = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE computer_id = ? AND status = 'approved' AND start_time < NOW() AND end_time > NOW()");
        $inUse->execute([$c['id']]);
        $busy = $inUse->fetchColumn() > 0;

        // Determine availability
        if ($c['status'] === 'maintenance') {
          $statusLabel = 'Maintenance';
          $statusColor = 'warning';
          $statusIcon = 'wrench';
        } elseif ($busy) {
          $statusLabel = 'In Use';
          $statusColor = 'danger';
          $statusIcon = 'user-clock';
        } else {
          $statusLabel = 'Available';
          $statusColor = 'success';
          $statusIcon = 'check-circle';
        }

        // Determine risk level based on health score
        $score = $c['health_score'];
        if ($score < 50) {
          $riskLabel = 'High Risk';
          $riskColor = 'danger';
        } elseif ($score < 70) {
          $riskLabel = 'Medium Risk';
          $riskColor = 'warning';
        } else {
          $riskLabel = 'Low Risk';
          $riskColor = 'success';
        }
      ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-laptop me-1"></i><?= htmlspecialchars($c['label']) ?></h5>
              <p><span class="badge bg-<?= $statusColor ?>"><i class="fas fa-<?= $statusIcon ?> me-1"></i><?= $statusLabel ?></span></p>
              <p>Health Score: <span class="badge bg-secondary"><?= $score ?>%</span></p>
              <p>Maintenance Risk: <span class="badge bg-<?= $riskColor ?>"><?= $riskLabel ?></span></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php include_once "../includes/footer.php"; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
