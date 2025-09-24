<?php
// Include configuration file for BASE_URL and session settings
require_once ROOT_PATH . 'includes/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set default language if not defined
if (!isset($_SESSION['lang']) || !in_array($_SESSION['lang'], ['en', 'ar'])) {
    $_SESSION['lang'] = 'en';
}

// Load appropriate language file
require_once ROOT_PATH . 'includes/languages/' . $_SESSION['lang'] . '.php';

// Handle language switch
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'ar']) && $_GET['lang'] !== $_SESSION['lang']) {
    $_SESSION['lang'] = $_GET['lang'];
    // Remove lang parameter from URL to prevent redirect loop
    $current_url = strtok($_SERVER['REQUEST_URI'], '?');
    $query_params = $_GET;
    unset($query_params['lang']);
    $new_url = $current_url . (empty($query_params) ? '' : '?' . http_build_query($query_params));
    header("Location: " . $new_url);
    exit();
}
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang']); ?>" dir="<?php echo ($_SESSION['lang'] === 'ar') ? 'rtl' : 'ltr'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lang['blog_title'] ?? 'My Blog'); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/style.css">
    <?php if ($_SESSION['lang'] === 'ar'): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap-rtl.min.css">
    <?php endif; ?>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/public/index.php"><?php echo htmlspecialchars($lang['blog_title'] ?? 'My Blog'); ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/public/index.php"><?php echo htmlspecialchars($lang['home'] ?? 'Home'); ?></a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/index.php"><?php echo htmlspecialchars($lang['dashboard'] ?? 'Dashboard'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/public/logout.php"><?php echo htmlspecialchars($lang['logout'] ?? 'Logout'); ?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/public/login.php"><?php echo htmlspecialchars($lang['login'] ?? 'Login'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/public/register.php"><?php echo htmlspecialchars($lang['register'] ?? 'Register'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="?lang=en">English</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?lang=ar">العربية</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">