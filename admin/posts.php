<?php
// Include configuration, database, and functions files
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize Database object
$db = new Database();

// Check if user is logged in (admin or author)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'author'])) {
    $_SESSION['message'] = $lang['access_denied'] ?? 'Access denied';
    $_SESSION['message_type'] = 'danger';
    redirect('public/login.php');
}

// Generate CSRF token for forms and actions
$csrf_token = generateCsrfToken();

// Get action parameter or default to 'manage'
$action = isset($_GET['action']) ? $_GET['action'] : 'manage';

// Handle different actions (manage, add, edit, delete)
switch ($action) {
    case 'manage':
        // Fetch all posts with username and category name
        $db->query("SELECT p.*, u.username, c.name as category_name 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    JOIN categories c ON p.category_id = c.id 
                    WHERE p.user_id = :user_id OR :role = 'admin' 
                    ORDER BY p.created_at DESC");
        $db->bind(':user_id', $_SESSION['user_id']);
        $db->bind(':role', $_SESSION['role']);
        $posts = $db->resultSet();
        if ($posts === false) {
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
                <h1><?php echo htmlspecialchars($lang['manage_posts'] ?? 'Manage Posts'); ?></h1>
                <a href="?action=add" class="btn btn-success mb-3"><?php echo htmlspecialchars($lang['add_new_post'] ?? 'Add New Post'); ?></a>
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
                            <th><?php echo htmlspecialchars($lang['post_title'] ?? 'Title'); ?></th>
                            <th><?php echo htmlspecialchars($lang['by'] ?? 'By'); ?></th>
                            <th><?php echo htmlspecialchars($lang['category'] ?? 'Category'); ?></th>
                            <th><?php echo htmlspecialchars($lang['status'] ?? 'Status'); ?></th>
                            <th><?php echo htmlspecialchars($lang['created_at'] ?? 'Created At'); ?></th>
                            <th><?php echo htmlspecialchars($lang['actions'] ?? 'Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($posts) > 0): ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['id']); ?></td>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($post['username']); ?></td>
                                    <td><?php echo htmlspecialchars($post['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($lang[$post['status']] ?? $post['status']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($post['created_at']))); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary"><?php echo htmlspecialchars($lang['edit'] ?? 'Edit'); ?></a>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo $post['id']; ?>"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></button>
                                        <div class="modal fade" id="deleteModal<?php echo $post['id']; ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><?php echo htmlspecialchars($lang['confirm_delete_post'] ?? 'Confirm Delete Post'); ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo sprintf($lang['delete_post_confirm_message'] ?? 'Are you sure you want to delete %s?', htmlspecialchars($post['title'])); ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo htmlspecialchars($lang['cancel'] ?? 'Cancel'); ?></button>
                                                        <a href="?action=delete&id=<?php echo $post['id']; ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" class="btn btn-danger"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7"><?php echo htmlspecialchars($lang['no_posts_table'] ?? 'No posts found'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'add':
        $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $db->resultSet();
        if ($categories === false) {
            $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
            $_SESSION['message_type'] = 'danger';
            redirect('public/index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'])) {
                $errors[] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
            } else {
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
                $status = $_POST['status'];
                $user_id = $_SESSION['user_id'];
                $image = null;

                $errors = [];
                if (empty($title)) $errors[] = $lang['post_title_required'] ?? 'Post title is required';
                if (strlen($title) > 255) $errors[] = $lang['post_title_too_long'] ?? 'Post title is too long';
                if (empty($content)) $errors[] = $lang['post_content_required'] ?? 'Post content is required';
                if (!$category_id) $errors[] = $lang['invalid_category_id'] ?? 'Invalid category';
                if (!in_array($status, ['draft', 'published'])) $errors[] = $lang['invalid_status'] ?? 'Invalid status';

                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                if (empty($slug)) {
                    $slug = strtolower(trim(str_replace(' ', '-', $title)));
                }
                $original_slug = $slug;
                $i = 1;
                while (true) {
                    $db->query("SELECT * FROM posts WHERE slug = :slug");
                    $db->bind(":slug", $slug);
                    if (!$db->single()) {
                        break;
                    }
                    $slug = $original_slug . '-' . $i++;
                }

                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = ROOT_PATH . 'public/assets/images/';
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image_name;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

                    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                        $errors[] = $lang['image_too_large'] ?? 'Image is too large';
                    } elseif (in_array($imageFileType, $allowed_types)) {
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            $image = $image_name;
                        } else {
                            $errors[] = $lang['image_upload_error'] ?? 'Error uploading image';
                        }
                    } else {
                        $errors[] = $lang['unsupported_image_format'] ?? 'Unsupported image format';
                    }
                }

                if (empty($errors)) {
                    $db->query("INSERT INTO posts (user_id, category_id, title, slug, content, image, status) 
                                VALUES (:user_id, :category_id, :title, :slug, :content, :image, :status)");
                    $db->bind(":user_id", $user_id);
                    $db->bind(":category_id", $category_id);
                    $db->bind(":title", $title);
                    $db->bind(":slug", $slug);
                    $db->bind(":content", $content);
                    $db->bind(":image", $image);
                    $db->bind(":status", $status);

                    if ($db->execute()) {
                        $_SESSION['message'] = $lang['post_added_success'] ?? 'Post added successfully';
                        $_SESSION['message_type'] = 'success';
                        redirect('admin/posts.php');
                    } else {
                        $_SESSION['message'] = $lang['error_adding_post'] ?? 'Error adding post';
                        $_SESSION['message_type'] = 'danger';
                    }
                }
            }
        }

        include ROOT_PATH . 'templates/header.php';
    ?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH . 'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo htmlspecialchars($lang['add_new_post'] ?? 'Add New Post'); ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=add" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="title"><?php echo htmlspecialchars($lang['post_title'] ?? 'Post Title'); ?></label>
                        <input type="text" name="title" id="title" class="form-control" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="content"><?php echo htmlspecialchars($lang['post_content'] ?? 'Post Content'); ?></label>
                        <textarea name="content" id="content" class="form-control" rows="10" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_id"><?php echo htmlspecialchars($lang['category'] ?? 'Category'); ?></label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image"><?php echo htmlspecialchars($lang['post_image'] ?? 'Post Image'); ?></label>
                        <input type="file" name="image" id="image" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <div class="form-group">
                        <label for="status"><?php echo htmlspecialchars($lang['status'] ?? 'Status'); ?></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="draft"><?php echo htmlspecialchars($lang['draft'] ?? 'Draft'); ?></option>
                            <option value="published"><?php echo htmlspecialchars($lang['published'] ?? 'Published'); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['add_post'] ?? 'Add Post'); ?></button>
                </form>
            </div>
        </div>
    <?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'edit':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['message'] = $lang['invalid_post_id'] ?? 'Invalid post ID';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/posts.php');
        }

        $db->query("SELECT * FROM posts WHERE id = :id");
        $db->bind(":id", $id);
        $post = $db->single();
        if (!$post) {
            $_SESSION['message'] = $lang['post_not_found'] ?? 'Post not found';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/posts.php');
        }

        if ($_SESSION['role'] !== 'admin' && $post['user_id'] != $_SESSION['user_id']) {
            $_SESSION['message'] = $lang['unauthorized_access'] ?? 'Unauthorized access';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/posts.php');
        }

        $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $db->resultSet();
        if ($categories === false) {
            $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
            $_SESSION['message_type'] = 'danger';
            redirect('public/index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'])) {
                $errors[] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
            } else {
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
                $status = $_POST['status'];
                $image = $post['image'];

                $errors = [];
                if (empty($title)) $errors[] = $lang['post_title_required'] ?? 'Post title is required';
                if (strlen($title) > 255) $errors[] = $lang['post_title_too_long'] ?? 'Post title is too long';
                if (empty($content)) $errors[] = $lang['post_content_required'] ?? 'Post content is required';
                if (!$category_id) $errors[] = $lang['invalid_category_id'] ?? 'Invalid category';
                if (!in_array($status, ['draft', 'published'])) $errors[] = $lang['invalid_status'] ?? 'Invalid status';

                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                if (empty($slug)) {
                    $slug = strtolower(trim(str_replace(' ', '-', $title)));
                }
                $original_slug = $slug;
                $i = 1;
                while (true) {
                    $db->query("SELECT * FROM posts WHERE slug = :slug AND id != :id");
                    $db->bind(":slug", $slug);
                    $db->bind(":id", $id);
                    if (!$db->single()) {
                        break;
                    }
                    $slug = $original_slug . '-' . $i++;
                }

                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = ROOT_PATH . 'public/assets/images/';
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image_name;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

                    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                        $errors[] = $lang['image_too_large'] ?? 'Image is too large';
                    } elseif (in_array($imageFileType, $allowed_types)) {
                        if ($post['image'] && file_exists($target_dir . $post['image'])) {
                            unlink($target_dir . $post['image']);
                        }
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                            $image = $image_name;
                        } else {
                            $errors[] = $lang['image_upload_error'] ?? 'Error uploading image';
                        }
                    } else {
                        $errors[] = $lang['unsupported_image_format'] ?? 'Unsupported image format';
                    }
                }

                if (empty($errors)) {
                    $db->query("UPDATE posts SET category_id = :category_id, title = :title, slug = :slug, content = :content, image = :image, status = :status WHERE id = :id");
                    $db->bind(":category_id", $category_id);
                    $db->bind(":title", $title);
                    $db->bind(":slug", $slug);
                    $db->bind(":content", $content);
                    $db->bind(":image", $image);
                    $db->bind(":status", $status);
                    $db->bind(":id", $id);

                    if ($db->execute()) {
                        $_SESSION['message'] = $lang['post_updated_success'] ?? 'Post updated successfully';
                        $_SESSION['message_type'] = 'success';
                        redirect('admin/posts.php');
                    } else {
                        $_SESSION['message'] = $lang['error_updating_post'] ?? 'Error updating post';
                        $_SESSION['message_type'] = 'danger';
                    }
                }
            }
        }

        include ROOT_PATH . 'templates/header.php';
    ?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH . 'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo htmlspecialchars($lang['edit_post'] ?? 'Edit Post'); ?>: <?php echo htmlspecialchars($post['title']); ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=edit&id=<?php echo htmlspecialchars($post['id']); ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="title"><?php echo htmlspecialchars($lang['post_title'] ?? 'Post Title'); ?></label>
                        <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="content"><?php echo htmlspecialchars($lang['post_content'] ?? 'Post Content'); ?></label>
                        <textarea name="content" id="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_id"><?php echo htmlspecialchars($lang['category'] ?? 'Category'); ?></label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($post['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image"><?php echo htmlspecialchars($lang['current_image'] ?? 'Current Image'); ?></label>
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo BASE_URL; ?>/public/assets/images/<?php echo htmlspecialchars($post['image']); ?>" width="150" class="mb-2">
                        <?php else: ?>
                            <p><?php echo htmlspecialchars($lang['no_image'] ?? 'No image'); ?></p>
                        <?php endif; ?>
                        <label for="image"><?php echo htmlspecialchars($lang['change_image'] ?? 'Change Image'); ?></label>
                        <input type="file" name="image" id="image" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                    </div>
                    <div class="form-group">
                        <label for="status"><?php echo htmlspecialchars($lang['status'] ?? 'Status'); ?></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="draft" <?php echo ($post['status'] == 'draft') ? 'selected' : ''; ?>><?php echo htmlspecialchars($lang['draft'] ?? 'Draft'); ?></option>
                            <option value="published" <?php echo ($post['status'] == 'published') ? 'selected' : ''; ?>><?php echo htmlspecialchars($lang['published'] ?? 'Published'); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['update_post'] ?? 'Update Post'); ?></button>
                </form>
            </div>
        </div>
<?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'delete':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $csrf_token_received = filter_input(INPUT_GET, 'csrf_token', FILTER_SANITIZE_STRING);
        if (!$id || !verifyCsrfToken($csrf_token_received)) {
            $_SESSION['message'] = $id ? ($lang['invalid_csrf_token'] ?? 'Invalid CSRF token') : ($lang['invalid_post_id'] ?? 'Invalid post ID');
            $_SESSION['message_type'] = 'danger';
            redirect('admin/posts.php');
        }

        $db->query("SELECT user_id, image FROM posts WHERE id = :id");
        $db->bind(":id", $id);
        $post = $db->single();
        if (!$post) {
            $_SESSION['message'] = $lang['post_not_found'] ?? 'Post not found';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/posts.php');
        }

        if ($_SESSION['role'] !== 'admin' && $post['user_id'] != $_SESSION['user_id']) {
            $_SESSION['message'] = $lang['unauthorized_access'] ?? 'Unauthorized access';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/posts.php');
        }

        $db->query("DELETE FROM posts WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            if ($post['image'] && file_exists(ROOT_PATH . 'public/assets/images/' . $post['image'])) {
                unlink(ROOT_PATH . 'public/assets/images/' . $post['image']);
            }
            $_SESSION['message'] = $lang['post_deleted_success'] ?? 'Post deleted successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $lang['error_deleting_post'] ?? 'Error deleting post';
            $_SESSION['message_type'] = 'danger';
        }
        redirect('admin/posts.php');
        break;

    default:
        redirect('admin/posts.php');
        break;
}
?>