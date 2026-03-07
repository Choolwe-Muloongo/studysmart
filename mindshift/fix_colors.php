<?php
/**
 * Fix color scheme for Mind Shift
 * - Lecturer: Red (#DC143C, #C41E3A) and Black
 * - Main pages: Pink text on black background
 */

$lecturerFiles = glob(__DIR__ . '/lecturer/*.php');
$mainFiles = [__DIR__ . '/index.php', __DIR__ . '/login.php', __DIR__ . '/register.php', __DIR__ . '/subscription.php'];

// Lecturer color replacements (Blue -> Red)
$lecturerReplacements = [
    '#3498db' => '#DC143C',  // Red
    '#2980b9' => '#C41E3A',  // Dark Red
    'rgba(52, 152, 219' => 'rgba(220, 20, 60',
    'rgba(41, 128, 185' => 'rgba(196, 30, 58',
];

// Main page replacements (Pink text on black)
$mainReplacements = [
    'background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #ff8c00 100%)' => 'background: #000000',
    'background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%)' => 'background: rgba(0, 0, 0, 0.95)',
    'color: white' => 'color: #FF69B4',  // Pink text
    'color: #667eea' => 'color: #FF69B4',
    '#ff8c00' => '#FF69B4',
    '#ff6b00' => '#FF1493',
    'rgba(255, 105, 180, 0.3)' => 'rgba(255, 105, 180, 0.3)',
    'rgba(255, 105, 180, 0.4)' => 'rgba(255, 105, 180, 0.4)',
];

function updateFile($filePath, $replacements) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return;
    }
    
    $content = file_get_contents($filePath);
    $original = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $original) {
        file_put_contents($filePath, $content);
        echo "Updated: " . basename($filePath) . "\n";
    }
}

// Update lecturer files
echo "Updating lecturer files...\n";
foreach ($lecturerFiles as $file) {
    updateFile($file, $lecturerReplacements);
}

// Update main files
echo "\nUpdating main files...\n";
foreach ($mainFiles as $file) {
    updateFile($file, $mainReplacements);
}

echo "\nDone!\n";
?>

