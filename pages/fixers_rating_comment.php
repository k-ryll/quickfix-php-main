<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handymen's Directory</title>
</head>
<body>
    <main class="container-fluid">
        <div class="fixers-list container-fluid center d-grid">
            <div class="row">
                <?php foreach ($fixers as $fixer): ?>
                    <div class="col mb-4">
                        <div class="fixer-card">
                            <h3><?php echo htmlspecialchars($fixer['name']); ?></h3>
                            <p>Rating: <?php echo htmlspecialchars($fixer['rating']); ?> / 5</p>
                            <p><?php echo htmlspecialchars($fixer['about']); ?></p>
                            <button class="btn btn-primary view-profile" 
                                    data-id="<?php echo $fixer['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($fixer['name']); ?>" 
                                    data-rating="<?php echo htmlspecialchars($fixer['rating']); ?>">
                                View Profile
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="fixerModal" tabindex="-1" aria-labelledby="fixerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fixerModalLabel">Handyman Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="modalContent"></div>
                        <div class="rating-comment-section mt-4">
                            <form id="ratingForm">
                                <h4>Leave a Rating and Comment</h4>
                                <input type="hidden" name="fixer_id" id="ratingFixerId">
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Rating</label>
                                    <select class="form-select" id="rating" name="rating" required>
                                        <option value="">Choose a rating</option>
                                        <option value="1">1 - Poor</option>
                                        <option value="2">2 - Fair</option>
                                        <option value="3">3 - Good</option>
                                        <option value="4">4 - Very Good</option>
                                        <option value="5">5 - Excellent</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Comment</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                            <div id="ratingsCommentsList" class="mt-4">
                                <h5>Recent Ratings and Comments</h5>
                                <ul id="ratingsCommentsContainer"></ul>
                            </div>
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
            button.addEventListener('click', async function () {
                const fixerId = this.getAttribute('data-id');
                document.getElementById('ratingFixerId').value = fixerId;

                // Fetch existing ratings and comments
                const response = await fetch(`/quickfix-php/pages/fetch_ratings.php?fixer_id=${fixerId}`);
                const data = await response.json();
                const ratingsCommentsContainer = document.getElementById('ratingsCommentsContainer');
                ratingsCommentsContainer.innerHTML = '';

                data.forEach(item => {
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${item.rating} / 5</strong>: ${item.comment}`;
                    ratingsCommentsContainer.appendChild(li);
                });

                const fixerModal = new bootstrap.Modal(document.getElementById('fixerModal'));
                fixerModal.show();
            });
        });

        // Handle form submission for rating and comment
        const ratingForm = document.getElementById('ratingForm');
        ratingForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(ratingForm);

            try {
                const response = await fetch('/quickfix-php/pages/submit_rating.php', {
                    method: 'POST',
                    body: formData,
                });

                const result = await response.json();
                if (result.status === 'success') {
                    alert('Rating submitted successfully!');
                    location.reload();
                } else {
                    alert('Failed to submit rating: ' + result.message);
                }
            } catch (error) {
                alert('An error occurred while submitting your rating.');
            }
        });
    });
    </script>
</body>
</html>
