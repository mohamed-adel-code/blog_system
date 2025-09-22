<?php
require_once dirname(__DIR__) .
    '/includes/config.php';
require_once dirname(__DIR__) .
    '/includes/database.php';

$db = new Database();

$post = null;
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
    $db->query("SELECT p.*, u.username, c.name as category_name FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.slug = :slug AND p.status = 'published'");
    $db->bind(":slug", $slug);
    $post = $db->single();
}

if (!$post) {
    header("Location: " . BASE_URL . "/public/index.php");
    exit();
}

// جلب التعليقات للمقال
$db->query("SELECT * FROM comments WHERE post_id = :post_id AND status = 'approved' ORDER BY created_at DESC");
$db->bind(":post_id", $post['id']);
$comments = $db->resultSet();

$comment_errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    $author_name = trim($_POST['author_name']);
    $author_email = trim($_POST['author_email']);
    $comment_text = trim($_POST['comment_text']);
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (empty($author_name)) {
        $comment_errors[] = $lang['name_required'];
    }
    if (empty($author_email)) {
        $comment_errors[] = $lang['email_required'];
    } elseif (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
        $comment_errors[] = $lang['invalid_email_format'];
    }
    if (empty($comment_text)) {
        $comment_errors[] = $lang['comment_required'];
    }

    if (empty($comment_errors)) {
        $db->query("INSERT INTO comments (post_id, user_id, author_name, author_email, comment, status) VALUES (:post_id, :user_id, :author_name, :author_email, :comment, 'pending')");
        $db->bind(":post_id", $post['id']);
        $db->bind(":user_id", $user_id);
        $db->bind(":author_name", $author_name);
        $db->bind(":author_email", $author_email);
        $db->bind(":comment", $comment_text);

        if ($db->execute()) {
            $_SESSION['message'] = $lang['comment_added_success'];
            $_SESSION['message_type'] = 'success';
            header("Location: " . BASE_URL . "/public/single_post.php?slug=" . $post['slug']);
            exit();
        } else {
            $comment_errors[] = $lang['error_adding_comment'];
        }
    }
}

include ROOT_PATH .
    'templates/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <?php if (!empty($post['image'])): ?>
                <img src="<?php echo BASE_URL; ?>/public/assets/images/<?php echo $post['image']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
            <?php endif; ?>
            <div class="card-body">
                <h1 class="card-title"><?php echo $post['title']; ?></h1>
                <p class="card-text"><small class="text-muted"><?php echo $lang["by"]; ?> <?php echo $post['username']; ?> <?php echo $lang["in"]; ?> <?php echo date('F j, Y', strtotime($post['created_at'])); ?> <?php echo $lang["in"]; ?> <a href="#"><?php echo $post['category_name']; ?></a></small></p>
                <hr>
                <div class="post-content">
                    <?php echo nl2br($post['content']); ?>
                </div>
            </div>
        </div>

        <!-- قسم التعليقات -->
        <div class="card mb-4">
            <div class="card-header"><?php echo $lang["comments"]; ?> (<?php echo count($comments); ?>)</div>
            <div class="card-body">
                <?php if (!empty($comment_errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($comment_errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']);
                    unset($_SESSION['message_type']); ?>
                <?php endif; ?>
                <form action="<?php echo BASE_URL; ?>/public/single_post.php?slug=<?php echo $post['slug']; ?>" method="POST">
                    <input type="hidden" name="add_comment" value="1">
                    <div class="form-group">
                        <label for="author_name"><?php echo $lang["your_name"]; ?></label>
                        <input type="text" name="author_name" id="author_name" class="form-control" value="<?php echo $_POST['author_name'] ?? (isset($_SESSION['username']) ? $_SESSION['username'] : ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="author_email"><?php echo $lang["your_email"]; ?></label>
                        <input type="email" name="author_email" id="author_email" class="form-control" value="<?php echo $_POST['author_email'] ?? (isset($_SESSION['email']) ? $_SESSION['email'] : ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="comment_text"><?php echo $lang["your_comment"]; ?></label>
                        <textarea name="comment_text" id="comment_text" class="form-control" rows="5" required><?php echo $_POST['comment_text'] ?? ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["add_comment"]; ?></button>
                </form>

                <hr>

                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="media mb-3">
                            <div class="media-body">
                                <h5 class="mt-0"><?php echo $comment['author_name']; ?> <small class="text-muted"><?php echo $lang["in"]; ?> <?php echo date('F j, Y', strtotime($comment['created_at'])); ?></small></h5>
                                <?php echo nl2br($comment['comment']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?php echo $lang["no_comments_yet"]; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php include ROOT_PATH .
            'templates/sidebar.php'; ?>
    </div>
</div>

<?php include ROOT_PATH .
    'templates/footer.php'; ?>