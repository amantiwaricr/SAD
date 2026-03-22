<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM nurses WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $conn->query("SELECT * FROM nurses ORDER BY id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO nurses (first_name, last_name, category, contact, email) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$data->first_name, $data->last_name, $data->category, $data->contact, $data->email])) {
        echo json_encode(["status" => "success", "message" => "Nurse added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add nurse"]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE nurses SET first_name=?, last_name=?, category=?, contact=?, email=? WHERE id=?");
    if ($stmt->execute([$data->first_name, $data->last_name, $data->category, $data->contact, $data->email, $data->id])) {
        echo json_encode(["status" => "success", "message" => "Nurse updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update nurse"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM nurses WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(["status" => "success", "message" => "Nurse deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete nurse"]);
    }
}
?>
