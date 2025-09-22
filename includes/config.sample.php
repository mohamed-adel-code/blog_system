<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/blog_system');
define('ROOT_PATH', dirname(__DIR__) . '/');

define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'blog_system');

define('DEFAULT_LANG', 'en'); 

if (isset($_GET['lang']) && ($_GET['lang'] == 'en' || $_GET['lang'] == 'ar')) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

require_once ROOT_PATH . 'includes/languages/' . $_SESSION['lang'] . '.php';
