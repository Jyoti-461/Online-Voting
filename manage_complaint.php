<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$_POST['delete_id']]);
        $_SESSION['success'] = "Record deleted successfully!";
    } catch (Exception $e) {
        $_SESSION['errors'] = ["Delete failed: " . $e->getMessage()];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
$voters = [];
try {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY submitted_at DESC");
    $voters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['errors'] = ["Fetch failed: " . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 0.95rem;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .action-btns {
            white-space: nowrap;
        }
        .delete-btn {
            color: #dc3545;
            background: none;
            border: none;
            padding: 5px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .delete-btn:hover {
            opacity: 0.8;
        }
        .delete-text {
            margin-left: 5px;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            position: relative;
            overflow-x: auto;
        }
        .message-container {
            margin: 0 0 20px 0;
            position: sticky;
            top: 20px;
            z-index: 1000;
        }
        @media (max-width: 768px) {
            table {
                font-size: 0.85rem;
            }
            th, td {
                padding: 8px;
            }
            .delete-text {
                display: none;
            }
            .delete-btn {
                padding: 5px;
                font-size: 0.9rem;
            }
            .message-container {
                top: 70px;
            }
            td:nth-child(3),
            th:nth-child(3),
            td:nth-child(6),
            th:nth-child(6) {
                display: none;
            }
        }
        @media (max-width: 480px) {
            table {
                font-size: 0.8rem;
            }
            th, td {
                padding: 6px;
            }
        }
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-size: 0.95rem;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Feedback</h2>
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
                    <a href="create_admin.php" class="nav-link">
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
                    <a href="manage_results.php" class="nav-link ">
                        <i class="fas fa-chart-bar"></i>
                        Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="manage_complaint.php" class="nav-link">
                        <i class="fa-solid fa-file-lines"></i>
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
        <div class="main-content">
            <div class="message-container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert success">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert error">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                        <?php unset($_SESSION['errors']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Gmail</th>
                <th>Mobile</th>
                <th>Description</th>
                <th>Submitted At</th>
            </tr>
            <?php foreach ($voters as $voter): ?>
                <tr>
                    <td><?= htmlspecialchars($voter['id']) ?></td>
                    <td><?= htmlspecialchars($voter['name']) ?></td>
                    <td><?= htmlspecialchars($voter['gmail']) ?></td>
                    <td><?= htmlspecialchars($voter['mobile']) ?></td>
                    <td><?= htmlspecialchars($voter['description']) ?></td>
                    <td><?= htmlspecialchars($voter['submitted_at']) ?></td>
                    <td class="action-btns">
                        <form method="POST" onsubmit="return confirmDelete('<?= htmlspecialchars($voter['name']) ?>')">
                            <input type="hidden" name="delete_id" value="<?= $voter['id'] ?>">
                            <button type="submit" class="delete-btn">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <script>
            function confirmDelete(name) {
                return confirm(`Are you sure you want to delete the record for ${name}?`);
            }
        </script>
    </div>
</body>
</html>