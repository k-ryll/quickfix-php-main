<link rel="stylesheet" href="styles/card.css">
<?php 
    $userId = $_SESSION['user_id'];
?>
<div class="fixer-profile">
<img src="<?php echo htmlspecialchars(file_exists($fixer['profile_img']) ? $fixer['profile_img'] : './assets/profile.jpg'); ?>" 
     class="profile-picture">
    
    <div class="fixer-info">
        <h3><?php echo htmlspecialchars($fixer['name']); ?></h3>
        <div class="rating mb-2">&#9733; <?php echo number_format($fixer['rating'], 1); ?></div>
        <div class="rating mb-2"> Answer Rate: <?php echo htmlspecialchars($fixer['answer_rate']); ?> %</div>
        <div class="skills-list mb-2">
            <?php if (!empty($fixer['skills'])): ?>
                <?php foreach ($fixer['skills'] as $skill): ?>
                    <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                <?php endforeach; ?>
            <?php else: ?>
                <span class="skill-tag">No skill set</span>
            <?php endif; ?>
        </div>
        <p class="mb-2"><?php echo htmlspecialchars($fixer['about']); ?></p>
        <a href="#" class="view-profile btnn btn-primary" 
           data-id="<?php echo $fixer['id']; ?>" 
           user-id="<?php echo $userId; ?>"
           data-name="<?php echo htmlspecialchars($fixer['name']); ?>" 
           data-skills='<?php echo htmlspecialchars(json_encode($fixer['skills'])); ?>' 
           data-about="<?php echo htmlspecialchars($fixer['about']); ?>" 
           data-profile-img="<?php echo htmlspecialchars($fixer['profile_img']); ?>"
           data-rating="<?php echo number_format($fixer['rating'], 1); ?>"
           data-answer="<?php echo htmlspecialchars($fixer['answer_rate']); ?>"
           data-location="<?php echo htmlspecialchars($fixer['location']); ?>"
           data-email="<?php echo htmlspecialchars($fixer['email']); ?>"
           data-phone="<?php echo htmlspecialchars($fixer['phone']); ?>"
           data-mobile="<?php echo htmlspecialchars($fixer['mobile']); ?>"
           data-address="<?php echo htmlspecialchars($fixer['address']); ?>"> 
           View Profile
           </a>
    </div>
</div>
