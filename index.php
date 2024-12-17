<?php
session_start(); // Start the session



// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    // If not logged in, redirect to the user login page
    header("Location: /quickfix-php/pages/login/userloginPage.php");
    exit();
}

// If logged in, proceed to load the requested page or homepage
$page = isset($_GET['page']) ? $_GET['page'] : 'homepage'; // Default to 'homepage' if no page is specified

// Include the requested page, ensuring the correct path
$pagePath = 'pages/' . $page . '.php'; 

// Special handling for account page
if ($page === 'account') {
    $pagePath = 'pages/account.php';
}else if($page === 'contacts') {
    $pagePath = 'pages/contacts.php';
} else if ($page === 'about') {
    $pagePath = 'pages/about.php';
} else if ($page === 'bookings') {
    $pagePath = 'pages/customerbookings.php';
}else if ($page === '') {
    $pagePath = 'pages/homepage.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickFix</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/homepage.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="functions.js" type="text/javascript"></script>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">QuickFix</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
            <li class="nav-item">
                    <a href="?page=homepage" class="nav-link btn btn-link">Homepage</a>
                </li>
                <li class="nav-item">
                    <a href="?page=bookings" class="nav-link btn btn-link">Bookings</a>
                </li>
                <li class="nav-item">
                    <a href="?page=about" class="nav-link btn btn-link">About</a>
                </li>
                <li class="nav-item">
                    <a href="?page=contacts" class="nav-link btn btn-link">Contacts</a>
                </li>
                <li class="nav-item">
    <a href="?page=account" class="nav-link btn btn-link">Account</a>
</li>
            </ul>
        </div>
    </nav>
</header>

    <main id="view-panel">
        <?php 
        // Include the requested page, check if the file exists first
        if (file_exists($pagePath)) {
            include $pagePath; // Include the requested page
        } else {
            echo "<p>Page not found.</p>"; // Handle the case where the page does not exist
        }
        ?>
    </main>
</body>
</html>