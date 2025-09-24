<?php
// Include configuration, database, and function files
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
$db = new Database();

// Initialize post variable
$post = null;
if (isset($_GET['slug'])) {
    // Sanitize slug (replace FILTER_SANITIZE_STRING)
    $slug = isset($_GET['slug']) ? sanitizeString(trim($_GET['slug'])) : '';
    $db->query("SELECT p.*, u.username, c.name as category_name FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.slug = :slug AND p.status = 'published'");
    $db->bind(":slug", $slug);
    $post = $db->single();

    // Check for query errors
    if ($post === false) {
        $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
        $_SESSION['message_type'] = 'danger';
        redirect('public/index.php');
    }
}

// Redirect if post not found
if (!$post) {
    $_SESSION['message'] = $lang['post_not_found'] ?? 'Post not found';
    $_SESSION['message_type'] = 'danger';
    redirect('public/index.php');
}

// Fetch approved comments for the post
$db->query("SELECT * FROM comments WHERE post_id = :post_id AND status = 'approved' ORDER BY created_at DESC");
$db->bind(":post_id", $post['id']);
$comments = $db->resultSet();

// Handle comment submission
$comment_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $comment_errors[] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
    } else {
        // Sanitize inputs (replace FILTER_SANITIZE_STRING and FILTER_SANITIZE_EMAIL)
        $author_name = isset($_POST['author_name']) ? sanitizeString(trim($_POST['author_name'])) : '';
        $author_email = isset($_POST['author_email']) ? sanitizeEmail(trim($_POST['author_email'])) : '';
        $comment_text = isset($_POST['comment_text']) ? sanitizeString(trim($_POST['comment_text'])) : '';
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Validate inputs
        if (empty($author_name)) {
            $comment_errors[] = $lang['name_required'] ?? 'Name is required';
        }
        if (empty($author_email)) {
            $comment_errors[] = $lang['email_required'] ?? 'Email is required';
        } elseif (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
            $comment_errors[] = $lang['invalid_email_format'] ?? 'Invalid email format';
        }
        if (empty($comment_text)) {
            $comment_errors[] = $lang['comment_required'] ?? 'Comment is required';
        }

        // If no errors, insert comment
        if (empty($comment_errors)) {
            $db->query("INSERT INTO comments (post_id, user_id, author_name, author_email, comment, status) VALUES (:post_id, :user_id, :author_name, :author_email, :comment, 'pending')");
            $db->bind(":post_id", $post['id']);
            $db->bind(":user_id", $user_id);
            $db->bind(":author_name", $author_name);
            $db->bind(":author_email", $author_email);
            $db->bind(":comment", $comment_text);

            if ($db->execute()) {
                $_SESSION['message'] = $lang['comment_added_success'] ?? 'Comment added successfully';
                $_SESSION['message_type'] = 'success';
                redirect('public/single_post.php?slug=' . urlencode($slug));
            } else {
                $comment_errors[] = $lang['error_adding_comment'] ?? 'Error adding comment';
            }
        }
    }
}

// Include header template
include ROOT_PATH . 'templates/header.php';
?>

<!-- Main content container -->
<div class="row">
    <div class="col-md-8">
        <!-- Post card -->
        <div class="card mb-4">
            <!-- Display post image if available -->
            <?php if (!empty($post['image'])): ?>
                <img src="<?php echo BASE_URL; ?>/public/assets/images/<?php echo htmlspecialchars($post['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
            <?php endif; ?>
            <div class="card-body">
                <!-- Post title -->
                <h1 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                <!-- Post metadata -->
                <p class="card-text">
                    <small class="text-muted">
                        <?php echo htmlspecialchars($lang['by'] ?? 'By'); ?>
                        <?php echo htmlspecialchars($post['username']); ?>
                        <?php echo htmlspecialchars($lang['in'] ?? 'on'); ?>
                        <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                        <?php echo htmlspecialchars($lang['in'] ?? 'in'); ?>
                        <a href="<?php echo BASE_URL; ?>/public/index.php?category=<?php echo urlencode($post['category_name']); ?>">
                            <?php echo htmlspecialchars($post['category_name']); ?>
                        </a>
                    </small>
                </p>
                <hr>
                <!-- Post content -->
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                </div>
            </div>
        </div>

        <!-- Comments section -->
        <div class="card mb-4">
            <!-- Comments header with count -->
            <div class="card-header"><?php echo htmlspecialchars($lang['comments'] ?? 'Comments'); ?> (<?php echo count($comments); ?>)</div>
            <div class="card-body">
                <!-- Display comment errors if any -->
                <?php if (!empty($comment_errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($comment_errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <!-- Display session message if set -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?>">
                        <?php echo htmlspecialchars($_SESSION['message']); ?>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>
                <!-- Comment form -->
                <form action="<?php echo BASE_URL; ?>/public/single_post.php?slug=<?php echo urlencode($slug); ?>" method="POST">
                    <!-- Hidden fields for CSRF and form identification -->
                    <input type="hidden" name="add_comment" value="1">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <!-- Author name input -->
                    <div class="form-group">
                        <label for="author_name"><?php echo htmlspecialchars($lang['your_name'] ?? 'Your Name'); ?></label>
                        <input type="text" name="author_name" id="author_name" class="form-control" value="<?php echo htmlspecialchars($_POST['author_name'] ?? (isset($_SESSION['username']) ? $_SESSION['username'] : '')); ?>" required>
                    </div>
                    <!-- Author email input -->
                    <div class="form-group">
                        <label for="author_email"><?php echo htmlspecialchars($lang['your_email'] ?? 'Your Email'); ?></label>
                        <input type="email" name="author_email" id="author_email" class="form-control" value="<?php echo htmlspecialchars($_POST['author_email'] ?? (isset($_SESSION['email']) ? $_SESSION['email'] : '')); ?>" required>
                    </div>
                    <!-- Comment text input -->
                    <div class="form-group">
                        <label for="comment_text"><?php echo htmlspecialchars($lang['your_comment'] ?? 'Your Comment'); ?></label>
                        <textarea name="comment_text" id="comment_text" class="form-control" rows="5" required><?php echo htmlspecialchars($_POST['comment_text'] ?? ''); ?></textarea>
                    </div>
                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['add_comment'] ?? 'Add Comment'); ?></button>
                </form>

                <hr>

                <!-- Display comments -->
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="media mb-3">
                            <div class="media-body">
                                <!-- Comment author and date -->
                                <h5 class="mt-0">
                                    <?php echo htmlspecialchars($comment['author_name']); ?>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($lang['in'] ?? 'on'); ?>
                                        <?php echo date('F j, Y', strtotime($comment['created_at'])); ?>
                                    </small>
                                </h5>
                                <!-- Comment content -->
                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- No comments message -->
                    <p><?php echo htmlspecialchars($lang['no_comments_yet'] ?? 'No comments yet'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Sidebar section -->
    <div class="col-md-4">
        <?php include ROOT_PATH . 'templates/sidebar.php'; ?>
    </div>
</div>

<!-- Include footer template -->
<?php include ROOT_PATH . 'templates/footer.php'; ?>