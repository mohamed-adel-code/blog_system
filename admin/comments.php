<?php
// Include configuration, database, and functions files
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['message'] = $lang['access_denied'] ?? 'Access denied';
    $_SESSION['message_type'] = 'danger';
    redirect('public/login.php');
}

// Initialize Database object
$db = new Database();

// Generate CSRF token for actions
$csrf_token = generateCsrfToken();

// Get action parameter or default to 'manage'
$action = isset($_GET['action']) ? $_GET['action'] : 'manage';

// Handle different actions (manage, approve, reject, delete)
switch ($action) {
    case 'manage':
        // Fetch all comments with post title and username
        $db->query("SELECT c.*, p.title as post_title, u.username 
                    FROM comments c 
                    LEFT JOIN posts p ON c.post_id = p.id 
                    LEFT JOIN users u ON c.user_id = u.id 
                    ORDER BY c.created_at DESC");
        $comments = $db->resultSet();
        if ($comments === false) {
            $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
            $_SESSION['message_type'] = 'danger';
            redirect('public/index.php');
        }

        // Include header template
        include ROOT_PATH . 'templates/header.php';
?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH . 'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo htmlspecialchars($lang['manage_comments'] ?? 'Manage Comments'); ?></h1>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?>">
                        <?php echo htmlspecialchars(isset($lang[$_SESSION['message']]) ? $lang[$_SESSION['message']] : $_SESSION['message']); ?>
                    </div>
                    <?php unset($_SESSION['message']);
                    unset($_SESSION['message_type']); ?>
                <?php endif; ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo htmlspecialchars($lang['id'] ?? 'ID'); ?></th>
                            <th><?php echo htmlspecialchars($lang['post_title'] ?? 'Post Title'); ?></th>
                            <th><?php echo htmlspecialchars($lang['author_name'] ?? 'Author'); ?></th>
                            <th><?php echo htmlspecialchars($lang['your_comment'] ?? 'Comment'); ?></th>
                            <th><?php echo htmlspecialchars($lang['status'] ?? 'Status'); ?></th>
                            <th><?php echo htmlspecialchars($lang['created_at'] ?? 'Created At'); ?></th>
                            <th><?php echo htmlspecialchars($lang['actions'] ?? 'Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($comment['id']); ?></td>
                                    <td><a href="<?php echo BASE_URL; ?>/public/single_post.php?id=<?php echo htmlspecialchars($comment['post_id']); ?>" target="_blank"><?php echo htmlspecialchars($comment['post_title'] ?? 'No Title'); ?></a></td>
                                    <td><?php echo htmlspecialchars($comment['username'] ?? 'Guest'); ?></td>
                                    <td><?php echo htmlspecialchars(substr($comment['comment'], 0, 50)); ?>...</td>
                                    <td><?php echo htmlspecialchars($lang[$comment['status']] ?? $comment['status']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($comment['created_at']))); ?></td>
                                    <td>
                                        <?php if ($comment['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo htmlspecialchars($comment['id']); ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" class="btn btn-sm btn-success"><?php echo htmlspecialchars($lang['approve'] ?? 'Approve'); ?></a>
                                            <a href="?action=reject&id=<?php echo htmlspecialchars($comment['id']); ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" class="btn btn-sm btn-warning"><?php echo htmlspecialchars($lang['reject'] ?? 'Reject'); ?></a>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo htmlspecialchars($comment['id']); ?>"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></button>
                                        <div class="modal fade" id="deleteModal<?php echo htmlspecialchars($comment['id']); ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><?php echo htmlspecialchars($lang['confirm_delete_comment'] ?? 'Confirm Delete Comment'); ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo sprintf($lang['delete_comment_confirm_message'] ?? 'Are you sure you want to delete %s?', htmlspecialchars(substr($comment['comment'], 0, 30))); ?>...</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo htmlspecialchars($lang['cancel'] ?? 'Cancel'); ?></button>
                                                        <a href="?action=delete&id=<?php echo htmlspecialchars($comment['id']); ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" class="btn btn-danger"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7"><?php echo htmlspecialchars($lang['no_comments_yet'] ?? 'No comments found'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'approve':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $csrf_token_received = filter_input(INPUT_GET, 'csrf_token', FILTER_SANITIZE_STRING);
        if (!$id || !verifyCsrfToken($csrf_token_received)) {
            $_SESSION['message'] = $id ? ($lang['invalid_csrf_token'] ?? 'Invalid CSRF token') : ($lang['invalid_comment_id'] ?? 'Invalid comment ID');
            $_SESSION['message_type'] = 'danger';
            redirect('admin/comments.php');
        }

        $db->query("UPDATE comments SET status = 'approved' WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = $lang['comment_approved_success'] ?? 'Comment approved successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $lang['error_updating_comment'] ?? 'Error updating comment';
            $_SESSION['message_type'] = 'danger';
        }
        redirect('admin/comments.php');
        break;

    case 'reject':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $csrf_token_received = filter_input(INPUT_GET, 'csrf_token', FILTER_SANITIZE_STRING);
        if (!$id || !verifyCsrfToken($csrf_token_received)) {
            $_SESSION['message'] = $id ? ($lang['invalid_csrf_token'] ?? 'Invalid CSRF token') : ($lang['invalid_comment_id'] ?? 'Invalid comment ID');
            $_SESSION['message_type'] = 'danger';
            redirect('admin/comments.php');
        }

        $db->query("UPDATE comments SET status = 'rejected' WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = $lang['comment_rejected_success'] ?? 'Comment rejected successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $lang['error_updating_comment'] ?? 'Error updating comment';
            $_SESSION['message_type'] = 'danger';
        }
        redirect('admin/comments.php');
        break;

    case 'delete':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $csrf_token_received = filter_input(INPUT_GET, 'csrf_token', FILTER_SANITIZE_STRING);
        if (!$id || !verifyCsrfToken($csrf_token_received)) {
            $_SESSION['message'] = $id ? ($lang['invalid_csrf_token'] ?? 'Invalid CSRF token') : ($lang['invalid_comment_id'] ?? 'Invalid comment ID');
            $_SESSION['message_type'] = 'danger';
            redirect('admin/comments.php');
        }

        $db->query("DELETE FROM comments WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = $lang['comment_deleted_success'] ?? 'Comment deleted successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $lang['error_deleting_comment'] ?? 'Error deleting comment';
            $_SESSION['message_type'] = 'danger';
        }
        redirect('admin/comments.php');
        break;

    default:
        redirect('admin/comments.php');
        break;
}
?>