<?php
function submitRatingComment($fixer_id, $user_id, $rating, $comment, $conn) {
    // Validate inputs
    if ($rating < 1 || $rating > 5) {
        return ['success' => false, 'message' => 'Rating must be between 1 and 5.'];
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO ratings_comments (fixer_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $fixer_id, $user_id, $rating, $comment);

    // Execute the statement and check for success
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Rating and comment submitted successfully.'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Error submitting rating and comment: ' . $stmt->error];
    }
}

function getRatingsComments($fixer_id, $conn) {
    $stmt = $conn->prepare("SELECT rc.rating, rc.comment, rc.created_at, u.first_name, u.last_name 
                             FROM ratings_comments rc 
                             JOIN users u ON rc.user_id = u.id 
                             WHERE rc.fixer_id = ? 
                             ORDER BY rc.created_at DESC");
    $stmt->bind_param("i", $fixer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $ratings_comments = [];
    while ($row = $result->fetch_assoc()) {
        $ratings_comments[] = $row;
    }

    $stmt->close();
    return $ratings_comments;
}

function getAverageRating($fixer_id, $conn) {
    $stmt = $conn->prepare("SELECT AVG(rating) as average_rating FROM ratings_comments WHERE fixer_id = ?");
    $stmt->bind_param("i", $fixer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $average_rating = $row['average_rating'] ? round($row['average_rating'], 1) : 0; // Round to 1 decimal place
    $stmt->close();
    return $average_rating;
}

?>