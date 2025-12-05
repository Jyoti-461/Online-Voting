<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$error = '';
$success = '';
$user = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception("User not found!");
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Security violation detected!");
        }
        if (isset($_POST['update_profile'])) {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $mobile = filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_STRING);
            if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
                throw new Exception("Invalid Indian mobile number!");
            }
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                throw new Exception("Email already registered!");
            }
            $stmt = $pdo->prepare("UPDATE users SET email = ?, mobile = ? WHERE id = ?");
            $stmt->execute([$email, $mobile, $_SESSION['user_id']]);
            $success = "Profile updated successfully!";
            $user['email'] = $email;
            $user['mobile'] = $mobile;
        }
        if (isset($_POST['change_password'])) {
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Current password is incorrect!");
            }            
            if (strlen($new_password) < 8 || 
                !preg_match('/[A-Z]/', $new_password) ||
                !preg_match('/[a-z]/', $new_password) ||
                !preg_match('/[0-9]/', $new_password) ||
                !preg_match('/[\^£$%&*()}{@#~?><>,|=_+¬-]/', $new_password)) {
                throw new Exception("New password does not meet requirements!");
            }           
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);           
            $success = "Password changed successfully!";
        }
        if (isset($_POST['update_photo'])) {
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                $photo_path = handle_file_upload($_FILES['profile_photo']);               
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                $stmt->execute([$photo_path, $_SESSION['user_id']]);
                $user['profile_photo'] = $photo_path;
                $success = "Profile photo updated successfully!";
            } else {
                throw new Exception("Please select a photo to upload.");
            }
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
function handle_file_upload($file) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            throw new Exception("No file was uploaded.");
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload error: " . $file['error']);
        }
        if (!file_exists($file['tmp_name'])) {
            throw new Exception("Temporary file missing.");
        }
        $target_dir = "uploads/profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
    $target_dir = "uploads/profiles/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_name = basename($file["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
    }
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        throw new Exception("Failed to upload image.");
    }
    return basename($target_file);
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
    <title>Voter Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .profile-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .profile-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
        }
        .alert {
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding-top: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Request Edit</h2>
    </div>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2>Voting System</h2>
                <p>Voter Panel</p>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="voter_dashboard.php" class="nav-link">
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
        <main class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            <div class="profile-section">
                <div class="row">
                    <div class="col-md-4 text-center">
<img src="<?php
    $photo_path = 'assets/default-profile.jpg';
    if (!empty($user['profile_photo'])) {
        $raw_path = explode(',', $user['profile_photo'])[0]; // Take first filename if multiple
        $clean_path = basename(trim($raw_path)); // Extract filename only
        $target_file = 'uploads/profiles/' . $clean_path;
        if (file_exists($target_file)) {
            $photo_path = $target_file;
        }
    }
    echo $photo_path; 
?>" 
class="profile-photo mb-3" 
alt="Profile Photo"
onerror="this.onerror=null;this.src='assets/default-profile.jpg';">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <div class="mb-3">
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" name="update_photo" class="btn btn-primary">
                                Update Photo
                            </button>
                        </form>
                    </div>
                    <div class="col-md-8">
                        <h2>Profile Information</h2>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" 
                                           value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" 
                                           value="<?= htmlspecialchars($user['dob']) ?>" disabled>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= htmlspecialchars($user['email']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="tel" name="mobile" class="form-control" 
                                           value="<?= htmlspecialchars($user['mobile']) ?>">
                                </div>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                Update Profile
                            </button>
                        </form>
                        <hr class="my-5">
                        <h3>Change Password</h3>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" name="change_password" class="btn btn-warning w-100">
                                        Change Password
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
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
            if(link.href === window.location.href) {
                link.classList.add('active');
            }
        });
    </script>
</body>
</html>