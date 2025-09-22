<?php
if (!isset($categories_sidebar)) {
    $db->query("SELECT * FROM categories ORDER BY name ASC");
    $db->execute();
    $categories_sidebar = $db->resultSet();
}
?>

<div class="card mb-4">
    <div class="card-header"><?php echo $lang["categories"]; ?></div>
    <div class="card-body">
        <div class="list-group">
            <?php if (count($categories_sidebar) > 0): ?>
                <?php foreach ($categories_sidebar as $category): ?>
                    <a href="<?php echo BASE_URL; ?>/public/index.php?category=<?php echo $category["slug"]; ?>" class="list-group-item list-group-item-action">
                        <?php echo $category["name"]; ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p><?php echo $lang["no_categories_yet"]; ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><?php echo $lang["admin_panel"]; ?></div>
    <div class="card-body">
        <div class="list-group">
            <?php if (isset($_SESSION["user_id"])): // إذا كان المستخدم مسجلاً دخوله 
            ?>
                <a href="<?php echo BASE_URL; ?>/admin/index.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <?php echo $lang["dashboard"]; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/posts.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'posts.php' ? 'active' : ''; ?>">
                    <?php echo $lang["manage_posts"]; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                    <?php echo $lang["manage_categories"]; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/comments.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'comments.php' ? 'active' : ''; ?>">
                    <?php echo $lang["manage_comments"]; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/users.php" class="list-group-item list-group-item-action <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <?php echo $lang["manage_users"]; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/logout.php" class="list-group-item list-group-item-action text-danger">
                    <?php echo $lang["logout"]; ?>
                </a>
            <?php else: // إذا لم يكن مسجلاً دخوله 
            ?>
                <a href="<?php echo BASE_URL; ?>/public/login.php" class="list-group-item list-group-item-action">
                    <?php echo $lang["login"]; ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/public/register.php" class="list-group-item list-group-item-action">
                    <?php echo $lang["new_account"]; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>