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
        $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $conn->query("SELECT * FROM patients ORDER BY id DESC");
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($patients);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, dob, gender, blood_group, contact, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$data->first_name, $data->last_name, $data->dob, $data->gender, $data->blood_group, $data->contact, $data->address])) {
        echo json_encode(["status" => "success", "message" => "Patient added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add patient"]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE patients SET first_name=?, last_name=?, dob=?, gender=?, blood_group=?, contact=?, address=? WHERE id=?");
    if ($stmt->execute([$data->first_name, $data->last_name, $data->dob, $data->gender, $data->blood_group, $data->contact, $data->address, $data->id])) {
        echo json_encode(["status" => "success", "message" => "Patient updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update patient"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(["status" => "success", "message" => "Patient deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete patient"]);
    }
}
?>
