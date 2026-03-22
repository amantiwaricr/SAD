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
    if (isset($_GET['patient_id'])) {
        $stmt = $conn->prepare("
            SELECT pn.id, pn.patient_id, pn.nurse_id, pn.assigned_date, pn.shift, 
                   n.first_name, n.last_name, n.category
            FROM patient_nurses pn
            JOIN nurses n ON pn.nurse_id = n.id
            WHERE pn.patient_id = ?
            ORDER BY pn.assigned_date DESC
        ");
        $stmt->execute([$_GET['patient_id']]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo json_encode(["status" => "error", "message" => "Missing patient_id"]);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO patient_nurses (patient_id, nurse_id, assigned_date, shift) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$data->patient_id, $data->nurse_id, $data->assigned_date, $data->shift])) {
        echo json_encode(["status" => "success", "message" => "Nurse assigned successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to assign nurse"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM patient_nurses WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(["status" => "success", "message" => "Assignment removed successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to remove assignment"]);
    }
}
?>
