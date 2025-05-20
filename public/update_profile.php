<?php
require_once "../includes/auth.php";
require_once "../config/db.php";

$userId = $_SESSION['user_id'];
$msg = "";

// Fetch current user data
$stmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    // Validate password if provided
    if (!empty($password)) {
        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[!@#$%^&*]).{8,}$/";
        if (!preg_match($pattern, $password)) {
            $msg = "<div class='alert alert-warning'>Password must have at least 8 characters, include uppercase, lowercase, number, and symbol.</div>";
        } elseif ($password !== $confirm) {
            $msg = "<div class='alert alert-danger'>Passwords do not match.</div>";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $passwordHash, $userId]);
            $_SESSION['name'] = $name;
            $msg = "<div class='alert alert-success'>Profile updated successfully.</div>";
        }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $userId]);
        $_SESSION['name'] = $name;
        $msg = "<div class='alert alert-success'>Profile updated (without changing password).</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
  <?php include_once "../includes/navbar.php"; ?>
  
  <div class="container my-5">
    <h2><i class="fas fa-user-edit me-2"></i>Update Profile</h2>
    <?= $msg ?>
    <form method="post" class="mt-4">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['name']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">New Password <small class="text-muted">(optional)</small></label>
        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password"
               pattern="(?=.*\\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}">
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
      <a href="dashboard_user.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>

  <?php include_once "../includes/footer.php"; ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
