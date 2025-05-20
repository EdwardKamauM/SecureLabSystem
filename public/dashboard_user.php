<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Get user info
$name = $_SESSION['name'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Get active bookings count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'approved' AND end_time > NOW()");
$stmt->execute([$user_id]);
$activeBookings = $stmt->fetchColumn();

// Get pending bookings count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$pendingBookings = $stmt->fetchColumn();

// Get available computers count
$availableComputers = $pdo->query("SELECT COUNT(*) FROM computers WHERE status = 'available'")->fetchColumn();

// Get user's recent booking history
$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        c.label AS computer_label, 
        c.status, 
        c.health_score, 
        c.maintenance_risk, 
        c.archived
    FROM bookings b
    JOIN computers c ON b.computer_id = c.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 3
");
$stmt->execute([$user_id]);
$recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once "../includes/header.php"; ?>

<body class="d-flex flex-column min-vh-100 bg-light">
  <?php include_once "../includes/navbar.php"; ?>

  <div class="container-fluid">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <div class="row">
        <div class="col-md-8">
          <h2><i class="fas fa-wave-square me-2"></i>Welcome, <?= htmlspecialchars($name) ?>!</h2>
          <p class="text-muted"><i class="fas fa-user-tag me-2"></i>Role: <?= ucfirst($role) ?></p>
        </div>
        <div class="col-md-4 text-end">
          <p class="current-time mb-0 fw-bold"><?= date('l, F j, Y') ?></p>
          <p class="text-muted"><?= date('h:i A') ?></p>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
      <!-- Available Computers Card -->
      <div class="col-md-4">
        <div class="card stat-card card-available mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <h5 class="card-title">Available Computers</h5>
                <h2 class="mb-0"><?= $availableComputers ?></h2>
              </div>
              <div class="col-4 text-end">
                <div class="card-icon bg-light text-success">
                  <i class="fas fa-desktop"></i>
                </div>
              </div>
            </div>
            <p class="card-text mt-3">
              <a href="book_computer.php" class="btn btn-sm btn-outline-success">
                <i class="fas fa-calendar-plus"></i> Book Now
              </a>
            </p>
          </div>
        </div>
      </div>

      <!-- Active Bookings Card -->
      <div class="col-md-4">
        <div class="card stat-card card-booked mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <h5 class="card-title">Active Bookings</h5>
                <h2 class="mb-0"><?= $activeBookings ?></h2>
              </div>
              <div class="col-4 text-end">
                <div class="card-icon bg-light text-primary">
                  <i class="fas fa-calendar-check"></i>
                </div>
              </div>
            </div>
            <p class="card-text mt-3">
              <a href="view_bookings.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list"></i> View All
              </a>
            </p>
          </div>
        </div>
      </div>

      <!-- Pending Bookings Card -->
      <div class="col-md-4">
        <div class="card stat-card card-maintenance mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-8">
                <h5 class="card-title">Pending Requests</h5>
                <h2 class="mb-0"><?= $pendingBookings ?></h2>
              </div>
              <div class="col-4 text-end">
                <div class="card-icon bg-light text-warning">
                  <i class="fas fa-hourglass-half"></i>
                </div>
              </div>
            </div>
            <p class="card-text mt-3">
              <a href="view_bookings.php#pending" class="btn btn-sm btn-outline-warning">
                <i class="fas fa-clock"></i> Check Status
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <h4 class="mb-3">Quick Actions</h4>
    <div class="row g-4 mb-4">
      <!-- Book a Computer -->
      <div class="col-md-6 col-lg-4">
        <a href="book_computer.php" class="text-decoration-none">
          <div class="card border-start border-primary border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-calendar-check me-2 text-primary"></i>Book a Computer</h5>
              <p class="card-text">Reserve available PCs based on time slots.</p>
            </div>
          </div>
        </a>
      </div>

      <!-- View Bookings -->
      <div class="col-md-6 col-lg-4">
        <a href="view_bookings.php" class="text-decoration-none">
          <div class="card border-start border-success border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-list me-2 text-success"></i>My Bookings</h5>
              <p class="card-text">See your upcoming and past bookings.</p>
            </div>
          </div>
        </a>
      </div>

      <!-- View Computer Status -->
      <div class="col-md-6 col-lg-4">
        <a href="computer_status.php" class="text-decoration-none">
          <div class="card border-start border-info border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-desktop me-2 text-info"></i>Computer Status</h5>
              <p class="card-text">Real-time PC status and availability overview.</p>
            </div>
          </div>
        </a>
      </div>

      <!-- Health & Maintenance (Technicians Only) -->
      <?php if ($role === 'technician'): ?>
      <div class="col-md-6 col-lg-4">
        <a href="computer_status.php#at-risk" class="text-decoration-none">
          <div class="card border-start border-danger border-4 shadow h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-heartbeat me-2 text-danger"></i>Health Dashboard</h5>
              <p class="card-text">View health status and maintenance alerts.</p>
            </div>
          </div>
        </a>
      </div>
      <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <h4 class="mb-3">Recent Bookings</h4>
    <div class="card shadow">
      <div class="card-body">
        <?php if (count($recentBookings) > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Computer</th>
                  <th>Location</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentBookings as $booking): ?>
                  <tr>
                    <td><?= htmlspecialchars($booking['computer_label']) ?></td>
                    <td><?= date('M d, Y', strtotime($booking['start_time'])) ?></td>
                    <td><?= date('h:i A', strtotime($booking['start_time'])) ?> - <?= date('h:i A', strtotime($booking['end_time'])) ?></td>
                    <td>
                      <?php if ($booking['status'] === 'approved'): ?>
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Approved</span>
                      <?php elseif ($booking['status'] === 'pending'): ?>
                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                      <?php elseif ($booking['status'] === 'rejected'): ?>
                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                      <?php else: ?>
                        <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($booking['status'])) ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="text-end mt-3">
            <a href="view_bookings.php" class="btn btn-sm btn-outline-primary">View All Bookings</a>
          </div>
        <?php else: ?>
          <div class="text-center py-4">
            <i class="fas fa-calendar-day fa-3x text-muted mb-3"></i>
            <p class="mb-0">No recent bookings found.</p>
            <a href="book_computer.php" class="btn btn-primary mt-3">Book a Computer Now</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- FAQs Section -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">
    <strong>Frequently Asked Questions (FAQs)</strong>
  </div>
  <div class="card-body">
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading1">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="false" aria-controls="faq1">
            How do I book a computer?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Go to the Bookings page, select your computer and time slot, then click "Book".
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faqHeading2">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
          Why can't I book a computer at a certain time?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
          The computer may already be booked or under maintenance. Try a different time or computer.
          </div>
        </div>
      </div>
      <!-- Add more FAQ items as needed -->
    </div>
  </div>
</div>

<!-- Feedback Section -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">
    <strong>Feedback</strong>
  </div>
  <div class="card-body">
    <?php if (isset($_GET['feedback']) && $_GET['feedback'] === 'success'): ?>
      <div class="alert alert-success">Thank you for your feedback!</div>
    <?php endif; ?>
    <form action="submit_feedback.php" method="POST">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_SESSION['name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Message</label>
        <textarea name="message" class="form-control" rows="3" required></textarea>
      </div>
      <button type="submit" class="btn btn-success">Send Feedback</button>
    </form>
  </div>
</div>

<!-- Help Section -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">
    <strong>Help & Support</strong>
  </div>
  <div class="card-body">
    <ul>
      <li>Email: <a href="mailto:support@yourdomain.com">support@yourdomain.com</a></li>
      <li>Phone: +123-456-7890</li>
      <li>Visit our <a href="help.html" target="_blank">Help Center</a></li>
    </ul>
  </div>
</div>

  <?php include_once "../includes/footer.php"; ?>
</body>
</html>