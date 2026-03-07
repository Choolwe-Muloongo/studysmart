<!-- Unified Student Sidebar Template -->
<nav class="sidebar student-sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-brand">
            <i class="fas fa-graduation-cap"></i>
            <span>StudySmart</span>
        </a>
        <small class="text-white-50 d-block mt-2" style="position: relative; z-index: 2;">Student Portal</small>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="courses.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span>My Courses</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="resources.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'resources.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Resources</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="videos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'videos.php' ? 'active' : ''; ?>">
                <i class="fas fa-video"></i>
                <span>Videos</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="sessions.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sessions.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <span>Sessions</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="grades.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'grades.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Progress</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="calendar.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar"></i>
                <span>Calendar</span>
            </a>
        </div>
        
        <div class="nav-item mt-4">
            <a href="../logout.php" class="nav-link logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>
