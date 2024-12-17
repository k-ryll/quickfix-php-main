<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $fixer_id = $_GET['fixer_id'];

    $stmt = $conn->prepare("SELECT ratings_comments.*, users.username 
                            FROM ratings_comments
                            JOIN users ON ratings_comments.user_id = users.userid
                            WHERE fixer_id = ?");
    $stmt->bind_param("i", $fixer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    echo json_encode($reviews);
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
