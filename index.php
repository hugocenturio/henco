<?php
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}
require_once 'config/config.php';

$message = '';
$message_class = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Connect to the database
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_error) {
        $message = 'Could not connect to the database. Please try again later.';
        $message_class = 'error';
    } else {
        // Verify if the user exists
        $stmt = $mysqli->prepare('SELECT user_id, username, password, is_active, role_id FROM users WHERE email = ?');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $username, $hashed_password, $is_active, $role_id);
                $stmt->fetch();
                if ($is_active && password_verify($password, $hashed_password)) {
                    // Prevent session fixation: regenerate ID on login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role_id'] = $role_id;
                    header('Location: dashboard.php');
                    exit();
                } else {
                    // Generic message to prevent email/account enumeration
                    $message = 'Incorrect email or password.';
                    $message_class = 'error';
                }
            } else {
                $message = 'Incorrect email or password.';
                $message_class = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Error preparing the query. Please try again.';
            $message_class = 'error';
        }
        $mysqli->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - System</title>
    <link rel="icon" type="image/png" sizes="16x16" href="favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .login-left {
            background-color: #7571f9; /* Customize as needed */
            color: #ffffff;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-right {
            padding: 4rem;
        }
        .brand-title {
            font-size: 3rem;
            font-weight: bold;
        }
        .login-image {
            width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="h-100">
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Left Section -->
            <div class="col-md-6 d-none d-md-flex login-left">
                <div>
                    <h1 class="brand-title text-white">Welcome to Henco!</h1>
                    <p>Your trusted partner in managing field worker orders efficiently.</p>
                </div>
            </div>
            <!-- Right Section -->
            <div class="col-md-6 login-right">
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="w-100" style="max-width: 400px;">
                        <h2 class="text-center mb-4">Login</h2>
                        <?php if (!empty($message)) : ?>
                            <div class="alert <?php echo $message_class == 'error' ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="" class="mt-3">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="text-center mt-3">
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.querySelector('.alert');
            if (notification) {
                setTimeout(() => {
                    const alert = new bootstrap.Alert(notification);
                    alert.close();
                }, 5000);
            }
        });
    </script>
</body>
</html>

