<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = $lang['invalid_csrf_token'];
        $_SESSION['message_type'] = 'danger';
        header("Location: " . BASE_URL . "/public/register.php");
        exit();
    }

    $username = sanitizeUsername(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $errors = [];

    if (empty($username)) {
        $errors[] = $lang['username'] . ' ' . $lang['name_required'];
    }
    if (empty($email)) {
        $errors[] = $lang['email'] . ' ' . $lang['name_required'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['invalid_email_format'];
    }
    if (empty($password)) {
        $errors[] = $lang['password'] . ' ' . $lang['name_required'];
    }
    if (strlen($password) < 8) {
        $errors[] = $lang['password_too_short'];
    }
    if ($password !== $confirm_password) {
        $errors[] = $lang['password_mismatch'];
    }

    $db->query("SELECT * FROM users WHERE username = :username OR email = :email");
    $db->bind(":username", $username);
    $db->bind(":email", $email);
    if ($db->single()) {
        $errors[] = $lang['username_exists'] . ' ' . $lang['email_exists'];
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $db->query("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'user')");
        $db->bind(":username", $username);
        $db->bind(":email", $email);
        $db->bind(":password", $hashed_password);

        if ($db->execute()) {
            $_SESSION['message'] = $lang['register_success'];
            $_SESSION['message_type'] = 'success';
            header("Location: " . BASE_URL . "/public/login.php");
            exit();
        } else {
            $_SESSION['message'] = $lang['error_adding_user'];
            $_SESSION['message_type'] = 'danger';
        }
    }
}

include ROOT_PATH . 'templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <div class="card-header"><?php echo $lang["new_account"]; ?></div>
            <div class="card-body">
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']);
                    unset($_SESSION['message_type']); ?>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <form action="<?php echo BASE_URL; ?>/public/register.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="form-group">
                        <label for="username"><?php echo $lang["username"]; ?></label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo $lang["email"]; ?></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password"><?php echo $lang["password"]; ?></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password"><?php echo $lang["confirm_password"]; ?></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $lang["register"]; ?></button>
                </form>
                <p class="mt-3"><?php echo $lang["already_have_account"]; ?> <a href="<?php echo BASE_URL; ?>/public/login.php"><?php echo $lang["login"]; ?></a></p>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/footer.php'; ?>