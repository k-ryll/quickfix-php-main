<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $name = $data['name'];
    $email = $data['email'];
    $phone = $data['phone'];
    $mobile = $data['mobile'];
    $address = $data['address'];

    $stmt = $conn->prepare("UPDATE fixers SET name = ?, email = ?, phone = ?, mobile = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $mobile, $address, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database update failed.']);
    }

    $stmt->close();
}
$conn->close();
?>