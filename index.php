<?php
/**
 * MyWebsite - Main Router
 */

require_once __DIR__ . '/init.php';

// Parse request path
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');

// Remove base path prefix if present
$basePath = trim(BASE_URL, '/');
if ($basePath && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}
$path = trim($path, '/');

// Split into segments
$segments = $path ? explode('/', $path) : [];

// Known app routes
$appRoutes = [
    '' => 'landing',
    'login' => 'login',
    'register' => 'register',
    'dashboard' => 'dashboard',
    'project' => 'project',
    'logout' => 'logout'
];

// API routes
$apiRoutes = [
    'auth' => 'auth',
    'projects' => 'projects',
    'upload' => 'upload',
    'generate' => 'generate',
    'designs' => 'designs',
    'publish' => 'publish'
];

// Route the request
$firstSegment = $segments[0] ?? '';

// Handle logout
if ($firstSegment === 'logout') {
    Auth::logout();
    redirect('/login');
}

// API routes
if ($firstSegment === 'api' && isset($segments[1])) {
    $apiRoute = $segments[1];
    if (isset($apiRoutes[$apiRoute])) {
        require BASE_PATH . '/api/' . $apiRoutes[$apiRoute] . '.php';
        exit;
    }
    json_response(['error' => 'API endpoint not found'], 404);
}

// App routes
if (isset($appRoutes[$firstSegment])) {
    require BASE_PATH . '/pages/' . $appRoutes[$firstSegment] . '.php';
    exit;
}

// Check if it's a portfolio URL (slug)
if ($firstSegment) {
    $db = Database::get();
    $stmt = $db->prepare("
        SELECT p.*, u.name as owner_name 
        FROM projects p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.slug = ? AND p.published = 1
    ");
    $stmt->execute([$firstSegment]);
    $project = $stmt->fetch();

    if ($project) {
        $pageSlug = $segments[1] ?? 'home';
        require BASE_PATH . '/pages/portfolio.php';
        exit;
    }
}

// 404 Not Found
http_response_code(404);
require BASE_PATH . '/pages/404.php';

