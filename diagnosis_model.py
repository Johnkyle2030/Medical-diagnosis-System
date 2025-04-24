#!/usr/bin/env python3
import sys
import json
import pandas as pd
import re
import random
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import os

# Set up logging
log_file = 'python_model_debug.log'
with open(log_file, 'a') as f:
    f.write(f"Script started\n")

# Get input symptoms from command line arguments or stdin
if len(sys.argv) > 1:
    input_symptoms = sys.argv[1]
    with open(log_file, 'a') as f:
        f.write(f"Got symptoms from command line: {input_symptoms}\n")
else:
    # Try to read from stdin
    try:
        input_symptoms = sys.stdin.read().strip()
        with open(log_file, 'a') as f:
            f.write(f"Got symptoms from stdin: {input_symptoms}\n")
        # If no input from stdin, use default
        if not input_symptoms:
            input_symptoms = "I'm feeling dizzy and have yellow skin"
            with open(log_file, 'a') as f:
                f.write(f"Using default symptoms: {input_symptoms}\n")
    except Exception as e:
        input_symptoms = "I'm feeling dizzy and have yellow skin"
        with open(log_file, 'a') as f:
            f.write(f"Error reading stdin, using default: {str(e)}\n")

# Get current script directory
script_dir = os.path.dirname(os.path.abspath(__file__))
with open(log_file, 'a') as f:
    f.write(f"Script directory: {script_dir}\n")

# Load datasets
try:
    # Construct full paths
    train_data_path = os.path.join(script_dir, 'csv_datasets/Train_data.csv')
    hospitals_data_path = os.path.join(script_dir, 'csv_datasets/kenyan_hospitals.csv')
    medications_data_path = os.path.join(script_dir, 'csv_datasets/medications.csv')
    disease_advice_path = os.path.join(script_dir, 'csv_datasets/disease_advice.csv')
    
    with open(log_file, 'a') as f:
        f.write(f"Loading datasets from: {train_data_path}, {hospitals_data_path}, {medications_data_path}, {disease_advice_path}\n")
    
    # Load disease dataset
    train_data = pd.read_csv(train_data_path)
    
    # Load hospitals dataset
    hospitals_data = pd.read_csv(hospitals_data_path)
    
    # Load medications dataset
    medications_data = pd.read_csv(medications_data_path)
    
    # Load disease advice dataset
    disease_advice_data = pd.read_csv(disease_advice_path)
    
    with open(log_file, 'a') as f:
        f.write(f"Successfully loaded datasets\n")
except Exception as e:
    # Return error if datasets can't be loaded
    with open(log_file, 'a') as f:
        f.write(f"Error loading datasets: {str(e)}\n")
    
    error_response = {
        "error": f"Could not load datasets: {str(e)}"
    }
    print(json.dumps(error_response))
    sys.exit(1)

