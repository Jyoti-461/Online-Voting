<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$success = '';
$errors = [];
$oldInput = [];
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}
if (isset($_SESSION['old_input'])) {
    $oldInput = $_SESSION['old_input'];
    unset($_SESSION['old_input']);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oldInput = $_POST;
    $errors = [];
    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    $gmail = trim($_POST['gmail'] ?? '');
    if (!filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    $mobile = trim($_POST['mobile'] ?? '');
    if (!preg_match('/^(\+91|0)?[6-9]\d{9}$/', $mobile)) {
        $errors[] = "Invalid Indian mobile number format";
    }
    $description = trim($_POST['description'] ?? '');
    if (empty($description)) {
        $errors[] = "Description is required";
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $oldInput;
        header("Location: voter_contact.php");
        exit();
    }
    try {
        $pdo = db_connect();
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO contacts 
            (name, gmail, mobile, description)
            VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $name,
            $gmail,
            $mobile,
            $description
        ]);
        $pdo->commit();
        $_SESSION['success'] = "Complaint Submitted Successfully!";
        header("Location: voter_contact.php");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['errors'] = ["Submission failed: " . $e->getMessage()];
        $_SESSION['old_input'] = $oldInput;
        header("Location: voter_contact.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body {
            margin: 0;
            background-color: #f8f9fa;
            padding-bottom: 100px;
            min-height: 100vh;
            position: relative;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .contact-form {
            max-width: 800px;
            width: 90%;
            margin: 2rem auto;
            padding: 2.5rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            min-height: 600px;
        }
        .contact-form h2 {
            margin-bottom: 1.5rem;
            color: #2d3436;
            text-align: center;
            font-size: 2rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #007bff;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        h5 {
            color: aliceblue;
        }
        input,
        textarea {
            width: 100%;
            padding: 0.6rem;
            border: 2px solid #e9ecef;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
            background: #f8f9fa;
            color: #2d3436;
        }
        input:focus,
        textarea:focus {
            background: #ffffff;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            outline: none;
        }
        input {
            height: auto;
            min-height: 40px;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
            padding: 0.8rem;
        }
        button {
            background: linear-gradient(to right, #007bff, #0069d9);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            width: 100%;
            margin-top: 1rem;
        }
        button:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
        }
        .social-links {
            background: #1a1a1a;
            padding: 2.1rem;
            text-align: center;
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #000;
            z-index: 1000;
        }
        .social-links a {
            color: white;
            margin: 0 15px;
            font-size: 28px;
            transition: color 0.3s ease;
            text-decoration: none;
        }
        .social-links a:hover {
            color: #007bff;
        }
        @media (max-width: 768px) {
            .contact-form {
                padding: 1.5rem;
            }
            .social-links {
                padding: 1.5rem;
                margin-top: 2rem;
            }
            .social-links a {
                font-size: 24px;
                margin: 0 10px;
            }
            input,
            textarea {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
            textarea {
                min-height: 100px;
            }
            button {
                padding: 0.7rem 1.2rem;
                font-size: 1.2rem;
            }
            @media (max-width: 480px) {
                .contact-form {
                    padding: 1.5rem;
                    min-height: auto;
                }
                .social-links a {
                    font-size: 20px;
                    margin: 0 8px;
                }
            }
        }
    </style> 
</head>
<body>
    <div class="mobile-header">
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <h2>Contact Us</h2>
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
                        <i class="fa-solid fa-phone-volume"></i>
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
        <div class="contact-form">
            <?php if ($success): ?>
                <div class="alert success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <h2>Contact Us</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" name="name" required
                           value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Gmail:</label>
                    <input type="email" name="gmail" required
                           value="<?= htmlspecialchars($oldInput['gmail'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Mobile Number:</label>
                    <input type="tel" name="mobile" required
                           value="<?= htmlspecialchars($oldInput['mobile'] ?? '') ?>"
                           placeholder="+91 or 0 prefix optional">
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" rows="5" required
                    ><?= htmlspecialchars($oldInput['description'] ?? '') ?></textarea>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
        <div class="social-links">
        <a href="[your-instagram-link]" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="[your-linkedin-link]" target="_blank"><i class="fab fa-linkedin"></i></a>
            <a href="mailto:[jyotijayantofficical@gmail.com]" target="_blank"><i class="fas fa-envelope"></i></a>
            <h5>Managed by Election Comission of India&nbsp;</h5>
        </div>
    </div>
</body>
</html>