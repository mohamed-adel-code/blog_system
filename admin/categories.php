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
        // جلب جميع الفئات
        $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $db->resultSet();

        include ROOT_PATH . 'templates/header.php';
?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH . 'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo $lang["manage_categories"]; ?></h1>
                <a href="?action=add" class="btn btn-success mb-3"><?php echo $lang["add_new_category"]; ?></a>
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
                            <th><?php echo $lang["category_name"]; ?></th>
                            <th><?php echo $lang["category_description"]; ?></th>
                            <th><?php echo $lang["actions"]; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo $category['name']; ?></td>
                                    <td><?php echo $category['description']; ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary"><?php echo $lang["edit"]; ?></a>
                                        <a href="?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $lang["confirm_delete_category"]; ?>');"><?php echo $lang["delete"]; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4"><?php echo $lang["no_categories_yet"]; ?></td>
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
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);

            $errors = [];
            if (empty($name)) $errors[] = $lang['category_name_required'];

            // توليد slug من الاسم
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            // إذا كان الـ slug فارغًا بعد التحويل (مثلاً إذا كان الاسم عربيًا فقط)، نستخدم الاسم الأصلي ونحوله بطريقة أخرى
            if (empty($slug)) {
                $slug = strtolower(trim(str_replace(' ', '-', $name))); // تحويل المسافات إلى شرطات
                // يمكن إضافة مكتبة لتحويل الأحرف العربية إلى لاتينية هنا لتحسين الـ slug
            }

            // التأكد من أن الـ slug فريد
            $original_slug = $slug;
            $i = 1;
            while (true) {
                $db->query("SELECT * FROM categories WHERE slug = :slug");
                $db->bind(":slug", $slug);
                if (!$db->single()) {
                    break;
                }
                $slug = $original_slug . '-' . $i++;
            }

            $db->query("SELECT * FROM categories WHERE name = :name");
            $db->bind(":name", $name);
            if ($db->single()) {
                $errors[] = $lang['category_exists'];
            }

            if (empty($errors)) {
                $db->query("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)");
                $db->bind(":name", $name);
                $db->bind(":slug", $slug);
                $db->bind(":description", $description);

                if ($db->execute()) {
                    $_SESSION['message'] = 'category_added_success';
                    $_SESSION['message_type'] = 'success';
                    header("Location: categories.php");
                    exit();
                } else {
                    $_SESSION['message'] = 'error_adding_category';
                    $_SESSION['message_type'] = 'danger';
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
                <h1><?php echo $lang["add_new_category"]; ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=add" method="POST">
                    <div class="form-group">
                        <label for="name"><?php echo $lang["category_name"]; ?></label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="description"><?php echo $lang["category_description"]; ?></label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["add_category"]; ?></button>
                </form>
            </div>
        </div>
    <?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'edit':
        $id = $_GET['id'];
        $db->query("SELECT * FROM categories WHERE id = :id");
        $db->bind(":id", $id);
        $category = $db->single();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);

            $errors = [];
            if (empty($name)) $errors[] = $lang['category_name_required'];

            // توليد slug من الاسم
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            if (empty($slug)) {
                $slug = strtolower(trim(str_replace(' ', '-', $name)));
            }

            // التأكد من أن الـ slug فريد (باستثناء الفئة الحالية)
            $original_slug = $slug;
            $i = 1;
            while (true) {
                $db->query("SELECT * FROM categories WHERE slug = :slug AND id != :id");
                $db->bind(":slug", $slug);
                $db->bind(":id", $id);
                if (!$db->single()) {
                    break;
                }
                $slug = $original_slug . '-' . $i++;
            }

            $db->query("SELECT * FROM categories WHERE name = :name AND id != :id");
            $db->bind(":name", $name);
            $db->bind(":id", $id);
            if ($db->single()) {
                $errors[] = $lang['category_exists'];
            }

            if (empty($errors)) {
                $db->query("UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id");
                $db->bind(":name", $name);
                $db->bind(":slug", $slug);
                $db->bind(":description", $description);
                $db->bind(":id", $id);

                if ($db->execute()) {
                    $_SESSION['message'] = 'category_updated_success';
                    $_SESSION['message_type'] = 'success';
                    header("Location: categories.php");
                    exit();
                } else {
                    $_SESSION['message'] = 'error_updating_category';
                    $_SESSION['message_type'] = 'danger';
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
                <h1><?php echo $lang["edit_category"]; ?>: <?php echo $category['name']; ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=edit&id=<?php echo $category['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="name"><?php echo $lang["category_name"]; ?></label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo $category['name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description"><?php echo $lang["category_description"]; ?></label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?php echo $category['description']; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["update_category"]; ?></button>
                </form>
            </div>
        </div>
<?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'delete':
        $id = $_GET['id'];
        $db->query("DELETE FROM categories WHERE id = :id");
        $db->bind(":id", $id);

        if ($db->execute()) {
            $_SESSION['message'] = 'category_deleted_success';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'error_deleting_category';
            $_SESSION['message_type'] = 'danger';
        }
        header("Location: categories.php");
        exit();
        break;

    default:
        header("Location: categories.php");
        exit();
        break;
}
?>