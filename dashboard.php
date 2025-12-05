<?php
session_start();
require_once 'includes/auth.php';
require_once __DIR__ . '/config/db.php';
if (!isset($_SESSION['is_admin_dba']) || !$_SESSION['is_admin_dba']) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $data['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'CSRF token mismatch.']);
        exit;
    }
    
    $password = $data['password'];
    $action = $data['action'] ?? 'reset';
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE is_admin_dba = 1 AND id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $pdo->beginTransaction();
    try {
        if ($action === 'reset') {
            // Original reset functionality
            $pdo->exec("UPDATE candidates SET votes = 0");
            $pdo->exec("UPDATE users SET has_voted = 0");
            $pdo->exec("DELETE FROM votes");
        } elseif ($action === 'save_data') {
            // Get current statistics
            $totalVotes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
            $totalCandidates = $pdo->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
            $totalVoters = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin_dba = 0")->fetchColumn();
            
            // Save snapshot data without resetting
            $snapshotData = [
                'candidates' => $pdo->query("SELECT * FROM candidates")->fetchAll(PDO::FETCH_ASSOC),
                'votes' => $pdo->query("SELECT * FROM votes")->fetchAll(PDO::FETCH_ASSOC),
                'voters' => $pdo->query("SELECT id, username, email, has_voted FROM users")->fetchAll(PDO::FETCH_ASSOC),
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $stmt = $pdo->prepare("INSERT INTO elections (total_votes, total_candidates, total_voters, snapshot_data) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $totalVotes,
                $totalCandidates,
                $totalVoters,
                json_encode($snapshotData)
            ]);
            
            // Removed the reset commands here to only save data
        }
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Operation failed: ' . $e->getMessage()]);
    }
    exit; 
}

// Get statistics for display
$totalVotes = 0;
$totalCandidates = 0;
$totalVoters = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM votes");
    $totalVotes = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM candidates");
    $totalCandidates = $stmt->fetchColumn();
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin_dba = 0");
    $totalVoters = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .danger-button {
            background-color: #dc3545; 
            color: white; 
            border: none; 
            padding: 15px 30px; 
            font-size: 20px; 
            font-weight: bold; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: background-color 0.3s; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); 
            margin-top: 20px;
        }
        .danger-button:hover {
            background-color: #c82333; 
        }
        .success-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
            margin-left: 20px;
        }
        .success-button:hover {
            background-color: #218838;
        }
        .warning-text {
            margin-top: 20px; 
            color: #dc3545; 
            font-size: 18px; 
            text-align: center; 
        }
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Admin Dashboard</h2>
    </div>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Voting System</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link ">
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
                    <a href="manage_candidates.php" class="nav-link">
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
                    <a href="manage_results.php" class="nav-link">
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
                if (link.href === window.location.href) {
                    link.classList.add('active');
                }
            });
        </script>
        <main class="main-content">
            <h1>Dashboard Overview</h1>
            <div class="cards-container">
                <div class="card">
                    <h3>Total Votes</h3>
                    <div class="stat-number"><?= htmlspecialchars(number_format($totalVotes)) ?></div>
                    <p>Votes cast so far</p>
                </div>
                <div class="card">
                    <h3>Candidates</h3>
                    <div class="stat-number"><?= htmlspecialchars(number_format($totalCandidates)) ?></div>
                    <p>Registered candidates</p>
                </div>
                <div class="card">
                    <h3>Voters</h3>
                    <div class="stat-number"><?= htmlspecialchars(number_format($totalVoters)) ?></div>
                    <p>Registered voters</p>
                </div>
            </div>
            <div class="text-center">
                <div class="button-group">
                    <button class="danger-button" id="resetVotesButton">Reset Votes</button>
                    <button class="success-button" id="saveDataBtn">Save Election Data</button>
                </div>
                <div class="warning-text">Warning: These actions cannot be undone!</div>
            </div>

            <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="passwordModalLabel">Confirm Action</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>Please enter your password to confirm:</p>
                            <input type="password" id="adminPassword" class="form-control" placeholder="Password">
                            <input type="hidden" id="actionType" value="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmAction">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Store the original confirm function
                let currentAction = '';
                
                // Reset Votes button
                document.getElementById('resetVotesButton').addEventListener('click', function() {
                    currentAction = 'reset';
                    var myModal = new bootstrap.Modal(document.getElementById('passwordModal'));
                    document.getElementById('passwordModalLabel').textContent = 'Confirm Reset Votes';
                    document.querySelector('.modal-body p').textContent = 'This will reset all votes and voting statuses. Please enter your password to confirm:';
                    myModal.show();
                });
                
                // Save Data button
                document.getElementById('saveDataBtn').addEventListener('click', function() {
                    currentAction = 'save_data';
                    var myModal = new bootstrap.Modal(document.getElementById('passwordModal'));
                    document.getElementById('passwordModalLabel').textContent = 'Confirm Save Election Data';
                    document.querySelector('.modal-body p').textContent = 'This will save a snapshot of current election data. Please enter your password to confirm:';
                    myModal.show();
                });
                
                // Confirm action handler
                document.getElementById('confirmAction').addEventListener('click', function() {
                    var password = document.getElementById('adminPassword').value;
                    var action = currentAction;
                    
                    fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            password: password,
                            csrf_token: '<?= $_SESSION['csrf_token'] ?>',
                            action: action
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (action === 'reset') {
                                alert('Votes have been reset successfully.');
                            } else {
                                alert('Election data saved successfully.');
                            }
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Operation failed'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred. Please check console for details.');
                    });
                });
            </script>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>