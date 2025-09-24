<?php
// Sample configuration file for the Blog System
// To use: Copy this file to config.php and update DB_USER, DB_PASS, and other settings as needed

// Start the PHP session for user data management
session_start();

// Load environment-specific configuration
$env = getenv('APP_ENV') ?: 'development'; // Default to development environment
if ($env === 'production') {
    // Disable error display in production
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    // Enable error display for development
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Project path constants
define('BASE_URL', 'http://localhost/blog_system'); // Base URL of the project
define('ROOT_PATH', dirname(__DIR__) . '/'); // Root path of the project
define('UPLOAD_DIR', ROOT_PATH . 'public/uploads/'); // Directory for file uploads
define('UPLOAD_URL', BASE_URL . '/public/uploads/'); // URL for accessing uploaded files

// Database configuration constants (update with your actual credentials)
define('DB_HOST', 'localhost'); // Database host
define('DB_USER', 'your_username'); // Database username (replace with your username)
define('DB_PASS', 'your_password'); // Database password (replace with your password)
define('DB_NAME', 'blog_system'); // Database name

// Language configuration
define('DEFAULT_LANG', 'en'); // Default language for the application
$supported_languages = ['en', 'ar']; // List of supported languages

// Set the current language based on GET parameter or session
$language = DEFAULT_LANG; // Default fallback
if (isset($_GET['lang']) && in_array($_GET['lang'], $supported_languages)) {
    $language = $_GET['lang']; // Set language from GET parameter if valid
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $supported_languages)) {
    $language = $_SESSION['lang']; // Use session language if valid
}
$_SESSION['lang'] = $language; // Store selected language in session

// Load the appropriate language file if it exists
$language_file = ROOT_PATH . 'includes/languages/' . $language . '.php';
if (file_exists($language_file)) {
    require_once $language_file;
} else {
    // Fallback to default language if file not found
    require_once ROOT_PATH . 'includes/languages/' . DEFAULT_LANG . '.php';
}
