<?php
require_once dirname(__DIR__) . 
'/includes/config.php';
require_once dirname(__DIR__) . 
'/includes/database.php';

// التحقق من تسجيل الدخول والصلاحيات (يجب أن يكون مسؤولاً)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$db = new Database();

// جلب إحصائيات لوحة التحكم
$db->query("SELECT COUNT(*) FROM posts");
$total_posts = $db->single()['COUNT(*)'];

$db->query("SELECT COUNT(*) FROM users");
$total_users = $db->single()['COUNT(*)'];

$db->query("SELECT COUNT(*) FROM categories");
$total_categories = $db->single()['COUNT(*)'];

$db->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'");
$pending_comments = $db->single()['COUNT(*)'];

include ROOT_PATH . 
'templates/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include ROOT_PATH . 
'templates/sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h1><?php echo $lang["dashboard"]; ?></h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $lang["posts"]; ?></h5>
                        <p class="card-text display-4"><?php echo $total_posts; ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="text-white stretched-link"><?php echo $lang["manage_posts"]; ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $lang["users"]; ?></h5>
                        <p class="card-text display-4"><?php echo $total_users; ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/users.php" class="text-white stretched-link"><?php echo $lang["manage_users"]; ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $lang["categories"]; ?></h5>
                        <p class="card-text display-4"><?php echo $total_categories; ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="text-white stretched-link"><?php echo $lang["manage_categories"]; ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $lang["comments"]; ?> (<?php echo $lang["pending"]; ?>)</h5>
                        <p class="card-text display-4"><?php echo $pending_comments; ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="text-white stretched-link"><?php echo $lang["manage_comments"]; ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 
'templates/footer.php'; ?>
