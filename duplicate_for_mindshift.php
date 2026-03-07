<?php
/**
 * Script to duplicate StudySmart project for Mind Shift
 * Changes:
 * - StudySmart -> Mind Shift
 * - Admin: Pink (#FF69B4, #FF1493) and Black
 * - Lecturer: Red (#DC143C, #C41E3A) and Black  
 * - Student: Keep same (Orange)
 * - Main: Pink text, black background
 */

$sourceDir = __DIR__;
$targetDir = __DIR__ . '/mindshift';

// Color mappings
$colorReplacements = [
    // Admin: Orange -> Pink
    '#FF8C00' => '#FF69B4',  // Primary pink
    '#FF4500' => '#FF1493',  // Dark pink
    'rgba(255, 140, 0' => 'rgba(255, 105, 180',  // Pink with alpha
    'rgba(255, 69, 0' => 'rgba(255, 20, 147',
    
    // Lecturer: Orange -> Red
    // We'll handle this separately for lecturer files
    
    // Text replacements
    'StudySmart' => 'Mind Shift',
    'studysmart' => 'mindshift',
    'Study Smart' => 'Mind Shift',
];

// Files/directories to copy
$copyItems = [
    'admin',
    'lecturer', 
    'student',
    'classes',
    'config',
    'includes',
    'migrations',
    'assets',
    'index.php',
    'login.php',
    'logout.php',
    'register.php',
    'subscription.php',
];

// Create target directory
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

function copyDirectory($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function replaceInFile($filePath, $replacements) {
    if (!file_exists($filePath)) return;
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
    }
}

// Copy files
echo "Copying files...\n";
foreach ($copyItems as $item) {
    $src = $sourceDir . '/' . $item;
    $dst = $targetDir . '/' . $item;
    
    if (file_exists($src)) {
        if (is_dir($src)) {
            copyDirectory($src, $dst);
        } else {
            copy($src, $dst);
        }
        echo "Copied: $item\n";
    }
}

// Recursively process all PHP, CSS, JS, HTML files
function processDirectory($dir, $replacements, $isLecturer = false) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['php', 'css', 'js', 'html'])) {
                $filePath = $file->getRealPath();
                
                // Special handling for lecturer files - Red instead of Pink
                if ($isLecturer && $ext === 'css') {
                    $lecturerReplacements = array_merge($replacements, [
                        '#FF8C00' => '#DC143C',  // Red
                        '#FF4500' => '#C41E3A',  // Dark red
                        'rgba(255, 140, 0' => 'rgba(220, 20, 60',
                        'rgba(255, 69, 0' => 'rgba(196, 30, 58',
                    ]);
                    replaceInFile($filePath, $lecturerReplacements);
                } else {
                    replaceInFile($filePath, $replacements);
                }
            }
        }
    }
}

// Process all files
echo "\nProcessing files...\n";

// Process admin files (Pink)
processDirectory($targetDir . '/admin', $colorReplacements, false);

// Process lecturer files (Red)
$lecturerReplacements = array_merge($colorReplacements, [
    '#FF8C00' => '#DC143C',  // Red
    '#FF4500' => '#C41E3A',  // Dark red
    'rgba(255, 140, 0' => 'rgba(220, 20, 60',
    'rgba(255, 69, 0' => 'rgba(196, 30, 58',
]);
processDirectory($targetDir . '/lecturer', $lecturerReplacements, true);

// Process student files (keep Orange - no changes needed for colors)
$studentReplacements = [
    'StudySmart' => 'Mind Shift',
    'studysmart' => 'mindshift',
    'Study Smart' => 'Mind Shift',
];
processDirectory($targetDir . '/student', $studentReplacements, false);

// Process other directories
processDirectory($targetDir . '/classes', $colorReplacements, false);
processDirectory($targetDir . '/includes', $colorReplacements, false);
processDirectory($targetDir . '/migrations', $colorReplacements, false);

// Process root files
replaceInFile($targetDir . '/index.php', $colorReplacements);
replaceInFile($targetDir . '/login.php', $colorReplacements);
replaceInFile($targetDir . '/logout.php', $colorReplacements);
replaceInFile($targetDir . '/register.php', $colorReplacements);
replaceInFile($targetDir . '/subscription.php', $colorReplacements);

// Update config/database.php if needed
if (file_exists($targetDir . '/config/database.php')) {
    $configContent = file_get_contents($targetDir . '/config/database.php');
    $configContent = str_replace('StudySmart', 'Mind Shift', $configContent);
    file_put_contents($targetDir . '/config/database.php', $configContent);
}

echo "\nDone! Mind Shift project created in: $targetDir\n";
echo "Please review and test the duplicated project.\n";
?>

