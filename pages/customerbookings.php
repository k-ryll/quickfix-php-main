<?php 
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';

// Get the logged-in user's ID (customer)
$customer_id = $_SESSION['user_id']; // Assuming this is set during login

// Fetch bookings for the logged-in customer and get the fixer's name
$stmt = $conn->prepare("
    SELECT 
        appointments.*, 
        fixers.name AS fixer_name 
    FROM 
        appointments 
    JOIN 
        fixers 
    ON 
        appointments.fixer_id = fixers.id 
    WHERE 
        appointments.customer_id = ? 
    ORDER BY 
        appointments.created_at DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
   
    <style>
        .border-pending { border: 3px solid orange !important; }
        .border-confirmed { border: 3px solid green !important; }
        .border-declined { border: 3px solid red !important; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">My Bookings</h1>

        <?php if (empty($appointments)): ?>
            <div class="alert alert-info text-center">
                You have no bookings at the moment.
                <?php echo "Session User ID: " . $_SESSION['user_id'] . "<br>"; ?>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($appointments as $appointment): 
                    // Determine the border class based on the status
                    $borderClass = '';
                    if ($appointment['status'] === 'pending') {
                        $borderClass = 'border-pending';
                    } elseif ($appointment['status'] === 'confirmed') {
                        $borderClass = 'border-confirmed';
                    } elseif ($appointment['status'] === 'declined') {
                        $borderClass = 'border-declined';
                    }
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card <?php echo $borderClass; ?> shadow">
                            <div class="card-body">
                                <h5 class="card-title">Booking with: <strong><?php echo htmlspecialchars($appointment['fixer_name']); ?></strong></h5>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($appointment['date']); ?></p>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($appointment['time']); ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge 
                                        <?php 
                                            if ($appointment['status'] === 'pending') echo 'bg-warning text-dark'; 
                                            elseif ($appointment['status'] === 'confirmed') echo 'bg-success'; 
                                            elseif ($appointment['status'] === 'declined') echo 'bg-danger'; 
                                        ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </p>
                                <p><strong>Details:</strong> <?php echo nl2br(htmlspecialchars($appointment['details'])); ?></p>
                            </div>
                            <div class="card-footer text-muted">
                                Booked on: <?php echo date('F j, Y, g:i a', strtotime($appointment['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
