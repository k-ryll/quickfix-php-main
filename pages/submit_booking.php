<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

$response = ['status' => 'error', 'message' => 'Something went wrong.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fixer_id = $_POST['fixer_id'];
    $customer_id = $_POST['customer_id']; // New field
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $details = $_POST['details'];

    $stmt = $conn->prepare("INSERT INTO appointments 
        (fixer_id, customer_id, customer_name, customer_email, date, time, details, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

    if ($stmt) {
        $stmt->bind_param("iisssss", 
            $fixer_id, 
            $customer_id, 
            $customer_name, 
            $customer_email, 
            $date, 
            $time, 
            $details
        );

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Your appointment has been submitted and is pending confirmation.';
        } else {
            $response['message'] = 'Failed to create appointment.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Database error: ' . $conn->error;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
