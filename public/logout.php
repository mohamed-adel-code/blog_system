<?php
// Include configuration file for BASE_URL and session settings
require_once dirname(__DIR__) . '/includes/config.php';
// Include functions file for additional utilities if needed
require_once dirname(__DIR__) . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
$_SESSION = array();

// Clear session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set success message for user feedback
$_SESSION['message'] = isset($lang['logout_success']) ? $lang['logout_success'] : 'تم تسجيل الخروج بنجاح';
$_SESSION['message_type'] = 'success';

// Redirect to login page
header("Location: " . BASE_URL . "/public/login.php");
exit();
