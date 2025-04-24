<?php
/**
 * Class to handle diagnosis-related functions
 */
class DiagnosisHandler {
    private $con;
    
    /**
     * Constructor
     */
    public function __construct($connection) {
        $this->con = $connection;
    }
    
    /**
     * Get a diagnosis by ID
     */
    public function getDiagnosis($diagnosis_id, $patient_id = null) {
        $query = "SELECT * FROM diagnoses WHERE id = ?";
        $params = ["i", $diagnosis_id];
        
        // If patient_id is provided, ensure diagnosis belongs to the patient
        if ($patient_id !== null) {
            $query .= " AND patient_id = ?";
            $params = ["ii", $diagnosis_id, $patient_id];
        }
        
        $stmt = $this->con->prepare($query);
        $stmt->bind_param(...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get hospitals recommended for a diagnosis
     */
    public function getDiagnosisHospitals($diagnosis_id) {
        $query = "SELECT * FROM diagnosis_hospitals WHERE diagnosis_id = ? ORDER BY rating DESC";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $diagnosis_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get medications recommended for a diagnosis
     */
    public function getDiagnosisMedications($diagnosis_id) {
        $query = "SELECT * FROM diagnosis_medications WHERE diagnosis_id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $diagnosis_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get patient information
     */
    public function getPatient($patient_id) {
        $query = "SELECT * FROM patients WHERE id = ?";
        $stmt = $this->con->prepare($query);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
}
?>