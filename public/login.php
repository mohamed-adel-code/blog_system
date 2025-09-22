<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// تحميل ملف اللغة بناءً على إعدادات اللغة (افتراضي: العربية)
require_once dirname(__DIR__) . '/includes/languages/ar.php'; // أو en.php حسب إعداداتك

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // التحقق من CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = $lang['invalid_csrf_token'];
        $_SESSION['message_type'] = 'danger';
        header("Location: " . BASE_URL . "/public/login.php");
        exit();
    }

    $username_or_email = filter_var(trim($_POST['username_or_email']), FILTER_SANITIZE_STRING);
    $password = trim($_POST['password']);

    $errors = [];

    if (empty($username_or_email)) {
        $errors[] = $lang['username_or_email'] . ' ' . $lang['name_required'];
    }
    if (empty($password)) {
        $errors[] = $lang['password'] . ' ' . $lang['name_required'];
    }

    if (empty($errors)) {
        // البحث عن المستخدم بناءً على اسم المستخدم أو الإيميل
        $db->query("SELECT * FROM users WHERE username = :username OR email = :email");
        $db->bind(":username", $username_or_email);
        $db->bind(":email", $username_or_email);
        $user = $db->single();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['message'] = $lang['login_success'];
            $_SESSION['message_type'] = 'success';
            header("Location: " . BASE_URL . "/admin/index.php");
            exit();
        } else {
            $errors[] = $lang['login_failed'];
        }
    }

    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

include ROOT_PATH . 'templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <div class="card-header"><?php echo $lang["login"]; ?></div>
            <div class="card-body">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']);
                    unset($_SESSION['message_type']); ?>
                <?php endif; ?>
                <form action="<?php echo BASE_URL; ?>/public/login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="form-group">
                        <label for="username_or_email"><?php echo $lang["username_or_email"]; ?></label>
                        <input type="text" name="username_or_email" id="username_or_email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo $lang["password"]; ?></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["login"]; ?></button>
                </form>
                <p class="mt-3"><?php echo $lang["dont_have_account"]; ?> <a href="<?php echo BASE_URL; ?>/public/register.php"><?php echo $lang["new_account"]; ?></a></p>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/footer.php'; ?>