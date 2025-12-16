<?php
session_start();
require_once 'config.php';

// Set security headers
setSecurityHeaders();
header('Content-Type: application/json');

// Rate limiting
$ip_address = $_SERVER['REMOTE_ADDR'];

// Initialize or get attempt data
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}

// Clean old attempts
$current_time = time();
$_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($attempt_time) use ($current_time) {
    return ($current_time - $attempt_time) < LOCKOUT_TIME;
});

// Check if IP is locked out
if (count($_SESSION['login_attempts']) >= MAX_LOGIN_ATTEMPTS) {
    echo json_encode([
        'success' => false, 
        'message' => 'Terlalu banyak percobaan login. Coba lagi dalam ' . (LOCKOUT_TIME / 60) . ' menit.'
    ]);
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
    exit;
}

$entered_password = $_POST['password'] ?? '';

// Verify password using constant time comparison
if (password_verify($entered_password, ADMIN_PASSWORD_HASH)) {
    // Clear login attempts on successful login
    $_SESSION['login_attempts'] = [];
    
    // Set secure session variables
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_login_time'] = time();
    $_SESSION['admin_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['last_regeneration'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // Log successful login (optional - for audit trail)
    error_log("Admin login successful from IP: " . $ip_address . " at " . date('Y-m-d H:i:s'));
    
    echo json_encode(['success' => true]);
} else {
    // Record failed attempt
    $_SESSION['login_attempts'][] = $current_time;
    
    // Log failed login attempt
    error_log("Admin login failed from IP: " . $ip_address . " at " . date('Y-m-d H:i:s'));
    
    $remaining_attempts = MAX_LOGIN_ATTEMPTS - count($_SESSION['login_attempts']);
    echo json_encode([
        'success' => false, 
        'message' => $remaining_attempts > 0 ? 
            'Password salah. Sisa percobaan: ' . $remaining_attempts :
            'Terlalu banyak percobaan gagal. Akun dikunci sementara.'
    ]);
}
?>