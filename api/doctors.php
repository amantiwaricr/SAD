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
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $conn->query("SELECT * FROM doctors ORDER BY id DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO doctors (first_name, last_name, specialization, contact, email) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$data->first_name, $data->last_name, $data->specialization, $data->contact, $data->email])) {
        echo json_encode(["status" => "success", "message" => "Doctor added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add doctor"]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE doctors SET first_name=?, last_name=?, specialization=?, contact=?, email=? WHERE id=?");
    if ($stmt->execute([$data->first_name, $data->last_name, $data->specialization, $data->contact, $data->email, $data->id])) {
        echo json_encode(["status" => "success", "message" => "Doctor updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update doctor"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(["status" => "success", "message" => "Doctor deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete doctor"]);
    }
}
?>
