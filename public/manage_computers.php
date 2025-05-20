<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Only admin can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_user.php");
    exit();
}

$msg = "";

// Add new PC
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_pc'])) {
    $label = $_POST['label'];
    $status = $_POST['status'];
    $health = $_POST['health_score'];

    $stmt = $pdo->prepare("INSERT INTO computers (label, status, health_score, archived) VALUES (?, ?, ?, 0)");
    try {
        $stmt->execute([$label, $status, $health]);
        $msg = "<div class='alert alert-success'>New PC added successfully.</div>";
    } catch (PDOException $e) {
        $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Archive PC
if (isset($_GET['archive'])) {
    $id = $_GET['archive'];
    $stmt = $pdo->prepare("UPDATE computers SET archived = 1 WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage_computers.php");
    exit();
}

// Get search term if any
$search = isset($_GET['pc_search']) ? trim($_GET['pc_search']) : '';

// Fetch active PCs with search functionality
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM computers WHERE archived = 0 AND (label LIKE ? OR id LIKE ?) ORDER BY label");
    $searchParam = "%" . $search . "%";
    $stmt->execute([$searchParam, $searchParam]);
    $pcs = $stmt->fetchAll();
} else {
    $pcs = $pdo->query("SELECT * FROM computers WHERE archived = 0 ORDER BY label")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Computers</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include_once "../includes/navbar.php"; ?>
<div class="container my-5">
  <h2 class="mb-4"><i class="fas fa-desktop me-2 text-primary"></i>Manage Computer Inventory</h2>
  <?= $msg ?>

  <!-- Search form for computers -->
  <div class="search-container mb-4">
    <form action="manage_computers.php" method="GET" class="d-flex">
        <input type="text" name="pc_search" class="form-control me-2" placeholder="Search computers by label or ID" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search me-1"></i>Search
        </button>
        <?php if(!empty($search)): ?>
            <a href="manage_computers.php" class="btn btn-secondary ms-2">Clear</a>
        <?php endif; ?>
    </form>
  </div>

  <form method="post" class="row g-3 mb-4 bg-white p-4 border rounded shadow-sm">
    <h5>Add New Computer</h5>
    <div class="col-md-4">
      <label class="form-label">Label (e.g., PC-101)</label>
      <input type="text" name="label" required class="form-control">
    </div>
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select name="status" class="form-select" required>
        <option value="available">Available</option>
        <option value="maintenance">Maintenance</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Initial Health Score (%)</label>
      <input type="number" name="health_score" min="0" max="100" class="form-control" required>
    </div>
    <div class="col-12">
      <button type="submit" name="add_pc" class="btn btn-success">
        <i class="fas fa-plus-circle me-1"></i>Add PC
      </button>
    </div>
  </form>

  <h5 class="mt-5 mb-3">Current Computers</h5>
  <?php if (count($pcs)): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-dark">
          <tr>
            <th>Label</th>
            <th>Status</th>
            <th>Health Score</th>
            <th>Maintenance Risk</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pcs as $pc): ?>
            <tr>
              <td><?= htmlspecialchars($pc['label']) ?></td>
              <td><?= htmlspecialchars($pc['status']) ?></td>
              <td><?= $pc['health_score'] ?>%</td>
              <td>
                <?php
                  $risk = $pc['maintenance_risk'] ?? 0;
                  if ($risk >= 0.7) {
                    echo '<span class="badge bg-danger">High</span>';
                  } elseif ($risk >= 0.4) {
                    echo '<span class="badge bg-warning text-dark">Medium</span>';
                  } else {
                    echo '<span class="badge bg-success">Low</span>';
                  }
                ?>
              </td>
              <td>
                <a href="?archive=<?= $pc['id'] ?>" class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Are you sure you want to archive this PC?')">
                  <i class="fas fa-box-archive"></i> Archive
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info">
      <?php if(!empty($search)): ?>
        No computers found matching "<?= htmlspecialchars($search) ?>". <a href="manage_computers.php" class="alert-link">Clear search</a>
      <?php else: ?>
        No active computers found.
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>