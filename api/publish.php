<?php
/**
 * Publish API Endpoint
 */

require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$userId = Auth::id();

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$projectId = (int)($data['project_id'] ?? 0);

if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
    json_response(['error' => 'Project not found'], 404);
}

$db = Database::get();

// Check if project has at least one page
$stmt = $db->prepare("SELECT COUNT(*) as count FROM pages WHERE project_id = ?");
$stmt->execute([$projectId]);
$pageCount = $stmt->fetch()['count'];

if ($pageCount === 0) {
    json_response(['error' => 'Create at least one page before publishing'], 400);
}

// Check if template exists
$stmt = $db->prepare("SELECT id FROM project_templates WHERE project_id = ?");
$stmt->execute([$projectId]);
if (!$stmt->fetch()) {
    json_response(['error' => 'No template found. Generate a design first.'], 400);
}

// Publish the project
Project::update($projectId, ['published' => 1]);

$project = Project::getById($projectId);

json_response([
    'success' => true,
    'url' => url('/' . $project['slug']),
    'message' => 'Your portfolio is now live!'
]);

