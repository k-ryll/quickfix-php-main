<?php
// Start a session
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form data when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Fetch the user data
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            
            // Password is correct, start a session for the user
            $_SESSION['user_id'] = $user['userid']; // Store the user's ID
            $_SESSION['user_email'] = $user['email'];

            // Redirect to homepage
            header("Location: ../../index.php");
            exit();
        } else {
            echo "<p>Incorrect password. Please try again.</p>";
        }
    } else {
        echo "<p>Email not found. Please sign up.</p>";
    }

    // Close the statement and connection
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickFix Customer Login</title>
</head>
<body>
<?php 
    include $_SERVER['DOCUMENT_ROOT'] .'/quickfix-php/components/nav.php'; ?>
    <div class="login-container">
        <div class="login-panel">
            <h1>Customer Login</h1>
            
            <!-- Make sure the form submits to itself (userloginPage.php) using POST -->
            <form class="login-form" method="POST" action="userloginPage.php">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
                <h3><a href="../signup/usersignupPage.php">Don't have an account?</a></h3>
            </form>
        </div>
    </div>
</body>
</html>
