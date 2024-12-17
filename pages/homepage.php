<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/quickfix-php/config/db.php';


$userId = $_SESSION['user_id'];
// Fetch a list of fixers to display on the homepage
$fixers = [];
$stmt = $conn->prepare("SELECT id, name, email, phone, mobile, address, location, about, rating, profile_img, skills, answer_rate FROM fixers LIMIT 20");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Decode the JSON skills
    $row['skills'] = json_decode($row['skills'], true);
    $fixers[] = $row;
}
$stmt->close();


// Handle search query
$searchResults = [];
if (isset($_GET['query'])) {
    $query = trim($_GET['query']);
    
    // Prepare the search query with multiple skill search variations
    $stmt = $conn->prepare("
        SELECT id, name, email, phone, mobile, address, location, about, rating, profile_img, skills 
        FROM fixers 
        WHERE name LIKE ? 
        OR skills LIKE ? 
        OR skills LIKE ? 
        OR skills LIKE ?
    ");
    
    if ($stmt) {
        $nameSearchTerm = "%" . $query . "%";
        $skillSearchTerm1 = "%\"" . $query . " Services\"%";
        $skillSearchTerm2 = "%\"" . ucfirst($query) . " Services\"%";
        $skillSearchTerm3 = "%\"" . $query . "\"%";
        
        $stmt->bind_param("ssss", 
            $nameSearchTerm, 
            $skillSearchTerm1, 
            $skillSearchTerm2,
            $skillSearchTerm3
        );
        
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            // Decode skills 
            $row['skills'] = !empty($row['skills']) ? json_decode($row['skills'], true) : [];
            $searchResults[] = $row;
        }
        $stmt->close();
    } else {
        die("Search query failed: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handymen's Directory</title>
   
  
</head>
<body>
    <main class="container-fluid">
        <div class="search-bar-container container">
            <div class="search-bar container">
                <form method="GET" action="" class="d-flex ">
                    <input type="text" name="query" class="form-control" placeholder="Search for a fixer by name or skill..." value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>" />
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>

        <div class="fixers-list container-fluid center d-grid">
    <div class="row">
        <?php if (empty($searchResults) && !isset($_GET['query'])): ?>
            <?php if (empty($fixers)): ?>
                <div class="col-12">
                    <p>No fixers available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($fixers as $fixer): ?>
                    <div class="col mb-4"> <!-- Changed here -->
                        <?php include 'fixer-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <?php if (empty($searchResults)): ?>
                <div class="col-12">
                    <p>No results found for "<?php echo htmlspecialchars($_GET['query']); ?>".</p>
                </div>
            <?php else: ?>
                <?php foreach ($searchResults as $fixer): ?>
                    <div class="col mb-4"> <!-- Changed here -->
                        <?php include 'fixer-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


        <!-- Modal -->
        <div class="modal fade" id="fixerModal" tabindex="-1" aria-labelledby="fixerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fixerModalLabel">Fixer Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modalContent">
                            <!-- Fixer information will be dynamically inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        
        document.addEventListener('DOMContentLoaded', function () {
    const viewProfileButtons = document.querySelectorAll('.view-profile');
    const modalContent = document.getElementById('modalContent');

    viewProfileButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            
            const userId = this.getAttribute('user-id');
            const fixerId = this.getAttribute('data-id');
            const fixerName = this.getAttribute('data-name');
            const fixerSkills = JSON.parse(this.getAttribute('data-skills'));
            const fixerAbout = this.getAttribute('data-about');
            const fixerProfileImg = this.getAttribute('data-profile-img');
            const fixerRating = this.getAttribute('data-rating');
            const fixerAnswerRating = this.getAttribute('data-answer');
            const fixerLocation = this.getAttribute('data-location');
            const fixerEmail = this.getAttribute('data-email');
            const fixerPhone = this.getAttribute('data-phone');
            const fixerMobile = this.getAttribute('data-mobile');
            const fixerAddress = this.getAttribute('data-address');

            const skillsHtml = fixerSkills.map(skill => 
                `<span class="skill-tag">${skill}</span>`
            ).join(' ');

            modalContent.innerHTML = `
                
                <div class="text-center">
                    <img src="${fixerProfileImg}" alt="Profile Image" class="profile-picture mb-3">
                    <h3>${fixerName}</h3>
                    <p class="rating">★ ${fixerRating}</p>
                    <p class="rating">Answer Rate: ${fixerAnswerRating} %</p>
                    <p><i class="bi bi-geo-alt"></i> ${fixerLocation}</p>
                    <div class="skills-list justify-content-center mb-3">
                        ${skillsHtml}
                    </div>
                    <p>${fixerAbout}</p>
                    <p><strong>Email:</strong> ${fixerEmail}</p>
                    <p><strong>Phone:</strong> ${fixerPhone}</p>
                    <p><strong>Mobile:</strong> ${fixerMobile}</p>
                    <p><strong>Address:</strong> ${fixerAddress}</p>
                </div>
                <form id="bookingForm" class="mt-4 center">
                    <input type="hidden" name="fixer_id" value="${fixerId}">
                    <input type="hidden" name="customer_id" value="${userId}"> 
                    <div class="mb-3">
                        <label for="customerName" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="customerName" name="customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="customerEmail" class="form-label">Your Email</label>
                        <input type="email" class="form-control" id="customerEmail" name="customer_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Time</label>
                        <input type="time" class="form-control" id="time" name="time" required>
                    </div>
                    <div class="mb-3">
                        <label for="details" class="form-label">Details</label>
                        <textarea class="form-control" id="details" name="details" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <!-- Rating and Comments Section -->
                <div class="ratings-section mt-4">
                    <h4>Rate and Review</h4>
                    <form id="ratingForm">
                        <input type="hidden" name="fixer_id" value="${fixerId}">
                        <div class="star-rating mb-3">
                            <input type="radio" name="rating" value="5" id="star5">
                            <label for="star5">★</label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4">★</label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3">★</label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2">★</label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1">★</label>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Your Review (Optional)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Rating</button>
                    </form>

                    <div id="existingRatings" class="mt-4">
                        <h4>Fixer Ratings</h4>
                        <div id="ratingsContainer">
                            <!-- Existing ratings will be loaded here -->
                        </div>
                    </div>
                </div>
                
                
            `;

            // Add CSS for star rating
            const style = document.createElement('style');
            style.textContent = `
                .star-rating {
                    unicode-bidi: bidi-override;
                    direction: rtl;
                    text-align: left;
                }
                .star-rating input {
                    display: none;
                }
                .star-rating label {
                    display: inline-block;
                    padding: 3px;
                    font-size: 2rem;
                    color: #ddd;
                    cursor: pointer;
                }
                .star-rating input:checked ~ label,
                .star-rating input:checked + label,
                .star-rating label:hover,
                .star-rating label:hover ~ label {
                    color: #ffc107;
                }
            `;
            document.head.appendChild(style);

            // Fetch existing ratings
            const fetchRatings = async () => {
                try {
                    const response = await fetch(`/quickfix-php/pages/get_fixer_ratings.php?fixer_id=${fixerId}`);
                    const ratings = await response.json();
                    
                    const ratingsContainer = document.getElementById('ratingsContainer');
                    if (ratings.length > 0) {
                        ratingsContainer.innerHTML = ratings.map(rating => `
                            <div class="rating-item mb-3 p-2 border rounded">
                                <div class="d-flex justify-content-between">
                                    <strong>${rating.user_name}</strong>
                                    <span class="text-warning">
                                        ${'★'.repeat(rating.rating)}${'☆'.repeat(5 - rating.rating)}
                                    </span>
                                </div>
                                ${rating.comment ? `<p class="mt-2">${rating.comment}</p>` : ''}
                                <small class="text-muted">${rating.created_at}</small>
                            </div>
                        `).join('');
                    } else {
                        ratingsContainer.innerHTML = '<p>No ratings yet.</p>';
                    }
                } catch (error) {
                    console.error('Error fetching ratings:', error);
                    document.getElementById('ratingsContainer').innerHTML = '<p>Error loading ratings.</p>';
                }
            };

            // Submit rating form
            const ratingForm = document.getElementById('ratingForm');
            ratingForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                
                const formData = new FormData(ratingForm);

                try {
                    const response = await fetch('/quickfix-php/pages/submit_rating.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        alert(result.message);
                        fetchRatings(); // Refresh ratings
                        ratingForm.reset();
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('An error occurred while submitting your rating.');
                    console.error('Error:', error);
                }
            });

            // Fetch ratings when modal opens
            fetchRatings();

            const fixerModal = new bootstrap.Modal(document.getElementById('fixerModal'));
            fixerModal.show();

            // Existing booking form submission logic remains the same
            const bookingForm = document.getElementById('bookingForm');
            bookingForm.addEventListener('submit', async function (e) {
                e.preventDefault();
                
                const formData = new FormData(bookingForm);

                try {
                    const response = await fetch('/quickfix-php/pages/submit_booking.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.status === 'success') {
                        alert(result.message);
                        bookingForm.reset();
                        fixerModal.hide();
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    alert('An error occurred while submitting your appointment.');
                    console.error('Error:', error);
                }
            });
        });
    });
});

</script>
</body>
</html>