<?php
/**
 * Upload Handler
 */
class Upload {
    
    public static function store(array $file, int $projectId, int $userId): array {
        // Validate file
        self::validate($file);
        
        // Determine file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $isImage = in_array($file['type'], ALLOWED_IMAGE_TYPES);
        $fileType = $isImage ? 'image' : 'text';
        
        // Generate unique filename
        $filename = self::generateFilename($extension);
        
        // Create directory if needed
        $uploadDir = UPLOAD_PATH . '/' . $userId . '/' . $projectId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move file
        $filePath = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Failed to save file');
        }
        
        // Extract text content for text files
        $extractedText = null;
        if ($fileType === 'text') {
            $extractedText = file_get_contents($filePath);
        }
        
        // Save to database
        $db = Database::get();
        $stmt = $db->prepare("
            INSERT INTO uploads (project_id, filename, original_name, file_type, file_path, mime_type, file_size, extracted_text)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $projectId,
            $filename,
            $file['name'],
            $fileType,
            $filePath,
            $file['type'],
            $file['size'],
            $extractedText
        ]);
        
        return self::getById(Database::lastInsertId());
    }
    
    public static function validate(array $file): void {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error: ' . self::getUploadError($file['error']));
        }
        
        // Check file size
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            throw new Exception('File too large. Maximum size is ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB');
        }
        
        // Check extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            throw new Exception('File type not allowed. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS));
        }
        
        // Verify MIME type for images
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
                throw new Exception('Invalid image file');
            }
        }
    }
    
    public static function getById(int $id): ?array {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM uploads WHERE id = ?");
        $stmt->execute([$id]);
        $upload = $stmt->fetch();
        
        if ($upload) {
            $upload['url'] = self::getUrl($upload);
        }
        
        return $upload ?: null;
    }
    
    public static function getByProject(int $projectId): array {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM uploads WHERE project_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$projectId]);
        $uploads = $stmt->fetchAll();
        
        foreach ($uploads as &$upload) {
            $upload['url'] = self::getUrl($upload);
        }
        
        return $uploads;
    }
    
    public static function getByIds(array $ids): array {
        if (empty($ids)) {
            return [];
        }
        
        $db = Database::get();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("SELECT * FROM uploads WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $uploads = $stmt->fetchAll();
        
        foreach ($uploads as &$upload) {
            $upload['url'] = self::getUrl($upload);
        }
        
        return $uploads;
    }
    
    public static function delete(int $id): bool {
        $upload = self::getById($id);
        if (!$upload) {
            return false;
        }
        
        // Delete file
        if (file_exists($upload['file_path'])) {
            unlink($upload['file_path']);
        }
        
        // Delete from database
        $db = Database::get();
        $stmt = $db->prepare("DELETE FROM uploads WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function belongsToProject(int $uploadId, int $projectId): bool {
        $db = Database::get();
        $stmt = $db->prepare("SELECT id FROM uploads WHERE id = ? AND project_id = ?");
        $stmt->execute([$uploadId, $projectId]);
        return (bool) $stmt->fetch();
    }
    
    public static function getUrl(array $upload): string {
        // Return relative URL for serving files
        $parts = explode('/storage/uploads/', $upload['file_path']);
        if (count($parts) === 2) {
            return url('/storage/uploads/' . $parts[1]);
        }
        return '';
    }
    
    private static function generateFilename(string $extension): string {
        return uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }
    
    private static function getUploadError(int $code): string {
        return match($code) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
            default => 'Unknown error'
        };
    }
}

