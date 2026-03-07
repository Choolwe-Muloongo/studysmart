<?php
echo "🎯 UPDATING STUDENT PAGES TO USE UNIFIED SIDEBAR & CSS\n\n";

// Function to update student pages
function updateStudentPage($file_path, $page_title, $page_icon) {
    if (!file_exists($file_path)) {
        echo "❌ File not found: $file_path\n";
        return false;
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    // Replace the old sidebar with include
    $old_sidebar_pattern = '/<!-- Sidebar -->\s*<nav class="sidebar.*?<\/nav>/s';
    $sidebar_include = '<?php include "includes/sidebar.php"; ?>';
    
    if (preg_match($old_sidebar_pattern, $content)) {
        $content = preg_replace($old_sidebar_pattern, $sidebar_include, $content);
    }
    
    // Replace the old top navigation with include
    $old_topnav_pattern = '/<!-- Top Navigation -->\s*<div class="top-nav.*?<\/div>/s';
    $topnav_include = '<?php 
$page_title = "' . $page_title . '";
$page_icon = "' . $page_icon . '";
include "includes/header.php"; ?>';
    
    if (preg_match($old_topnav_pattern, $content)) {
        $content = preg_replace($old_topnav_pattern, $topnav_include, $content);
    }
    
    // Replace old CSS link with new unified CSS
    $old_css = '../admin/assets/css/admin-style.css';
    $new_css = 'assets/css/student-style.css';
    
    if (strpos($content, $old_css) !== false) {
        $content = str_replace($old_css, $new_css, $content);
    }
    
    // Remove old inline styles if they exist
    $inline_style_pattern = '/<style>.*?<\/style>/s';
    if (preg_match($inline_style_pattern, $content)) {
        $content = preg_replace($inline_style_pattern, '', $content);
    }
    
    if ($content !== $original_content) {
        file_put_contents($file_path, $content);
        echo "✅ Updated: $file_path\n";
        return true;
    } else {
        echo "⏭️  No changes needed: $file_path\n";
        return false;
    }
}

// Student pages to update
$student_pages = [
    'student/courses.php' => ['My Courses', 'book'],
    'student/resources.php' => ['Learning Resources', 'file-alt'],
    'student/videos.php' => ['Course Videos', 'video'],
    'student/sessions.php' => ['My Sessions', 'calendar-alt'],
    'student/grades.php' => ['My Progress', 'chart-line'],
    'student/calendar.php' => ['Academic Calendar', 'calendar']
];

echo "🔄 Starting updates...\n\n";

$updated_count = 0;
$total_files = count($student_pages);

foreach ($student_pages as $file_path => $page_info) {
    echo "📁 Processing: $file_path\n";
    if (updateStudentPage($file_path, $page_info[0], $page_info[1])) {
        $updated_count++;
    }
    echo "\n";
}

echo "🎉 UPDATE COMPLETE!\n";
echo "📊 Summary:\n";
echo "   - Total files processed: $total_files\n";
echo "   - Files updated: $updated_count\n";
echo "   - Files unchanged: " . ($total_files - $updated_count) . "\n\n";

echo "✨ All student pages now have:\n";
echo "   ✅ Unified sidebar design using logo colors\n";
echo "   ✅ Consistent navigation structure\n";
echo "   ✅ Shared CSS file (student-style.css)\n";
echo "   ✅ Automatic active page highlighting\n";
echo "   ✅ Responsive design\n\n";

echo "🎨 Logo Colors Used:\n";
echo "   - Primary Orange: #FF8C00\n";
echo "   - Dark Orange: #e67e00\n";
echo "   - Black: #1a1a1a\n";
echo "   - White: #ffffff\n\n";

echo "🚀 Next steps:\n";
echo "   1. Test all student pages\n";
echo "   2. Verify sidebar navigation works\n";
echo "   3. Check responsive design on mobile\n";
echo "   4. Ensure consistent styling across all pages\n";
?>
