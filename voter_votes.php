<?php
require_once 'includes/auth.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if ($_SESSION['has_voted'] ?? false) {
    header("Location: voter_dashboard.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_POST['candidate_id']]);
        $stmt = $pdo->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([$_POST['candidate_id']]);
        $stmt = $pdo->prepare("UPDATE users SET has_voted = TRUE WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $pdo->commit();
        $_SESSION['has_voted'] = true;
        header("Location: voter_dashboard.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error submitting vote: " . $e->getMessage();
    }
}
$candidates = $pdo->query("SELECT * FROM candidates")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cast Vote</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            background-color: #f5f6fa;
        }
        .container {
            width: calc(100% - 280px);
            background: white;
            padding: 50px;
            margin: 50px auto;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            text-align: center;
        }
        .candidate label {
            display: block;
            padding: 15px;
            cursor: pointer;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 30px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 18px;
        }
        .error {
            background: #e74c3c;
            color: white;
        }
        .candidates {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            justify-content: center;
        }
        .candidate {
            background: #ecf0f1;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: 0.3s;
            font-size: 20px;
        }
        .candidate:hover {
            background: #d5d8dc;
        }
        .candidate input {
            margin-right: 15px;
            transform: scale(1.5);
        }
        .candidate h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .candidate p {
            font-size: 18px;
        }
        .btn {
            background: #27ae60;
            color: white;
            padding: 18px 30px;
            border: none;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 25px;
        }
        .btn:hover {
            background: #219150;
        }
        @media (max-width: 1200px) {
            .candidates {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 992px) {
            .candidates {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 40px;
            }
            .candidates {
                grid-template-columns: repeat(1, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Cast Vote</h2>
    </div>
   <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Voting System</h2>
                <p>Voter Panel</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="voter_dashboard.php" class="nav-link ">
                        <i class="fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="voter_edits.php" class="nav-link">
                        <i class="fa-solid fa-info"></i>
                        Request Edit
                    </a>
                </li>
                <li class="nav-item">
                    <a href="voter_votes.php" class="nav-link">
                        <i class="fa-solid fa-check-to-slot"></i>
                        Cast Vote
                    </a>
                </li>
                <li class="nav-item">
                    <a href="voter_results.php" class="nav-link">
                        <i class="fa-solid fa-square-poll-vertical"></i>
                        View Results
                    </a>
                </li>
                <li class="nav-item">
                    <a href="voter_contact.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Contact Us
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
        <div class="container">
            <h1>Cast Your Vote</h1>
            <?php if (isset($error)): ?>
                <div class="alert error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="candidates">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="candidate">
                            <input type="radio" name="candidate_id"
                                value="<?= $candidate['id'] ?>" required>
                            <h3><?= $candidate['name'] ?></h3>
                            <p>Party: <?= $candidate['party'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn">Submit Vote</button>
            </form>
        </div>
    </div>
</body>
</html>