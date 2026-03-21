<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "error", "message" => "Unauthorized"]); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $query = "SELECT s.*, CONCAT(d.first_name, ' ', d.last_name) as doctor_name 
              FROM doctor_schedules s 
              JOIN doctors d ON s.doctor_id = d.id 
              ORDER BY s.day_of_week, s.start_time";
    $stmt = $conn->query($query);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, available) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$data->doctor_id, $data->day_of_week, $data->start_time, $data->end_time, $data->available ?? 1])) {
        echo json_encode(["status" => "success", "message" => "Schedule added"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add schedule"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM doctor_schedules WHERE id = ?");
    if ($stmt->execute([$id])) { echo json_encode(["status" => "success"]); }
}
?>
