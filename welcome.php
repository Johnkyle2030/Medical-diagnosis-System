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

// Get user data
$patient_id = $_SESSION['id'];
$query = "SELECT * FROM patients WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user's previous diagnoses
$query = "SELECT * FROM diagnoses WHERE patient_id = ? ORDER BY diagnosis_date DESC LIMIT 5";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$recent_diagnoses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Medical Diagnosis System</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
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
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            cursor: pointer;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }
        
        .symptom-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .symptom-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .severity-slider {
            display: none;
        }
        
        .severity-slider.active {
            display: block;
            margin-top: 0.5rem;
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
                        Welcome, <?php echo htmlspecialchars($user['fname'] . ' ' . $user['lname']); ?>
                    </span>
                    <a href="logout.php" class="nav-link text-warning">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
    <div class="row">
            <!-- Quick Stats Section -->
            <div class="col-md-12 mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h4><i class="fas fa-stethoscope me-2"></i>Last Checkup</h4>
                            <p class="mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h4><i class="fas fa-heartbeat me-2"></i>Health Score</h4>
                            <p class="mb-0"></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h4><i class="fas fa-calendar-check me-2"></i>Next Appointment</h4>
                            <p class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Main Features -->
        <div class="row">
            <div class="col-md-6">
                <div class="feature-card" data-bs-toggle="modal" data-bs-target="#diagnosisModal">
                    <div class="feature-icon">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h4>Start New Diagnosis</h4>
                    <p>Check your symptoms and get instant health recommendations.</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card" data-bs-toggle="modal" data-bs-target="#historyModal">
                    <div class="feature-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h4>View History</h4>
                    <p>Access your previous diagnoses and recommendations.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
                        <div class="feature-card" data-bs-toggle="modal" data-bs-target="#appointmentModal">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h4>Schedule Appointment</h4>
                            <p>Book appointments with healthcare providers.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card" data-bs-toggle="modal" data-bs-target="#medicationModal">
                            <div class="feature-icon">
                                <i class="fas fa-pills"></i>
                            </div>
                            <h4>Medication Tracker</h4>
                            <p>Track your medications and get reminders.</p>
                        </div>
                    </div>
                </div>
            </div>


        <!-- Recent Diagnoses -->
        <?php if (!empty($recent_diagnoses)): ?>
        <div class="mt-4">
            <h4><i class="fas fa-clipboard-list me-2"></i>Recent Diagnoses</h4>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Condition</th>
                            <th>Severity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_diagnoses as $diagnosis): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($diagnosis['diagnosis_date'])); ?></td>
                            <td><?php echo htmlspecialchars($diagnosis['condition_name']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo strtolower($diagnosis['severity_level']) === 'high' ? 'danger' : (strtolower($diagnosis['severity_level']) === 'medium' ? 'warning' : 'success'); ?>">
                                    <?php echo htmlspecialchars($diagnosis['severity_level']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="diagnosis_results.php?id=<?php echo $diagnosis['id']; ?>" class="btn btn-sm btn-primary">
                                    View Details
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Diagnosis Modal -->
    <div class="modal fade" id="diagnosisModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Start New Diagnosis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="diagnosisForm" action="process_diagnosis.php" method="POST">
                        <input type="hidden" name="diagnosis_form" value="1">
                        
                        <!-- Symptom Selection -->
                        <div class="mb-4">
    <label class="form-label fw-bold">Enter Your Symptoms</label>
    <textarea class="form-control" name="custom_symptoms" rows="4" 
              placeholder="Enter your symptoms here... e.g. I'm feeling dizzy, "></textarea>
</div>

                        <!-- Duration Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">How long have you been experiencing these symptoms?</label>
                            <select class="form-select" name="duration" required>
                                <option value="">Select duration</option>
                                <option value="less_than_day">Less than 24 hours</option>
                                <option value="1_3_days">1-3 days</option>
                                <option value="4_7_days">4-7 days</option>
                                <option value="1_2_weeks">1-2 weeks</option>
                                <option value="more_than_2_weeks">More than 2 weeks</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-stethoscope me-2"></i>Start Assessment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diagnosis History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (empty($recent_diagnoses)): ?>
                        <p class="text-center">No previous diagnoses found.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Condition</th>
                                        <th>Severity</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_diagnoses as $diagnosis): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($diagnosis['diagnosis_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($diagnosis['condition_name']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo strtolower($diagnosis['severity_level']) === 'high' ? 'danger' : (strtolower($diagnosis['severity_level']) === 'medium' ? 'warning' : 'success'); ?>">
                                                <?php echo htmlspecialchars($diagnosis['severity_level']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="diagnosis_results.php?id=<?php echo $diagnosis['id']; ?>" 
                                               class="btn btn-sm btn-primary">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide severity sliders based on symptom selection
        document.querySelectorAll('.symptom-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const symptomKey = this.id.replace('symptom_', '');
                const severitySlider = document.getElementById('severity_' + symptomKey);
                if (this.checked) {
                    severitySlider.classList.add('active');
                } else {
                    severitySlider.classList.remove('active');
                }
            });
        });

      
    </script>
    <!-- Medication Modal -->

                
    <div class="modal fade" id="medicationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Medication Tracker</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php
                // Ensure database connection is established
                if (!isset($con) || !$con) {
                    $con = mysqli_connect("localhost", "username", "password", "database_name");
                    if (mysqli_connect_errno()) {
                        echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        exit();
                    }
                }
                
                // Get patient ID from session or request parameter if not already defined
                if (!isset($patient_id)) {
                    $patient_id = $_SESSION['patient_id'] ?? $_GET['patient_id'] ?? null;
                    if (!$patient_id) {
                        echo '<div class="alert alert-danger">Patient ID not provided</div>';
                        exit();
                    }
                }
                
                // Get user's medications with diagnosis information
                $query = "SELECT m.*, d.condition_name, d.condition_category FROM medications m 
                          LEFT JOIN diagnoses d ON m.diagnosis_id = d.id 
                          WHERE m.patient_id = ? 
                          ORDER BY m.start_date DESC";
                $stmt = $con->prepare($query);
                $stmt->bind_param("i", $patient_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                // Check if query was successful
                if (!$result) {
                    echo '<div class="alert alert-danger">Error retrieving medications: ' . $con->error . '</div>';
                    exit();
                }
                
                $medications = $result->fetch_all(MYSQLI_ASSOC);
                
                // Get diagnoses for the add new medication form dropdown
                $query = "SELECT id, condition_name FROM diagnoses WHERE patient_id = ? ORDER BY diagnosis_date DESC";
                $stmt = $con->prepare($query);
                $stmt->bind_param("i", $patient_id);
                $stmt->execute();
                $diagnoses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                
                if (empty($medications)):
                ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>You currently have no active medications.
                    </div>
                    
                    <div class="text-center mb-3">
                        <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#addMedicationForm">
                            <i class="fas fa-plus me-2"></i>Add New Medication
                        </button>
                    </div>
                <?php else: ?>
                    <div class="mb-3">
                        <button class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#addMedicationForm">
                            <i class="fas fa-plus me-2"></i>Add New Medication
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Medication</th>
                                    <th>Dosage</th>
                                    <th>Schedule</th>
                                    <th>For Condition</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($medications as $med): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($med['medication_name']); ?></td>
                                    <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                    <td><?php echo htmlspecialchars($med['schedule']); ?></td>
                                    <td><?php echo htmlspecialchars($med['condition_name'] ?? 'Unknown'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($med['start_date'])); ?></td>
                                    <td><?php echo $med['end_date'] ? date('M d, Y', strtotime($med['end_date'])) : 'Ongoing'; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#medInfo<?php echo $med['id']; ?>">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="editMedication(<?php echo $med['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="process_medication.php?action=complete&id=<?php echo $med['id']; ?>&patient_id=<?php echo $patient_id; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="process_medication.php?action=delete&id=<?php echo $med['id']; ?>&patient_id=<?php echo $patient_id; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this medication?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="collapse" id="medInfo<?php echo $med['id']; ?>">
                                    <td colspan="7">
                                        <div class="card card-body">
                                            <h6>Healing Advice for <?php echo htmlspecialchars($med['condition_name'] ?? 'This Condition'); ?>:</h6>
                                            <p><?php echo nl2br(htmlspecialchars($med['healing_advice'] ?? 'No specific advice available')); ?></p>
                                            <h6>Additional Notes:</h6>
                                            <p><?php echo nl2br(htmlspecialchars($med['notes'] ?? 'No notes available')); ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <!-- Add New Medication Form -->
                <div class="collapse" id="addMedicationForm">
                    <div class="card card-body mb-3">
                        <h5 class="mb-3">Add New Medication</h5>
                        <form action="process_medication.php" method="post">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="medication_name" class="form-label">Medication Name</label>
                                    <input type="text" class="form-control" id="medication_name" name="medication_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="diagnosis_id" class="form-label">For Condition</label>
                                    <select class="form-select" id="diagnosis_id" name="diagnosis_id">
                                        <option value="">-- Select Condition --</option>
                                        <?php foreach ($diagnoses as $diag): ?>
                                            <option value="<?php echo $diag['id']; ?>"><?php echo htmlspecialchars($diag['condition_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="dosage" class="form-label">Dosage</label>
                                    <input type="text" class="form-control" id="dosage" name="dosage" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="schedule" class="form-label">Schedule</label>
                                    <input type="text" class="form-control" id="schedule" name="schedule" placeholder="e.g. 3 times daily" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date (optional)</label>
                                <input type="date" class="form-control" id="end_date" name="end_date">
                            </div>
                            
                            <div class="mb-3">
                                <label for="healing_advice" class="form-label">Healing Advice</label>
                                <textarea class="form-control" id="healing_advice" name="healing_advice" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#addMedicationForm">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Medication</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Edit Medication Form (Initially Hidden) -->
                <div class="modal fade" id="editMedicationModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Medication</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form action="process_medication.php" method="post" id="editMedicationForm">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                                    <input type="hidden" name="medication_id" id="edit_medication_id">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="edit_medication_name" class="form-label">Medication Name</label>
                                            <input type="text" class="form-control" id="edit_medication_name" name="medication_name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="edit_diagnosis_id" class="form-label">For Condition</label>
                                            <select class="form-select" id="edit_diagnosis_id" name="diagnosis_id">
                                                <option value="">-- Select Condition --</option>
                                                <?php foreach ($diagnoses as $diag): ?>
                                                    <option value="<?php echo $diag['id']; ?>"><?php echo htmlspecialchars($diag['condition_name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="edit_dosage" class="form-label">Dosage</label>
                                            <input type="text" class="form-control" id="edit_dosage" name="dosage" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="edit_schedule" class="form-label">Schedule</label>
                                            <input type="text" class="form-control" id="edit_schedule" name="schedule" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="edit_start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="edit_start_date" name="start_date" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_end_date" class="form-label">End Date (optional)</label>
                                        <input type="date" class="form-control" id="edit_end_date" name="end_date">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_healing_advice" class="form-label">Healing Advice</label>
                                        <textarea class="form-control" id="edit_healing_advice" name="healing_advice" rows="2"></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="edit_notes" class="form-label">Additional Notes</label>
                                        <textarea class="form-control" id="edit_notes" name="notes" rows="2"></textarea>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Medication</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <script>
                // Function to fetch medication details and open edit modal
                function editMedication(medicationId) {
                    // Use AJAX to fetch medication details
                    fetch('get_medication.php?id=' + medicationId)
                        .then(response => response.json())
                        .then(data => {
                            // Populate the edit form
                            document.getElementById('edit_medication_id').value = data.id;
                            document.getElementById('edit_medication_name').value = data.medication_name;
                            document.getElementById('edit_diagnosis_id').value = data.diagnosis_id || '';
                            document.getElementById('edit_dosage').value = data.dosage;
                            document.getElementById('edit_schedule').value = data.schedule;
                            document.getElementById('edit_start_date').value = data.start_date;
                            document.getElementById('edit_end_date').value = data.end_date || '';
                            document.getElementById('edit_healing_advice').value = data.healing_advice || '';
                            document.getElementById('edit_notes').value = data.notes || '';
                            
                            // Show the modal
                            var editModal = new bootstrap.Modal(document.getElementById('editMedicationModal'));
                            editModal.show();
                        })
                        .catch(error => {
                            console.error('Error fetching medication details:', error);
                            alert('Error loading medication details. Please try again.');
                        });
                }
                </script>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>