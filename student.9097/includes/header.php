<!-- Unified Student Header Template -->
<div class="top-nav">
    <h1>
        <i class="fas fa-<?php echo $page_icon ?? 'graduation-cap'; ?>"></i>
        <?php echo $page_title ?? 'Student Portal'; ?>
    </h1>
    <div class="user-info">
        <div class="user-avatar">
            <?php echo strtoupper(substr($current_user['first_name'], 0, 1)); ?>
        </div>
        <span>Welcome, <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
    </div>
</div>
