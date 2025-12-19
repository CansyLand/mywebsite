<?php
/**
 * Auth API Endpoint
 */

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

switch ($action) {
    case 'register':
        handleRegister();
        break;
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        json_response(['error' => 'Invalid action'], 400);
}

function handleRegister(): void {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $name = trim($data['name'] ?? '');
    
    // Validation
    $errors = [];
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if ($errors) {
        json_response(['error' => implode(', ', $errors)], 400);
    }
    
    try {
        $user = Auth::register($email, $password, $name);
        json_response([
            'success' => true,
            'user' => $user,
            'redirect' => url('/dashboard')
        ]);
    } catch (Exception $e) {
        json_response(['error' => $e->getMessage()], 400);
    }
}

function handleLogin(): void {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        json_response(['error' => 'Email and password are required'], 400);
    }
    
    $user = Auth::login($email, $password);
    
    if ($user) {
        json_response([
            'success' => true,
            'user' => $user,
            'redirect' => url('/dashboard')
        ]);
    } else {
        json_response(['error' => 'Invalid email or password'], 401);
    }
}

function handleLogout(): void {
    Auth::logout();
    json_response([
        'success' => true,
        'redirect' => url('/login')
    ]);
}

