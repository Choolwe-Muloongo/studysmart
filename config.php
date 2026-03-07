<?php
// Configuration file for Document AI Processor

// OpenRouter API Configuration
define('OPENROUTER_API_KEY', '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'); // Replace with your actual API key
define('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1');

// Application Settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['txt', 'pdf', 'doc', 'docx']);
define('UPLOAD_DIR', 'uploads/');

// AI Model Settings
define('DEFAULT_MODEL', 'google/gemma-3n-e2b-it:free');
define('MAX_TOKENS', 4000);
define('TEMPERATURE', 0.7);

// Error Reporting (set to false in production)
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?> 