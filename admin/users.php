<?php
require_once dirname(__DIR__) .
    '/includes/config.php';
require_once dirname(__DIR__) .
    '/includes/database.php';

// التحقق من تسجيل الدخول والصلاحيات (يجب أن يكون مسؤولاً)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$db = new Database();

$action = isset($_GET['action']) ? $_GET['action'] : 'manage';

switch ($action) {
    case 'manage':
        // جلب جميع المستخدمين
        $db->query("SELECT * FROM users ORDER BY username ASC");
        $users = $db->resultSet();

        include ROOT_PATH .
            'templates/header.php';
?>
        <div class="row">
            <div class="col-md-3">
                <?php include ROOT_PATH .
                    'templates/sidebar.php'; ?>
            </div>
            <div class="col-md-9">
                <h1><?php echo $lang["manage_users"]; ?></h1>
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
                            <th><?php echo $lang["username_col"]; ?></th>
                            <th><?php echo $lang["email_col"]; ?></th>
                            <th><?php echo $lang["role_col"]; ?></th>
                            <th><?php echo $lang["created_at"]; ?></th>
                            <th><?php echo $lang["actions"]; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $lang[$user['role']]; ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary"><?php echo $lang["edit"]; ?></a>
                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('<?php echo $lang["confirm_delete_user"]; ?>');"><?php echo $lang["delete"]; ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6"><?php echo $lang["no_users_yet"]; ?></td>
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

    case 'edit':
        $id = $_GET['id'];
        $db->query("SELECT * FROM users WHERE id = :id");
        $db->bind(":id", $id);
        $user = $db->single();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $role = trim($_POST['role']);

            $errors = [];
            if (empty($username)) $errors[] = $lang['username_col'] . ' ' . $lang['name_required'];
            if (empty($email)) $errors[] = $lang['email_col'] . ' ' . $lang['name_required'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = $lang['invalid_email_format'];

            // التحقق من وجود اسم المستخدم أو البريد الإلكتروني بالفعل (باستثناء المستخدم الحالي)
            $db->query("SELECT * FROM users WHERE (username = :username OR email = :email) AND id != :id");
            $db->bind(":username", $username);
            $db->bind(":email", $email);
            $db->bind(":id", $id);
            if ($db->single()) {
                $errors[] = $lang['username_exists'] . ' ' . $lang['email_exists'];
            }

            if (empty($errors)) {
                $db->query("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id");
                $db->bind(":username", $username);
                $db->bind(":email", $email);
                $db->bind(":role", $role);
                $db->bind(":id", $id);

                if ($db->execute()) {
                    $_SESSION['message'] = 'user_updated_success';
                    $_SESSION['message_type'] = 'success';
                    header("Location: users.php");
                    exit();
                } else {
                    $_SESSION['message'] = 'error_updating_user';
                    $_SESSION['message_type'] = 'danger';
                }
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
                <h1><?php echo $lang["edit_user"]; ?>: <?php echo $user['username']; ?></h1>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="?action=edit&id=<?php echo $user['id']; ?>" method="POST">
                    <div class="form-group">
                        <label for="username"><?php echo $lang["username_col"]; ?></label>
                        <input type="text" name="username" id="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo $lang["email_col"]; ?></label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="role"><?php echo $lang["role"]; ?></label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>><?php echo $lang["user"]; ?></option>
                            <option value="author" <?php echo ($user['role'] == 'author') ? 'selected' : ''; ?>><?php echo $lang["author"]; ?></option>
                            <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>><?php echo $lang["admin"]; ?></option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["update_user"]; ?></button>
                </form>
            </div>
        </div>
<?php
        include ROOT_PATH .
            'templates/footer.php';
        break;

    case 'delete':
        $id = $_GET['id'];
        // منع حذف المستخدم الحالي أو المستخدمين المسؤولين الآخرين (يمكن تحسين هذا)
        if ($id == $_SESSION['user_id']) {
            $_SESSION['message'] = 'cannot_delete_self';
            $_SESSION['message_type'] = 'danger';
        } else {
            $db->query("DELETE FROM users WHERE id = :id");
            $db->bind(":id", $id);

            if ($db->execute()) {
                $_SESSION['message'] = 'user_deleted_success';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'error_deleting_user';
                $_SESSION['message_type'] = 'danger';
            }
        }
        header("Location: users.php");
        exit();
        break;

    default:
        header("Location: users.php");
        exit();
        break;
}
?>