<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

// التحقق من تسجيل الدخول والصلاحيات (يجب أن يكون مسؤولاً)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$db = new Database();
$action = isset($_GET['action']) ? $_GET['action'] : 'manage';

switch ($action) {
    case 'manage':
        // جلب جميع التعليقات مع معلومات المقال والمستخدم
        $db->query("SELECT c.*, p.title as post_title, u.username 
                    FROM comments c 
                    LEFT JOIN posts p ON c.post_id = p.id 
                    LEFT JOIN users u ON c.user_id = u.id 
                    ORDER BY c.created_at DESC");
        $comments = $db->resultSet();

        include ROOT_PATH . 'templates/header.php';
?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH . 'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo $lang["manage_comments"]; ?></h1>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                        <?php echo $lang[$_SESSION['message']]; ?>
                    </div>
                    <?php unset($_SESSION['message']);
                    unset($_SESSION['message_type']); ?>
                <?php endif; ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo $lang["id"]; ?></th>
                            <th><?php echo $lang["post_title"]; ?></th>
                            <th><?php echo $lang["author_name"]; ?></th>
                            <th><?php echo $lang["your_comment"]; ?></th>
                            <th><?php echo $lang["status"]; ?></th>
                            <th><?php echo $lang["created_at"]; ?></th>
                            <th><?php echo $lang["actions"]; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo $comment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($comment['post_title']); ?></td>
                                    <td><?php echo htmlspecialchars($comment['author_name']); ?>
                                        (<?php echo $comment['username'] ? htmlspecialchars($comment['username']) : 'Guest'; ?>)
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($comment['comment'], 0, 50)); ?>...</td>
                                    <td><?php echo $lang[$comment['status']]; ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></td>
                                    <td>
                                        <?php if ($comment['status'] == 'pending'): ?>
                                            <a href="?action=approve&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-success"><?php echo $lang["approve"]; ?></a>
                                            <a href="?action=reject&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-warning"><?php echo $lang["reject"]; ?></a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $lang["confirm_delete_comment"]; ?>');"><?php echo $lang["delete"]; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7"><?php echo $lang["no_comments_yet"]; ?></td>
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
        $id = $_GET['id'];
        $db->query("UPDATE comments SET status = 'approved' WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = 'comment_approved_success';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'error_updating_comment';
            $_SESSION['message_type'] = 'danger';
        }
        header("Location: comments.php");
        exit();
        break;

    case 'reject':
        $id = $_GET['id'];
        $db->query("UPDATE comments SET status = 'rejected' WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = 'comment_rejected_success';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'error_updating_comment';
            $_SESSION['message_type'] = 'danger';
        }
        header("Location: comments.php");
        exit();
        break;

    case 'delete':
        $id = $_GET['id'];
        $db->query("DELETE FROM comments WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = 'comment_deleted_success';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'error_deleting_comment';
            $_SESSION['message_type'] = 'danger';
        }
        header("Location: comments.php");
        exit();
        break;

    default:
        header("Location: comments.php");
        exit();
        break;
}
?>