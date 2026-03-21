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
        $stmt = $conn->prepare("
            SELECT a.*, 
                   p.first_name as patient_first_name, p.last_name as patient_last_name, 
                   d.first_name as doctor_first_name, d.last_name as doctor_last_name 
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE a.id = ?
        ");
        $stmt->execute([$_GET['id']]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    } else {
        $stmt = $conn->query("
            SELECT a.*, 
                   p.first_name as patient_first_name, p.last_name as patient_last_name, 
                   d.first_name as doctor_first_name, d.last_name as doctor_last_name 
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            JOIN doctors d ON a.doctor_id = d.id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC
        ");
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($appointments);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$data->patient_id, $data->doctor_id, $data->appointment_date, $data->appointment_time, $data->status ?? 'scheduled', $data->notes ?? ''])) {
        echo json_encode(["status" => "success", "message" => "Appointment scheduled successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to schedule appointment"]);
    }
} elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("UPDATE appointments SET patient_id=?, doctor_id=?, appointment_date=?, appointment_time=?, status=?, notes=? WHERE id=?");
    if ($stmt->execute([$data->patient_id, $data->doctor_id, $data->appointment_date, $data->appointment_time, $data->status, $data->notes, $data->id])) {
        echo json_encode(["status" => "success", "message" => "Appointment updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update appointment"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(["status" => "success", "message" => "Appointment deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete appointment"]);
    }
}
?>
