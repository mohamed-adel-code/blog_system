<?php
require_once dirname(__DIR__) .
    '/includes/config.php';
require_once dirname(__DIR__) .
    '/includes/database.php';

$db = new Database();

// جلب جميع المقالات المنشورة مع اسم المستخدم والفئة
$db->query("SELECT p.*, u.username, c.name as category_name FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id WHERE p.status = 'published' ORDER BY p.created_at DESC");
$posts = $db->resultSet();

include ROOT_PATH .
    'templates/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php echo $lang[$_SESSION['message']]; ?>
                </div>
                <?php unset($_SESSION['message']);
                unset($_SESSION['message_type']); ?>
            <?php endif; ?>
            <h1 class="mb-4"><?php echo $lang['latest_posts']; ?></h1>
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-4">
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo BASE_URL .
                                            '/public/assets/images/' . $post['image']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h2 class="card-title"><a href="<?php echo BASE_URL; ?>/public/single_post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none"><?php echo $post['title']; ?></a></h2>
                            <p class="card-text"><small class="text-muted"><?php echo $lang['posted_by']; ?> <?php echo $post['username']; ?> <?php echo $lang['in']; ?> <?php echo date(
                        'F j, Y',
                        strtotime($post['created_at'])
                    ); ?> <?php echo $lang['in_category']; ?> <a href="#"><?php echo $post['category_name']; ?></a></small></p>
                            <p class="card-text"><?php echo substr($post['content'], 0, 200); ?>...</p>
                            <a href="<?php echo BASE_URL; ?>/public/single_post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-primary"><?php echo $lang['read_more']; ?> &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <?php echo $lang['no_posts_to_display']; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <?php include ROOT_PATH .
                'templates/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include ROOT_PATH .
    'templates/footer.php'; ?>