<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/functions.php'; // Include the functions file
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the user login page
    header("Location: /quickfix-php/pages/login/userloginPage.php");
    exit();
}


// Get the logged-in user's ID
$user_id = $_SESSION['user_id']; // Replace with actual user ID logic

// Handle POST request to save profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle skill updates
    if ((isset($_POST['skills']) && is_array($_POST['skills'])) || !empty($_POST['custom_skill'])) {
        // Start with existing skills from checkboxes
        $skills = isset($_POST['skills']) ? $_POST['skills'] : [];
        
        // Add custom skill if provided
        if (!empty($_POST['custom_skill'])) {
            // Trim and sanitize the custom skill
            $custom_skill = trim(htmlspecialchars($_POST['custom_skill']));
            if (!empty($custom_skill)) {
                $skills[] = $custom_skill;
            }
        }
        
        // Remove duplicates
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
        
        // Ensure the upload directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $user_id . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Handle file upload
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            // Update profile image path in database
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

// Decode the skills JSON
$saved_skills = json_decode($profile['skills'], true) ?? [];

// Fetch the user's email
$fixer_id = $profile['id'];
$status = 'confirmed';
// Fetch user's certificates
$stmt = $conn->prepare("SELECT id, certificate_img, created_at FROM certifications WHERE fixer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$certificates = $result->fetch_all(MYSQLI_ASSOC);
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
    
    // Ensure the upload directory exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $file_extension = pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION);
    $new_filename = time() . '-'.'certificate_' . $user_id . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    // Validate the file type (e.g., only allow images and PDFs)
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($file_extension, $allowed_types)) {
        $message = "Invalid file type. Only JPG, PNG, and PDF files are allowed.";
    } elseif (move_uploaded_file($_FILES['certificate']['tmp_name'], $target_file)) {
        // Insert certificate details into the certifications table
        $certificate_img_path = './assets/certificates/' . $new_filename;
        $stmt = $conn->prepare("
            INSERT INTO certifications (fixer_id, certificate_img)
            VALUES (?, ?)
        ");
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Fix Handyman Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/profile.css">
    <style>
        .booking-card {
            transition: transform 0.3s;
        }
        .booking-card:hover {
            transform: scale(1.02);
        }
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-completed {
            background-color: #28a745;
            color: white;
        }</style>
</head>
<body>
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <form id="profilePicForm" method="POST" enctype="multipart/form-data">
                        <img src=".<?php echo htmlspecialchars($profile['profile_img'] ?: '../assets/profile.jpg'); ?>" 
                             class="card-img-top profile-img" alt="Handyman Profile Image">
                        <input type="file" id="profilePicInput" name="profile_pic" class="d-none" accept="image/*">
                        <button type="button" class="btn btn-secondary btn-block" onclick="document.getElementById('profilePicInput').click()">
                            Change Profile Picture
                        </button>
                    </form>
                    <div class="card-body">
                        <h5 class="card-title titles"><?php echo htmlspecialchars($profile['name']); ?></h5>
                        <p class="card-text">Rating: <?php echo number_format((float)$profile['rating'], 1); ?>/5</p>
                        <p class="card-text">Location: <?php echo htmlspecialchars($profile['location'] ?: 'Not provided'); ?></p>
                    </div>
                </div>

                
    <div class="container-fluid mt-4">
        <div class="row">
           
    

        </div>
    </div>

    


            </div>

            <div class="col-md-8">
                <form id="profileDetailsForm" method="POST">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Professional Details</h2>
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control user-editable" 
                                       value="<?php echo htmlspecialchars($profile['name']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="tel" name="phone" class="form-control user-editable" 
                                       value="<?php echo htmlspecialchars($profile['phone'] ?: 'Not provided'); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Mobile</label>
                                <input type="tel" name="mobile" class="form-control user-editable" 
                                       value="<?php echo htmlspecialchars($profile['mobile'] ?: 'Not provided'); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" class="form-control user-editable" 
                                       value="<?php echo htmlspecialchars($profile['address'] ?: 'Not provided'); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control user-editable" 
                                       value="<?php echo htmlspecialchars($profile['location'] ?: 'Not provided'); ?>" readonly>
                            </div>
                            <button type="button" class="btn btn-secondary" onclick="toggleEdit()">Edit Details</button>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h2 class="card-title">About Me</h2>
                            <textarea name="about" class="form-control user-editable" rows="3" readonly><?php 
                                echo htmlspecialchars($profile['about'] ?: 'No description provided'); 
                            ?></textarea>
                            <button type="button" class="btn btn-secondary mt-2" onclick="toggleEdit()">Edit About Me</button>
                        </div>
                    </div>
                </form>

                <div class="card mt-4">
                    <div class="card-body">
                        <h2 class="card-title">Skills</h2>
                        <ul class="list-group mb-3">
                            <?php foreach ($saved_skills as $skill): ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($skill); ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <form id="skillsForm" method="POST" action="">
                            <h5>Select Skills:</h5>
                            <?php
                            $available_skills = [
                                "Electrical Services",
                                "Furniture Assembly",
                                "Door & Lock Installation",
                                "Plumbing Services",
                                "Air Conditioning Services",
                                "Carpentry Services",
                                "Painting Services",
                                "Appliance Repair",
                                "General Repairs",
                                "Flooring Services", 
                                "Outdoor Maintenance",
                                "Cleaning Services"
                            ];
                            foreach ($available_skills as $available_skill): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skills[]" 
                                           value="<?php echo $available_skill; ?>" 
                                           id="<?php echo strtolower(str_replace(' ', '_', $available_skill)); ?>">
                                    <label class="form-check-label" for="<?php echo strtolower(str_replace(' ', '_', $available_skill)); ?>">
                                        <?php echo $available_skill; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <!-- New custom skill input -->
            <div class="form-group mt-3">
                <label for="customSkill">Add a Custom Skill</label>
                <input type="text" class="form-control" id="customSkill" name="custom_skill" 
                       placeholder="Enter a skill not in the list">
            </div>
            
            <button type="submit" class="btn btn-success mt-2">Save Skills</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // Pre-check the skills that are already saved
        document.addEventListener("DOMContentLoaded", () => {
            const savedSkills = <?php echo json_encode($saved_skills); ?>;
            document.querySelectorAll('input[name="skills[]"]').forEach((checkbox) => {
                if (savedSkills.includes(checkbox.value)) {
                    checkbox.checked = true;
                }
            });

            // Handle profile picture upload
            document.getElementById('profilePicInput').addEventListener('change', function(event) {
                const form = document.getElementById('profilePicForm');
                const formData = new FormData(form);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    location.reload(); // Reload to show the new profile picture
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error uploading profile picture');
                });
            });
        });

        function toggleEdit() {
            const editableInputs = document.querySelectorAll('.user-editable');
            const saveButton = document.querySelector('.btn-secondary');
            
            if (editableInputs[0].readOnly) {
                // Enable editing
                editableInputs.forEach(input => input.readOnly = false);
                saveButton.textContent = 'Save Changes';
                saveButton.classList.remove('btn-secondary');
                saveButton.classList.add('btn-primary');
            } else {
                // Save changes
                const form = document.getElementById('profileDetailsForm');
                const formData = new FormData(form);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(result => {
                    location.reload(); // Reload to show updated details
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating profile');
                });
            }
        }

      
    </script>
</body>
</html>