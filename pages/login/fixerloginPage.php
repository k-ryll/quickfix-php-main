<?php
// Include database configuration file
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

// Start session
session_start();

// Initialize error array
$errors = [];

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    // Validate input
    if (!$email) $errors[] = "Please enter a valid email address.";
    if (empty($password)) $errors[] = "Password is required.";

    // Check if no errors
    if (empty($errors)) {
        // Query to check user credentials
        $sql = "SELECT id, name, password FROM fixers WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            // Verify user exists
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $name, $hashedPassword);
                $stmt->fetch();

                // Verify password
                if (password_verify($password, $hashedPassword)) {
                    // Set session variables
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_type'] = 'fixer';
                    // Redirect to dashboard or desired page
                    header("Location: ../fixerProfile.php");
                    exit();
                } else {
                    $errors[] = "Incorrect password.";
                }
            } else {
                $errors[] = "No account found with that email.";
            }

            $stmt->close();
        } else {
            $errors[] = "Failed to prepare the statement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>QuickFix Handyman Login</title>
</head>
<body>
<?php 
    include $_SERVER['DOCUMENT_ROOT'] .'/quickfix-php/components/nav.php'; ?>

    <div class="login-container">
        <div class="login-panel">
            <h1>Handyman Login</h1>

            <!-- Display errors -->
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form class="login-form" method="POST" action="">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
                <h3><a href="../signup/fixersignupPage.php">Don't have an account?</a></h3>
            </form>
        </div>
    </div>
</body>
</html>
