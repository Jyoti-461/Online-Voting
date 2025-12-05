<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
if (!isset($_SESSION['is_admin_dba']) || !$_SESSION['is_admin_dba']) {
    header("Location: index.php");
    exit();
}
$error = '';
$success = '';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security violation detected!");
        }
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        if (empty($username) || empty($password) || empty($email)) {
            throw new Exception("All fields are required!");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format!");
        }
        if (strlen($password) < 12) {
            throw new Exception("Password must be at least 12 characters!");
        }
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            throw new Exception("Username or email already exists!");
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (username, password, email, is_admin_dba, created_at)
            VALUES (?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$username, $hashed_password, $email]);
        $success = "Admin created successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .admin-form {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .password-rules {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }
        @media (max-width: 768px) {
            .admin-form {
                margin: 1rem;
                padding: 1.5rem;
            }
            .password-rules ul {
                padding-left: 1.5rem;
            }
            .dashboard-container {
                margin-left: 0;
            }
        }
        @media (max-width: 480px) {
            .admin-form {
                padding: 1rem;
            }
            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>New Admin</h2>
    </div>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Voting System</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="create_admin.php" class="nav-link ">
                        <i class="fa-solid fa-user-tie"></i>
                        New Admin
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_candidates.php" class="nav-link ">
                        <i class="fas fa-users"></i>
                        Candidates
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_voters.php" class="nav-link">
                        <i class="fas fa-user-friends"></i>
                        Voters
                    </a>
                </li>
                <li class="nav-item">
                    <a href="results.php" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_complaint.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Feedback
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
        <div class="container">
            <div class="admin-form">
                <h2 class="mb-4">Create New Admin</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                        <div class="password-rules">
                            Must contain:
                            <ul>
                                <li>Minimum 12 characters</li>
                                <li>At least 1 uppercase letter</li>
                                <li>At least 1 lowercase letter</li>
                                <li>At least 1 number</li>
                                <li>At least 1 special character</li>
                            </ul>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
        <script>
            document.querySelector('.menu-toggle').addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    const sidebar = document.querySelector('.sidebar');
                    if (!sidebar.contains(e.target) && !e.target.closest('.menu-toggle')) {
                        sidebar.classList.remove('active');
                    }
                }
            });
            document.querySelectorAll('.nav-link').forEach(link => {
                if (link.href === window.location.href) {
                    link.classList.add('active');
                }
            });
        </script>
        </div>
</body>
</html>