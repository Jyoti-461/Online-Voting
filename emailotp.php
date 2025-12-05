<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once 'includes/functions.php';
$error = '';
$success = '';
$email_exists = true;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reset_password'])) {
        try {
            $pdo->beginTransaction();
            $email = $_POST['email'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            if (!isset($_SESSION['otp_time']) || (time() - $_SESSION['otp_time']) > 600) {
                throw new Exception("OTP has expired. Please request a new one.");
            }
            if ($new_password !== $confirm_password) {
                throw new Exception("Confirm password does not match!");
            }
            if (strlen($new_password) < 8) {
                throw new Exception("Password must be at least 8 characters long!");
            }
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $email]);
            if ($stmt->rowCount() === 0) {
                throw new Exception("No user found with this email address");
            }
            $pdo->commit();
            $success = "Password reset successfully!";
            unset($_SESSION['otp_time']);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['check_email'])) {
        try {
            $email = $_POST['email'];
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $email_exists = $stmt->fetchColumn() > 0;
            header('Content-Type: application/json');
            echo json_encode(['exists' => $email_exists]);
            exit;
        } catch(PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database error']);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }
        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }
        h2 {
            color: #34495e;
            margin-bottom: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #2c3e50;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 93%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus {
            border-color: #3498db;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #2980b9;
        }
        .alert {
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .error {
            background: #ffebee;
            color: #c62828;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .otpverify, .password-reset {
            display: none;
            margin-top: 1.5rem;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        #timer {
            color: #e74c3c;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        #resend-otp-btn {
            opacity: 0.5;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        #resend-otp-btn.active {
            opacity: 1;
            pointer-events: all;
        }
        .password-strength {
            font-size: 0.8em;
            margin-top: 5px;
            display: none;
            color: #e74c3c;
        }
        .links {
            color: #fff;
            text-align: center;
            margin-top: 1rem;
        }
        .links a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        } 
        .links a:hover {
            color: #fff;
        } 
    </style>
    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
    <script>
        emailjs.init("SI-bIxou0gEgCKqN2");       
        let otp_val;
        let otpDisplayTimeout;
        let countdownInterval;
        function startTimer(duration) {
    const timerDisplay = document.getElementById('timer');
    const resendBtn = document.getElementById('resend-otp-btn');
    let timeLeft = duration;
    countdownInterval = setInterval(() => {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerDisplay.textContent = `OTP valid for: ${minutes}:${seconds.toString().padStart(2, '0')}`;
        if (timeLeft === 540) { 
            resendBtn.classList.add('active');
            resendBtn.innerHTML = 'Resend OTP';
            resendBtn.style.pointerEvents = 'all';
        }
        if (--timeLeft < 0) {
            clearInterval(countdownInterval);
            timerDisplay.textContent = "OTP expired!";
            document.querySelector('.otpverify').style.display = 'none';
        }
    }, 1000);
}
        function sendOTP(isResend = false) {
            if(isResend) {
                document.getElementById('resend-otp-btn').classList.remove('active');
                updateResendButton(60);
            }
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;           
            if (!emailRegex.test(email)) {
                showError("Please enter a valid email address!");
                return;
            }
            const formData = new FormData();
            formData.append('check_email', true);
            formData.append('email', email);
            fetch('emailotp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showError(data.error);
                    return;
                }               
                if (!data.exists) {
                    showError('Email does not exist! Contact admin.');
                    return;
                }                
                otp_val = Math.floor(100000 + Math.random() * 900000); 
                console.log("Generated OTP:", otp_val);
                emailjs.send("service_pwubrer", "template_19q0pmk", {
                    to_email: email,
                    message: otp_val
                }).then(
                    response => {
                        document.getElementById('send-otp-btn').style.display = 'none';
                        document.getElementById('resend-otp-btn').style.display = 'inline-block';
                        document.querySelector('.otpverify').style.display = "block";
                        document.getElementById('email-error').textContent = '';
                        document.getElementById('success-message').textContent = 'OTP sent successfully! Check your email.';
                        document.getElementById('success-message').style.display = 'block';
                        startTimer(600);                        
                        fetch('store_otp_time.php', { method: 'POST' });
                    },
                    error => {
                        console.error("EmailJS Error:", error);
                        const otpDisplay = document.getElementById('otp-display');
                        otpDisplay.textContent = `OTP: ${otp_val}`;
                        otpDisplay.style.display = 'block';
                        otpDisplayTimeout = setTimeout(() => {
                            otpDisplay.style.display = 'none';
                        }, 10000);
                        showError("Error sending OTP. Displaying OTP for 10 seconds.");
                    }
                );
            });
        }
        function updateResendButton(seconds) {
            const resendBtn = document.getElementById('resend-otp-btn');
            if(seconds > 0) {
                resendBtn.innerHTML = `Resend OTP (${seconds}s)`;
                setTimeout(() => updateResendButton(seconds - 1), 1000);
            } else {
                resendBtn.classList.add('active');
                resendBtn.innerHTML = 'Resend OTP';
            }
        }
        function showError(message) {
            const errorDiv = document.getElementById('email-error');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => errorDiv.style.display = 'none', 5000);
        }
        function validatePassword() {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            const strengthMsg = document.getElementById('password-strength-msg');           
            if(newPassword.length < 8) {
                strengthMsg.textContent = "Password must be at least 8 characters!";
                strengthMsg.style.display = 'block';
                return false;
            }           
            if(newPassword !== confirmPassword) {
                showError("Confirm password does not match!");
                return false;
            }
            strengthMsg.style.display = 'none';
            return true;
        }       
        document.querySelector('input[name="new_password"]').addEventListener('input', function(e) {
            const strengthMsg = document.getElementById('password-strength-msg');
            if(e.target.value.length > 0) {
                strengthMsg.style.display = 'block';
                if(e.target.value.length < 8) {
                    strengthMsg.textContent = "Password must be at least 8 characters!";
                } else {
                    strengthMsg.textContent = "Password strength: Good";
                }
            } else {
                strengthMsg.style.display = 'none';
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Online Voting System</h1>
        <div class="login-box">
            <h2>Password Reset</h2>
            <div id="success-message" class="success" style="display: none;"></div>
            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert success"><?= htmlspecialchars($success) ?>
                    <div class="links">
                        <a href="index.php" class="btn">Back to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Registered Email:</label>
                    <input type="email" id="email" name="email" 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$" 
                           title="Enter valid email (e.g., user@domain.com)"
                           required>
                    <div id="email-error" class="alert error" style="display: none;"></div>
                    <div id="otp-display" class="otp-display" style="display: none;"></div>
                </div>
                <div class="form-group otpverify">
                    <div id="timer"></div>
                    <label>OTP Verification:</label>
                    <div class="otp-inputs">
                        <input type="text" id="otp_inp" placeholder="Enter 6-digit OTP" required>
                        <button class="btn" id="otp_btn">Verify OTP</button>
                    </div>
                </div>
                <form class="form-group password-reset" method="POST" onsubmit="return validatePassword()">
                    <input type="hidden" name="email" id="hidden-email">
                    <div class="form-group">
                        <label>New Password:</label>
                        <input type="password" name="new_password" required>
                        <div id="password-strength-msg" class="password-strength"></div>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password:</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="reset_password" class="btn">Reset Password</button>
                </form>
                <button class="btn" id="send-otp-btn" onclick="sendOTP()">Send OTP</button>
                <button class="btn" id="resend-otp-btn" onclick="sendOTP(true)" style="display: none;">
                    Resend OTP (60s)
                </button>
                <div class="links">
                    <a href="index.php" class="btn">Back to Login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
       document.getElementById('otp_btn').addEventListener('click', function() {
    const otp_inp = document.getElementById('otp_inp').value;
    if (otp_inp == otp_val) {
        document.querySelector('.password-reset').style.display = 'block';
        document.getElementById('hidden-email').value = document.getElementById('email').value;
        document.querySelector('.otpverify').style.display = 'none';
        clearInterval(countdownInterval); // Clear the interval
        document.getElementById('timer').textContent = "";
    } else {
        showError('Invalid OTP. Try Again.');
    }
});
    </script>
</body>
</html>