<?php
// Include the database configuration file
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

// Initialize error messages and success flag
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate input data
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $firstName = htmlspecialchars(trim($_POST['first_name']));
    $lastName = htmlspecialchars(trim($_POST['last_name']));
    $mobile = htmlspecialchars(trim($_POST['mobile']));
    $password = trim($_POST['password']);

    // Validate inputs
    if (!$email) $errors[] = "Invalid email address.";
    if (empty($firstName) || empty($lastName)) $errors[] = "First and last name are required.";
    if (empty($mobile) || !is_numeric($mobile)) $errors[] = "Valid mobile number is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";

    // If no errors, proceed with inserting data
    if (empty($errors)) {
        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert data into the fixers table, now including the password
        $sql = "INSERT INTO fixers (name, email, mobile, password, profile_img, about, rating) VALUES (?, ?, ?, ?, '/assets/profile.jpg', '', 0)";
        $stmt = $conn->prepare($sql);
        $fullName = $firstName . ' ' . $lastName;

        if ($stmt) {
            // Bind parameters for the query
            $stmt->bind_param("ssss", $fullName, $email, $mobile, $hashedPassword);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Error inserting data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Error preparing query: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickFix Sign Up</title>
</head>
<body>
<?php 
    include $_SERVER['DOCUMENT_ROOT'] .'/quickfix-php/components/nav.php'; ?>

    <div class="signup-container">
        <div class="signup-panel">
            <h1>Create your <br> Handyman account</h1>
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($success): ?>
                <div class="success-message">
                    <p>Account created successfully! <a href="../login/fixerloginPage.php">Log in here</a>.</p>
                </div>
            <?php endif; ?>
            <form class="signup-form" method="POST" action="">
                <input type="email" name="email" placeholder="Email Address" required>
                <div class="name">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                </div>
                <input type="number" name="mobile" placeholder="Mobile Number" required>
                <input type="password" name="password" placeholder="Create a Password" required>
                <button type="submit">Create</button>
                <h3><a href="../login/fixerloginPage.php">Already have an account?</a></h3>
            </form>
        </div>
    </div>
</body>
</html>
