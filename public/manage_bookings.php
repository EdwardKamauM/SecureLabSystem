<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Only admin or technician allowed
if (!in_array($_SESSION['role'], ['admin'])) {
    header("Location: dashboard_user.php");
    exit();
}

// Get search term if any
$search = isset($_GET['booking_search']) ? trim($_GET['booking_search']) : '';

// Fetch bookings with search functionality
if (!empty($search)) {
    $query = "
      SELECT b.*, u.name AS user_name, c.label AS computer_label 
      FROM bookings b
      JOIN users u ON b.user_id = u.id
      JOIN computers c ON b.computer_id = c.id
      WHERE u.name LIKE ? OR c.label LIKE ?
      ORDER BY b.start_time DESC
    ";
    $stmt = $pdo->prepare($query);
    $searchParam = "%" . $search . "%";
    $stmt->execute([$searchParam, $searchParam]);
    $bookings = $stmt->fetchAll();
} else {
    // Fetch all bookings
    $stmt = $pdo->query("
      SELECT b.*, u.name AS user_name, c.label AS computer_label 
      FROM bookings b
      JOIN users u ON b.user_id = u.id
      JOIN computers c ON b.computer_id = c.id
      ORDER BY b.start_time DESC
    ");
    $bookings = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Bookings - Secure Lab</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-calendar-check me-2"></i>Manage Bookings</h2>

    <!-- Search form for bookings -->
    <div class="search-container mb-4">
      <form action="manage_bookings.php" method="GET" class="d-flex">
          <input type="text" name="booking_search" class="form-control me-2" placeholder="Search by user name or computer" value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit" class="btn btn-primary">
              <i class="fas fa-search me-1"></i>Search
          </button>
          <?php if(!empty($search)): ?>
              <a href="manage_bookings.php" class="btn btn-secondary ms-2">Clear</a>
          <?php endif; ?>
      </form>
    </div>

    <?php if (count($bookings)): ?>
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
          <thead class="table-primary">
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Computer</th>
              <th>Start Time</th>
              <th>End Time</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bookings as $index => $b): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($b['user_name']) ?></td>
                <td><?= htmlspecialchars($b['computer_label']) ?></td>
                <td><?= date("M d, Y H:i", strtotime($b['start_time'])) ?></td>
                <td><?= date("M d, Y H:i", strtotime($b['end_time'])) ?></td>
                <td>
                  <?php if ($b['status'] === 'approved'): ?>
                    <span class="badge bg-success">Approved</span>
                  <?php elseif ($b['status'] === 'rejected'): ?>
                    <span class="badge bg-danger">Rejected</span>
                    <?php if (!empty($b['rejection_reason'])): ?>
                      <i class="fas fa-info-circle ms-1" data-bs-toggle="tooltip" title="<?= htmlspecialchars($b['rejection_reason']) ?>"></i>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($b['status'] === 'pending'): ?>
                    <a href="approve_booking.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-success me-1">
                      <i class="fas fa-check"></i> Approve
                    </a>
                    <!-- Replace direct reject link with modal trigger -->
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $b['id'] ?>">
                      <i class="fas fa-times"></i> Reject
                    </button>
                    
                    <!-- Modal for rejection justification -->
                    <div class="modal fade" id="rejectModal<?= $b['id'] ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?= $b['id'] ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel<?= $b['id'] ?>">Reject Booking</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <form action="reject_booking.php" method="post">
                            <div class="modal-body">
                              <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                              <div class="mb-3">
                                <label for="justification<?= $b['id'] ?>" class="form-label">Rejection Justification:</label>
                                <textarea class="form-control" id="justification<?= $b['id'] ?>" name="justification" rows="3" required></textarea>
                                <div class="form-text">
                                  Please provide a reason for rejecting this booking. This will be visible to the user.
                                </div>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-danger">Reject Booking</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php elseif ($b['status'] === 'rejected' && !empty($b['rejection_reason'])): ?>
                    <!-- View rejection reason button for already rejected bookings -->
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewReasonModal<?= $b['id'] ?>">
                      <i class="fas fa-eye"></i> View Reason
                    </button>
                    
                    <!-- Modal to display rejection reason -->
                    <div class="modal fade" id="viewReasonModal<?= $b['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Rejection Reason</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="rejection-reason p-3 border-start border-danger border-4">
                              <?= htmlspecialchars($b['rejection_reason']) ?>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  <?php else: ?>
                    <span class="text-muted">No action</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <?php if(!empty($search)): ?>
          No bookings found matching "<?= htmlspecialchars($search) ?>". <a href="manage_bookings.php" class="alert-link">Clear search</a>
        <?php else: ?>
          No bookings found.
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php include_once "../includes/footer.php"; ?>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/scripts.js"></script>
  <script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });
      
      // Character counter for justification text areas
      const textareas = document.querySelectorAll('textarea[name="justification"]');
      textareas.forEach(function(textarea) {
        textarea.addEventListener('input', function() {
          const maxLength = 500;
          const currentLength = this.value.length;
          const remaining = maxLength - currentLength;
          
          let counterId = 'char-counter-' + this.id;
          let counter = document.getElementById(counterId);
          
          if (!counter) {
            counter = document.createElement('div');
            counter.id = counterId;
            counter.className = 'form-text mt-1';
            this.parentNode.appendChild(counter);
          }
          
          counter.textContent = remaining + ' characters remaining';
          
          if (remaining < 50) {
            counter.classList.add('text-danger');
            counter.classList.remove('text-muted');
          } else {
            counter.classList.add('text-muted');
            counter.classList.remove('text-danger');
          }
        });
      });
    });
  </script>
</body>
</html>