<?php
// Include database and functions if not already included
require_once ROOT_PATH . 'includes/database.php';
require_once ROOT_PATH . 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database object if not already set
if (!isset($db)) {
    $db = new Database();
}

// Fetch categories from database if not already set
if (!isset($categories_sidebar)) {
    $db->query("SELECT * FROM categories ORDER BY name ASC");
    $categories_sidebar = $db->resultSet();

    // Check for database errors
    if ($categories_sidebar === false) {
        $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
        $_SESSION['message_type'] = 'danger';
        redirect('public/index.php');
    }
}
?>

<!-- Categories section -->
<div class="card mb-4">
    <!-- Card header for categories -->
    <div class="card-header"><?php echo htmlspecialchars($lang['categories'] ?? 'Categories'); ?></div>
    <div class="card-body">
        <div class="list-group">
            <?php if (count($categories_sidebar) > 0): ?>
                <!-- Loop through categories -->
                <?php foreach ($categories_sidebar as $category): ?>
                    <a href="<?php echo BASE_URL; ?>/public/index.php?category=<?php echo urlencode($category['slug']); ?>" class="list-group-item list-group-item-action">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Display message if no categories exist -->
                <p><?php echo htmlspecialchars($lang['no_categories_yet'] ?? 'No categories yet'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Admin panel section -->
<div class="card mb-4">
    <!-- Card header for admin panel -->
    <div class="card-header"><?php echo htmlspecialchars($lang['admin_panel'] ?? 'Admin Panel'); ?></div>
    <div class="card-body">
        <div class="list-group">
            <?php if (isset($_SESSION['user_id'])): // If user is logged in 
            ?>
                <!-- Dashboard link -->
                <a href="<?php echo BASE_URL; ?>/admin/index.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($lang['dashboard'] ?? 'Dashboard'); ?>
                </a>
                <!-- Manage posts link -->
                <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($lang['manage_posts'] ?? 'Manage Posts'); ?>
                </a>
                <!-- Manage categories link -->
                <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($lang['manage_categories'] ?? 'Manage Categories'); ?>
                </a>
                <!-- Manage comments link -->
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($lang['manage_comments'] ?? 'Manage Comments'); ?>
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): // Show only for admin 
                ?>
                    <!-- Manage users link -->
                    <a href="<?php echo BASE_URL; ?>/admin/users.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($lang['manage_users'] ?? 'Manage Users'); ?>
                    </a>
                <?php endif; ?>
                <!-- Logout link -->
                <a href="<?php echo BASE_URL; ?>/public/logout.php" class="list-group-item list-group-item-action text-danger">
                    <?php echo htmlspecialchars($lang['logout'] ?? 'Logout'); ?>
                </a>
            <?php else: // If user is not logged in 
            ?>
                <!-- Login link -->
                <a href="<?php echo BASE_URL; ?>/public/login.php" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($lang['login'] ?? 'Login'); ?>
                </a>
                <!-- Register link -->
                <a href="<?php echo BASE_URL; ?>/public/register.php" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($lang['new_account'] ?? 'Register'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>