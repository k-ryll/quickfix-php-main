<?php
// Database connection
require_once 'db_connection.php';

if (isset($_GET['fixer_id'])) {
    $fixerId = $_GET['fixer_id'];

    // Fetch certificates for the fixer
    $sql = "SELECT filename, filepath FROM certificates WHERE fixer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fixerId);
    $stmt->execute();
    $result = $stmt->get_result();

    $certificates = [];
    while ($row = $result->fetch_assoc()) {
        $certificates[] = $row;
    }
    $stmt->close();

    // Return certificates as JSON
    echo json_encode($certificates);
} else {
    echo json_encode([]);
}
?>
