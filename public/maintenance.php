<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Only allow admin or technician
if (!in_array($_SESSION['role'], ['admin', 'technician'])) {
  header("Location: dashboard_user.php");
  exit();
}

// Fetch at-risk computers
$stmt = $pdo->prepare("SELECT * FROM computers WHERE maintenance_risk = 1 ORDER BY label");
$stmt->execute();
$computers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Maintenance Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container my-5">
    <h2 class="mb-4 text-danger"><i class="fas fa-tools me-2"></i>At-Risk Computers</h2>

    <?php if (count($computers)): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-danger">
          <tr>
            <th>#</th>
            <th>Label</th>
            <th>Health Score</th>
            <th>Usage (hrs)</th>
            <th>Age (months)</th>
            <th>Error Count</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($computers as $i => $c): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= htmlspecialchars($c['label']) ?></strong></td>
            <td><span class="badge bg-<?= $c['health_score'] >= 70 ? 'success' : ($c['health_score'] >= 40 ? 'warning' : 'danger') ?>">
              <?= $c['health_score'] ?>%</span></td>
            <td><?= $c['usage_hours'] ?? 'N/A' ?></td>
            <td><?= $c['age_months'] ?? 'N/A' ?></td>
            <td><?= $c['error_count'] ?? 'N/A' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="alert alert-success">All computers are currently healthy. ✅</div>
    <?php endif; ?>
  </div>

  <?php include_once "../includes/footer.php"; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
