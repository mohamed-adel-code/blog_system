<?php
require_once dirname(__DIR__) .
    '/includes/config.php';
require_once dirname(__DIR__) .
    '/includes/database.php';

$db = new Database();

// التحقق من تسجيل الدخول والصلاحيات (يجب أن يكون مسؤولاً أو مؤلفًا)
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'manage';

switch ($action) {
    case 'manage':
        // جلب جميع المقالات
        $db->query("SELECT p.*, u.username, c.name as category_name FROM posts p JOIN users u ON p.user_id = u.id JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
        $posts = $db->resultSet();

        include ROOT_PATH .
            'templates/header.php';
?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH .
                    'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo $lang["manage_posts"]; ?></h1>
                <a href="?action=add" class="btn btn-success mb-3"><?php echo $lang["add_new_post"]; ?></a>
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                        <?php echo $lang[$_SESSION['message']]; // هنا نستخدم مفتاح اللغة 
                        ?>
                    </div>
                    <?php unset($_SESSION['message']);
                    unset($_SESSION['message_type']); ?>
                <?php endif; ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><?php echo $lang["id"]; ?></th>
                            <th><?php echo $lang["post_title"]; ?></th>
                            <th><?php echo $lang["by"]; ?></th>
                            <th><?php echo $lang["category"]; ?></th>
                            <th><?php echo $lang["status"]; ?></th>
                            <th><?php echo $lang["created_at"]; ?></th>
                            <th><?php echo $lang["actions"]; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($posts) > 0): ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td><?php echo $post['id']; ?></td>
                                    <td><?php echo $post['title']; ?></td>
                                    <td><?php echo $post['username']; ?></td>
                                    <td><?php echo $post['category_name']; ?></td>
                                    <td><?php echo $lang[$post['status']]; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary"><?php echo $lang["edit"]; ?></a>
                                        <a href="?action=delete&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $lang["confirm_delete_post"]; ?>');"><?php echo $lang["delete"]; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7"><?php echo $lang["no_posts_table"]; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
        include ROOT_PATH .
            'templates/footer.php';
        break;

    case 'add':
        // جلب الفئات لإظهارها في قائمة الاختيار
        $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $db->resultSet();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'];
            $slug = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", "-", $title)));
            $content = $_POST['content'];
            $category_id = $_POST['category_id'];
            $status = $_POST['status'];
            $user_id = $_SESSION['user_id']; // استخدام معرف المستخدم المسجل
            $image = null;

            // معالجة رفع الصورة
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = ROOT_PATH .
                    'public/assets/images/';
                $image_name = basename($_FILES['image']['name']);
                $target_file = $target_dir . $image_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

                if (in_array($imageFileType, $allowed_types)) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image = $image_name;
                    } else {
                        $_SESSION['message'] = 'image_upload_error';
                        $_SESSION['message_type'] = 'danger';
                    }
                } else {
                    $_SESSION['message'] = 'unsupported_image_format';
                    $_SESSION['message_type'] = 'danger';
                }
            }

            $db->query("INSERT INTO posts (user_id, category_id, title, slug, content, image, status) VALUES (:user_id, :category_id, :title, :slug, :content, :image, :status)");
            $db->bind(":user_id", $user_id);
            $db->bind(":category_id", $category_id);
            $db->bind(":title", $title);
            $db->bind(":slug", $slug);
            $db->bind(":content", $content);
            $db->bind(":image", $image);
            $db->bind(":status", $status);

            if ($db->execute()) {
                $_SESSION['message'] = 'post_added_success';
                $_SESSION['message_type'] = 'success';
                header("Location: posts.php");
                exit();
            } else {
                $_SESSION['message'] = 'error_adding_post';
                $_SESSION['message_type'] = 'danger';
            }
        }

        include ROOT_PATH .
            'templates/header.php';
    ?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH .
                    'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo $lang["add_new_post"]; ?></h1>
                <form action="?action=add" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title"><?php echo $lang["post_title"]; ?></label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="content"><?php echo $lang["post_content"]; ?></label>
                        <textarea name="content" id="content" class="form-control" rows="10" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_id"><?php echo $lang["category"]; ?></label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image"><?php echo $lang["post_image"]; ?></label>
                        <input type="file" name="image" id="image" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label for="status"><?php echo $lang["status"]; ?></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="draft"><?php echo $lang["draft"]; ?></option>
                            <option value="published"><?php echo $lang["published"]; ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["add_post"]; ?></button>
                </form>
            </div>
        </div>
    <?php
        include ROOT_PATH .
            'templates/footer.php';
        break;

    case 'edit':
        $id = $_GET['id'];
        // جلب المقال الحالي
        $db->query("SELECT * FROM posts WHERE id = :id");
        $db->bind(":id", $id);
        $post = $db->single();

        // جلب الفئات لإظهارها في قائمة الاختيار
        $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $db->resultSet();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $title = $_POST['title'];
            $slug = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", "-", $title)));
            $content = $_POST['content'];
            $category_id = $_POST['category_id'];
            $status = $_POST['status'];
            $image = $post['image']; // احتفاظ بالصورة القديمة افتراضيًا

            // معالجة رفع الصورة الجديدة
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $target_dir = ROOT_PATH .
                    'public/assets/images/';
                $image_name = basename($_FILES['image']['name']);
                $target_file = $target_dir . $image_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];

                if (in_array($imageFileType, $allowed_types)) {
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image = $image_name;
                    }
                }
            }

            $db->query("UPDATE posts SET category_id = :category_id, title = :title, slug = :slug, content = :content, image = :image, status = :status WHERE id = :id");
            $db->bind(":category_id", $category_id);
            $db->bind(":title", $title);
            $db->bind(":slug", $slug);
            $db->bind(":content", $content);
            $db->bind(":image", $image);
            $db->bind(":status", $status);
            $db->bind(":id", $id);

            if ($db->execute()) {
                $_SESSION['message'] = 'post_updated_success';
                $_SESSION['message_type'] = 'success';
                header("Location: posts.php");
                exit();
            } else {
                $_SESSION['message'] = 'error_updating_post';
                $_SESSION['message_type'] = 'danger';
            }
        }

        include ROOT_PATH .
            'templates/header.php';
    ?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH .
                    'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo $lang["edit_post"]; ?>: <?php echo $post['title']; ?></h1>
                <form action="?action=edit&id=<?php echo $post['id']; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title"><?php echo $lang["post_title"]; ?></label>
                        <input type="text" name="title" id="title" class="form-control" value="<?php echo $post['title']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="content"><?php echo $lang["post_content"]; ?></label>
                        <textarea name="content" id="content" class="form-control" rows="10" required><?php echo $post['content']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category_id"><?php echo $lang["category"]; ?></label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($post['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="image"><?php echo $lang["current_image"]; ?></label>

                        <?php if (!empty($post['image'])): ?>
                            <img src="<?php echo BASE_URL; ?>/public/assets/images/<?php echo $post['image']; ?>" width="150" class="mb-2">


                        <?php else: ?>
                            <p><?php echo $lang["no_image"]; ?></p>
                        <?php endif; ?>
                        <label for="image"><?php echo $lang["change_image"]; ?></label>
                        <input type="file" name="image" id="image" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label for="status"><?php echo $lang["status"]; ?></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="draft" <?php echo ($post['status'] == 'draft') ? 'selected' : ''; ?>><?php echo $lang["draft"]; ?></option>
                            <option value="published" <?php echo ($post['status'] == 'published') ? 'selected' : ''; ?>><?php echo $lang["published"]; ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["update_post"]; ?></button>
                </form>
            </div>
        </div>
<?php
        include ROOT_PATH .
            'templates/footer.php';
        break;

    case 'delete':
        $id = $_GET['id'];
        $db->query("DELETE FROM posts WHERE id = :id");
        $db->bind(":id", $id);

        if ($db->execute()) {
            $_SESSION['message'] = 'post_deleted_success';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'error_deleting_post';
            $_SESSION['message_type'] = 'danger';
        }
        header("Location: posts.php");
        exit();
        break;

    default:
        header("Location: posts.php");
        exit();
        break;
}
?>