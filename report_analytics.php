<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Make sure only admins and technicians can access this page
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'technician') {
  header("Location: dashboard_user.php");
  exit();
}

// Time period for analytics
$period = isset($_GET['period']) ? $_GET['period'] : 'week';

// Get data for charts based on time period
switch ($period) {
  case 'month':
    $dateFormat = "%Y-%m-%d";
    $timespan = "30 DAY";
    $groupBy = "DATE(created_at)";
    break;
  case 'quarter':
    $dateFormat = "%Y-%m-%d";
    $timespan = "90 DAY";
    $groupBy = "DATE(created_at)";
    break;
  default: // week
    $dateFormat = "%Y-%m-%d";
    $timespan = "7 DAY";
    $groupBy = "DATE(created_at)";
    break;
}

// Bookings by day
$bookingsByDayStmt = $pdo->prepare("
  SELECT 
    DATE_FORMAT(created_at, :dateFormat) as date,
    COUNT(*) as count
  FROM bookings
  WHERE created_at >= DATE_SUB(NOW(), INTERVAL $timespan)
  GROUP BY $groupBy
  ORDER BY date ASC
");
$bookingsByDayStmt->execute(['dateFormat' => $dateFormat]);
$bookingsByDay = $bookingsByDayStmt->fetchAll(PDO::FETCH_ASSOC);

// Computer usage hours by ID
$computerUsageStmt = $pdo->prepare("
  SELECT 
    computer_id,
    SUM(TIMESTAMPDIFF(HOUR, start_time, end_time)) as hours_used
  FROM bookings
  WHERE status = 'approved'
  AND created_at >= DATE_SUB(NOW(), INTERVAL $timespan)
  GROUP BY computer_id
  ORDER BY hours_used DESC
  LIMIT 10
");
$computerUsageStmt->execute();
$computerUsage = $computerUsageStmt->fetchAll(PDO::FETCH_ASSOC);

// Get computer names for the usage chart
$computerIds = array_column($computerUsage, 'computer_id');
if (!empty($computerIds)) {
  $placeholders = str_repeat('?,', count($computerIds) - 1) . '?';
  $computerNamesStmt = $pdo->prepare("
    SELECT id, name FROM computers WHERE id IN ($placeholders)
  ");
  $computerNamesStmt->execute($computerIds);
  $computerNames = $computerNamesStmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Get health score trends
$healthScoreStmt = $pdo->prepare("
  SELECT 
    health_score,
    COUNT(*) as count
  FROM computers
  GROUP BY health_score
  ORDER BY health_score ASC
");
$healthScoreStmt->execute();
$healthScores = $healthScoreStmt->fetchAll(PDO::FETCH_ASSOC);

// Get booking status distribution
$bookingStatusStmt = $pdo->prepare("
  SELECT 
    status,
    COUNT(*) as count
  FROM bookings
  WHERE created_at >= DATE_SUB(NOW(), INTERVAL $timespan)
  GROUP BY status
");
$bookingStatusStmt->execute();
$bookingStatus = $bookingStatusStmt->fetchAll(PDO::FETCH_ASSOC);

// Get daily user signups
$userSignupsStmt = $pdo->prepare("
  SELECT 
    DATE_FORMAT(created_at, :dateFormat) as date,
    COUNT(*) as count
  FROM users
  WHERE created_at >= DATE_SUB(NOW(), INTERVAL $timespan)
  GROUP BY $groupBy
  ORDER BY date ASC
");
$userSignupsStmt->execute(['dateFormat' => $dateFormat]);
$userSignups = $userSignupsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get usage by time of day
$usageByTimeStmt = $pdo->prepare("
  SELECT 
    HOUR(start_time) as hour,
    COUNT(*) as count
  FROM bookings
  WHERE created_at >= DATE_SUB(NOW(), INTERVAL $timespan)
  AND status = 'approved'
  GROUP BY HOUR(start_time)
  ORDER BY hour ASC
");
$usageByTimeStmt->execute();
$usageByTime = $usageByTimeStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body class="bg-light d-flex flex-column min-vh-100">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2><i class="fas fa-chart-line me-2"></i>Analytics Dashboard</h2>
      
      <div class="btn-group">
        <a href="?period=week" class="btn btn-outline-primary <?= $period == 'week' ? 'active' : '' ?>">Last Week</a>
        <a href="?period=month" class="btn btn-outline-primary <?= $period == 'month' ? 'active' : '' ?>">Last Month</a>
        <a href="?period=quarter" class="btn btn-outline-primary <?= $period == 'quarter' ? 'active' : '' ?>">Last Quarter</a>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card shadow-sm h-100 border-start border-primary border-4">
          <div class="card-body">
            <h6 class="card-title text-muted">Total Bookings</h6>
            <h3 class="card-text">
              <?= array_sum(array_column($bookingsByDay, 'count')) ?>
              <small class="text-muted fs-6"></small>
            </h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm h-100 border-start border-success border-4">
          <div class="card-body">
            <h6 class="card-title text-muted">Approval Rate</h6>
            <?php
            $approved = 0;
            $total = 0;
            foreach ($bookingStatus as $status) {
              $total += $status['count'];
              if ($status['status'] == 'approved') {
                $approved = $status['count'];
              }
            }
            $approvalRate = $total > 0 ? round(($approved / $total) * 100) : 0;
            ?>
            <h3 class="card-text">
              <?= $approvalRate ?>%
            </h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm h-100 border-start border-warning border-4">
          <div class="card-body">
            <h6 class="card-title text-muted">New Users</h6>
            <h3 class="card-text">
              <?= array_sum(array_column($userSignups, 'count')) ?>
            </h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm h-100 border-start border-danger border-4">
          <div class="card-body">
            <h6 class="card-title text-muted">Avg Health Score</h6>
            <?php
            $totalScore = 0;
            $totalComputers = 0;
            foreach ($healthScores as $score) {
              $totalScore += $score['health_score'] * $score['count'];
              $totalComputers += $score['count'];
            }
            $avgHealthScore = $totalComputers > 0 ? round($totalScore / $totalComputers) : 0;
            ?>
            <h3 class="card-text">
              <?= $avgHealthScore ?>/100
            </h3>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- Bookings Over Time -->
      <div class="col-lg-8">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Booking Trends</h5>
          </div>
          <div class="card-body">
            <canvas id="bookingTrendsChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Booking Status -->
      <div class="col-lg-4">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Booking Status</h5>
          </div>
          <div class="card-body">
            <canvas id="bookingStatusChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Most Used Computers -->
      <div class="col-lg-8">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Most Used Computers (Hours)</h5>
          </div>
          <div class="card-body">
            <canvas id="computerUsageChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Health Score Distribution -->
      <div class="col-lg-4">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Health Score Distribution</h5>
          </div>
          <div class="card-body">
            <canvas id="healthScoreChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Usage By Time of Day -->
      <div class="col-lg-12">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Usage By Time of Day</h5>
          </div>
          <div class="card-body">
            <canvas id="usageByTimeChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include_once "../includes/footer.php"; ?>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Prepare data for charts
    const bookingTrendsData = <?= json_encode($bookingsByDay) ?>;
    const bookingStatusData = <?= json_encode($bookingStatus) ?>;
    const computerUsageData = <?= json_encode($computerUsage) ?>;
    const computerNames = <?= !empty($computerNames) ? json_encode($computerNames) : '{}' ?>;
    const healthScoreData = <?= json_encode($healthScores) ?>;
    const usageByTimeData = <?= json_encode($usageByTime) ?>;
    
    // Set up charts after page loads
    document.addEventListener('DOMContentLoaded', function() {
      // Booking trends chart
      const bookingTrendsCtx = document.getElementById('bookingTrendsChart').getContext('2d');
      new Chart(bookingTrendsCtx, {
        type: 'line',
        data: {
          labels: bookingTrendsData.map(item => item.date),
          datasets: [{
            label: 'Bookings',
            data: bookingTrendsData.map(item => item.count),
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          },
          responsive: true,
          maintainAspectRatio: false
        }
      });

      // Booking status chart
      const bookingStatusCtx = document.getElementById('bookingStatusChart').getContext('2d');
      new Chart(bookingStatusCtx, {
        type: 'doughnut',
        data: {
          labels: bookingStatusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
          datasets: [{
            data: bookingStatusData.map(item => item.count),
            backgroundColor: [
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 206, 86, 0.7)',
              'rgba(75, 192, 192, 0.7)',
              'rgba(255, 99, 132, 0.7)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });

      // Computer usage chart
      const computerUsageCtx = document.getElementById('computerUsageChart').getContext('2d');
      new Chart(computerUsageCtx, {
        type: 'bar',
        data: {
          labels: computerUsageData.map(item => computerNames[item.computer_id] || `PC #${item.computer_id}`),
          datasets: [{
            label: 'Hours Used',
            data: computerUsageData.map(item => item.hours_used),
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          },
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y'
        }
      });

      // Health score chart
      const healthScoreCtx = document.getElementById('healthScoreChart').getContext('2d');
      
      // Group health scores into ranges
      const scoreRanges = {
        '0-25': 0,
        '26-50': 0,
        '51-75': 0,
        '76-100': 0
      };
      
      healthScoreData.forEach(item => {
        const score = parseInt(item.health_score);
        if (score <= 25) scoreRanges['0-25'] += parseInt(item.count);
        else if (score <= 50) scoreRanges['26-50'] += parseInt(item.count);
        else if (score <= 75) scoreRanges['51-75'] += parseInt(item.count);
        else scoreRanges['76-100'] += parseInt(item.count);
      });
      
      new Chart(healthScoreCtx, {
        type: 'pie',
        data: {
          labels: Object.keys(scoreRanges),
          datasets: [{
            data: Object.values(scoreRanges),
            backgroundColor: [
              'rgba(255, 99, 132, 0.7)',
              'rgba(255, 159, 64, 0.7)',
              'rgba(255, 205, 86, 0.7)',
              'rgba(75, 192, 192, 0.7)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });

      // Usage by time of day chart
      const usageByTimeCtx = document.getElementById('usageByTimeChart').getContext('2d');
      
      // Create 24-hour time labels
      const hourLabels = Array.from({length: 24}, (_, i) => {
        const hour = i % 12 || 12;
        const ampm = i < 12 ? 'AM' : 'PM';
        return `${hour}${ampm}`;
      });
      
      // Fill in missing hours with 0 count
      const hourCounts = Array(24).fill(0);
      usageByTimeData.forEach(item => {
        hourCounts[parseInt(item.hour)] = parseInt(item.count);
      });
      
      new Chart(usageByTimeCtx, {
        type: 'bar',
        data: {
          labels: hourLabels,
          datasets: [{
            label: 'Bookings',
            data: hourCounts,
            backgroundColor: 'rgba(153, 102, 255, 0.7)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
              }
            }
          },
          responsive: true,
          maintainAspectRatio: false
        }
      });
    });
  </script>
</body>
</html>