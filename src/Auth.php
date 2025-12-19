<?php
/**
 * Authentication Helper
 */
class Auth {
    
    public static function register(string $email, string $password, string $name): array {
        $db = Database::get();
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email already registered');
        }
        
        // Create user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)");
        $stmt->execute([$email, $hash, $name]);
        
        $userId = Database::lastInsertId();
        
        // Auto-login
        self::loginById($userId, $name, $email);
        
        return [
            'id' => $userId,
            'email' => $email,
            'name' => $name
        ];
    }
    
    public static function login(string $email, string $password): ?array {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            self::loginById($user['id'], $user['name'], $user['email']);
            return [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ];
        }
        
        return null;
    }
    
    private static function loginById(int $id, string $name, string $email): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
    }
    
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }
    
    public static function check(): bool {
        return isset($_SESSION['user_id']);
    }
    
    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email']
        ];
    }
    
    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }
}

