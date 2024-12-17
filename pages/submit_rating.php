<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'You must be logged in to submit a rating.'
    ]);
    exit;
}

// Validate input
$fixer_id = $_POST['fixer_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = $_POST['comment'] ?? null;
$user_id = $_SESSION['user_id'];

// Validate inputs
if (!$fixer_id || !$rating || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid rating or missing fixer.'
    ]);
    exit;
}

try {
    // Check if user has already rated this fixer
    $check_stmt = $conn->prepare("SELECT id FROM ratings_comments WHERE user_id = ? AND fixer_id = ?");
    $check_stmt->bind_param("ii", $user_id, $fixer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing rating
        $stmt = $conn->prepare("UPDATE ratings_comments SET rating = ?, comment = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ? AND fixer_id = ?");
        $stmt->bind_param("isii", $rating, $comment, $user_id, $fixer_id);
    } else {
        // Insert new rating
        $stmt = $conn->prepare("INSERT INTO ratings_comments (fixer_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iis", $fixer_id, $user_id, $rating, $comment);
    }

    // Execute the statement
    $stmt->execute();

    // Recalculate average rating for the fixer
    $avg_rating_stmt = $conn->prepare("
        UPDATE fixers 
        SET rating = (
            SELECT ROUND(AVG(rating), 1) 
            FROM ratings_comments 
            WHERE fixer_id = ?
        ) 
        WHERE id = ?
    ");
    $avg_rating_stmt->bind_param("ii", $fixer_id, $fixer_id);
    $avg_rating_stmt->execute();

    echo json_encode([
        'status' => 'success', 
        'message' => 'Rating submitted successfully!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error submitting rating: ' . $e->getMessage()
    ]);
}