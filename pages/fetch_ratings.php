<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

if (isset($_GET['fixer_id'])) {
    $fixerId = intval($_GET['fixer_id']);
    $stmt = $conn->prepare("SELECT rating, comment, created_at FROM ratings_comments WHERE fixer_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $fixerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $ratings = [];
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
    }

    echo json_encode($ratings);
    $stmt->close();
}
?>
