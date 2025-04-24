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

// Function to determine gradient based on confidence
function getConfidenceGradient($confidence) {
    if ($confidence > 80) {
        return 'linear-gradient(135deg, #2ecc71, #27ae60)';
    } elseif ($confidence > 60) {
        return 'linear-gradient(135deg, #f39c12, #e67e22)';
    } else {
        return 'linear-gradient(135deg, #e74c3c, #c0392b)';
    }
}

// Function to determine badge class based on severity
function getSeverityClass($severity) {
    $severity = strtolower($severity);
    if ($severity === 'high') {
        return 'danger';
    } elseif ($severity === 'medium') {
        return 'warning';
    } else {
        return 'success';
    }
}
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            padding-bottom: 80px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 1rem 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.4rem;
        }
        
        .diagnosis-card {
            background: white;
            border-radius: 15px;
            padding: 0;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .diagnosis-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .diagnosis-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .diagnosis-category {
            display: inline-block;
            padding: 0.4rem 1rem;
            background: rgba(255,255,255,0.2);
            border-radius: 30px;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .diagnosis-content {
            padding: 2rem;
        }
        
        .patient-panel {
            background: var(--light-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .patient-info {
            flex-grow: 1;
        }
        
        .patient-info h3 {
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .patient-detail {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }
        
        .confidence-meter {
            width: 150px;
            height: 150px;
            background: white;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .confidence-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .confidence-label {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .circle-progress {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            top: 0;
            left: 0;
            border: 10px solid;
            border-color: var(--success-color) var(--success-color) transparent transparent;
            transform: rotate(45deg);
            box-sizing: border-box;
        }
        
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .section-title i {
            margin-right: 0.7rem;
            background: var(--light-color);
            padding: 0.7rem;
            border-radius: 10px;
            color: var(--secondary-color);
        }
        
        .nav-tabs {
            border-bottom: none;
            margin-bottom: 1rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            background: var(--light-color);
            color: var(--dark-color);
            border-radius: 6px;
            padding: 0.7rem 1.2rem;
            margin-right: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link:hover {
            background: rgba(52, 152, 219, 0.1);
        }
        
        .nav-tabs .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }
        
        .hospital-card {
            border-radius: 10px;
            border-left: 5px solid var(--secondary-color);
            padding: 1.2rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hospital-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .medication-card {
            border-radius: 10px;
            border-left: 5px solid var(--success-color);
            padding: 1.2rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .medication-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .advice-card {
            background-color: rgba(52, 152, 219, 0.1);
            border-left: 5px solid var(--secondary-color);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }
        
        .star-rating {
            color: #f39c12;
            font-size: 1.2rem;
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            background: var(--secondary-color);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .print-btn:hover {
            background: var(--primary-color);
            transform: scale(1.1);
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .severity-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .diagnosis-date {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .old-diagnosis-alert {
            background: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            padding: 0.8rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .old-diagnosis-alert i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        
        @media print {
            .navbar, .print-btn, .btn-back, .nav-tabs {
                display: none;
            }
            
            body {
                background-color: white;
                padding-bottom: 0;
            }
            
            .container {
                width: 100%;
                max-width: 100%;
            }
            
            .diagnosis-card {
                box-shadow: none;
            }
            
            .tab-pane {
                display: block !important;
                opacity: 1 !important;
            }
            
            .tab-content > .tab-pane {
                display: block !important;
                opacity: 1 !important;
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
                        <i class="fas fa-user-circle me-1"></i>
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
                    <!-- Diagnosis Banner with Announcement -->
                    <div class="diagnosis-banner">
                        <div class="diagnosis-date">
                            <?php echo date('F j, Y', strtotime($diagnosis['diagnosis_date'])); ?>
                        </div>
                        <h1 class="diagnosis-title">
                            You have been diagnosed with <?php echo htmlspecialchars($diagnosis['condition_name']); ?>
                        </h1>
                        <div class="diagnosis-category">
                            <?php echo htmlspecialchars($diagnosis['condition_category']); ?> Condition
                        </div>
                    </div>

                    <div class="diagnosis-content">
                        <!-- Display alert for old diagnosis -->
                        <?php if ($days_old > 30): ?>
                            <div class="old-diagnosis-alert">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>This diagnosis is over a month old. Please consider consulting with a healthcare professional for an updated assessment.</div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Patient Info and Confidence Panel -->
                        <div class="patient-panel">
                            <div class="patient-info">
                                <h3>Patient Information</h3>
                                <div class="patient-detail"><strong>Name:</strong> <?php echo htmlspecialchars($patient['fname'] . ' ' . $patient['lname']); ?></div>
                                <div class="patient-detail"><strong>Birth Place:</strong> <?php echo htmlspecialchars($patient['birth_place'] ?? 'Not specified'); ?></div>
                                <div class="patient-detail"><strong>Current City:</strong> <?php echo htmlspecialchars($patient['current_city'] ?? 'Not specified'); ?></div>
                                <div class="patient-detail">
                                    <strong>Severity Level:</strong> 
                                    <span class="severity-badge bg-<?php echo getSeverityClass($diagnosis['severity_level']); ?>">
                                        <?php echo htmlspecialchars($diagnosis['severity_level']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="confidence-meter" style="border: 10px solid <?php echo getConfidenceGradient($diagnosis['confidence']); ?>">
                                <div class="confidence-value"><?php echo number_format($diagnosis['confidence'], 2); ?>%</div>
                                <div class="confidence-label">Diagnostic Confidence</div>
                            </div>
                        </div>
                        
                        <!-- Reported Symptoms -->
                        <div class="section-card">
                            <h4 class="section-title">
                                <i class="fas fa-clipboard-check"></i>
                                Reported Symptoms
                            </h4>
                            <p class="symptoms-text"><?php echo nl2br(htmlspecialchars($diagnosis['symptoms'])); ?></p>
                        </div>
                        
                        <!-- Tabbed Content for Hospitals and Medications -->
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="hospitals-tab" data-bs-toggle="tab" data-bs-target="#hospitals" type="button" role="tab" aria-controls="hospitals" aria-selected="true">
                                    <i class="fas fa-hospital me-2"></i>Recommended Hospitals
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="medications-tab" data-bs-toggle="tab" data-bs-target="#medications" type="button" role="tab" aria-controls="medications" aria-selected="false">
                                    <i class="fas fa-pills me-2"></i>Medications & Advice
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="myTabContent">
                            <!-- Hospitals Tab -->
                            <div class="tab-pane fade show active" id="hospitals" role="tabpanel" aria-labelledby="hospitals-tab">
                                <div class="section-card">
                                    <h5 class="mb-3">Please select a healthcare facility from the options below:</h5>
                                    
                                    <?php if (empty($hospitals)): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>No specific healthcare facilities recommended at this time.
                                        </div>
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
                                                        <div class="mt-3">
                                                            <a href="https://maps.google.com/?q=<?php echo urlencode($hospital['hospital_name'] . ', ' . $hospital['location']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-map-marker-alt me-1"></i> View on Map
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Medications Tab -->
                            <div class="tab-pane fade" id="medications" role="tabpanel" aria-labelledby="medications-tab">
                                <div class="section-card">
                                    <h5 class="mb-3">Recommended Medications</h5>
                                    <div class="alert alert-danger mb-3">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <strong>Important:</strong> Always consult with a healthcare professional before taking any medication.
                                    </div>
                                    
                                    <?php if (empty($medications)): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>No specific medications recommended at this time.
                                        </div>
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
                                    
                                    <h5 class="mt-4 mb-3">Medical Advice</h5>
                                    <div class="advice-card">
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($diagnosis['recommendations'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button onclick="window.print()" class="btn print-btn" title="Print Diagnosis Report">
        <i class="fas fa-print"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        
        // Highlight hospitals on hover
        const hospitalCards = document.querySelectorAll('.hospital-card');
        hospitalCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                hospitalCards.forEach(c => c.style.opacity = '0.7');
                this.style.opacity = '1';
            });
            
            card.addEventListener('mouseleave', function() {
                hospitalCards.forEach(c => c.style.opacity = '1');
            });
        });
    </script>
</body>
</html>