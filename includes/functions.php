<?php
// Helper functions for the Blog System

// Ensure config.php is included for session and other configurations
if (!defined('ROOT_PATH')) {
    require_once 'config.php';
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a CSRF token for form security
function generateCsrfToken()
{
    try {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time(); // Store token creation time
        }
        return $_SESSION['csrf_token'];
    } catch (Exception $e) {
        error_log('CSRF token generation failed: ' . $e->getMessage());
        return false;
    }
}

// Alias for generate_csrf_token to support legacy code
function generate_csrf_token()
{
    return generateCsrfToken();
}

// Verify a CSRF token
function verifyCsrfToken($token)
{
    // Check token and its validity (e.g., expires after 1 hour)
    if (
        isset($_SESSION['csrf_token']) &&
        isset($_SESSION['csrf_token_time']) &&
        time() - $_SESSION['csrf_token_time'] <= 3600 && // 1-hour validity
        hash_equals($_SESSION['csrf_token'], $token)
    ) {
        unset($_SESSION['csrf_token']); // Remove token after use
        unset($_SESSION['csrf_token_time']);
        return true;
    }
    error_log('CSRF token verification failed: Token mismatch or expired');
    return false;
}

// Alias for verify_csrf_token to support legacy code
function verify_csrf_token($token)
{
    return verifyCsrfToken($token);
}

// Sanitize username input (supports Unicode for multilingual usernames)
function sanitizeUsername($input, $allow_unicode = true)
{
    $input = trim($input); // Remove leading/trailing whitespace
    $input = strip_tags($input); // Remove HTML/PHP tags
    if ($allow_unicode) {
        $input = preg_replace('/[^\p{L}\p{N}_-]/u', '', $input); // Allow Unicode letters, numbers, underscore, hyphen
    } else {
        $input = preg_replace('/[^a-zA-Z0-9_-]/', '', $input); // Allow only alphanumeric, underscore, hyphen
    }
    return $input;
}

// Sanitize email input
function sanitizeEmail($input)
{
    $input = trim($input); // Remove leading/trailing whitespace
    $input = strip_tags($input); // Remove HTML/PHP tags
    $input = filter_var($input, FILTER_SANITIZE_EMAIL); // Sanitize email format
    return $input;
}

// Sanitize general string input
function sanitizeString($input)
{
    $input = trim($input); // Remove leading/trailing whitespace
    $input = strip_tags($input); // Remove HTML/PHP tags
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8'); // Escape special characters
    return $input;
}

// Redirect to a URL
function redirect($url)
{
    header('Location: ' . BASE_URL . '/' . ltrim($url, '/'));
    exit();
}

// Display a message (success, error, etc.)
function displayMessage($type, $message)
{
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

// Transliterate Arabic characters to Latin for slugs
function transliterate($string)
{
    $transliteration = [
        'أ' => 'a',
        'إ' => 'i',
        'آ' => 'a',
        'ا' => 'a',
        'ب' => 'b',
        'ت' => 't',
        'ث' => 'th',
        'ج' => 'j',
        'ح' => 'h',
        'خ' => 'kh',
        'د' => 'd',
        'ذ' => 'dh',
        'ر' => 'r',
        'ز' => 'z',
        'س' => 's',
        'ش' => 'sh',
        'ص' => 's',
        'ض' => 'd',
        'ط' => 't',
        'ظ' => 'z',
        'ع' => 'a',
        'غ' => 'gh',
        'ف' => 'f',
        'ق' => 'q',
        'ك' => 'k',
        'ل' => 'l',
        'م' => 'm',
        'ن' => 'n',
        'ه' => 'h',
        'و' => 'w',
        'ي' => 'y',
        'ة' => 'h',
        'ى' => 'a',
        'ئ' => 'y',
        'ؤ' => 'w'
    ];
    $string = strtr($string, $transliteration);
    $string = preg_replace('/[^\w\s-]/u', '', $string); // Remove non-word characters
    $string = preg_replace('/\s+/', '-', $string); // Replace spaces with hyphens
    return trim($string, '-');
}
