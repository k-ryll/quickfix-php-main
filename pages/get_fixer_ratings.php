<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

header('Content-Type: application/json');

// Validate fixer_id
$fixer_id = $_GET['fixer_id'] ?? null;

if (!$fixer_id) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid fixer ID'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT rc.id, rc.comment, rc.rating, rc.created_at, u.first_name AS user_name
FROM ratings_comments rc
JOIN users u ON rc.user_id = u.userid
WHERE rc.fixer_id = ?
ORDER BY rc.created_at DESC;
    ");
    $stmt->bind_param("i", $fixer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $ratings = [];
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }

    echo json_encode($ratings);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Error fetching ratings: ' . $e->getMessage()
    ]);
} ?>