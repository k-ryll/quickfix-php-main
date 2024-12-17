<?php
// Place all PHP logic at the top
$error_message = '';
$success_message = '';

require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    try {
        // Create a connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Sanitize and validate inputs
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $first_name = htmlspecialchars(trim($_POST['first_name']));
        $last_name = htmlspecialchars(trim($_POST['last_name']));
        $number = htmlspecialchars(trim($_POST['number']));
        
        // Basic validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if the email already exists
        $check_email_query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("This email is already registered. Please use another email.");
        }

       // Hash password
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Insert the new user
$insert_query = "INSERT INTO users (email, first_name, last_name, number, password, createdAt) VALUES (?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("sssss", $email, $first_name, $last_name, $number, $password);

if ($stmt->execute()) {
    include 'classes/session.php';
    $_SESSION['user_email'] = $email;
    $_SESSION['user_type'] = 'customer';
    
    // Redirect to homepage
    header("Location: ../../index.php");
    exit();
} else {
    throw new Exception("Error creating account: " . $stmt->error);
}
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../styles/signupPage.css">
    <title>QuickFix Sign Up</title>
</head>
<body>
    <?php 
    include $_SERVER['DOCUMENT_ROOT'] .'/quickfix-php/components/nav.php'; ?>

    <div class="signup-container">
        <div class="signup-panel">
            <h1>Create your <br> User account</h1>
            
            <?php if ($error_message): ?>
                <div class="error-message">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <form class="signup-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email Address" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                <div class="name">
                    <input type="text" name="first_name" placeholder="First Name" 
                           value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" 
                           required>
                    <input type="text" name="last_name" placeholder="Last Name" 
                           value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" 
                           required>
                </div>
                <div class="form-group">
                    <input type="tel" name="number" placeholder="Mobile Number" 
                           pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number"
                           value="<?php echo isset($_POST['number']) ? htmlspecialchars($_POST['number']) : ''; ?>" 
                           required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Create a Password" 
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" 
                           title="Password must be at least 8 characters long and include both letters and numbers"
                           required>
                </div>
                <button type="submit">Create Account</button>
                <p class="login-link">
                    Already have an account? <a href="../login/userloginPage.php">Login here</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>