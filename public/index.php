<?php
session_start();
require_once "../config/db.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $captcha_input = $_POST["captcha_input"];

    if (strtolower($captcha_input) !== strtolower($_SESSION['captcha_code'])) {
        $msg = "<div class='alert alert-danger'>Incorrect CAPTCHA. Please try again.</div>";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'approved' && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_user.php");
            }
            exit();
        } else {
            $msg = "<div class='alert alert-danger'>Invalid email or password.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Secure Lab</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #e3f2fd, #f1f8ff);
        }
        .login-card {
            margin-top: 4rem;
            padding: 2rem;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 login-card">
            <h2 class="text-center mb-4 text-primary"><i class="fas fa-lock me-2"></i>Secure Login</h2>
            <?php if ($msg): ?>
                <?= $msg ?>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required autocomplete="username">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">CAPTCHA</label><br>
                    <img src="captcha.php" alt="CAPTCHA Image" class="mb-2 border rounded"><br>
                    <input type="text" name="captcha_input" class="form-control" required placeholder="Enter CAPTCHA">
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
                <div class="text-center mt-3">
                    <a href="register.php" class="text-decoration-none"><i class="fas fa-user-plus me-1"></i>Don't have an account? Register</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            var input = document.getElementById("password");
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
