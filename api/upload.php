<?php
/**
 * Upload API Endpoint
 */

require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$userId = Auth::id();

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'DELETE':
        handleDelete();
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
    
    $uploads = Upload::getByProject($projectId);
    json_response(['uploads' => $uploads]);
}

function handlePost(): void {
    global $userId;
    
    $projectId = (int)($_POST['project_id'] ?? $_GET['project_id'] ?? 0);
    
    if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
        json_response(['error' => 'Project not found'], 404);
    }
    
    if (empty($_FILES['file'])) {
        json_response(['error' => 'No file uploaded'], 400);
    }
    
    try {
        $upload = Upload::store($_FILES['file'], $projectId, $userId);
        json_response([
            'success' => true,
            'upload' => $upload
        ], 201);
    } catch (Exception $e) {
        json_response(['error' => $e->getMessage()], 400);
    }
}

function handleDelete(): void {
    global $userId;
    
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $uploadId = (int)($data['id'] ?? $_GET['id'] ?? 0);
    $projectId = (int)($data['project_id'] ?? $_GET['project_id'] ?? 0);
    
    if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
        json_response(['error' => 'Project not found'], 404);
    }
    
    if (!$uploadId || !Upload::belongsToProject($uploadId, $projectId)) {
        json_response(['error' => 'File not found'], 404);
    }
    
    if (Upload::delete($uploadId)) {
        json_response(['success' => true]);
    } else {
        json_response(['error' => 'Delete failed'], 400);
    }
}

