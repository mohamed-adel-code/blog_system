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

// Handle different actions (manage, edit, delete)
switch ($action) {
    case 'manage':
        // Fetch all users ordered by username
        $db->query("SELECT * FROM users ORDER BY username ASC");
        $users = $db->resultSet();
        if ($users === false) {
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
                <h1><?php echo htmlspecialchars($lang['manage_users'] ?? 'Manage Users'); ?></h1>
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
                            <th><?php echo htmlspecialchars($lang['username_col'] ?? 'Username'); ?></th>
                            <th><?php echo htmlspecialchars($lang['email_col'] ?? 'Email'); ?></th>
                            <th><?php echo htmlspecialchars($lang['role_col'] ?? 'Role'); ?></th>
                            <th><?php echo htmlspecialchars($lang['created_at'] ?? 'Created At'); ?></th>
                            <th><?php echo htmlspecialchars($lang['actions'] ?? 'Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($lang[$user['role']] ?? $user['role']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo htmlspecialchars($user['id']); ?>" class="btn btn-sm btn-primary"><?php echo htmlspecialchars($lang['edit'] ?? 'Edit'); ?></a>
                                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></button>
                                        <div class="modal fade" id="deleteModal<?php echo htmlspecialchars($user['id']); ?>" tabindex="-1" role="dialog">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><?php echo htmlspecialchars($lang['confirm_delete_user'] ?? 'Confirm Delete User'); ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><?php echo sprintf($lang['delete_user_confirm_message'] ?? 'Are you sure you want to delete %s?', htmlspecialchars($user['username'])); ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo htmlspecialchars($lang['cancel'] ?? 'Cancel'); ?></button>
                                                        <a href="?action=delete&id=<?php echo htmlspecialchars($user['id']); ?>&csrf_token=<?php echo htmlspecialchars($csrf_token); ?>" class="btn btn-danger"><?php echo htmlspecialchars($lang['delete'] ?? 'Delete'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6"><?php echo htmlspecialchars($lang['no_users_yet'] ?? 'No users found'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php
        include ROOT_PATH . 'templates/footer.php';
        break;

    case 'edit':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            $_SESSION['message'] = $lang['invalid_user_id'] ?? 'Invalid user ID';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/users.php');
        }

        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(":id", $id);
        $user = $db->single();
        if (!$user) {
            $_SESSION['message'] = $lang['user_not_found'] ?? 'User not found';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/users.php');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'])) {
                $errors[] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
            } else {
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $role = trim($_POST['role']);

                $errors = [];
                if (empty($username)) $errors[] = $lang['username_required'] ?? 'Username is required';
                if (strlen($username) > 50) $errors[] = $lang['username_too_long'] ?? 'Username is too long';
                if (empty($email)) $errors[] = $lang['email_required'] ?? 'Email is required';
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $lang['invalid_email_format'] ?? 'Invalid email format';
                if (!in_array($role, ['user', 'author', 'admin'])) $errors[] = $lang['invalid_role'] ?? 'Invalid role';

                $db->query("SELECT * FROM users WHERE username = :username AND id != :id");
                $db->bind(":username", $username);
                $db->bind(":id", $id);
                if ($db->single()) {
                    $errors[] = $lang['username_exists'] ?? 'Username already exists';
                }

                $db->query("SELECT * FROM users WHERE email = :email AND id != :id");
                $db->bind(":email", $email);
                $db->bind(":id", $id);
                if ($db->single()) {
                    $errors[] = $lang['email_exists'] ?? 'Email already exists';
                }

                if (empty($errors)) {
                    $db->query("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id");
                    $db->bind(":username", $username);
                    $db->bind(":email", $email);
                    $db->bind(":role", $role);
                    $db->bind(":id", $id);

                    if ($db->execute()) {
                        $_SESSION['message'] = $lang['user_updated_success'] ?? 'User updated successfully';
                        $_SESSION['message_type'] = 'success';
                        redirect('admin/users.php');
                    } else {
                        $_SESSION['message'] = $lang['error_updating_user'] ?? 'Error updating user';
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
                <h1><?php echo htmlspecialchars($lang['edit_user'] ?? 'Edit User'); ?>: <?php echo htmlspecialchars($user['username']); ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=edit&id=<?php echo htmlspecialchars($user['id']); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="username"><?php echo htmlspecialchars($lang['username_col'] ?? 'Username'); ?></label>
                        <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo htmlspecialchars($lang['email_col'] ?? 'Email'); ?></label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="role"><?php echo htmlspecialchars($lang['role'] ?? 'Role'); ?></label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>><?php echo htmlspecialchars($lang['user'] ?? 'User'); ?></option>
                            <option value="author" <?php echo ($user['role'] == 'author') ? 'selected' : ''; ?>><?php echo htmlspecialchars($lang['author'] ?? 'Author'); ?></option>
                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>><?php echo htmlspecialchars($lang['admin'] ?? 'Admin'); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['update_user'] ?? 'Update User'); ?></button>
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
            $_SESSION['message'] = $id ? ($lang['invalid_csrf_token'] ?? 'Invalid CSRF token') : ($lang['invalid_user_id'] ?? 'Invalid user ID');
            $_SESSION['message_type'] = 'danger';
            redirect('admin/users.php');
        }

        $db->query("SELECT role FROM users WHERE id = :id");
        $db->bind(":id", $id);
        $user = $db->single();
        if (!$user) {
            $_SESSION['message'] = $lang['user_not_found'] ?? 'User not found';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/users.php');
        }

        if ($id == $_SESSION['user_id']) {
            $_SESSION['message'] = $lang['cannot_delete_self'] ?? 'Cannot delete yourself';
            $_SESSION['message_type'] = 'danger';
            redirect('admin/users.php');
        }

        if ($user['role'] == 'admin') {
            $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $admin_count = $db->single()['count'];
            if ($admin_count <= 1) {
                $_SESSION['message'] = $lang['cannot_delete_last_admin'] ?? 'Cannot delete the last admin';
                $_SESSION['message_type'] = 'danger';
                redirect('admin/users.php');
            }
        }

        $db->query("DELETE FROM users WHERE id = :id");
        $db->bind(":id", $id);
        if ($db->execute()) {
            $_SESSION['message'] = $lang['user_deleted_success'] ?? 'User deleted successfully';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = $lang['error_deleting_user'] ?? 'Error deleting user';
            $_SESSION['message_type'] = 'danger';
        }
        redirect('admin/users.php');
        break;

    default:
        redirect('admin/users.php');
        break;
}
?>