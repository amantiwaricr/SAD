<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "error"]); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $query = "SELECT l.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name 
              FROM lab_checkups l JOIN patients p ON l.patient_id = p.id ORDER BY l.test_date DESC";
    $stmt = $conn->query($query);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO lab_checkups (patient_id, test_name, result, test_date, status) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$data->patient_id, $data->test_name, $data->result, $data->test_date, $data->status ?? 'pending'])) {
        echo json_encode(["status" => "success"]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE lab_checkups SET result=?, status=? WHERE id=?");
    if ($stmt->execute([$data->result, $data->status, $data->id])) {
        echo json_encode(["status" => "success"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM lab_checkups WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["status" => "success"]);
}
?>
