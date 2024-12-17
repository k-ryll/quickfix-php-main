<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: ../login/userloginPage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['user_email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    // Validate new password
    if ($new_password !== $confirm_new_password) {
        $_SESSION['error_message'] = "New passwords do not match.";
        header("Location: ../pages/account.php");
        exit();
    }

    // Fetch current password from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error_message'] = "Current password is incorrect.";
        header("Location: ../pages/account.php");
        exit();
    }

    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_password_hash, $email);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Password changed successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to change password.";
    }

    $stmt->close();
    $conn->close();

    header("Location: ../pages/account.php");
    exit();
}
?>