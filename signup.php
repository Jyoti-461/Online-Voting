<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
$errors = [];
$old_form = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $mobile = sanitize_input($_POST['mobile']);
    $gender = sanitize_input($_POST['gender']);
    $dob = $_POST['dob'];
    $nationality = sanitize_input($_POST['nationality']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ? OR mobile = ?");
    $stmt->execute([$username, $email, $mobile]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Username, email or mobile number already exists!";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long!";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter!";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter!";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number!";
    }
    if (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $password)) {
        $errors[] = "Password must contain at least one special character!";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match!";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    }
    if (!preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors[] = "Invalid Number Format";
    }
    $today = new DateTime();
    $birthdate = new DateTime($dob);
    $age = $today->diff($birthdate)->y;
    if ($age < 18) {
        $errors[] = "You must be at least 18 years old (Current age: $age)";
    }
    if (isset($_FILES['profile_photo'])) {
        $photo = $_FILES['profile_photo'];
        $allowed_types = ['image/jpeg', 'image/png'];
        $detected_type = mime_content_type($photo['tmp_name']);
        if (!in_array($detected_type, $allowed_types)) {
            $errors[] = "Only JPG/PNG images allowed!";
        }
    }
    if ($photo['size'] > 10097152) {
        $errors[] = "File too large! Max 10MB allowed";
    }
    if (empty($errors)) {
        unset($_SESSION['old_form']);
        try {
            $pdo->beginTransaction();
            $photo_path = '';
            if (isset($photo)) {
                $upload_dir = __DIR__ . '/uploads/profiles/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 755, true);
                $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
                $filename = uniqid('profile_') . '.' . $ext;
                move_uploaded_file($photo['tmp_name'], $upload_dir . $filename);
                $photo_path = 'uploads/profiles/' . $filename;
            }
            $stmt = $pdo->prepare("INSERT INTO users 
                (username, password, email, mobile, gender, dob, nationality, profile_photo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([
                $username,
                $password_hash,
                $email,
                $mobile,
                $gender,
                $dob,
                $nationality,
                $photo_path
            ]);
            $pdo->commit();
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: index.php");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
    else {
        $old_form = $_POST;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .registration-form {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            margin: 2rem auto;
            max-width: 700px;
            transition: all 0.3s ease;
        }
        .registration-form h2 {
            color: #1e3c72;
            font-weight: 600;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-label {
            font-weight: 500;
            color: #2a5298;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }
        .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
        }
        .btn-primary {
            background: #1e3c72;
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #2a5298;
            transform: translateY(-2px);
        }
        .password-rules {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid #e0e0e0;
        }
        .password-rules ul {
            margin-bottom: 0;
            padding-left: 1.25rem;
        }
        .alert-danger {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .text-muted {
            font-size: 0.85rem;
            color: #6c757d!important;
        }
        a {
            color: #1e3c72;
            font-weight: 500;
            text-decoration: none;
        }
        a:hover {
            color: #2a5298;
            text-decoration: underline;
        }
        .form-control[type="file"] {
            padding: 0.5rem;
            border: 2px dashed #e0e0e0;
        }
        .form-control[type="file"]:focus {
            border-color: #1e3c72;
        }
        .alert-danger div {
            padding: 0.25rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-form">
            <h2 class="text-center mb-4">Indian Voter Registration</h2>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div><?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control"
                            value="<?= htmlspecialchars($old_form['username'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
    value="<?= htmlspecialchars($old_form['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control" pattern="[6-9]{1}\d{9}"
                            value="<?= htmlspecialchars($old_form['mobile'] ?? '') ?>" required>
                        <small class="form-text text-muted">Indian mobile number (10 digits starting with 6-9)</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control"
                            value="<?= htmlspecialchars($old_form['dob'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select</option>
                            <option value="Male" <?= ($old_form['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($old_form['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                            <option value="Other" <?= ($old_form['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nationality</label>
                        <select name="nationality" class="form-select" required>
                            <option value="Indian">Indian</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" 
                        value="<?= htmlspecialchars($old_form['password'] ?? '') ?>" required>
                        <div class="password-rules">
                            Must contain:
                            <ul>
                                <li>Minimum 8 characters</li>
                                <li>At least 1 uppercase</li>
                                <li>At least 1 lowercase</li>
                                <li>At least 1 number</li>
                                <li>At least 1 special character</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" 
                        value="<?= htmlspecialchars($old_form['confirm_password'] ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Profile Photo</label>
                        <input type="file" name="profile_photo" class="form-control" accept="image/jpeg, image/png" required>
                        <small class="form-text text-muted">JPG or PNG only (max 10MB)</small>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </div>
                </div>
            </form>
            <div class="mt-3 text-center">
                Already have an account? <a href="index.php">Login here</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>