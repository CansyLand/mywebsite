<?php
/**
 * Portfolio Renderer
 * Variables available: $project, $pageSlug
 */

$db = Database::get();

// Get template (header/footer/css)
$stmt = $db->prepare("SELECT * FROM project_templates WHERE project_id = ?");
$stmt->execute([$project['id']]);
$template = $stmt->fetch();

if (!$template) {
    http_response_code(404);
    require BASE_PATH . '/pages/404.php';
    exit;
}

// Get the requested page
$stmt = $db->prepare("SELECT * FROM pages WHERE project_id = ? AND slug = ?");
$stmt->execute([$project['id'], $pageSlug]);
$page = $stmt->fetch();

if (!$page) {
    // Try home if no page found
    if ($pageSlug !== 'home') {
        $stmt = $db->prepare("SELECT * FROM pages WHERE project_id = ? AND slug = 'home'");
        $stmt->execute([$project['id']]);
        $page = $stmt->fetch();
    }
    
    if (!$page) {
        http_response_code(404);
        require BASE_PATH . '/pages/404.php';
        exit;
    }
}

// Get all pages for navigation
$stmt = $db->prepare("SELECT slug, title FROM pages WHERE project_id = ? ORDER BY sort_order");
$stmt->execute([$project['id']]);
$allPages = $stmt->fetchAll();

// Assemble and output the page
echo PageBuilder::assemblePage($template, $page, $project, $allPages);

