<?php
session_start();
require_once 'DiagnosisHandler.php';

// Create a log file for debugging
function logMessage($message) {
    $logFile = 'diagnosis_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

logMessage("Script started");

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    logMessage("No session ID found - redirecting to index.php");
    header("Location: index.php");
    exit();
}

// Check if form was submitted
if (!isset($_POST['diagnosis_form'])) {
    logMessage("Form not submitted - redirecting to welcome.php");
    header("Location: welcome.php");
    exit();
}

logMessage("Form submitted with session ID: " . $_SESSION['id']);

// Database connection
$con = mysqli_connect("localhost", "root", "", "medical");
if (!$con) {
    logMessage("Database connection failed: " . mysqli_connect_error());
    die("Connection failed: " . mysqli_connect_error());
}
logMessage("Database connection successful");

// Get user data
$patient_id = $_SESSION['id'];
$query = "SELECT * FROM patients WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    logMessage("No user found with ID: $patient_id");
    $_SESSION['error'] = "User not found.";
    header("Location: welcome.php");
    exit();
}
logMessage("User data retrieved for ID: $patient_id");

// Get form data
$symptoms = isset($_POST['custom_symptoms']) ? $_POST['custom_symptoms'] : '';
$duration = isset($_POST['duration']) ? $_POST['duration'] : '';

logMessage("Symptoms: $symptoms, Duration: $duration");

// Save symptoms to a temporary file
$temp_file = tempnam(sys_get_temp_dir(), 'symptoms_');
file_put_contents($temp_file, $symptoms);
logMessage("Saved symptoms to temporary file: $temp_file");

// Call Python script to process diagnosis
$python_script = "python";
$script_path = dirname(__FILE__) . "/diagnosis_model.py";

// Execute Python script with symptoms as stdin
$descriptorspec = array(
   0 => array("pipe", "r"),  
   1 => array("pipe", "w"),  
   2 => array("pipe", "w")   
);

$process = proc_open("$python_script $script_path", $descriptorspec, $pipes);
$output = "";
$errors = "";

if (is_resource($process)) {
    // Write symptoms to stdin
    fwrite($pipes[0], $symptoms);
    fclose($pipes[0]);

    // Get output from stdout
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    
    // Get any errors
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    
    // Close the process
    $return_value = proc_close($process);
    
    logMessage("Python script return value: $return_value");
    if (!empty($errors)) {
        logMessage("Python script errors: $errors");
    }
}

logMessage("Python script output: " . ($output ?: "No output"));

// Parse JSON response from Python script
$diagnosis_result = json_decode($output, true);

if (!$diagnosis_result) {
    logMessage("Failed to parse JSON from Python script output");
    $_SESSION['error'] = "Could not process diagnosis. Please try again.";
    header("Location: welcome.php");
    exit();
}

logMessage("Successfully parsed diagnosis result: " . json_encode($diagnosis_result));

// Map duration to severity level
$severity_map = [
    'less_than_day' => 'Low',
    '1_3_days' => 'Low',
    '4_7_days' => 'Medium',
    '1_2_weeks' => 'Medium',
    'more_than_2_weeks' => 'High'
];

$severity_level = $severity_map[$duration] ?? 'Medium';
$condition_category = ($duration == 'more_than_2_weeks') ? 'Chronic' : 'Acute';

try {
    // Store diagnosis in database
    $query = "INSERT INTO diagnoses (patient_id, diagnosis_date, condition_name, condition_category, 
              severity_level, symptoms, recommendations, confidence) 
              VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)";
              
    $stmt = $con->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $con->error);
    }
    
    $confidence_value = isset($diagnosis_result['confidence']) ? 
        floatval(str_replace('%', '', $diagnosis_result['confidence'])) : 0;
    
    $stmt->bind_param("isssssd", 
        $patient_id, 
        $diagnosis_result['diagnosis'], 
        $condition_category,
        $severity_level, 
        $symptoms, 
        $diagnosis_result['advice'], 
        $confidence_value
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $diagnosis_id = $stmt->insert_id;
    logMessage("Diagnosis stored with ID: $diagnosis_id");
    
    // Store recommended hospitals for this diagnosis
    if (!empty($diagnosis_result['hospitals'])) {
        logMessage("Processing hospitals data");
        $query = "INSERT INTO diagnosis_hospitals (diagnosis_id, hospital_name, specialties, location, county, contact, rating) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Hospital prepare failed: " . $con->error);
        }
        
        foreach ($diagnosis_result['hospitals'] as $hospital) {
            $stmt->bind_param("isssssd", 
                $diagnosis_id, 
                $hospital['name'], 
                $hospital['specialties'], 
                $hospital['location'], 
                $hospital['county'], 
                $hospital['contact'], 
                $hospital['rating']
            );
            if (!$stmt->execute()) {
                logMessage("Failed to store hospital: " . $stmt->error);
            }
        }
        logMessage("Hospitals data processed");
    }
    
    // Store recommended medications for this diagnosis
    if (!empty($diagnosis_result['medications'])) {
        logMessage("Processing medications data");
        $query = "INSERT INTO diagnosis_medications (diagnosis_id, medication_name, dosage, instructions, frequency, side_effects) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($query);
        if (!$stmt) {
            throw new Exception("Medication prepare failed: " . $con->error);
        }
        
        foreach ($diagnosis_result['medications'] as $medication) {
            $stmt->bind_param("isssss", 
                $diagnosis_id, 
                $medication['name'], 
                $medication['dosage'], 
                $medication['instructions'], 
                $medication['frequency'], 
                $medication['side_effects']
            );
            if (!$stmt->execute()) {
                logMessage("Failed to store medication: " . $stmt->error);
            }
        }
        logMessage("Medications data processed");
    }
    
    // Log before redirect
    logMessage("Redirecting to diagnosis_results.php?id=$diagnosis_id");
    
    // Redirect to diagnosis results page
    header("Location: diagnosis_results.php?id=" . $diagnosis_id);
    exit();
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred during diagnosis processing. Please try again.";
    header("Location: welcome.php");
    exit();
}

logMessage("End of script (this should not be reached)");
?>