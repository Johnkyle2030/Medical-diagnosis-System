<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

// Database connection
$con = mysqli_connect("localhost", "root", "", "medical");
if (!$con) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Get medication ID from request
$med_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$patient_id = $_SESSION['id'];

if ($med_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid medication ID']);
    exit();
}

// Fetch medication details
$query = "SELECT * FROM medications WHERE id = ? AND patient_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("ii", $med_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $medication = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($medication);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Medication not found or access denied']);
}

$con->close();
?>