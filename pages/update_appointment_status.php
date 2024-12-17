<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

$response = ['status' => 'error', 'message' => 'Failed to update status.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['id'];
    $status = $data['status'];

    if (in_array($status, ['pending', 'confirmed', 'declined'])) {
        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $appointment_id);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Appointment status updated successfully.';
        }
        $stmt->close();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
