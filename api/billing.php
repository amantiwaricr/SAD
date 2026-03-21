<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "error", "message" => "Unauthorized"]); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $query = "SELECT b.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name 
              FROM billing b JOIN patients p ON b.patient_id = p.id ORDER BY b.date DESC";
    $stmt = $conn->query($query);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO billing (patient_id, amount, status, date, description) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$data->patient_id, $data->amount, $data->status, $data->date, $data->description])) {
        echo json_encode(["status" => "success", "message" => "Bill added"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE billing SET status=? WHERE id=?");
    if ($stmt->execute([$data->status, $data->id])) {
        echo json_encode(["status" => "success", "message" => "Status updated"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM billing WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["status" => "success"]);
}
?>
