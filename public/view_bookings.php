<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

$userId = $_SESSION['user_id'];

// Fetch bookings for this user
$stmt = $pdo->prepare("
    SELECT b.*, c.label AS computer_name
    FROM bookings b
    JOIN computers c ON b.computer_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.start_time DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookings</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-list me-2"></i>My Bookings</h2>

    <?php if (count($bookings) > 0): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle">
          <thead class="table-primary">
            <tr>
              <th>#</th>
              <th>Computer</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $index => $b): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($b['computer_name']) ?></td>
                <td><?= date("M d, Y H:i", strtotime($b['start_time'])) ?></td>
                <td><?= date("M d, Y H:i", strtotime($b['end_time'])) ?></td>
                <td>
                  <?php if ($b['status'] === 'approved'): ?>
                    <span class='badge bg-success'>Approved</span>
                  <?php elseif ($b['status'] === 'rejected'): ?>
                    <span class='badge bg-danger'>Rejected</span>
                  <?php else: ?>
                    <span class='badge bg-warning text-dark'>Pending</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($b['status'] === 'rejected'): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reasonModal<?= $b['id'] ?>">
                      <i class="fas fa-info-circle"></i> View Reason
                    </button>
                    
                    <!-- Modal to display rejection reason -->
                    <div class="modal fade" id="reasonModal<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                              <i class="fas fa-times-circle me-2"></i>Booking Rejection
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <p class="fw-bold">Your booking for "<?= htmlspecialchars($b['computer_name']) ?>" was rejected with the following reason:</p>
                            <div class="rejection-reason p-3 border-start border-danger border-4">
                              <?= nl2br(htmlspecialchars($b['rejection_reason'] ?? 'No reason provided.')) ?>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php elseif ($b['status'] === 'approved'): ?>
                    <span class="text-success"><i class="fas fa-check-circle"></i> Confirmed</span>
                  <?php elseif ($b['status'] === 'pending'): ?>
                    <span class="text-muted"><i class="fas fa-clock"></i> Awaiting approval</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Add summary section for rejected bookings -->
      <?php
      $rejectedBookings = array_filter($bookings, function($b) {
          return $b['status'] === 'rejected';
      });
      
      if (count($rejectedBookings) > 0): 
      ?>
        <div class="mt-4">
          <h4 class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Rejected Bookings</h4>
          <div class="list-group">
            <?php foreach ($rejectedBookings as $b): ?>
              <div class="list-group-item list-group-item-danger">
                <div class="d-flex w-100 justify-content-between">
                  <h5 class="mb-1"><?= htmlspecialchars($b['computer_name']) ?></h5>
                  <small><?= date("M d, Y", strtotime($b['start_time'])) ?></small>
                </div>
                <p class="mb-1"><strong>Reason for rejection:</strong> 
                  <?= nl2br(htmlspecialchars($b['rejection_reason'] ?? 'No reason provided.')) ?>
                </p>
                <small>Time period: <?= date("H:i", strtotime($b['start_time'])) ?> - <?= date("H:i", strtotime($b['end_time'])) ?></small>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      
    <?php else: ?>
      <div class="alert alert-info">You haven't booked any computers yet.</div>
    <?php endif; ?>
  </div>

  <?php include_once "../includes/footer.php"; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });
    });
  </script>
</body>
</html>