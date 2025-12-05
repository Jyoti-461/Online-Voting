<?php
/**
 * Database Connection
 * @return PDO
 */
function db_connect() {
    static $pdo;
    if (!$pdo) {
        try {
            $pdo = new PDO(
                'mysql:host=localhost;dbname=voting_system;charset=utf8',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Connection to database failed. Please try again later.");
        }
    }
    return $pdo;
}
/**
 * Sanitize Input Data
 * @param mixed $data
 * @return string
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
/**
 * Generate CSRF Token
 * @return string
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
/**
 * Validate CSRF Token
 * @param string $token
 * @return bool
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
/**
 * Handle File Uploads
 * @param array $file
 * @param string $target_dir
 * @return string
 */
 function handle_file_upload($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    $detected_type = mime_content_type($file['tmp_name']);
    if (!array_key_exists($detected_type, $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG/PNG allowed.");
    }
    $upload_dir = __DIR__ . '/../uploads/candidates/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    $filename = bin2hex(random_bytes(16)) . '.' . $allowed_types[$detected_type];
    move_uploaded_file($file['tmp_name'], $upload_dir . $filename);
    return 'uploads/candidates/' . $filename;
}
/**
 * Check User Authentication
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
/**
 * Check Admin Privileges
 * @return bool
 */
function is_admin_user() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}
/**
 * Generate Pagination
 * @param int $total_items
 * @param int $per_page
 * @param int $current_page
 * @return array
 */
function generate_pagination($total_items, $per_page, $current_page) {
    return [
        'total_pages' => ceil($total_items / $per_page),
        'offset' => ($current_page - 1) * $per_page,
        'prev_page' => max(1, $current_page - 1),
        'next_page' => min(ceil($total_items / $per_page), $current_page + 1)
    ];
}
/**
 * Error Handler
 * @param string $message
 * @param int $http_code
 */
function handle_error($message, $http_code = 500) {
    http_response_code($http_code);
    error_log("System Error: " . $message);
    die("An error occurred. Please try again later.");
}
/**
 * Redirect with Message
 * @param string $url
 * @param string $type
 * @param string $message
 */
function redirect($url, $type = null, $message = null) {
    if ($type && $message) {
        $_SESSION[$type] = $message;
    }
    header("Location: $url");
    exit();
}
/**
 * Generate CAPTCHA
 * @return array [question, answer]
 */
function generate_captcha() {
    $num1 = rand(10, 20);
    $num2 = rand(1, 9);
    return [
        'question' => "$num1 + $num2 = ?",
        'answer' => $num1 + $num2
    ];
}
/**
 * Get Candidate Details
 * @param int $candidate_id
 * @return array
 */
function get_candidate_by_id($candidate_id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->execute([$candidate_id]);
    return $stmt->fetch();
}
/**
 * Check Voting Eligibility
 * @param int $user_id
 * @return bool
 */
function has_voted($user_id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result && $result['has_voted'];
}
?>