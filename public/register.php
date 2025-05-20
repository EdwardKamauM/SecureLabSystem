<?php 
require_once "../config/db.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    
    if ($_POST["password"] !== $_POST["confirm_password"]) {
        $msg = "<div class='alert alert-danger'>Passwords do not match.</div>";
    } else {
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $role = $_POST["role"];

        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE name = ? AND email = ?");
        $check->execute([$name, $email]);

        if ($check->fetchColumn() > 0) {
            $msg = "<div class='alert alert-danger'>A user with this name and email already exists.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'pending')");
            try {
                $stmt->execute([$name, $email, $password, $role]);
                $msg = "<div class='alert alert-success'>Registration successful. Awaiting admin approval.</div>";
            } catch (PDOException $e) {
                $msg = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Secure Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">
    <div class="container col-md-6 offset-md-3 bg-white shadow p-4 rounded border">
        <h2 class="mb-3 text-center text-primary"><i class="fas fa-user-plus me-2"></i>User Registration</h2>
        <?php if ($msg): ?>
            <?= $msg ?>
        <?php endif; ?>
        <form method="post" onsubmit="return validatePasswords()">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" required class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" required class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Password <small class="text-muted">(Min 8 chars, uppercase, number, symbol)</small></label>
                <div class="input-group">
                    <input type="password" name="password" id="password" required class="form-control"
                           pattern="(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}" 
                           title="At least 8 characters, one uppercase letter, one number, and one special character.">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" required class="form-control">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm_password')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Select Role</label>
                <select name="role" required class="form-select">
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="technician">Technician</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
            <a href="index.php" class="btn btn-link w-100 text-center">Already have an account? Login</a>
        </form>
    </div>

    <script>
        function validatePasswords() {
            var pass = document.getElementById("password").value;
            var confirm = document.getElementById("confirm_password").value;
            if (pass !== confirm) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }

        function togglePassword(id) {
            var input = document.getElementById(id);
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
