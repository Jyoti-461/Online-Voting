<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
function generateCaptcha() {
    $num1 = rand(1, 15);
    $num2 = rand(1, 15);
    $_SESSION['captcha_answer'] = $num1 + $num2;
    return "$num1 + $num2";
}
function validateCaptcha($userAnswer) {
    if (!isset($_SESSION['captcha_answer']) || (int)$userAnswer !== $_SESSION['captcha_answer']) {
        return false;
    }
    unset($_SESSION['captcha_answer']);
    return true;
}
function loginUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, password, is_admin_dba FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return $user['is_admin_dba']; // Return user role (admin/voter)
    } else {
        return false; // Incorrect password
    }
}
?>