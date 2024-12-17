<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /quickfix-php/pages/login/userloginPage.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
        $message = $stmt->execute() ? "Skills updated successfully!" : "Error updating skills: " . $conn->error;
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
        $message = $stmt->execute() ? "Profile updated successfully!" : "Error updating profile: " . $conn->error;
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

// Fetch user's certificates
$stmt = $conn->prepare("SELECT id, certificate_img, created_at FROM certifications WHERE fixer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$certificates = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch appointments for the logged-in user
$stmt = $conn->prepare("SELECT * FROM appointments WHERE fixer_id = ?");
$stmt->bind_param("s", $fixer_id);
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
        $message = $stmt->execute() ? "Certificate uploaded successfully!" : "Error uploading certificate: " . $conn->error;
        $stmt->close();
        $conn->close();
    } else {
        $message = "Error moving the uploaded file.";
    }
}
?>

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

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<div class="container mt-4">
    <div class="alert alert-info" role="alert">
        <?php if (isset($message)) echo $message; ?>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title">Upload Past Works and Certificates</h2>
            <form id="certificateUploadForm" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="certificate">Certificate File (JPG, PNG, PDF)</label>
                    <input type="file" id="certificate" name="certificate" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
            <?php if (!empty($profile['certificate_img'])): ?>
                <p class="mt-3">
                    <strong>Uploaded Images:</strong>
                    <a href=".<?php echo htmlspecialchars($profile['certificate_img']); ?>" target="_blank">View Certificate</a>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="card-title">My Certificates and Works</h2>
            <?php if (empty($certificates)): ?>
                <div class="alert alert-info">Nothing uploaded yet.</div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($certificates as $certificate): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <a href=".<?php echo htmlspecialchars($certificate['certificate_img']); ?>" target="_blank">
                                    <img src=".<?php echo htmlspecialchars($certificate['certificate_img']); ?>" class="card-img-top" alt="Certificate">
                                </a>
                                <div class="card-body">
                                    <p class="card-text">
                                        <small class="text-muted">Uploaded on: <?php echo date('F j, Y', strtotime($certificate['created_at'])); ?></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>