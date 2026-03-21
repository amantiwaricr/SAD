<?php
session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(["status" => "error"]); exit; }

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->query("SELECT * FROM pharmacy_inventory ORDER BY item_name ASC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $stmt = $conn->prepare("INSERT INTO pharmacy_inventory (item_name, quantity, price, expiry_date) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$data->item_name, $data->quantity, $data->price, $data->expiry_date])) {
        echo json_encode(["status" => "success"]);
    }
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM pharmacy_inventory WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(["status" => "success"]);
}
?>
