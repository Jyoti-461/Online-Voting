<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
if (!isset($_SESSION['is_admin_dba']) || !$_SESSION['is_admin_dba']) {
    header("Location: index.php");
    exit();
}
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
$candidates = [];
$edit_candidate = null;
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
try {
    $pdo = db_connect();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security violation detected!");
        }
        if (isset($_POST['add_candidate'])) {
            try {
                $photo_path = handle_file_upload($_FILES['photo']);
                $pdo->beginTransaction();
                $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
                $party = filter_input(INPUT_POST, 'party', FILTER_SANITIZE_STRING);
                $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
                $stmt = $pdo->prepare("INSERT INTO candidates 
                                      (name, party, bio, photo_url) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $party, $bio, $photo_path]);
                $pdo->commit();
                $_SESSION['success'] = "Candidate added successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Add failed: " . $e->getMessage();
            }
        }
        if (isset($_POST['edit_candidate'])) {
            try {
                $candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
                $current_photo = filter_input(INPUT_POST, 'current_photo', FILTER_SANITIZE_STRING);
                $photo_url = $current_photo;
                if (!empty($_FILES['photo']['name'])) {
                    $photo_url = handle_file_upload($_FILES['photo']);
                }
                $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
                $party = filter_input(INPUT_POST, 'party', FILTER_SANITIZE_STRING);
                $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("UPDATE candidates 
                                      SET name = ?, party = ?, bio = ?, photo_url = ?
                                      WHERE id = ?");
                $stmt->execute([$name, $party, $bio, $photo_url, $candidate_id]);
                $pdo->commit();
                $_SESSION['success'] = "Candidate updated successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Edit failed: " . $e->getMessage();
            }
        }
        if (isset($_POST['delete'])) {
            try {
                $candidate_id = filter_input(INPUT_POST, 'candidate_id', FILTER_VALIDATE_INT);
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("DELETE FROM candidates WHERE id = ?");
                $stmt->execute([$candidate_id]);
                $pdo->commit();
                $_SESSION['success'] = "Candidate deleted successfully!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Delete failed: " . $e->getMessage();
            }
        }
        header("Location: manage_candidates.php");
        exit();
    }
    if (isset($_GET['edit'])) {
        $candidate_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
        if ($candidate_id) {
            $stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
            $stmt->execute([$candidate_id]);
            $edit_candidate = $stmt->fetch();
        }
    }
    $stmt = $pdo->query("SELECT * FROM candidates ORDER BY created_at DESC");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: manage_candidates.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .candidate-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem;
            perspective: 1000px;
        }
        .candidate-card {
            background: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            transform-style: preserve-3d;
            position: relative;
            cursor: pointer;
        }
        .candidate-card:hover {
            transform: translateY(-10px) rotateX(5deg) rotateY(5deg) scale(1.03);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2),
                0 10px 10px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }
        .edit-modal-img {
    max-width: 100%;
    max-height: 200px;
    width: auto;
    height: auto;
    object-fit: cover;
    border-radius: 8px;
    margin: 10px 0;
    display: block;
}
        .candidate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .candidate-card:hover::before {
            opacity: 1;
        }
        .candidate-photo {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
            transition: transform 0.3s ease;
        }
        .candidate-card:hover .candidate-photo {
            transform: scale(1.05);
        }
        .card-body {
            padding: 1.5rem;
            background: linear-gradient(to bottom, #ffffff, #f8f9fa);
            border-radius: 0 0 15px 15px;
            position: relative;
            z-index: 1;
        }
        .card-title {
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: #2c3e50;
        }
        .card-text {
            font-size: 0.9rem;
            color: #6c757d;
            line-height: 1.5;
        }
        .card-text strong {
            color: #34495e;
        }
        @media (max-width: 768px) {
            .candidate-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1rem;
            }
            .candidate-card {
                margin-bottom: 0;
            }
            .candidate-photo {
                height: 200px;
            }
        }
        @media (max-width: 576px) {
            .card-body {
                padding: 1rem;
            }
            .card-title {
                font-size: 1.1rem;
            }
            .card-text {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Candidates</h2>
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
        </script>
        <main class="main-content">
            <div class="container-fluid">
                <h2 class="my-4">Manage Candidates</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Add New Candidate</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Political Party</label>
                                <input type="text" name="party" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Biography</label>
                                <textarea name="bio" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="photo" class="form-control" accept="image/jpeg, image/png">
                            </div>
                            <button type="submit" name="add_candidate" class="btn btn-primary">
                                Add Candidate
                            </button>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="editModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Candidate</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="candidate_id" value="<?= $edit_candidate['id'] ?? '' ?>">
                                    <input type="hidden" name="current_photo" value="<?= $edit_candidate['photo_url'] ?? '' ?>">
                                    <div class="mb-3">
                                        <label>Full Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="<?= htmlspecialchars($edit_candidate['name'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Political Party</label>
                                        <input type="text" name="party" class="form-control"
                                            value="<?= htmlspecialchars($edit_candidate['party'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Biography</label>
                                        <textarea name="bio" class="form-control" rows="3"><?=
                                            htmlspecialchars($edit_candidate['bio'] ?? '') ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label>Profile Photo</label>
                                        <?php if ($edit_candidate['photo_url'] ?? ''): ?>
                                            <img src="<?= htmlspecialchars($edit_candidate['photo_url']) ?>"
                                                class="edit-modal-img mb-2">
                                        <?php endif; ?>
                                        <input type="file" name="photo" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="edit_candidate" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="candidate-grid">
                    <?php foreach ($candidates as $candidate): ?>
                        <div class="candidate-card">
                            <?php if ($candidate['photo_url']): ?>
                                <img src="<?= htmlspecialchars($candidate['photo_url']) ?>"
                                    class="candidate-photo"
                                    alt="<?= htmlspecialchars($candidate['name']) ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($candidate['name']) ?></h5>
                                <p class="card-text">
                                    <strong>Party:</strong> <?= htmlspecialchars($candidate['party']) ?><br>
                                    <strong>Bio:</strong> <?= htmlspecialchars($candidate['bio']) ?><br>
                                    <strong>Registered:</strong> <?= date('d M Y', strtotime($candidate['created_at'])) ?>
                                </p>
                                <div class="d-flex gap-2 mt-3">
                                    <a href="?edit=<?= $candidate['id'] ?>" class="btn btn-warning btn-sm">
                                        Edit
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this candidate?')">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="candidate_id" value="<?= $candidate['id'] ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        <?php if (isset($_GET['edit'])): ?>
                            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
                            editModal.show();
                        <?php endif; ?>
                    });
                </script>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>