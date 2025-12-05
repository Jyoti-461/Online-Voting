<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['is_admin_dba']) || !$_SESSION['is_admin_dba']) {
    header("Location: index.php");
    exit();
}
$error = '';
$success = '';
$voters = [];
try {
    $pdo = db_connect();
    $stmt = $pdo->query("
        SELECT id, username, email, mobile, gender, dob, nationality, profile_photo, created_at 
        FROM users 
        WHERE is_admin_dba = 0
        ORDER BY created_at DESC
    ");
    $voters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security violation detected!");
        }
        if (isset($_POST['update_voter'])) {
            $voter_id = filter_input(INPUT_POST, 'voter_id', FILTER_VALIDATE_INT);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_STRING);
            $nationality = filter_input(INPUT_POST, 'nationality', FILTER_SANITIZE_STRING);
            $dob = $_POST['dob'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format!");
            }
            if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
                throw new Exception("Invalid Indian mobile number!");
            }
            $stmt = $pdo->prepare("
                SELECT id FROM users 
                WHERE (email = ? OR mobile = ?) 
                AND id != ? 
                AND is_admin_dba = 0
            ");
            $stmt->execute([$email, $mobile, $voter_id]);
            if ($stmt->fetch()) {
                throw new Exception("Email or mobile number already exists!");
            }
            $stmt = $pdo->prepare("
                UPDATE users SET 
                email = ?, 
                mobile = ?, 
                nationality = ?, 
                dob = ? 
                WHERE id = ?
            ");
            $stmt->execute([$email, $mobile, $nationality, $dob, $voter_id]);
            $success = "Voter details updated successfully!";
        }
        if (isset($_POST['delete_voter'])) {
            $voter_id = filter_input(INPUT_POST, 'voter_id', FILTER_VALIDATE_INT);
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$voter_id]);
            $pdo->commit();
            $success = "Voter deleted successfully!";
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Voters</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
.voter-grid {
  display: -webkit-box;
  display: -ms-flexbox;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  padding: 1rem;
  width: 100%;
  -webkit-box-sizing: border-box;
          box-sizing: border-box;
  margin: 0 auto;
}
.voter-card {
  background: #ffffff;
  border-radius: 10px;
  -webkit-box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
          box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  min-width: 280px;
  -webkit-transition: all 0.3s ease;
  transition: all 0.3s ease;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
      -ms-flex-direction: column;
          flex-direction: column;
  break-inside: avoid;
}
.voter-card:hover {
  -webkit-transform: translateY(-5px);
          transform: translateY(-5px);
  -webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
          box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}
.voter-card .card-body {
  -webkit-box-flex: 1;
      -ms-flex: 1;
          flex: 1;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-orient: vertical;
  -webkit-box-direction: normal;
      -ms-flex-direction: column;
          flex-direction: column;
}
.voter-card .profile-photo {
  width: 150px;
  height: 150px;
  -o-object-fit: cover;
     object-fit: cover;
  border-radius: 50%;
  margin: 0 auto 1rem;
  border: 3px solid #4a90e2;
}
.voter-card .action-buttons {
  margin-top: auto;
  padding-top: 1.5rem;
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  gap: 0.8rem;
  -webkit-box-pack: end;
      -ms-flex-pack: end;
          justify-content: flex-end;
}
@media (min-width: 1400px) {
  .voter-grid {
    grid-template-columns: repeat(4, 1fr);
    max-width: 1600px;
  }
}
@media (min-width: 1200px) and (max-width: 1399px) {
  .voter-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (min-width: 992px) and (max-width: 1199px) {
  .voter-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (min-width: 768px) and (max-width: 991px) {
  .voter-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 767px) {
  .voter-grid {
    grid-template-columns: 1fr;
  }
  .voter-card {
    min-width: auto;
    width: 100%;
  }
}
@media (max-width: 480px) {
  .voter-card {
    padding: 1rem;
  }
  .profile-photo {
    width: 100px;
    height: 100px;
  }
  .action-buttons {
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
        -ms-flex-direction: column;
            flex-direction: column;
  }
}
    </style>
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Voters</h2>
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
                    <a href="manage_voters.php" class="nav-link ">
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
        <div class="container-fluid">
            <h2 class="my-4">Manage Voters</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <div class="voter-grid">
    <?php foreach ($voters as $voter): ?>
    <div class="voter-card">
        <div class="card-body">
            <div class="profile-section">
                <?php
                $profile_path = '';
                if (!empty($voter['profile_photo'])) {
                    $raw_path = trim(explode(',', $voter['profile_photo'])[0]); // Take first filename if multiple
                    $fixed_path = str_replace('uploadsprofiles', 'uploads/profiles', $raw_path);
                    if (strpos($fixed_path, 'uploads/profiles/') === 0) {
                        $profile_path = $fixed_path;
                    } else {
                        $profile_path = 'uploads/profiles/' . ltrim($fixed_path, '/');
                    }
                }
                ?>
                <img src="<?= file_exists($profile_path) ? $profile_path : 'assets/default-profile.jpg' ?>" 
                     class="profile-photo" 
                     alt="Profile Photo"
                     onerror="this.onerror=null;this.src='assets/default-profile.jpg';">
            </div>
            <div class="voter-details">
                <h5 class="card-title"><?= htmlspecialchars($voter['username']) ?></h5>
                <p class="card-text text-muted">
                    <i class="bi bi-envelope"></i> <?= htmlspecialchars($voter['email']) ?><br>
                    <i class="bi bi-phone"></i> <?= htmlspecialchars($voter['mobile']) ?><br>
                    <i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($voter['dob'])) ?>
                </p>
            </div>
            <div class="action-buttons">
                <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal"
                        data-voter='<?= json_encode($voter) ?>'>
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <form method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this voter?')">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="voter_id" value="<?= $voter['id'] ?>">
                    <button type="submit" name="delete_voter" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
            <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Voter Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="voter_id" id="editVoterId">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="editEmail" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="tel" name="mobile" id="editMobile" class="form-control" required
                                        pattern="[6-9]{1}\d{9}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="dob" id="editDob" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nationality</label>
                                    <select name="nationality" id="editNationality" class="form-select" required>
                                        <option value="Indian">Indian</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="update_voter" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                const editModal = document.getElementById('editModal');
                editModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const voterData = JSON.parse(button.dataset.voter);
                    document.getElementById('editVoterId').value = voterData.id;
                    document.getElementById('editEmail').value = voterData.email;
                    document.getElementById('editMobile').value = voterData.mobile;
                    document.getElementById('editDob').value = voterData.dob;
                    document.getElementById('editNationality').value = voterData.nationality;
                });
            </script>
</body>
</html>