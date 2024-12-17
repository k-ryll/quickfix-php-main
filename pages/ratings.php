<?php
// Ensure the session is started
session_start();

// Get the logged-in user's ID
$user_id = $_SESSION['user_id']; // Replace with your actual session logic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickFix Ratings</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
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

    <!-- Ratings Section -->
    <main class="container mt-5">
        <h4 class="mb-4">Fixer Ratings</h4>
        <div id="ratingsContainer" class="row g-3">
            <!-- Existing ratings will be loaded here -->
        </div>
    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Fetch Ratings Script -->
    <script>
        // Pass PHP session variable to JavaScript
        const fixerId = <?php echo json_encode($user_id); ?>;

        // Fetch existing ratings
        const fetchRatings = async () => {
            try {
                if (!fixerId) {
                    console.error("Fixer ID is not defined.");
                    document.getElementById('ratingsContainer').innerHTML = `
                        <div class="col-12 text-center text-danger">
                            <p>Error: Fixer ID is missing.</p>
                        </div>`;
                    return;
                }

                const response = await fetch(`/quickfix-php/pages/get_fixer_ratings.php?fixer_id=${encodeURIComponent(fixerId)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const ratings = await response.json();
                const ratingsContainer = document.getElementById('ratingsContainer');

                if (ratings && ratings.length > 0) {
                    ratingsContainer.innerHTML = ratings.map(rating => `
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <strong>${sanitizeHTML(rating.user_name)}</strong>
                                        <span class="text-warning">
                                            ${'★'.repeat(rating.rating)}${'☆'.repeat(5 - rating.rating)}
                                        </span>
                                    </div>
                                    ${rating.comment ? `<p class="mt-2 text-muted">${sanitizeHTML(rating.comment)}</p>` : ''}
                                    <small class="text-muted">${sanitizeHTML(rating.created_at)}</small>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    ratingsContainer.innerHTML = `
                        <div class="col-12 text-center">
                            <p class="text-muted">No ratings yet.</p>
                        </div>`;
                }
            } catch (error) {
                console.error('Error fetching ratings:', error);
                document.getElementById('ratingsContainer').innerHTML = `
                    <div class="col-12 text-center text-danger">
                        <p>Error loading ratings.</p>
                    </div>`;
            }
        };

        // Simple sanitization function to prevent XSS
        const sanitizeHTML = (str) => {
            const temp = document.createElement('div');
            temp.textContent = str;
            return temp.innerHTML;
        };

        // Fetch ratings on page load
        fetchRatings();
    </script>
</body>
</html>
