<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

if (!isset($_SESSION['user_email'])) {
    header("Location: ../login/userloginPage.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['user_email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $number = $_POST['number'];

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, number = ? WHERE email = ?");
    $stmt->bind_param("ssss", $first_name, $last_name, $number, $email);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to update profile.";
    }
    
    $stmt->close();
    $conn->close();

    header("Location: ../../index.php");
    exit();
}
?>