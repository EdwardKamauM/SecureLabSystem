<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

$msg = "";

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $computer_id = $_POST['computer_id'];
    $start_time  = $_POST['start_time'];
    $end_time    = $_POST['end_time'];

    // Check time validity
    $now = date('Y-m-d H:i:s');
    if ($start_time < $now) {
        $msg = "<div class='alert alert-danger'>You cannot book a past time.</div>";
    } elseif ($end_time <= $start_time) {
        $msg = "<div class='alert alert-danger'>End time must be after start time.</div>";
    } else {
        // Prevent overlapping bookings with approved only
        $chk = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE computer_id = ? AND status = 'approved' AND (start_time < ? AND end_time > ?)");
        $chk->execute([$computer_id, $end_time, $start_time]);
        if ($chk->fetchColumn() > 0) {
            $msg = "<div class='alert alert-warning'>This computer is already booked for that slot.</div>";
        } else {
            // Insert booking with status 'pending'
            $ins = $pdo->prepare("INSERT INTO bookings (user_id, computer_id, start_time, end_time, status) VALUES (?, ?, ?, ?, 'pending')");
            $ins->execute([$_SESSION['user_id'], $computer_id, $start_time, $end_time]);
            $msg = "<div class='alert alert-info'>Booking request submitted and is pending admin approval.</div>";
        }
    }
}

// Fetch available computers for the chosen slot (based on approved bookings only)
$defaultStart = date('Y-m-d\TH:i', time());
$defaultEnd   = date('Y-m-d\TH:i', time() + 3600);

$availStmt = $pdo->prepare("SELECT c.id, c.label FROM computers c WHERE c.status != 'maintenance' AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.computer_id = c.id AND b.status = 'approved' AND (b.start_time < :end AND b.end_time > :start)) ORDER BY c.label");
$availStmt->execute([
    ':start' => $_POST['start_time'] ?? $defaultStart,
    ':end'   => $_POST['end_time']   ?? $defaultEnd,
]);
$computers = $availStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Book a Computer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-laptop me-2"></i>Book a Computer</h2>
    <?= $msg ?>

    <form method="post" class="row g-3 mb-4">
      <div class="col-md-5">
        <label class="form-label">Start Time</label>
        <input type="datetime-local" name="start_time" class="form-control"
               value="<?= htmlspecialchars($_POST['start_time'] ?? $defaultStart) ?>" 
               min="<?= date('Y-m-d\TH:i') ?>" required>
      </div>
      <div class="col-md-5">
        <label class="form-label">End Time</label>
        <input type="datetime-local" name="end_time" class="form-control"
               value="<?= htmlspecialchars($_POST['end_time'] ?? $defaultEnd) ?>" 
               min="<?= date('Y-m-d\TH:i') ?>" required>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">
          <i class="fas fa-check-circle me-2"></i>Check
        </button>
      </div>
    </form>

    <?php if (count($computers)): ?>
      <form method="post">
        <input type="hidden" name="start_time" value="<?= htmlspecialchars($_POST['start_time'] ?? $defaultStart) ?>">
        <input type="hidden" name="end_time"   value="<?= htmlspecialchars($_POST['end_time']   ?? $defaultEnd) ?>">
        <div class="list-group mb-3">
          <?php foreach ($computers as $c): ?>
            <label class="list-group-item d-flex justify-content-between align-items-center">
              <input class="form-check-input me-2" type="radio" name="computer_id" value="<?= $c['id'] ?>" required>
              <?= htmlspecialchars($c['label']) ?>
              <i class="fas fa-laptop text-success"></i>
            </label>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn btn-success">
          <i class="fas fa-book me-2"></i>Request Booking
        </button>
      </form>
    <?php else: ?>
      <div class="alert alert-info">No computers available for that time slot.</div>
    <?php endif; ?>
  </div>

  <?php include_once "../includes/footer.php"; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
