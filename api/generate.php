<?php
/**
 * Generate API Endpoint
 */

require_auth();

$method = $_SERVER['REQUEST_METHOD'];
$userId = Auth::id();

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$projectId = (int)($data['project_id'] ?? 0);
$uploadIds = $data['upload_ids'] ?? [];
$prompt = trim($data['prompt'] ?? '');
$referenceUrl = trim($data['reference_url'] ?? '');
$pageTitle = trim($data['page_title'] ?? 'Home');
$feedback = trim($data['feedback'] ?? '');

// Validate project ownership
if (!$projectId || !Project::belongsToUser($projectId, $userId)) {
    json_response(['error' => 'Project not found'], 404);
}

// Validate uploads
if (empty($uploadIds)) {
    json_response(['error' => 'Please select at least one file'], 400);
}

// Get uploads
$uploads = Upload::getByIds($uploadIds);
if (empty($uploads)) {
    json_response(['error' => 'Selected files not found'], 400);
}

// Verify uploads belong to project
foreach ($uploads as $upload) {
    if ($upload['project_id'] != $projectId) {
        json_response(['error' => 'Invalid file selection'], 400);
    }
}

// Separate images and text
$images = [];
$textContent = '';

foreach ($uploads as $upload) {
    if ($upload['file_type'] === 'image') {
        $images[] = $upload;
    } else {
        if ($upload['extracted_text']) {
            $textContent .= $upload['extracted_text'] . "\n\n";
        }
    }
}

// Update project with prompt/reference if provided
if ($prompt || $referenceUrl) {
    Project::update($projectId, [
        'style_prompt' => $prompt,
        'reference_url' => $referenceUrl
    ]);
}

// Generate designs
try {
    $htmlDesigns = AI::generate([
        'page_title' => $pageTitle,
        'prompt' => $prompt,
        'reference_url' => $referenceUrl,
        'feedback' => $feedback,
        'images' => $images,
        'text_content' => trim($textContent)
    ]);
    
    // Save designs to database
    $db = Database::get();
    $batchId = generate_uuid();
    $designs = [];
    
    // Save generation request
    $stmt = $db->prepare("
        INSERT INTO generation_requests (project_id, selected_uploads, prompt, reference_url, batch_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $projectId,
        json_encode($uploadIds),
        $prompt . ($feedback ? "\n\nFeedback: " . $feedback : ''),
        $referenceUrl,
        $batchId
    ]);
    
    // Save each design
    foreach ($htmlDesigns as $html) {
        $stmt = $db->prepare("
            INSERT INTO designs (project_id, batch_id, html_content)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$projectId, $batchId, $html]);
        
        $designId = Database::lastInsertId();
        $designs[] = [
            'id' => $designId,
            'project_id' => $projectId,
            'batch_id' => $batchId,
            'html_content' => $html,
            'is_starred' => false,
            'is_selected' => false
        ];
    }
    
    json_response([
        'success' => true,
        'designs' => $designs,
        'batch_id' => $batchId
    ]);
    
} catch (Exception $e) {
    json_response(['error' => $e->getMessage()], 500);
}

