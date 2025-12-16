<?php
session_start();
require_once 'config.php';

// Set security headers
setSecurityHeaders();

// Log logout for audit trail
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    error_log("Admin logout from IP: " . $_SERVER['REMOTE_ADDR'] . " at " . date('Y-m-d H:i:s'));
}

// Clear all session data
$_SESSION = array();

// Delete session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect with cache prevention headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Location: blog.php?logout=success');
exit;
?>