<?php
// Include configuration, database, and functions
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize Database object
$db = new Database();

// Fetch categories for sidebar
$db->query("SELECT * FROM categories ORDER BY name ASC");
$categories_sidebar = $db->resultSet();
if ($categories_sidebar === false) {
    $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
    $_SESSION['message_type'] = 'danger';
}

// Handle category filter
$category_slug = isset($_GET['category']) ? trim($_GET['category']) : null;
$posts = [];

try {
    if ($category_slug) {
        // Fetch posts by category slug
        $db->query("SELECT p.*, u.username, c.name as category_name 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id 
                    WHERE p.status = 'published' AND c.slug = :category_slug 
                    ORDER BY p.created_at DESC");
        $db->bind(':category_slug', $category_slug);
    } else {
        // Fetch all published posts
        $db->query("SELECT p.*, u.username, c.name as category_name 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id 
                    WHERE p.status = 'published' 
                    ORDER BY p.created_at DESC");
    }
    $posts = $db->resultSet();
    if ($posts === false) {
        $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
        $_SESSION['message_type'] = 'danger';
    }
} catch (Exception $e) {
    error_log("Error fetching posts: " . $e->getMessage());
    $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
    $_SESSION['message_type'] = 'danger';
}

// Include header template
include ROOT_PATH . 'templates/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <!-- Include sidebar template -->
        <?php include ROOT_PATH . 'templates/sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <!-- Display page title -->
        <h1><?php echo htmlspecialchars($lang['latest_posts'] ?? 'Latest Posts'); ?></h1>
        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?>">
                <?php echo htmlspecialchars(isset($lang[$_SESSION['message']]) ? $lang[$_SESSION['message']] : $_SESSION['message']); ?>
            </div>
            <?php unset($_SESSION['message']);
            unset($_SESSION['message_type']); ?>
        <?php endif; ?>
        <!-- Display posts -->
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="card mb-4">
                    <?php if (!empty($post['image'])): ?>
                        <img src="<?php echo BASE_URL; ?>/public/assets/images/<?php echo htmlspecialchars($post['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="card-text">
                            <?php
                            $content = strip_tags($post['content']);
                            echo htmlspecialchars(substr($content, 0, 200)) . (strlen($content) > 200 ? '...' : '');
                            ?>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                <?php echo sprintf(
                                    $lang['posted_by_on'] ?? 'Posted by %s on %s in %s',
                                    htmlspecialchars($post['username']),
                                    htmlspecialchars(date('Y-m-d', strtotime($post['created_at']))),
                                    htmlspecialchars($post['category_name'])
                                ); ?>
                            </small>
                        </p>
                        <a href="<?php echo BASE_URL; ?>/public/single_post.php?slug=<?php echo urlencode($post['slug']); ?>" class="btn btn-primary"><?php echo htmlspecialchars($lang['read_more'] ?? 'Read More'); ?></a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?php echo htmlspecialchars($lang['no_posts_found'] ?? 'No posts found'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . 'templates/footer.php'; ?>