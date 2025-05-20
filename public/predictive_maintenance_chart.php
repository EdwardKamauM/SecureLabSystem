<?php
require_once "../includes/auth.php";
require_once "../config/db.php";
require_once "../includes/functions.php";

$computers = $pdo->query("SELECT label, maintenance_risk FROM computers ORDER BY label")->fetchAll();
$labels = [];
$risks = [];

foreach ($computers as $pc) {
    $labels[] = $pc['label'];
    $risks[] = round($pc['maintenance_risk'] * 100, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Predictive Maintenance Chart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
  <?php include_once "../includes/navbar.php"; ?>
  <div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-chart-line me-2 text-danger"></i>Predictive Maintenance Analysis</h2>

    <canvas id="riskChart" height="100"></canvas>
  </div>

  <script>
    const ctx = document.getElementById('riskChart').getContext('2d');
    const riskChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
          label: 'Maintenance Risk (%)',
          data: <?= json_encode($risks) ?>,
          backgroundColor: function(ctx) {
            const value = ctx.raw;
            if (value >= 70) return 'rgba(220, 53, 69, 0.7)'; // red
            if (value >= 40) return 'rgba(255, 193, 7, 0.7)'; // yellow
            return 'rgba(40, 167, 69, 0.7)'; // green
          },
          borderColor: 'rgba(0,0,0,0.1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: {
              label: ctx => ctx.raw + "%"
          }}
        },
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            title: { display: true, text: 'Risk (%)' }
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
