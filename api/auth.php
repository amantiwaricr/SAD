<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $data = json_decode(file_get_contents("php://input"));
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Using simple password_verify. For tests, you can use the admin123 hash.
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        echo json_encode([
            "status" => "success", 
            "message" => "Login successful", 
            "role" => $user['role'],
            "username" => $user['username']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    }
} elseif ($action === 'register') {
    $data = json_decode(file_get_contents("php://input"));
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Username and password are required"]);
        exit;
    }

    try {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(["status" => "error", "message" => "Username already taken"]);
            exit;
        }

        // Hash the password and insert the new user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'receptionist'; // Default role for standard sign-ups
        
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $role]);
        $new_user_id = $conn->lastInsertId();

        echo json_encode([
            "status" => "success", 
            "message" => "Registration successful", 
            "role" => $role,
            "username" => $username
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error occurred"]);
    }
} elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(["status" => "success", "message" => "Logged out"]);
} elseif ($action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            "status" => "success", 
            "user" => [
                "username" => $_SESSION['username'], 
                "role" => $_SESSION['role']
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Not logged in"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
}
?>
