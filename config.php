<?php
// Configuration file for admin settings
// In production, move this outside web root or use environment variables

// Admin password hash - change this in production!
// Current password: adminnjr
// To generate new hash: echo password_hash('your_new_password', PASSWORD_DEFAULT);
define('ADMIN_PASSWORD_HASH', '$2y$10$7lmjCBB3KqLVESHOenQHMuV0p84evfK91PRTI80.BZv72SvG.xcOu');

// Session security settings
define('SESSION_TIMEOUT', 7200); // 2 hours
define('SESSION_REGENERATE_INTERVAL', 300); // 5 minutes

// Rate limiting settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// Security headers
function setSecurityHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Only set HTTPS headers if using HTTPS
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Generate secure CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>