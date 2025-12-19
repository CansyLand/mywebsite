<?php
/**
 * Project Model
 */
class Project {
    
    public static function create(int $userId, string $name, ?string $description = null): array {
        $db = Database::get();
        $slug = self::generateSlug($name);
        
        $stmt = $db->prepare("
            INSERT INTO projects (user_id, name, slug, description) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $name, $slug, $description]);
        
        $id = Database::lastInsertId();
        
        // Create upload directory
        $uploadDir = UPLOAD_PATH . '/' . $userId . '/' . $id;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        return self::getById($id);
    }
    
    public static function getById(int $id): ?array {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
    
    public static function getBySlug(string $slug): ?array {
        $db = Database::get();
        $stmt = $db->prepare("
            SELECT p.*, u.name as owner_name 
            FROM projects p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.slug = ?
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }
    
    public static function getByUser(int $userId): array {
        $db = Database::get();
        $stmt = $db->prepare("
            SELECT p.*, 
                   (SELECT COUNT(*) FROM pages WHERE project_id = p.id) as page_count,
                   (SELECT COUNT(*) FROM uploads WHERE project_id = p.id) as file_count
            FROM projects p 
            WHERE p.user_id = ? 
            ORDER BY p.updated_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public static function update(int $id, array $data): bool {
        $db = Database::get();
        
        $fields = [];
        $values = [];
        
        $allowed = ['name', 'description', 'style_prompt', 'reference_url', 'reference_notes', 'published'];
        
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $values[] = $id;
        
        $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }
    
    public static function delete(int $id): bool {
        $db = Database::get();
        
        // Get project for cleanup
        $project = self::getById($id);
        if (!$project) {
            return false;
        }
        
        // Delete upload directory
        $uploadDir = UPLOAD_PATH . '/' . $project['user_id'] . '/' . $id;
        if (is_dir($uploadDir)) {
            self::deleteDirectory($uploadDir);
        }
        
        // Delete from database (cascades to related tables)
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function belongsToUser(int $projectId, int $userId): bool {
        $db = Database::get();
        $stmt = $db->prepare("SELECT id FROM projects WHERE id = ? AND user_id = ?");
        $stmt->execute([$projectId, $userId]);
        return (bool) $stmt->fetch();
    }
    
    public static function generateSlug(string $name): string {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        if (empty($slug)) {
            $slug = 'project';
        }
        
        // Ensure uniqueness
        $db = Database::get();
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $stmt = $db->prepare("SELECT id FROM projects WHERE slug = ?");
            $stmt->execute([$slug]);
            if (!$stmt->fetch()) {
                break;
            }
            $slug = $originalSlug . '-' . $counter++;
        }
        
        return $slug;
    }
    
    private static function deleteDirectory(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? self::deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

