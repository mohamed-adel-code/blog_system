<?php require_once ROOT_PATH . 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION["lang"]; ?>" dir="<?php echo ($_SESSION["lang"] == "ar") ? "rtl" : "ltr"; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang["blog_title"]; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/style.css">
    <?php if ($_SESSION["lang"] == "ar"): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/assets/css/bootstrap-rtl.min.css">
    <?php endif; ?>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>/public/index.php"><?php echo $lang["blog_title"]; ?></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/public/index.php"><?php echo $lang["home"]; ?></a>
                    </li>
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/index.php"><?php echo $lang["dashboard"]; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/public/logout.php"><?php echo $lang["logout"]; ?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/public/login.php"><?php echo $lang["login"]; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/public/register.php"><?php echo $lang["register"]; ?></a>
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