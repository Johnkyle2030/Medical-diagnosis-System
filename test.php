<?php
session_start();
require_once 'DiagnosisHandler.php';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Check if diagnosis ID is provided
if (!isset($_GET['id'])) {
    header("Location: welcome.php");
    exit();
}

$diagnosis_id = $_GET['id'];
$patient_id = $_SESSION['id'];

// Database connection
$con = mysqli_connect("localhost", "root", "", "medical");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize DiagnosisHandler
$diagnosisHandler = new DiagnosisHandler($con);

// Get diagnosis data, ensuring it belongs to the current patient
$diagnosis = $diagnosisHandler->getDiagnosis($diagnosis_id, $patient_id);
if (!$diagnosis) {
    $_SESSION['error'] = "Diagnosis not found or access denied.";
    header("Location: welcome.php");
    exit();
}

// Get patient data
$patient = $diagnosisHandler->getPatient($patient_id);

// Get recommended hospitals
$hospitals = $diagnosisHandler->getDiagnosisHospitals($diagnosis_id);

// Get recommended medications
$medications = $diagnosisHandler->getDiagnosisMedications($diagnosis_id);

// Calculate diagnosis age
$diagnosis_date = new DateTime($diagnosis['diagnosis_date']);
$now = new DateTime();
$interval = $now->diff($diagnosis_date);
$days_old = $interval->days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosis Results - HealthGuard</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .diagnosis-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .confidence-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }
        
        .hospital-card {
            border-radius: 8px;
            border-left: 4px solid var(--secondary-color);
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }
        
        .medication-card {
            border-radius: 8px;
            border-left: 4px solid var(--success-color);
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }
        
        .advice-card {
            background-color: rgba(52, 152, 219, 0.1);
            border-left: 4px solid var(--secondary-color);
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        .category-badge {
            padding: 0.35rem 0.7rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-left: 0.5rem;
        }
        
        .patient-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .star-rating {
            color: #f39c12;
        }
        
        .print-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        @media print {
            .navbar, .print-btn, .btn-back {
                display: none;
            }
            
            body {
                background-color: white;
            }
            
            .container {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-heartbeat me-2"></i>HealthGuard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        <?php echo htmlspecialchars($patient['fname'] . ' ' . $patient['lname']); ?>
                    </span>
                    <a href="logout.php" class="nav-link text-warning">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col-md-12">
                <a href="welcome.php" class="btn btn-outline-secondary btn-back">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="diagnosis-card">
                    <div class="result-header">
                        <div>
                            <h2 class="mb-2">
                                <?php echo htmlspecialchars($diagnosis['condition_name']); ?>
                                <span class="category-badge bg-<?php echo ($diagnosis['condition_category'] === 'Chronic') ? 'warning' : 'info'; ?>">
                                    <?php echo htmlspecialchars($diagnosis['condition_category']); ?>
                                </span>
                            </h2>
                            <div class="patient-info">
                                <div><strong>Patient:</strong> <?php echo htmlspecialchars($patient['fname'] . ' ' . $patient['lname']); ?></div>
                                <div><strong>Birth Place:</strong> <?php echo htmlspecialchars($patient['birth_place'] ?? 'Not specified'); ?></div>
                                <div><strong>Current City:</strong> <?php echo htmlspecialchars($patient['current_city'] ?? 'Not specified'); ?></div>
                                <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($diagnosis['diagnosis_date'])); ?></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-center mb-2">Diagnostic Confidence</div>
                            <div class="confidence-badge bg-<?php echo ($diagnosis['confidence'] > 80) ? 'success' : (($diagnosis['confidence'] > 60) ? 'warning' : 'danger'); ?>">
                                <?php echo number_format($diagnosis['confidence'], 2); ?>%
                            </div>
                            <?php if ($days_old > 30): ?>
                                <div class="mt-2 text-danger small">
                                    <i class="fas fa-exclamation-triangle me-1"></i> This diagnosis is over a month old
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4><i class="fas fa-clipboard-check me-2"></i>Reported Symptoms</h4>
                            <p><?php echo nl2br(htmlspecialchars($diagnosis['symptoms'])); ?></p>
                            
                            <div class="severity-indicator mb-2">
                                <strong>Severity Level:</strong>
                                <span class="badge bg-<?php echo strtolower($diagnosis['severity_level']) === 'high' ? 'danger' : (strtolower($diagnosis['severity_level']) === 'medium' ? 'warning' : 'success'); ?> ms-2">
                                    <?php echo htmlspecialchars($diagnosis['severity_level']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4><i class="fas fa-hospital me-2"></i>Recommended Healthcare Facilities</h4>
                            <?php if (empty($hospitals)): ?>
                                <p class="text-muted">No specific healthcare facilities recommended.</p>
                            <?php else: ?>
                                <?php foreach ($hospitals as $hospital): ?>
                                    <div class="hospital-card">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h5><?php echo htmlspecialchars($hospital['hospital_name']); ?></h5>
                                                <div><strong>Location:</strong> <?php echo htmlspecialchars($hospital['location'] . ', ' . $hospital['county']); ?></div>
                                                <div><strong>Contact:</strong> <?php echo htmlspecialchars($hospital['contact']); ?></div>
                                                <div><strong>Specialties:</strong> <?php echo htmlspecialchars(str_replace(';', ', ', $hospital['specialties'])); ?></div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="star-rating">
                                                    <?php 
                                                    $rating = floatval($hospital['rating']);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<i class="fas fa-star"></i>';
                                                        } elseif ($i - 0.5 <= $rating) {
                                                            echo '<i class="fas fa-star-half-alt"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star"></i>';
                                                        }
                                                    }
                                                    ?>
                                                    <div class="mt-2"><?php echo number_format($hospital['rating'], 1); ?>/5</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h4><i class="fas fa-pills me-2"></i>Recommended Medications</h4>
                            <div class="small text-danger mb-2">
                                <i class="fas fa-exclamation-circle me-1"></i> Always consult with a healthcare professional before taking any medication.
                            </div>
                            <?php if (empty($medications)): ?>
                                <p class="text-muted">No specific medications recommended.</p>
                            <?php else: ?>
                                <?php foreach ($medications as $medication): ?>
                                    <div class="medication-card">
                                        <h5><?php echo htmlspecialchars($medication['medication_name']); ?></h5>
                                        <div><strong>Dosage:</strong> <?php echo htmlspecialchars($medication['dosage']); ?></div>
                                        <div><strong>Frequency:</strong> <?php echo htmlspecialchars($medication['frequency']); ?></div>
                                        <div><strong>Instructions:</strong> <?php echo htmlspecialchars($medication['instructions']); ?></div>
                                        <div class="mt-2 text-danger small">
                                            <strong>Possible Side Effects:</strong> <?php echo htmlspecialchars($medication['side_effects']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fas fa-comment-medical me-2"></i>Medical Advice</h4>
                            <div class="advice-card">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($diagnosis['recommendations'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button onclick="window.print()" class="btn btn-primary print-btn">
        <i class="fas fa-print"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


Code of diagnosis_results.php