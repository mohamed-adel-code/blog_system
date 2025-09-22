<?php
// التحقق إذا كانت الـ session مش شغالة قبل بدءها
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        unset($_SESSION['csrf_token']); // إزالة التوكن بعد الاستخدام
        return true;
    }
    return false;
}

function sanitizeUsername($input)
{
    $input = trim($input);
    $input = strip_tags($input);
    $input = preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    return $input;
}
