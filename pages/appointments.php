<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/functions.php'; // Include the functions file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /quickfix-php/pages/login/userloginPage.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Replace with actual user ID logic

// Handle POST request to save profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle skill updates
    if ((isset($_POST['skills']) && is_array($_POST['skills'])) || !empty($_POST['custom_skill'])) {
        $skills = isset($_POST['skills']) ? $_POST['skills'] : [];
        if (!empty($_POST['custom_skill'])) {
            $custom_skill = trim(htmlspecialchars($_POST['custom_skill']));
            if (!empty($custom_skill)) {
                $skills[] = $custom_skill;
            }
        }
        $skills = array_unique($skills);
        $skills_json = json_encode($skills);

        $stmt = $conn->prepare("UPDATE fixers SET skills = ? WHERE id = ?");
        $stmt->bind_param("si", $skills_json, $user_id);

        if ($stmt->execute()) {
            $message = "Skills updated successfully!";
        } else {
            $message = "Error updating skills: " . $conn->error;
        }
        $stmt->close();
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_pic'])) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/assets/uploads/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user_id . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $profile_img_path = './assets/uploads/' . $new_filename;
            $stmt = $conn->prepare("UPDATE fixers SET profile_img = ? WHERE id = ?");
            $stmt->bind_param("si", $profile_img_path, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Handle profile details update
    if (isset($_POST['name']) || isset($_POST['about'])) {
        $name = $_POST['name'] ?? '';
        $about = $_POST['about'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $mobile = $_POST['mobile'] ?? '';
        $address = $_POST['address'] ?? '';
        $location = $_POST['location'] ?? '';

        $stmt = $conn->prepare("UPDATE fixers SET name = ?, about = ?, phone = ?, mobile = ?, address = ?, location = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $name, $about, $phone, $mobile, $address, $location, $user_id);

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch the updated user profile data
$stmt = $conn->prepare("SELECT id, name, email, phone, mobile, address, location, about, rating, profile_img, skills FROM fixers WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

$saved_skills = json_decode($profile['skills'], true) ?? [];
$fixer_id = $profile['id'];
$status = 'confirmed';

// Fetch user's certificates
$stmt = $conn->prepare("SELECT id, certificate_img, created_at FROM certifications WHERE fixer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$certificates = $result-> fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch appointments for the logged-in user
$stmt = $conn->prepare("SELECT * FROM appointments WHERE fixer_id = ? AND status = ?");
$stmt->bind_param("ss", $fixer_id, $status);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle certificate upload
if (isset($_FILES['certificate'])) {
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/assets/certificates/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION);
    $new_filename = time() . '-certificate_' . $user_id . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($file_extension, $allowed_types)) {
        $message = "Invalid file type. Only JPG, PNG, and PDF files are allowed.";
    } elseif (move_uploaded_file($_FILES['certificate']['tmp_name'], $target_file)) {
        $certificate_img_path = './assets/certificates/' . $new_filename;
        $stmt = $conn->prepare("INSERT INTO certifications (fixer_id, certificate_img) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $certificate_img_path);

        if ($stmt->execute()) {
            $message = "Certificate uploaded successfully!";
        } else {
            $message = "Error uploading certificate: " . $conn->error;
        }
        $stmt->close();
        $conn->close();
    } else {
        $message = "Error moving the uploaded file.";
    }
}

?>
 <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
 <header class="bg-light text-dark p-3 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">QuickFix</h1>
            <nav class="d-flex gap-2">
                <a href="fixerProfile.php" class="btn btn-outline-primary btn-sm">Home Page</a>
                <a href="certificates.php" class="btn btn-outline-secondary btn-sm">Gallery</a>
                <a href="appointments.php" class="btn btn-outline-secondary btn-sm">Appointments</a>
                <a href="bookinglist.php" class="btn btn-outline-secondary btn-sm">Booking List</a>
                <a href="ratings.php" class="btn btn-outline-secondary btn-sm">Ratings</a>
                <a href="/quickfix-php/pages/logout.php" class="btn btn-danger btn-sm">Log Out</a>
            </nav>
        </div>
    </header>
<div class="card container">
    <div class="card-body">
        <h2 class="card-title">My Appointments</h2>
        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">
                You have no appointments at the moment.
            </div>
        <?php else: ?>
            <div class="col container-fluid">
                <?php foreach ($appointments as $appointment): ?>
                    <div class="mb-2">
                        <div class="card booking-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    Appointment with: <?php echo htmlspecialchars($appointment['customer_name']); ?>
                                </h5>
                                <p class="card-text">
                                    <strong>Date:</strong> <?php echo date('F j, Y', strtotime($appointment['date'])); ?><br>
                                    <strong>Time:</strong> <?php echo date('h:i A', strtotime($appointment['time'])); ?><br>
                                    <strong>Details:</strong> <?php echo htmlspecialchars($appointment['details']); ?>
                                </p>
                                <small class="text-muted">
                                    Booked on: <?php echo date('F j, Y', strtotime($appointment['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>