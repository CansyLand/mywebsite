<?php
/**
 * MyWebsite Configuration Example
 * Copy this file to config.php and fill in your actual values
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Berlin');

// Base paths
define('BASE_PATH', __DIR__);
define('BASE_URL', '/mywebsite');

// Database
define('DB_PATH', BASE_PATH . '/database/mywebsite.sqlite');

// xAI API Configuration
define('XAI_API_KEY', getenv('XAI_API_KEY') ?: 'your-xai-api-key-here');
define('XAI_API_URL', 'https://api.x.ai/v1/chat/completions');
define('XAI_MODEL', 'grok-beta');

// Upload settings
define('UPLOAD_PATH', BASE_PATH . '/storage/uploads');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_TEXT_TYPES', ['text/plain', 'text/markdown', 'application/octet-stream']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'txt', 'md']);

// Session settings
define('SESSION_NAME', 'mywebsite_session');
define('SESSION_LIFETIME', 60 * 60 * 24 * 7); // 7 days

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

?>