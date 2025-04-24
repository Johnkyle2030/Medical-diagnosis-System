#!/usr/bin/env python3
import sys
import json
import pandas as pd
import re
import random
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

# Set up logging
import os
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
    
    with open(log_file, 'a') as f:
        f.write(f"Loading datasets from: {train_data_path}, {hospitals_data_path}, {medications_data_path}\n")
    
    # Load disease dataset
    train_data = pd.read_csv(train_data_path)
    
    # Load hospitals dataset
    hospitals_data = pd.read_csv(hospitals_data_path)
    
    # Load medications dataset
    medications_data = pd.read_csv(medications_data_path)
    
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

    # Get condition category (just an example - in real system this would be more sophisticated)
    condition_category = "Chronic" if confidence > 80 else "Acute"

    # Filter hospitals based on specialties that might treat this disease
    def get_relevant_specialties(disease):
        # Map diseases to relevant specialties (simplified example)
        specialty_map = {
            'Jaundice': ['General Medicine', 'Gastroenterology'],
            'Malaria': ['General Medicine', 'Infectious Disease'],
            'Tuberculosis': ['Pulmonology', 'Infectious Disease'],
            'Dengue': ['General Medicine', 'Infectious Disease'],
            'Typhoid': ['General Medicine', 'Infectious Disease'],
            'Diabetes': ['Endocrinology', 'General Medicine'],
            'Hypertension': ['Cardiology', 'General Medicine'],
            'Heart Attack': ['Cardiology', 'Emergency Medicine'],
            'Pneumonia': ['Pulmonology', 'General Medicine'],
            'Asthma': ['Pulmonology', 'Allergy'],
            # Add more mappings as needed
        }
        
        # Return relevant specialties or default to General Medicine
        return specialty_map.get(disease, ['General Medicine'])

    # Get matching hospitals
    relevant_specialties = get_relevant_specialties(disease)

    # Find matching hospitals
    matching_hospitals = []
    for _, hospital in hospitals_data.iterrows():
        hospital_specialties = str(hospital['specialties']).split(';')
        if any(specialty in hospital_specialties for specialty in relevant_specialties):
            matching_hospitals.append({
                "name": hospital['name'],
                "specialties": hospital['specialties'],
                "location": hospital['location'],
                "county": hospital['county'],
                "contact": hospital['contact'],
                "rating": float(hospital['rating'])
            })

    # Sort hospitals by rating
    matching_hospitals.sort(key=lambda x: x['rating'], reverse=True)
    matching_hospitals = matching_hospitals[:3]  # Take top 3

    with open(log_file, 'a') as f:
        f.write(f"Found {len(matching_hospitals)} matching hospitals\n")

    # Get relevant medications for the disease
    def get_relevant_medications(disease):
        # Filter medications for the disease
        relevant_meds = medications_data[medications_data['disease'].str.contains(disease, case=False, na=False)]
        
        # If no specific medications found, return empty list
        if relevant_meds.empty:
            return []
        
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

    # Get medications for the diagnosed disease
    medications = get_relevant_medications(disease)

    with open(log_file, 'a') as f:
        f.write(f"Found {len(medications)} relevant medications\n")

    # If no specific medications found, provide generic advice
    if not medications:
        # Just for demo purposes, let's add a mock medication
        medications = [{
            "name": "Consult a doctor",
            "dosage": "As prescribed",
            "instructions": "Seek professional medical advice",
            "frequency": "As directed",
            "side_effects": "Medications should only be taken under medical supervision"
        }]

    # Generate medical advice based on the condition
    def generate_advice(disease, confidence, condition_category):
        if condition_category == "Chronic":
            advice = "Consult with a healthcare professional for personalized advice regarding your condition. "
            advice += "These long-lasting symptoms require thorough medical evaluation. "
            advice += "Consider seeing a specialist for ongoing management."
        else:
            advice = "Your symptoms suggest a possible acute condition. "
            advice += "It's recommended to follow up with a healthcare provider for confirmation. "
            advice += "Rest and stay hydrated while awaiting professional medical advice."
        
        return advice

    # Generate advice
    advice = generate_advice(disease, confidence, condition_category)

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