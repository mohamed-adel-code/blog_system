<?php
// Include configuration, database, and function files
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
        $_SESSION['message_type'] = 'danger';
        redirect('public/login.php');
    }

    // Sanitize and validate input
    $username_or_email = isset($_POST['username_or_email']) ? sanitizeString(trim($_POST['username_or_email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $errors = [];

    if (empty($username_or_email)) {
        $errors[] = $lang['username_or_email'] . ' ' . ($lang['name_required'] ?? 'is required');
    }
    if (empty($password)) {
        $errors[] = $lang['password'] . ' ' . ($lang['name_required'] ?? 'is required');
    }

    // Process login if no validation errors
    if (empty($errors)) {
        // Query user by username or email
        $db->query("SELECT * FROM users WHERE username = :username OR email = :email");
        $db->bind(":username", $username_or_email);
        $db->bind(":email", $username_or_email);
        $user = $db->single();

        // Check for query errors
        if ($user === false) {
            $errors[] = $lang['database_error'] ?? 'Database error occurred';
        } elseif ($user && password_verify($password, $user['password'])) {
            // Successful login: Set session variables and redirect
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['message'] = $lang['login_success'] ?? 'Logged in successfully';
            $_SESSION['message_type'] = 'success';
            redirect('admin/index.php');
        } else {
            // Invalid credentials
            $errors[] = $lang['login_failed'] ?? 'Invalid username or password';
        }
    }

    // Store errors in session for display
    if (!empty($errors)) {
        $_SESSION['message'] = implode('<br>', $errors);
        $_SESSION['message_type'] = 'danger';
    }
}

// Include header template
include ROOT_PATH . 'templates/header.php';
?>

<!-- Login form container -->
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <!-- Card header with login title -->
            <div class="card-header"><?php echo htmlspecialchars($lang['login'] ?? 'Login'); ?></div>
            <div class="card-body">
                <!-- Display session message if set -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($_SESSION['message_type'] ?? 'info'); ?>">
                        <?php echo htmlspecialchars($_SESSION['message']); ?>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>
                <!-- Login form -->
                <form action="<?php echo BASE_URL; ?>/public/login.php" method="POST">
                    <!-- CSRF token for security -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <!-- Username or email input -->
                    <div class="form-group">
                        <label for="username_or_email"><?php echo htmlspecialchars($lang['username_or_email'] ?? 'Username or Email'); ?></label>
                        <input type="text" name="username_or_email" id="username_or_email" class="form-control" required>
                    </div>
                    <!-- Password input -->
                    <div class="form-group">
                        <label for="password"><?php echo htmlspecialchars($lang['password'] ?? 'Password'); ?></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['login'] ?? 'Login'); ?></button>
                </form>
                <!-- Link to registration page -->
                <p class="mt-3">
                    <?php echo htmlspecialchars($lang['dont_have_account'] ?? 'Don\'t have an account?'); ?>
                    <a href="<?php echo BASE_URL; ?>/public/register.php"><?php echo htmlspecialchars($lang['new_account'] ?? 'Create an account'); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Include footer template -->
<?php include ROOT_PATH . 'templates/footer.php'; ?>