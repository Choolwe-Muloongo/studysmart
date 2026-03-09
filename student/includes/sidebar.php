<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar student-sidebar" id="sidebar">
    <div class="sidebar-header">
        <?php
        require_once __DIR__ . '/../../includes/brand_logo.php';
        render_brand_logo([
            'href' => 'dashboard.php',
            'class' => 'sidebar-brand',
            'size' => 'md',
            'logo_path' => '../WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png',
            'alt' => 'StudySmart logo'
        ]);
        ?>
        <small class="text-white-50 d-block mt-2" style="position: relative; z-index: 2;">Student Portal</small>
    </div>

    <div class="sidebar-nav">
        <div class="nav-item"><a href="dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a></div>
        <div class="nav-item"><a href="courses.php" class="nav-link <?php echo $current_page === 'courses.php' ? 'active' : ''; ?>"><i class="fas fa-book"></i><span>My Courses</span></a></div>
        <div class="nav-item"><a href="resources.php" class="nav-link <?php echo $current_page === 'resources.php' ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i><span>Resources</span></a></div>
        <div class="nav-item"><a href="videos.php" class="nav-link <?php echo in_array($current_page, ['videos.php', 'watch_video.php'], true) ? 'active' : ''; ?>"><i class="fas fa-video"></i><span>Videos</span></a></div>
        <div class="nav-item"><a href="music.php" class="nav-link <?php echo $current_page === 'music.php' ? 'active' : ''; ?>"><i class="fas fa-music"></i><span>Music</span></a></div>
        <div class="nav-item"><a href="timetable.php" class="nav-link <?php echo $current_page === 'timetable.php' ? 'active' : ''; ?>"><i class="fas fa-table"></i><span>Timetable</span></a></div>
        <div class="nav-item"><a href="sessions.php" class="nav-link <?php echo $current_page === 'sessions.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i><span>Sessions</span></a></div>
        <div class="nav-item"><a href="calendar.php" class="nav-link <?php echo $current_page === 'calendar.php' ? 'active' : ''; ?>"><i class="fas fa-calendar"></i><span>Calendar</span></a></div>
        <div class="nav-item"><a href="grades.php" class="nav-link <?php echo $current_page === 'grades.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i><span>Grades</span></a></div>
        <div class="nav-item"><a href="download.php" class="nav-link <?php echo $current_page === 'download.php' ? 'active' : ''; ?>"><i class="fas fa-download"></i><span>Downloads</span></a></div>
        <div class="nav-item mt-4"><a href="../logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></div>
    </div>
</nav>

<button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
