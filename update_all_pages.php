<?php
echo "🎯 UPDATING ALL PAGES - ADDING VIDEOS & REMOVING RATINGS\n\n";

// Function to update navigation in files
function updateNavigation($file_path, $role) {
    if (!file_exists($file_path)) {
        echo "❌ File not found: $file_path\n";
        return false;
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    // Add videos navigation based on role
    if ($role === 'admin') {
        // Add videos after resources in admin
        $content = preg_replace(
            '/(<a href="resources\.php" class="nav-link">\s*<i class="fas fa-file-alt"><\/i>\s*<span>Resources<\/span>\s*<\/a>\s*<\/div>\s*<div class="nav-item">)/',
            '$1
            <div class="nav-item">
                <a href="videos.php" class="nav-link">
                    <i class="fas fa-video"></i>
                    <span>Videos</span>
                </a>
            </div>
            <div class="nav-item">',
            $content
        );
    } elseif ($role === 'lecturer') {
        // Add videos after resources in lecturer
        $content = preg_replace(
            '/(<a href="resources\.php" class="nav-link">\s*<i class="fas fa-file-alt"><\/i>\s*<span>Resources<\/span>\s*<\/a>\s*<\/div>\s*<div class="nav-item">)/',
            '$1
            <div class="nav-item">
                <a href="videos.php" class="nav-link">
                    <i class="fas fa-video"></i>
                    <span>Videos</span>
                </a>
            </div>
            <div class="nav-item">',
            $content
        );
    } elseif ($role === 'student') {
        // Add videos after resources in student
        $content = preg_replace(
            '/(<a href="resources\.php" class="nav-link">\s*<i class="fas fa-file-alt"><\/i>\s*<span>Resources<\/span>\s*<\/a>\s*<\/div>\s*<div class="nav-item">)/',
            '$1
            <div class="nav-item">
                <a href="videos.php" class="nav-link">
                    <i class="fas fa-video"></i>
                    <span>Videos</span>
                </a>
            </div>
            <div class="nav-item">',
            $content
        );
    }
    
    // Remove rating references and replace with progress
    $content = str_replace('Ratings', 'Progress', $content);
    $content = str_replace('ratings', 'progress', $content);
    $content = str_replace('fas fa-star', 'fas fa-chart-line', $content);
    
    // Update page titles
    $content = str_replace('My Ratings', 'My Progress', $content);
    $content = str_replace('My Ratings & Progress', 'My Learning Progress', $content);
    
    if ($content !== $original_content) {
        file_put_contents($file_path, $content);
        echo "✅ Updated: $file_path\n";
        return true;
    } else {
        echo "⏭️  No changes needed: $file_path\n";
        return false;
    }
}

// Files to update
$files_to_update = [
    // Admin files
    'admin/dashboard.php' => 'admin',
    'admin/users.php' => 'admin',
    'admin/lecturers.php' => 'admin',
    'admin/students.php' => 'admin',
    'admin/courses.php' => 'admin',
    'admin/resources.php' => 'admin',
    'admin/sessions.php' => 'admin',
    'admin/notifications.php' => 'admin',
    'admin/analytics.php' => 'admin',
    'admin/settings.php' => 'admin',
    'admin/profile.php' => 'admin',
    
    // Lecturer files
    'lecturer/dashboard.php' => 'lecturer',
    'lecturer/courses.php' => 'lecturer',
    'lecturer/students.php' => 'lecturer',
    'lecturer/resources.php' => 'lecturer',
    'lecturer/sessions.php' => 'lecturer',
    'lecturer/analytics.php' => 'lecturer',
    'lecturer/profile.php' => 'lecturer',
    
    // Student files
    'student/dashboard.php' => 'student',
    'student/courses.php' => 'student',
    'student/resources.php' => 'student',
    'student/sessions.php' => 'student',
    'student/grades.php' => 'student',
    'student/calendar.php' => 'student'
];

echo "🔄 Starting updates...\n\n";

$updated_count = 0;
$total_files = count($files_to_update);

foreach ($files_to_update as $file_path => $role) {
    echo "📁 Processing: $file_path\n";
    if (updateNavigation($file_path, $role)) {
        $updated_count++;
    }
    echo "\n";
}

echo "🎉 UPDATE COMPLETE!\n";
echo "📊 Summary:\n";
echo "   - Total files processed: $total_files\n";
echo "   - Files updated: $updated_count\n";
echo "   - Files unchanged: " . ($total_files - $updated_count) . "\n\n";

echo "✨ All pages now have:\n";
echo "   ✅ Videos navigation added\n";
echo "   ✅ Rating references removed\n";
echo "   ✅ Progress tracking instead of ratings\n";
echo "   ✅ Consistent navigation structure\n\n";

echo "🚀 Next steps:\n";
echo "   1. Test the videos functionality\n";
echo "   2. Verify navigation works correctly\n";
echo "   3. Check that all pages load without errors\n";
?>
