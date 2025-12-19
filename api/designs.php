<?php
/**
 * Designs API Endpoint
 */

require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$userId = Auth::id();

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'PUT':
        handlePut();
        break;
    case 'POST':
        handlePost();
        break;
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function handleGet(): void {
    global $userId;
    
    $projectId = (int)($_GET['project_id'] ?? 0);
    
    if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
        json_response(['error' => 'Project not found'], 404);
    }
    
    $db = Database::get();
    $stmt = $db->prepare("SELECT * FROM designs WHERE project_id = ? ORDER BY created_at DESC");
    $stmt->execute([$projectId]);
    $designs = $stmt->fetchAll();
    
    json_response(['designs' => $designs]);
}

function handlePut(): void {
    global $userId;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $designId = (int)($data['id'] ?? 0);
    $projectId = (int)($data['project_id'] ?? 0);
    $action = $data['action'] ?? '';
    
    if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
        json_response(['error' => 'Project not found'], 404);
    }
    
    $db = Database::get();
    
    // Verify design belongs to project
    $stmt = $db->prepare("SELECT * FROM designs WHERE id = ? AND project_id = ?");
    $stmt->execute([$designId, $projectId]);
    $design = $stmt->fetch();
    
    if (!$design) {
        json_response(['error' => 'Design not found'], 404);
    }
    
    if ($action === 'toggle_star') {
        $newValue = !$design['is_starred'];
        $stmt = $db->prepare("UPDATE designs SET is_starred = ? WHERE id = ?");
        $stmt->execute([$newValue ? 1 : 0, $designId]);
        
        $design['is_starred'] = $newValue;
        json_response(['success' => true, 'design' => $design]);
    }
    
    json_response(['error' => 'Invalid action'], 400);
}

function handlePost(): void {
    global $userId;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $designId = (int)($data['id'] ?? 0);
    $projectId = (int)($data['project_id'] ?? 0);
    $pageTitle = trim($data['page_title'] ?? 'Home');
    
    if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
        json_response(['error' => 'Project not found'], 404);
    }
    
    $db = Database::get();
    
    // Get the design
    $stmt = $db->prepare("SELECT * FROM designs WHERE id = ? AND project_id = ?");
    $stmt->execute([$designId, $projectId]);
    $design = $stmt->fetch();
    
    if (!$design) {
        json_response(['error' => 'Design not found'], 404);
    }
    
    // Mark as selected
    $stmt = $db->prepare("UPDATE designs SET is_selected = 0 WHERE project_id = ?");
    $stmt->execute([$projectId]);
    
    $stmt = $db->prepare("UPDATE designs SET is_selected = 1 WHERE id = ?");
    $stmt->execute([$designId]);
    
    // Extract header, footer, and content
    $parts = PageBuilder::extractParts($design['html_content']);
    
    // Check if this is the first page (no template exists)
    $stmt = $db->prepare("SELECT id FROM project_templates WHERE project_id = ?");
    $stmt->execute([$projectId]);
    $existingTemplate = $stmt->fetch();
    
    if (!$existingTemplate) {
        // Save template (header/footer/css)
        $stmt = $db->prepare("
            INSERT INTO project_templates (project_id, header_html, footer_html, global_css)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $projectId,
            $parts['header'],
            $parts['footer'],
            $parts['css']
        ]);
    }
    
    // Create or update page
    $pageSlug = PageBuilder::generatePageSlug($pageTitle);
    
    $stmt = $db->prepare("SELECT id FROM pages WHERE project_id = ? AND slug = ?");
    $stmt->execute([$projectId, $pageSlug]);
    $existingPage = $stmt->fetch();
    
    if ($existingPage) {
        $stmt = $db->prepare("
            UPDATE pages SET title = ?, content_html = ?, updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$pageTitle, $parts['main'], $existingPage['id']]);
        $pageId = $existingPage['id'];
    } else {
        // Get next sort order
        $stmt = $db->prepare("SELECT MAX(sort_order) as max_order FROM pages WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $maxOrder = $stmt->fetch()['max_order'] ?? -1;
        
        $stmt = $db->prepare("
            INSERT INTO pages (project_id, slug, title, content_html, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $projectId,
            $pageSlug,
            $pageTitle,
            $parts['main'],
            $maxOrder + 1
        ]);
        $pageId = Database::lastInsertId();
    }
    
    // Update design with page_id
    $stmt = $db->prepare("UPDATE designs SET page_id = ? WHERE id = ?");
    $stmt->execute([$pageId, $designId]);
    
    json_response([
        'success' => true,
        'page_id' => $pageId,
        'page_slug' => $pageSlug
    ]);
}

