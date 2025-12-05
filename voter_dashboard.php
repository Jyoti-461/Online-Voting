<?php 
session_start();
require_once 'includes/auth.php';
require_once __DIR__ . '/config/db.php';

// Initialize variables
$totalVotes = 0;
$totalCandidates = 0;
$totalVoters = 0;
$error = '';
$success = '';
$user = [];

// Memoization function for user data
function getUserData($pdo, $userId) {
    static $userCache = null;
    static $lastFetchTime = 0;
    $cacheDuration = 300; // 5 minutes cache
    
    if ($userCache === null || (time() - $lastFetchTime) > $cacheDuration || $userCache['id'] != $userId) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_admin_dba = 0");
        $stmt->execute([$userId]);
        $userCache = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $lastFetchTime = time();
    }
    
    return $userCache;
}

// Memoization function for statistics
function getDashboardStats($pdo) {
    static $statsCache = null;
    static $statsFetchTime = 0;
    $cacheDuration = 300; // 5 minutes cache
    
    if ($statsCache === null || (time() - $statsFetchTime) > $cacheDuration) {
        try {
            $statsCache = [
                'votes' => $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn(),
                'candidates' => $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn(),
                'voters' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin_dba = 0")->fetchColumn()
            ];
            $statsFetchTime = time();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $statsCache = ['votes' => 0, 'candidates' => 0, 'voters' => 0];
        }
    }
    
    return $statsCache;
}

// Get data with memoization
try {
    $user = getUserData($pdo, $_SESSION['user_id'] ?? 0);
    if (empty($user)) {
        throw new Exception("User not found or unauthorized!");
    }
    $user['has_voted'] = $user['has_voted'] ?? 0;
    
    $stats = getDashboardStats($pdo);
    $totalVotes = $stats['votes'];
    $totalCandidates = $stats['candidates'];
    $totalVoters = $stats['voters'];
} catch (Exception $e) {
    $error = $e->getMessage();
}

$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .voting-status {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 1200px;
            padding: 1.5rem;
            z-index: 1000;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .vote-button {
            position: relative;
            padding: 1.2rem 2.5rem;
            font-size: 1.25rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #28a745 0%, #218838 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            color: white;
            text-decoration: none;
        }
        .vote-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
        }
        .vote-button i {
            font-size: 1.4rem;
        }
        .status-text {
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .card {
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        @media (max-width: 768px) {
            .voting-status {
                padding: 1rem;
            }
            .vote-button {
                padding: 1rem 1.8rem;
                font-size: 1.1rem;
            }
        }
        .error-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            max-width: 400px;
        }
       
/* Report Button Container */
.report-button-container {
    position: fixed;
    bottom: 20px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    z-index: 1000;
    padding: 15px; 
    margin-bottom: 150px;
}

/* Report Button */
#generateReportBtn {
    background-color: #e74c3c;
    color: white;
    border: none;
    padding: 12px 30px;
    font-size: 16px;
    border-radius: 50px;
    transition: all 0.3s;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 600;
}

#generateReportBtn:hover {
    background-color: #c0392b;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
}

#generateReportBtn:disabled {
    background-color: #95a5a6;
    cursor: not-allowed;
    transform: none !important;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .report-button-container {
        padding: 10px;
    }
    #generateReportBtn {
        padding: 10px 20px;
        font-size: 14px;
    }
}
    </style>
</head>
<body>
    <?php if ($error): ?>
        <div class="error-alert alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Voter Dashboard</h2>
    </div>
    
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Voting System</h2>
                <p>Voter Panel</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="voter_dashboard.php" class="nav-link active">
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
                        <i class="fas fa-comment"></i>
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

        <main class="main-content">
            <h1>Welcome, <?= htmlspecialchars($user['username'] ?? 'Voter') ?></h1>
            <div class="cards-container">
                <div class="card">
                    <div class="card-body">
                        <h3>Total Votes</h3>
                        <div class="stat-number"><?= htmlspecialchars(number_format($totalVotes)) ?></div>
                        <p>Votes cast so far</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h3>Candidates</h3>
                        <div class="stat-number"><?= htmlspecialchars(number_format($totalCandidates)) ?></div>
                        <p>Registered candidates</p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h3>Voters</h3>
                        <div class="stat-number"><?= htmlspecialchars(number_format($totalVoters)) ?></div>
                        <p>Registered voters</p>
                    </div>
                </div>
            </div>
        </main>
        <!-- Add this at the bottom of your page, just before </body> -->
<div class="report-button-container">
    <button id="generateReportBtn">
        <i class="fas fa-file-pdf"></i> Generate Report
    </button>
</div>

        <div class="voting-status">
            <h3 class="status-text mb-3">
                Voting Status: 
                <span class="<?= $user['has_voted'] ? 'text-danger' : 'text-success' ?>">
                    <?= $user['has_voted'] ? 'Already Voted' : 'Eligible to Vote' ?>
                </span>
            </h3>
            <?php if (!$user['has_voted']): ?>
                <a href="voter_votes.php" class="vote-button">
                    <i class="fa-solid fa-check-to-slot"></i>
                    Cast Your Vote Now
                </a>
            <?php else: ?>
                <div class="text-muted">
                    <i class="fa-solid fa-check-circle"></i>
                    Thank you for voting!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.menu-toggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.sidebar');
                if (!sidebar.contains(e.target) && !e.target.closest('.menu-toggle')) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Auto-refresh every 5 minutes (300000ms)
        setTimeout(() => {
            window.location.reload();
        }, 300000);

        // Highlight active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            if(link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>
    <script>
document.getElementById('generateReportBtn').addEventListener('click', function() {
    const btn = this;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    btn.classList.add('loading');
    btn.disabled = true;
    
    // Create a hidden form for submission
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'generate_report.php';
    form.target = '_blank';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?= $_SESSION['csrf_token'] ?>';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
    
    // Clean up after delay
    setTimeout(() => {
        document.body.removeChild(form);
        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate Report';
        btn.classList.remove('loading');
        btn.disabled = false;
    }, 3000);
});
</script>
</body>
</html>