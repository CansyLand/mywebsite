<?php
/**
 * Projects API Endpoint
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
    case 'PUT':
        handlePut();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function handleGet(): void {
    global $userId;
    
    $projectId = $_GET['id'] ?? null;
    
    if ($projectId) {
        $project = Project::getById((int)$projectId);
        if (!$project || !Project::belongsToUser((int)$projectId, $userId)) {
            json_response(['error' => 'Project not found'], 404);
        }
        json_response(['project' => $project]);
    }
    
    $projects = Project::getByUser($userId);
    json_response(['projects' => $projects]);
}

function handlePost(): void {
    global $userId;
    
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $name = trim($data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    
    if (empty($name)) {
        json_response(['error' => 'Project name is required'], 400);
    }
    
    try {
        $project = Project::create($userId, $name, $description ?: null);
        json_response([
            'success' => true,
            'project' => $project,
            'redirect' => url('/project?id=' . $project['id'])
        ], 201);
    } catch (Exception $e) {
        json_response(['error' => $e->getMessage()], 400);
    }
}

function handlePut(): void {
    global $userId;
    
    $data = json_decode(file_get_contents('php://input'), true);
    $projectId = (int)($data['id'] ?? $_GET['id'] ?? 0);
    
    if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
        json_response(['error' => 'Project not found'], 404);
    }
    
    unset($data['id']);
    
    if (Project::update($projectId, $data)) {
        $project = Project::getById($projectId);
        json_response(['success' => true, 'project' => $project]);
    } else {
        json_response(['error' => 'Update failed'], 400);
    }
}

function handleDelete(): void {
    global $userId;
    
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $projectId = (int)($data['id'] ?? $_GET['id'] ?? 0);
    
    if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
        json_response(['error' => 'Project not found'], 404);
    }
    
    if (Project::delete($projectId)) {
        json_response(['success' => true]);
    } else {
        json_response(['error' => 'Delete failed'], 400);
    }
}

