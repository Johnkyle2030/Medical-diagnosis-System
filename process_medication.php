<?php
session_start();
require_once 'DiagnosisHandler.php';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$con = mysqli_connect("localhost", "root", "", "medical");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set patient_id from session or request
$patient_id = isset($_POST['patient_id']) ? $_POST['patient_id'] : (isset($_GET['patient_id']) ? $_GET['patient_id'] : $_SESSION['id']);

// Process medication actions
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    // Add new medication
    $medication_name = $_POST['medication_name'];
    $dosage = $_POST['dosage'];
    $schedule = $_POST['schedule'];
    $diagnosis_id = !empty($_POST['diagnosis_id']) ? $_POST['diagnosis_id'] : NULL;
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
    $healing_advice = $_POST['healing_advice'];
    $notes = $_POST['notes'];
    
    $query = "INSERT INTO medications (patient_id, diagnosis_id, medication_name, dosage, schedule, 
              start_date, end_date, healing_advice, notes, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("iisssssss", $patient_id, $diagnosis_id, $medication_name, $dosage, 
                      $schedule, $start_date, $end_date, $healing_advice, $notes);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Medication added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding medication: " . $con->error;
    }
    
    header("Location: welcome.php");
    exit();
}

// Update existing medication
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $medication_id = $_POST['medication_id'];
    $medication_name = $_POST['medication_name'];
    $dosage = $_POST['dosage'];
    $schedule = $_POST['schedule'];
    $diagnosis_id = !empty($_POST['diagnosis_id']) ? $_POST['diagnosis_id'] : NULL;
    $start_date = $_POST['start_date'];
    $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : NULL;
    $healing_advice = $_POST['healing_advice'];
    $notes = $_POST['notes'];
    
    // Verify this medication belongs to the logged-in user
    $query = "SELECT id FROM medications WHERE id = ? AND patient_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $medication_id, $patient_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Update medication
        $query = "UPDATE medications SET 
                  medication_name = ?, dosage = ?, schedule = ?, 
                  diagnosis_id = ?, start_date = ?, end_date = ?, 
                  healing_advice = ?, notes = ? 
                  WHERE id = ?";
                  
        $stmt = $con->prepare($query);
        $stmt->bind_param("ssssssssi", $medication_name, $dosage, $schedule, 
                          $diagnosis_id, $start_date, $end_date, 
                          $healing_advice, $notes, $medication_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Medication updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating medication: " . $con->error;
        }
    } else {
        $_SESSION['error_message'] = "Invalid medication ID!";
    }
    
    header("Location: welcome.php");
    exit();
}

// Mark medication as completed
if (isset($_GET['action']) && $_GET['action'] == 'complete' && isset($_GET['id'])) {
    $med_id = $_GET['id'];
    
    // Verify this medication belongs to the logged-in user
    $query = "SELECT id FROM medications WHERE id = ? AND patient_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $med_id, $patient_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Update medication status
        $query = "UPDATE medications SET status = 'completed', end_date = CURDATE() WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $med_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Medication marked as completed!";
        } else {
            $_SESSION['error_message'] = "Error updating medication: " . $con->error;
        }
    } else {
        $_SESSION['error_message'] = "Invalid medication ID!";
    }
    
    header("Location: welcome.php");
    exit();
}

// Delete medication
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $med_id = $_GET['id'];
    
    // Verify this medication belongs to the logged-in user
    $query = "SELECT id FROM medications WHERE id = ? AND patient_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $med_id, $patient_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Delete the medication
        $query = "DELETE FROM medications WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $med_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Medication deleted successfully!";
        } else {
            $_SESSION['error_message'] = "Error deleting medication: " . $con->error;
        }
    } else {
        $_SESSION['error_message'] = "Invalid medication ID!";
    }
    
    header("Location: welcome.php");
    exit();
}

// If no action specified, redirect back to dashboard
header("Location: welcome.php");
exit();
?>