<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$candidates = $pdo->query("SELECT * FROM candidates ORDER BY votes DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>View Results</h2>
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
            if(link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4">Voting Results</h1>
            <p class="lead">Current standings</p>
        </div>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($candidates as $candidate): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php if ($candidate['photo_url']): ?>
                            <img src="<?= $candidate['photo_url'] ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($candidate['name']) ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($candidate['name']) ?></h5>
                            <p class="text-center fw-bold text-uppercase text-primary bg-light p-3 rounded shadow">
                             <?= htmlspecialchars($candidate['bio']) ?><br>
                             </p>
                            <p class="card-text">
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($candidate['party']) ?>
                                </span>
                            </p>
                            <span class="fw-semibold text-dark bg-light px-3 py-1 border rounded shadow-sm fs-4">Votes: <?= $candidate['votes'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>