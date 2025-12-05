<?php
session_start();
require_once 'includes/auth.php';
require_once __DIR__ . '/config/db.php';
if (isset($_SESSION['is_admin_dba'])) {
    if ($_SESSION['is_admin_dba'] == 1) {
        header("Location: dashboard.php");
    } else {
        header("Location: voter_dashboard.php");
    }
    exit();
}
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();   
    $_SESSION['has_voted'] = $user['has_voted'] ?? 0;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $captchaAnswer = $_POST['captcha'];
    if (!validateCaptcha($captchaAnswer)) {
        $error = "Invalid CAPTCHA! Please try again.";
    } else {
        $userRole = loginUser($username, $password);
        if ($userRole !== false) {
            $_SESSION['is_admin_dba'] = (int)$userRole;
            if ($userRole == 1) {
                header("Location: dashboard.php");
            } else {
                header("Location: voter_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    }
}
$captchaQuestion = generateCaptcha();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System - Login</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="container">
        <h1>Online Voting System</h1>
        <div class="login-box">
            <h2>Login</h2>
            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>           
            <form method="POST">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>                
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>CAPTCHA: <?= $captchaQuestion ?> = ?</label>
                    <input type="number" name="captcha" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>           
            <div class="links">
                <a href="signup.php">Create Account</a>
            </div>
            <div class="links">
                <a href="emailotp.php">Forgot Password</a>
            </div>
        </div>
    </div>
    <script>
document.querySelector('form').addEventListener('submit', function(e) {
    const btn = this.querySelector('.btn');
    btn.classList.add('loading');
    setTimeout(() => btn.classList.remove('loading'), 2000);
});
</script>
</body>
</html>