<?php
require_once "../includes/auth.php";
require_once "../config/db.php";
require_once "../includes/functions.php";

$computers = $pdo->query("SELECT label, health_score FROM computers ORDER BY label")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Predictive Maintenance Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
  <?php include_once "../includes/navbar.php"; ?>
  <div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-heartbeat me-2 text-danger"></i>Predictive Maintenance Dashboard</h2>
    <p class="text-muted">Health score determines maintenance risk in this view. Easier to interpret for presentation.</p>

    <?php
      $labels = [];
      $health_scores = [];
      foreach ($computers as $pc) {
        $labels[] = $pc['label'];
        $health_scores[] = round($pc['health_score'], 2);
      }
    ?>

    <!-- Chart First -->
    <div class="mb-5">
      <h5 class="mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i>Computer Health Score Chart</h5>
      <canvas id="healthChart" height="100"></canvas>
    </div>

    <!-- Table Below -->
    <div class="table-responsive mt-5">
      <h5 class="mb-3"><i class="fas fa-list me-2 text-secondary"></i>Health Score & Risk Table</h5>
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>Computer</th>
            <th>Health Score</th>
            <th>Maintenance Risk (Derived)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($computers as $pc): ?>
            <?php
              $score = $pc['health_score'];
              if ($score < 50) {
                $badge = "danger";
                $label = "High";
              } elseif ($score < 70) {
                $badge = "warning";
                $label = "Medium";
              } else {
                $badge = "success";
                $label = "Low";
              }
            ?>
            <tr>
              <td><?= htmlspecialchars($pc['label']) ?></td>
              <td><?= $score ?>%</td>
              <td><span class="badge bg-<?= $badge ?>"><?= $label ?> Risk</span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('healthChart').getContext('2d');
    const healthChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
          label: 'Health Score (%)',
          data: <?= json_encode($health_scores) ?>,
          backgroundColor: function(ctx) {
            const value = ctx.raw;
            if (value >= 70) return 'rgba(40, 167, 69, 0.7)';
            if (value >= 50) return 'rgba(255, 193, 7, 0.7)';
            return 'rgba(220, 53, 69, 0.7)';
          },
          borderColor: 'rgba(0,0,0,0.1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ctx.raw + "%" } }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: { display: true, text: 'Health Score (%)' }
          },
          x: {
            title: { display: true, text: 'Computers' }
          }
        }
      }
    });
  </script>
</body>
</html>