try:
    # Create TF-IDF vectorizer
    vectorizer = TfidfVectorizer(stop_words='english')

    # Fit and transform the training data
    train_vectors = vectorizer.fit_transform(train_data['text'])

    # Transform the input symptoms
    input_vector = vectorizer.transform([input_symptoms])

    # Calculate cosine similarity
    similarity_scores = cosine_similarity(input_vector, train_vectors).flatten()

    # Get the most similar disease
    max_index = similarity_scores.argmax()
    disease = train_data.iloc[max_index]['label']
    confidence = similarity_scores[max_index] * 100

    with open(log_file, 'a') as f:
        f.write(f"Diagnosis: {disease}, Confidence: {confidence}\n")

    # Get condition category
    def determine_condition_category(disease):
        # Define chronic and acute conditions
        chronic_conditions = [
            'Diabetes', 'Hypertension', 'Bronchial Asthma', 'Arthritis', 'Psoriasis', 
            'Varicose Veins', 'Cervical spondylosis', 'gastroesophageal reflux disease'
        ]
        acute_conditions = [
            'Jaundice', 'Malaria', 'Typhoid', 'Dengue', 'Pneumonia', 'Common Cold',
            'Chicken pox', 'Impetigo', 'urinary tract infection', 'Migraine',
            'Fungal infection', 'peptic ulcer disease', 'drug reaction', 'allergy'
        ]
        
        if disease in chronic_conditions:
            return "Chronic"
        elif disease in acute_conditions:
            return "Acute"
        else:
            # Default
            return "Unknown"
    
    condition_category = determine_condition_category(disease)

    # Map diseases to relevant specialties
    def get_relevant_specialties(disease):
        specialty_map = {
            'Jaundice': ['Gastroenterology', 'Hepatology', 'Internal Medicine'],
            'Malaria': ['Infectious Disease', 'Internal Medicine'],
            'Typhoid': ['Infectious Disease', 'Internal Medicine'],
            'Dengue': ['Infectious Disease', 'Internal Medicine'],
            'Pneumonia': ['Pulmonology', 'Internal Medicine'],
            'Common Cold': ['General Medicine', 'Family Medicine'],
            'Chicken pox': ['Infectious Disease', 'Dermatology'],
            'Impetigo': ['Dermatology', 'Infectious Disease'],
            'Psoriasis': ['Dermatology'],
            'Fungal infection': ['Dermatology', 'Infectious Disease'],
            'Varicose Veins': ['Vascular Surgery', 'General Surgery'],
            'Diabetes': ['Endocrinology', 'Internal Medicine'],
            'Hypertension': ['Cardiology', 'Internal Medicine'],
            'Bronchial Asthma': ['Pulmonology', 'Allergy and Immunology'],
            'Arthritis': ['Rheumatology', 'Orthopedics'],
            'Acne': ['Dermatology'],
            'urinary tract infection': ['Urology', 'Nephrology'],
            'allergy': ['Allergy and Immunology', 'Dermatology'],
            'gastroesophageal reflux disease': ['Gastroenterology'],
            'drug reaction': ['Allergy and Immunology', 'Dermatology'],
            'peptic ulcer disease': ['Gastroenterology'],
            'Migraine': ['Neurology'],
            'Cervical spondylosis': ['Orthopedics', 'Neurology'],
            'Dimorphic Hemorrhoids': ['Colorectal Surgery', 'Gastroenterology']
        }
        
        # Return relevant specialties or default to General Medicine
        return specialty_map.get(disease, ['General Medicine', 'Internal Medicine'])

    # Get matching hospitals
    relevant_specialties = get_relevant_specialties(disease)

    # Find nearby hospitals based on specialties
    def find_matching_hospitals(hospitals_data, relevant_specialties, user_location=None):
        matching_hospitals = []
        for _, hospital in hospitals_data.iterrows():
            hospital_specialties = str(hospital['specialties']).split(';') if pd.notna(hospital['specialties']) else []
            
            # Check if any relevant specialty is in hospital specialties
            match_found = False
            for specialty in relevant_specialties:
                if any(re.search(re.escape(specialty), hosp_spec, re.IGNORECASE) for hosp_spec in hospital_specialties):
                    match_found = True
                    break
            
            # Include hospital if it matches specialties or has no specialties listed (assume general)
            if match_found or not hospital_specialties:
                # Calculate approximate distance based on location if user location is provided
                distance = "Unknown"
                if user_location:
                    # Simple string matching for now - in a real app, use geocoding and actual distance calculation
                    if user_location.lower() in hospital['location'].lower():
                        distance = "Nearby (0-5 km)"
                    elif random.random() < 0.3:  # 30% chance of being relatively close
                        distance = f"{random.randint(5, 20)} km"
                    else:
                        distance = f"{random.randint(20, 100)} km"
                else:
                    # No user location provided
                    major_cities = ['Nairobi', 'Mombasa', 'Kisumu', 'Nakuru', 'Eldoret']
                    if any(city in hospital['location'] for city in major_cities):
                        distance = f"Located in {next((city for city in major_cities if city in hospital['location']), 'major city')}"
                    else:
                        distance = f"{random.randint(5, 100)} km from nearest major city"
                
                matching_hospitals.append({
                    "name": hospital['name'],
                    "location": hospital['location'],
                    "specialization": hospital['specialties'] if pd.notna(hospital['specialties']) else "General Medicine",
                    "contact": hospital['contact'] if pd.notna(hospital['contact']) else "Contact information not available",
                    "rating": float(hospital['rating']) if pd.notna(hospital['rating']) else 0.0,
                    "distance": distance
                })

        # Sort hospitals by rating and then by distance (if available)
        matching_hospitals.sort(key=lambda x: (x['rating'], 0 if "Nearby" in x.get('distance', '') else 1), reverse=True)
        return matching_hospitals[:3]  # Take top 3

    # Get user location if available (in real app, this would come from user input)
    user_location = None  # Example: "Nairobi"
    matching_hospitals = find_matching_hospitals(hospitals_data, relevant_specialties, user_location)

    with open(log_file, 'a') as f:
        f.write(f"Found {len(matching_hospitals)} matching hospitals\n")

    # Get relevant medications for the disease
    def get_relevant_medications(disease, medications_data):
        try:
            # Case-insensitive matching of disease name
            disease_pattern = re.escape(disease)
            disease_mask = medications_data['disease'].str.contains(disease_pattern, case=False, na=False, regex=True)
            
            relevant_meds = medications_data[disease_mask]
            
            # If no specific medications found, provide a general response
            if relevant_meds.empty:
                with open(log_file, 'a') as f:
                    f.write(f"No medications found for '{disease}'. Returning general advice.\n")
                
                return [{
                    "name": f"Consult a doctor for {disease}",
                    "dosage": "As prescribed by healthcare provider",
                    "instructions": "This condition requires proper medical evaluation before medication",
                    "frequency": "As directed by your healthcare provider", 
                    "side_effects": "Medications should only be taken under medical supervision"
                }]
            
            # Convert to list of dictionaries
            medications_list = []
            for _, med in relevant_meds.iterrows():
                medications_list.append({
                    "name": med['name'],
                    "dosage": med['dosage'],
                    "instructions": med['instructions'],
                    "frequency": med['frequency'],
                    "side_effects": med['side_effects']
                })
            
            return medications_list
            
        except Exception as e:
            with open(log_file, 'a') as f:
                f.write(f"Error in get_relevant_medications: {str(e)}\n")
            return [{
                "name": "Consult a doctor",
                "dosage": "As prescribed",
                "instructions": "Seek professional medical advice for proper medication",
                "frequency": "As directed", 
                "side_effects": "Medications should only be taken under medical supervision"
            }]

    # Get medications for the diagnosed disease
    medications = get_relevant_medications(disease, medications_data)

    with open(log_file, 'a') as f:
        f.write(f"Found {len(medications)} relevant medications\n")

    # Generate medical advice based on the condition
    def generate_advice(disease, disease_advice_data):
        try:
            # Case-insensitive matching of disease name
            disease_pattern = re.escape(disease)
            advice_mask = disease_advice_data['disease'].str.contains(disease_pattern, case=False, na=False, regex=True)
            
            relevant_advice = disease_advice_data[advice_mask]
            
            # If specific advice is found, return it
            if not relevant_advice.empty:
                return relevant_advice.iloc[0]['advice']
            
            # Default general advice
            return """
1. Consult with a healthcare professional for personalized advice regarding your condition.
2. Rest and stay hydrated while following your doctor's instructions.
3. Take medications only as prescribed by a healthcare professional.
4. Monitor your symptoms and seek emergency care if they worsen significantly.
5. Follow up with your healthcare provider as recommended.
            """
            
        except Exception as e:
            with open(log_file, 'a') as f:
                f.write(f"Error in generate_advice: {str(e)}\n")
            return "Please consult a healthcare professional for advice on your condition."

    # Generate advice
    advice = generate_advice(disease, disease_advice_data)

    # Prepare the response
    diagnosis_result = {
        "diagnosis": disease,
        "confidence": f"{confidence:.2f}%",
        "condition_category": condition_category,
        "hospitals": matching_hospitals,
        "medications": medications,
        "advice": advice
    }

    with open(log_file, 'a') as f:
        f.write(f"Preparing to output JSON result\n")

    # Output as JSON
    print(json.dumps(diagnosis_result))
    
    with open(log_file, 'a') as f:
        f.write(f"Successfully output JSON result\n")
except Exception as e:
    with open(log_file, 'a') as f:
        f.write(f"Error in processing: {str(e)}\n")
    
    error_response = {
        "diagnosis": "Unknown",
        "confidence": "0%",
        "condition_category": "Unknown",
        "hospitals": [],
        "medications": [{
            "name": "Error in diagnosis",
            "dosage": "N/A",
            "instructions": "Please try again or contact support",
            "frequency": "N/A", 
            "side_effects": "N/A"
        }],
        "advice": f"An error occurred during diagnosis: {str(e)}. Please try again or consult a doctor."
    }
    print(json.dumps(error_response))