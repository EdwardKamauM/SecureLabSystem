<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

// Only admin allowed
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard_user.php");
    exit();
}

$msg = "";

// Handle new user addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'approved')");
    try {
        $stmt->execute([$name, $email, $password, $role]);
        $msg = "<div class='alert alert-success'>✅ User added successfully!</div>";
    } catch (PDOException $e) {
        $msg = "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
    }
}

// Archive user
if (isset($_GET['archive'])) {
    $uid = $_GET['archive'];
    $pdo->prepare("UPDATE users SET status = 'archived' WHERE id = ?")->execute([$uid]);
    header("Location: manage_users.php");
    exit();
}

// Approve user
if (isset($_GET['approve'])) {
    $uid = $_GET['approve'];
    $pdo->prepare("UPDATE users SET status = 'approved' WHERE id = ?")->execute([$uid]);
    header("Location: manage_users.php");
    exit();
}

// Fetch users with search functionality
$search = isset($_GET['user_search']) ? trim($_GET['user_search']) : '';

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (name LIKE ? OR email LIKE ?) ORDER BY status DESC, role, name");
    $searchParam = "%" . $search . "%";
    $stmt->execute([$searchParam, $searchParam]);
    $users = $stmt->fetchAll();
} else {
    $users = $pdo->query("SELECT * FROM users ORDER BY status DESC, role, name")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include_once "../includes/navbar.php"; ?>

<div class="container my-5">
  <h2 class="mb-4"><i class="fas fa-users-cog me-2 text-primary"></i>Manage Users</h2>
  <?= $msg ?>

  <!-- Search form for users -->
  <div class="search-container mb-4">
    <form action="manage_users.php" method="GET" class="d-flex">
        <input type="text" name="user_search" class="form-control me-2" placeholder="Search users by name or email" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search me-1"></i>Search
        </button>
        <?php if(!empty($search)): ?>
            <a href="manage_users.php" class="btn btn-secondary ms-2">Clear</a>
        <?php endif; ?>
    </form>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title"><i class="fas fa-user-plus me-2 text-success"></i>Add New User</h5>
      <form method="post" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" required class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Email</label>
          <input type="email" name="email" required class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" required class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Role</label>
          <select name="role" required class="form-select">
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="technician">Technician</option>
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" name="add_user" class="btn btn-success w-100">
            <i class="fas fa-plus"></i> Add
          </button>
        </div>
      </form>
    </div>
  </div>

  <?php if(count($users) > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= ucfirst($u['role']) ?></td>
              <td>
                <span class="badge bg-<?= $u['status'] === 'approved' ? 'success' : ($u['status'] === 'pending' ? 'warning text-dark' : 'secondary') ?>">
                  <?= ucfirst($u['status']) ?>
                </span>
              </td>
              <td>
                <?php if ($u['status'] === 'pending'): ?>
                  <a href="?approve=<?= $u['id'] ?>" class="btn btn-sm btn-success">
                    <i class="fas fa-check"></i> Approve
                  </a>
                <?php endif; ?>
                <?php if ($u['status'] !== 'archived'): ?>
                  <a href="?archive=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Archive this user?')">
                    <i class="fas fa-box-archive"></i> Archive
                  </a>
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
        No users found matching "<?= htmlspecialchars($search) ?>". <a href="manage_users.php" class="alert-link">Clear search</a>
      <?php else: ?>
        No users found in the system.
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>