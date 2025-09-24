<?php
// Include configuration, database, and function files
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log session data for debugging
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = $lang['access_denied'] ?? 'Access denied';
    $_SESSION['message_type'] = 'danger';
    redirect('public/login.php');
}

// Initialize Database object
$db = new Database();

// Fetch dashboard statistics with error handling
$stats = [
    'total_posts' => 0,
    'published_posts' => 0,
    'total_users' => 0,
    'total_categories' => 0,
    'pending_comments' => 0,
    'approved_comments' => 0
];

try {
    // Total posts
    $db->query("SELECT COUNT(*) as count FROM posts");
    $result = $db->single();
    $stats['total_posts'] = $result ? (int)$result['count'] : 0;

    // Published posts
    $db->query("SELECT COUNT(*) as count FROM posts WHERE status = 'published'");
    $result = $db->single();
    $stats['published_posts'] = $result ? (int)$result['count'] : 0;

    // Total users
    $db->query("SELECT COUNT(*) as count FROM users");
    $result = $db->single();
    $stats['total_users'] = $result ? (int)$result['count'] : 0;

    // Total categories
    $db->query("SELECT COUNT(*) as count FROM categories");
    $result = $db->single();
    $stats['total_categories'] = $result ? (int)$result['count'] : 0;

    // Pending comments
    $db->query("SELECT COUNT(*) as count FROM comments WHERE status = 'pending'");
    $result = $db->single();
    $stats['pending_comments'] = $result ? (int)$result['count'] : 0;

    // Approved comments
    $db->query("SELECT COUNT(*) as count FROM comments WHERE status = 'approved'");
    $result = $db->single();
    $stats['approved_comments'] = $result ? (int)$result['count'] : 0;
} catch (Exception $e) {
    // Log error and redirect
    error_log("Database error: " . $e->getMessage());
    $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
    $_SESSION['message_type'] = 'danger';
    redirect('public/index.php');
}

// Include header template
try {
    include ROOT_PATH . 'templates/header.php';
} catch (Exception $e) {
    error_log("Error including header.php: " . $e->getMessage());
    $_SESSION['message'] = $lang['template_error'] ?? 'Error loading template';
    $_SESSION['message_type'] = 'danger';
    redirect('public/index.php');
}
?>

<div class="row">
    <div class="col-md-3">
        <!-- Include sidebar template -->
        <?php
        try {
            include ROOT_PATH . 'templates/sidebar.php';
        } catch (Exception $e) {
            error_log("Error including sidebar.php: " . $e->getMessage());
            $_SESSION['message'] = $lang['template_error'] ?? 'Error loading template';
            $_SESSION['message_type'] = 'danger';
            redirect('public/index.php');
        }
        ?>
    </div>
    <div class="col-md-9">
        <!-- Display dashboard title -->
        <h1><?php echo htmlspecialchars($lang['dashboard'] ?? 'Dashboard'); ?></h1>

        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?>">
                <?php echo htmlspecialchars(isset($lang[$_SESSION['message']]) ? $lang[$_SESSION['message']] : $_SESSION['message']); ?>
            </div>
            <?php
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <!-- Display statistics in Bootstrap cards -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lang['posts'] ?? 'Posts'); ?></h5>
                        <p class="card-text display-4"><?php echo htmlspecialchars($stats['total_posts']); ?></p>
                        <p class="card-text"><?php echo sprintf($lang['published_posts'] ?? 'Published: %d', $stats['published_posts']); ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="text-white stretched-link"><?php echo htmlspecialchars($lang['manage_posts'] ?? 'Manage Posts'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lang['users'] ?? 'Users'); ?></h5>
                        <p class="card-text display-4"><?php echo htmlspecialchars($stats['total_users']); ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/users.php" class="text-white stretched-link"><?php echo htmlspecialchars($lang['manage_users'] ?? 'Manage Users'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lang['categories'] ?? 'Categories'); ?></h5>
                        <p class="card-text display-4"><?php echo htmlspecialchars($stats['total_categories']); ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="text-white stretched-link"><?php echo htmlspecialchars($lang['manage_categories'] ?? 'Manage Categories'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($lang['comments'] ?? 'Comments'); ?> (<?php echo htmlspecialchars($lang['pending'] ?? 'Pending'); ?>)</h5>
                        <p class="card-text display-4"><?php echo htmlspecialchars($stats['pending_comments']); ?></p>
                        <p class="card-text"><?php echo sprintf($lang['approved_comments'] ?? 'Approved: %d', $stats['approved_comments']); ?></p>
                        <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="text-white stretched-link"><?php echo htmlspecialchars($lang['manage_comments'] ?? 'Manage Comments'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer template
try {
    include ROOT_PATH . 'templates/footer.php';
} catch (Exception $e) {
    error_log("Error including footer.php: " . $e->getMessage());
    $_SESSION['message'] = $lang['template_error'] ?? 'Error loading template';
    $_SESSION['message_type'] = 'danger';
    redirect('public/index.php');
}
?>