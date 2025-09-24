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

// Generate CSRF token for forms and actions
$csrf_token = generateCsrfToken();

// Get action parameter or default to 'manage'
$action = isset($_GET['action']) ? $_GET['action'] : 'manage';

// Handle different actions (manage, add, edit, delete)
switch ($action) {
    case 'manage':
        // Fetch all categories ordered by name
        $db->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $db->resultSet();
        if ($categories === false) {
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
                <h1><?php echo htmlspecialchars($lang['manage_categories'] ?? 'Manage Categories'); ?></h1>
                <a href="?action=add" class="btn btn-success mb-3"><?php echo htmlspecialchars($lang['add_new_category'] ?? 'Add New Category'); ?></a>
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
                            <th><?php echo htmlspecialchars($lang['category_name'] ?? 'Name'); ?></th>
                            <th><?php echo htmlspecialchars($lang['category_description'] ?? 'Description'); ?></th>
                            <th><?php echo htmlspecialchars($lang['actions'] ?? 'Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['id']); ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo htmlspecialchars($category['id']); ?>" class="btn btn-sm btn-primary"><?php echo htmlspecialchars($lang['edit'] ?? 'Edit'); ?></a>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></button>
                                        <div class="modal fade" id="deleteModal<?php echo htmlspecialchars($category['id']); ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><?php echo htmlspecialchars($lang['confirm_delete_category'] ?? 'Confirm Delete Category'); ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo sprintf($lang['delete_category_confirm_message'] ?? 'Are you sure you want to delete %s?', htmlspecialchars($category['name'])); ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo htmlspecialchars($lang['cancel'] ?? 'Cancel'); ?></button>
                                                        <a href="?action=delete&id=<?php echo htmlspecialchars($category['id']); ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" class="btn btn-danger"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4"><?php echo htmlspecialchars($lang['no_categories_yet'] ?? 'No categories found'); ?></td>
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
            if (!verifyCsrfToken($_POST['csrf_token'])) {
                $errors[] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
            } else {
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);

                $errors = [];
                if (empty($name)) $errors[] = $lang['category_name_required'] ?? 'Category name is required';
                if (strlen($name) > 100) $errors[] = $lang['category_name_too_long'] ?? 'Category name is too long';

                // Generate slug from category name
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', transliterate($name))));
                if (empty($slug)) {
                    $slug = strtolower(trim(str_replace(' ', '-', $name)));
                }

                // Ensure slug is unique
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

                // Check if category name already exists
                $db->query("SELECT * FROM categories WHERE name = :name");
                $db->bind(":name", $name);
                if ($db->single()) {
                    $errors[] = $lang['category_exists'] ?? 'Category already exists';
                }

                if (empty($errors)) {
                    $db->query("INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)");
                    $db->bind(":name", $name);
                    $db->bind(":slug", $slug);
                    $db->bind(":description", $description);

                    if ($db->execute()) {
                        $_SESSION['message'] = $lang['category_added_success'] ?? 'Category added successfully';
                        $_SESSION['message_type'] = 'success';
                        redirect('admin/categories.php');
                    } else {
                        $_SESSION['message'] = $lang['error_adding_category'] ?? 'Error adding category';
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
                <h1><?php echo htmlspecialchars($lang['add_new_category'] ?? 'Add New Category'); ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=add" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="name"><?php echo htmlspecialchars($lang['category_name'] ?? 'Category Name'); ?></label>
                        <input type="text" name="name" id="name" class="form-control" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="description"><?php echo htmlspecialchars($lang['category_description'] ?? 'Description'); ?></label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['add_category'] ?? 'Add Category'); ?></button>
                </form>
            </div>
        </div>
    <?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'edit':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['message'] = $lang['invalid_category_id'] ?? 'Invalid category ID';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/categories.php');
        }
        $db->query("SELECT * FROM categories WHERE id = :id");
        $db->bind(":id", $id);
        $category = $db->single();
        if (!$category) {
            $_SESSION['message'] = $lang['category_not_found'] ?? 'Category not found';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/categories.php');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'])) {
                $errors[] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
            } else {
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);

                $errors = [];
                if (empty($name)) $errors[] = $lang['category_name_required'] ?? 'Category name is required';
                if (strlen($name) > 100) $errors[] = $lang['category_name_too_long'] ?? 'Category name is too long';

                // Generate slug from category name
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', transliterate($name))));
                if (empty($slug)) {
                    $slug = strtolower(trim(str_replace(' ', '-', $name)));
                }

                // Ensure slug is unique (excluding current category)
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

                // Check if category name already exists (excluding current category)
                $db->query("SELECT * FROM categories WHERE name = :name AND id != :id");
                $db->bind(":name", $name);
                $db->bind(":id", $id);
                if ($db->single()) {
                    $errors[] = $lang['category_exists'] ?? 'Category already exists';
                }

                if (empty($errors)) {
                    $db->query("UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id");
                    $db->bind(":name", $name);
                    $db->bind(":slug", $slug);
                    $db->bind(":description", $description);
                    $db->bind(":id", $id);

                    if ($db->execute()) {
                        $_SESSION['message'] = $lang['category_updated_success'] ?? 'Category updated successfully';
                        $_SESSION['message_type'] = 'success';
                        redirect('admin/categories.php');
                    } else {
                        $_SESSION['message'] = $lang['error_updating_category'] ?? 'Error updating category';
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
                <h1><?php echo htmlspecialchars($lang['edit_category'] ?? 'Edit Category'); ?>: <?php echo htmlspecialchars($category['name']); ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=edit&id=<?php echo htmlspecialchars($category['id']); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="name"><?php echo htmlspecialchars($lang['category_name'] ?? 'Category Name'); ?></label>
                        <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($category['name']); ?>" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="description"><?php echo htmlspecialchars($lang['category_description'] ?? 'Description'); ?></label>
                        <textarea name="description" id="description" class="form-control" rows="3"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['update_category'] ?? 'Update Category'); ?></button>
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
            $_SESSION['message'] = $id ? ($lang['invalid_csrf_token'] ?? 'Invalid CSRF token') : ($lang['invalid_category_id'] ?? 'Invalid category ID');
            $_SESSION['message_type'] = 'danger';
            redirect('admin/categories.php');
        }

        $db->query("SELECT COUNT(*) as count FROM posts WHERE category_id = :id");
        $db->bind(":id", $id);
        $result = $db->single();
        if ($result === false) {
            $_SESSION['message'] = $lang['database_error'] ?? 'Database error occurred';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/categories.php');
        }
        if ($result['count'] > 0) {
            $_SESSION['message'] = $lang['category_has_posts'] ?? 'Cannot delete category with associated posts';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/categories.php');
        }

        $db->query("DELETE FROM categories WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = $lang['category_deleted_success'] ?? 'Category deleted successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $lang['error_deleting_category'] ?? 'Error deleting category';
            $_SESSION['message_type'] = 'danger';
        }
        redirect('admin/categories.php');
        break;

    default:
        redirect('admin/categories.php');
        break;
}
?>