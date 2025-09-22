<?php
require_once dirname(__DIR__) . '/includes/config.php'; // تحميل BASE_URL وإعدادات الـ session
require_once dirname(__DIR__) . '/includes/functions.php'; // لو عايز تستخدم دوال إضافية

// بدء الـ session إذا لم تبدأ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إزالة كل بيانات الـ session
$_SESSION = array();

// تدمير الـ session
session_destroy();

// إعادة توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: " . BASE_URL . "/public/login.php");
exit();
