<?php
// Include configuration, database, and function files
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Initialize database connection
$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $_SESSION['message'] = $lang['invalid_csrf_token'] ?? 'Invalid CSRF token';
        $_SESSION['message_type'] = 'danger';
        redirect('public/register.php');
    }

    // Sanitize and validate input
    $username = isset($_POST['username']) ? sanitizeUsername(trim($_POST['username'])) : '';
    $email = isset($_POST['email']) ? sanitizeEmail(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $errors = [];

    // Validate inputs
    if (empty($username)) {
        $errors[] = ($lang['username'] ?? 'Username') . ' ' . ($lang['name_required'] ?? 'is required');
    }
    if (empty($email)) {
        $errors[] = ($lang['email'] ?? 'Email') . ' ' . ($lang['name_required'] ?? 'is required');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['invalid_email_format'] ?? 'Invalid email format';
    }
    if (empty($password)) {
        $errors[] = ($lang['password'] ?? 'Password') . ' ' . ($lang['name_required'] ?? 'is required');
    }
    if (strlen($password) < 8) {
        $errors[] = $lang['password_too_short'] ?? 'Password must be at least 8 characters';
    }
    if ($password !== $confirm_password) {
        $errors[] = $lang['password_mismatch'] ?? 'Passwords do not match';
    }

    // Check if username or email already exists
    $db->query("SELECT * FROM users WHERE username = :username OR email = :email");
    $db->bind(":username", $username);
    $db->bind(":email", $email);
    $user = $db->single();

    // Check for query errors or existing user
    if ($user === false) {
        $errors[] = $lang['database_error'] ?? 'Database error occurred';
    } elseif ($user) {
        $errors[] = ($lang['username_exists'] ?? 'Username already exists') . ' ' . ($lang['email_exists'] ?? 'or email already exists');
    }

    // If no errors, register the user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $db->query("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, 'user')");
        $db->bind(":username", $username);
        $db->bind(":email", $email);
        $db->bind(":password", $hashed_password);

        if ($db->execute()) {
            $_SESSION['message'] = $lang['register_success'] ?? 'Registration successful';
            $_SESSION['message_type'] = 'success';
            redirect('public/login.php');
        } else {
            $_SESSION['message'] = $lang['error_adding_user'] ?? 'Error adding user';
            $_SESSION['message_type'] = 'danger';
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

<!-- Registration form container -->
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card mt-5">
            <!-- Card header with registration title -->
            <div class="card-header"><?php echo htmlspecialchars($lang['new_account'] ?? 'Create New Account'); ?></div>
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
                <!-- Display validation errors if any -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <!-- Registration form -->
                <form action="<?php echo BASE_URL; ?>/public/register.php" method="POST">
                    <!-- CSRF token for security -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <!-- Username input -->
                    <div class="form-group">
                        <label for="username"><?php echo htmlspecialchars($lang['username'] ?? 'Username'); ?></label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <!-- Email input -->
                    <div class="form-group">
                        <label for="email"><?php echo htmlspecialchars($lang['email'] ?? 'Email'); ?></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <!-- Password input -->
                    <div class="form-group">
                        <label for="password"><?php echo htmlspecialchars($lang['password'] ?? 'Password'); ?></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <!-- Confirm password input -->
                    <div class="form-group">
                        <label for="confirm_password"><?php echo htmlspecialchars($lang['confirm_password'] ?? 'Confirm Password'); ?></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    <!-- Submit button -->
                    <button type="submit" class="btn btn-primary"><?php echo htmlspecialchars($lang['register'] ?? 'Register'); ?></button>
                </form>
                <!-- Link to login page -->
                <p class="mt-3">
                    <?php echo htmlspecialchars($lang['already_have_account'] ?? 'Already have an account?'); ?>
                    <a href="<?php echo BASE_URL; ?>/public/login.php"><?php echo htmlspecialchars($lang['login'] ?? 'Login'); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Include footer template -->
<?php include ROOT_PATH . 'templates/footer.php'; ?>