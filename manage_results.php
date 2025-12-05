<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
if (!isset($_SESSION['is_admin_dba']) || !$_SESSION['is_admin_dba']) {
    header("Location: index.php");
    exit();
}
try {
    $results = $pdo->query("
        SELECT 
            c.id,
            c.name,
            c.party,
            c.photo_url,
            COUNT(v.id) AS votes,
            (COUNT(v.id) / (SELECT COUNT(*) FROM votes) * 100) AS percentage
        FROM candidates c
        LEFT JOIN votes v ON c.id = v.candidate_id
        GROUP BY c.id
        ORDER BY votes DESC
    ")->fetchAll();
    $total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
    $total_voters = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $voter_turnout = ($total_votes / $total_voters) * 100;
} catch (PDOException $e) {
    die("Error fetching results: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">   
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
.results-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px;
    font-family: 'Arial', sans-serif;
}
.results-header {
    background: #2c3e50;
    color: white;
    padding: 30px;
    border-radius: 12px;
    margin-bottom: 30px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}
.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.chart-container {
    margin: 40px 0;
    background: white;
    padding: 30px;
    border-radius: 12px;
}
.results-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.results-table th, .results-table td {
    padding: 20px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}
.results-table th {
    background: #f8f9fa;
}
.export-options {
    margin: 20px 0;
    display: flex;
    gap: 10px;
}
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.btn-export {
    background: #27ae60;
    color: white;
}
.btn-settings {
    background: #2980b9;
    color: white;
}
.candidate-photo {
    width: 50px;
    height: auto;
    border-radius: 4px;
}
@media (min-width: 1200px) {
    .results-container {
        padding: 50px;
    }
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 40px;
    }
    .stat-card {
        padding: 35px;
    }
    .results-table th, 
    .results-table td {
        padding: 20px;
        font-size: 1.1em;
    }
}
@media (max-width: 992px) {
    .results-container {
        padding: 30px 20px;
    }
    .stats-grid {
        gap: 25px;
    }
    .chart-container {
        padding: 20px;
    }
}
@media (max-width: 768px) {
    .results-container {
        padding: 20px 15px;
        padding-top: 70px;
    }
    .results-header {
        padding: 20px;
        margin-bottom: 20px;
    }
    .stats-grid {
        gap: 15px;
        margin-bottom: 25px;
    }
    .stat-card {
        padding: 20px;
    }
    .chart-container {
        margin: 25px 0;
        padding: 15px;
    }
    .results-table th, 
    .results-table td {
        padding: 12px;
        font-size: 0.9em;
    }
    .candidate-photo {
        width: 40px !important;
    }
}
@media (max-width: 480px) {
    .results-container {
        padding: 15px 10px;
    }
    .stat-card h3 {
        font-size: 1.1em;
    }
    .stat-number {
        font-size: 1.3em;
    }
    .export-options {
        flex-wrap: wrap;
    }
    .btn {
        flex: 1 1 100%;
        margin-bottom: 8px;
    }
}
.chart-container canvas {
    max-width: 100%;
    height: auto !important;
    min-height: 300px;
}
.results-table {
    overflow-x: auto;
    display: block;
}
.dashboard-container {
    position: relative;
    min-height: 100vh;
    overflow-x: hidden;
}
.mobile-header {
    z-index: 1000;
    background: #2c3e50;
}
.sidebar {
    z-index: 999;
}
.sidebar.active {
    z-index: 1001;
    box-shadow: 2px 0 10px rgba(0,0,0,0.2);
}
@media (max-width: 768px) {
    html, body {
        overflow-x: hidden;
    }
}
</style>
</head>
<body>
        <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Results</h2>
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
    <div class="results-container">
        <div class="results-header">
            <h1>Election Results Dashboard</h1>
            <p>Last Updated: <?= date('Y-m-d H:i:s') ?></p>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Votes Cast</h3>
                <p class="stat-number"><?= number_format($total_votes) ?></p>
            </div>
            <div class="stat-card">
                <h3>Voter Turnout</h3>
                <p class="stat-number"><?= number_format($voter_turnout, 1) ?>%</p>
            </div>
            <div class="stat-card">
                <h3>Leading Candidate</h3>
                <p><?= $results[0]['name'] ?? 'N/A' ?></p>
                <small><?= number_format($results[0]['votes'] ?? 0) ?> votes</small>
            </div>
        </div>
        <div class="chart-container">
            <canvas id="resultsChart"></canvas>
        </div>
        
        <table class="results-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Party</th>
                    <th>Votes</th>
                    <th>Percentage</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $candidate): ?>
                <tr>
                    <td>
                        <img src="<?= $candidate['photo_url'] ?>" 
                             class="candidate-photo" 
                             alt="<?= htmlspecialchars($candidate['name']) ?>"
                             width="50">
                        <?= htmlspecialchars($candidate['name']) ?>
                    </td>
                    <td><?= htmlspecialchars($candidate['party']) ?></td>
                    <td><?= number_format($candidate['votes']) ?></td>
                    <td><?= number_format($candidate['percentage'], 1) ?>%</td>
                    
                        
                    
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        const ctx = document.getElementById('resultsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($results, 'name')) ?>,
                datasets: [{
                    label: 'Votes',
                    data: <?= json_encode(array_column($results, 'votes')) ?>,
                    backgroundColor: [
                        '#3498db',
                        '#2ecc71',
                        '#e74c3c',
                        '#f1c40f',
                        '#9b59b6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Vote Distribution by Candidate'
                    }
                }
            }
        });
        
    </script>
</body>
</html>