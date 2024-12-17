<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

// Get the logged-in user's ID
$user_id = $_SESSION['user_id']; // Replace with actual user ID logic

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if skills are submitted
    if (isset($_POST['skills']) && is_array($_POST['skills'])) {
        $skills = $_POST['skills'];

        // Convert skills array into JSON
        $skills_json = json_encode($skills);

        // Save skills into the database
        $stmt = $conn->prepare("UPDATE fixers SET skills = ? WHERE id = ?");
        $stmt->bind_param("si", $skills_json, $user_id);

        if ($stmt->execute()) {
            echo "Skills updated successfully!";
        } else {
            echo "Error updating skills: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "No skills selected!";
    }
}

$conn->close();
?>
