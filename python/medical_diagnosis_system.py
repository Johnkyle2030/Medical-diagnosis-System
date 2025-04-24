import sys
import json
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score
import os
import re
import pickle
import nltk
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords

# Fix: Correctly download necessary NLTK data
try:
    nltk.download('punkt', quiet=True)
    nltk.download('stopwords', quiet=True)
except Exception as e:
    print(f"Error downloading NLTK resources: {str(e)}")
    # Define a fallback tokenization function
    def word_tokenize(text):
        return text.split()

class MedicalDiagnosisSystem:
    def __init__(self, model_path=None):
        """
        Initialize the medical diagnosis system
        model_path: Path to a saved model file (optional)
        """
        # Set up stopwords with error handling
        try:
            self.stop_words = set(stopwords.words('english'))
        except:
            # Fallback if stopwords aren't available
            self.stop_words = set(['a', 'an', 'the', 'and', 'but', 'if', 'or', 'because', 'as', 'what', 'when', 'where', 'how', 'is', 'are', 'was', 'were'])
        
        # Set paths
        root_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        self.dataset_dir = os.path.join(root_dir, 'csv_datasets')
        self.model_dir = os.path.join(root_dir, 'models')
        
        # Create directories if they don't exist
        os.makedirs(self.dataset_dir, exist_ok=True)
        os.makedirs(self.model_dir, exist_ok=True)
        
        # Load hospitals and medications data with error handling
        try:
            self.hospitals = pd.read_csv(os.path.join(self.dataset_dir, 'kenyan_hospitals.csv'))
            self.medications = pd.read_csv(os.path.join(self.dataset_dir, 'medications.csv'))
        except FileNotFoundError as e:
            print(f"Warning: {str(e)}. Using empty datasets.")
            # Create empty dataframes with required columns if files don't exist
            self.hospitals = pd.DataFrame(columns=['name', 'specialties', 'location', 'county', 'contact', 'rating', 'level'])
            self.medications = pd.DataFrame(columns=['name', 'condition', 'dosage', 'duration_category', 'instructions', 'frequency', 'side_effects'])
        
        # Load or train the model
        if model_path and os.path.exists(model_path):
            self.load_model(model_path)
        else:
            # Train new model
            try:
                self.train_new_model()
            except FileNotFoundError as e:
                print(f"Error: {str(e)}. Cannot train model.")
                sys.exit(1)
                
        # Duration mapping
        self.duration_map = {
            'less_than_day': 'Acute',
            '1_3_days': 'Acute',
            '4_7_days': 'Subacute',
            '1_2_weeks': 'Subacute',
            'more_than_2_weeks': 'Chronic'
        }
    
    def preprocess_text(self, text):
        """Clean and preprocess text data"""
        # Handle None or empty strings
        if not text or pd.isna(text):
            return ""
            
        # Convert to lowercase
        text = str(text).lower()
        # Remove special characters and digits
        text = re.sub(r'[^\w\s]', ' ', text)
        text = re.sub(r'\d+', ' ', text)
        
        # Tokenize with error handling
        try:
            # Check if NLTK resources are available
            nltk_resource_available = True
            try:
                # Try accessing tokenizer to confirm it's loaded properly
                _ = word_tokenize("Test")
            except LookupError:
                nltk_resource_available = False
                
            if nltk_resource_available:
                tokens = word_tokenize(text)
                # Remove stopwords
                tokens = [word for word in tokens if word not in self.stop_words]
                # Join tokens back into string
                return ' '.join(tokens)
            else:
                # Fall back to simple tokenization if NLTK resources not available
                return ' '.join([word for word in text.split() if word not in self.stop_words])
                
        except Exception as e:
            print(f"Warning: Error in text preprocessing: {str(e)}. Using simplified processing.")
            # Fallback simple processing
            return ' '.join([word for word in text.split() if word not in self.stop_words])
    
    def train_new_model(self):
        """Train a model using text descriptions of symptoms"""
        # Load training data
        train_data_path = os.path.join(self.dataset_dir, 'Train_data.csv')
        
        if not os.path.exists(train_data_path):
            raise FileNotFoundError(f"Training data not found at {train_data_path}")
            
        symptoms_data = pd.read_csv(train_data_path)
        
        # Ensure required columns exist
        required_columns = ['label', 'text']
        for col in required_columns:
            if col not in symptoms_data.columns:
                raise ValueError(f"Required column '{col}' not found in training data")
        
        # Preprocess text data
        symptoms_data['processed_text'] = symptoms_data['text'].apply(self.preprocess_text)
        
        # Split data
        X = symptoms_data['processed_text']
        y = symptoms_data['label']
        
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
        
        # Vectorize text features
        self.vectorizer = TfidfVectorizer(max_features=5000)
        X_train_vectorized = self.vectorizer.fit_transform(X_train)
        
        # Train model
        self.model = RandomForestClassifier(n_estimators=100, random_state=42)
        self.model.fit(X_train_vectorized, y_train)
        
        # Evaluate model
        X_test_vectorized = self.vectorizer.transform(X_test)
        y_pred = self.model.predict(X_test_vectorized)
        accuracy = accuracy_score(y_test, y_pred)
        print(f"Model training complete. Accuracy: {accuracy:.2f}")
        
        # Save the model
        self.save_model()
        
    def save_model(self, filename='medical_diagnosis_model.pkl'):
        """Save the trained model and vectorizer to disk"""
        model_path = os.path.join(self.model_dir, filename)
        with open(model_path, 'wb') as f:
            pickle.dump({'model': self.model, 'vectorizer': self.vectorizer}, f)
        print(f"Model saved to {model_path}")
    
    def load_model(self, model_path):
        """Load a trained model from disk"""
        try:
            with open(model_path, 'rb') as f:
                model_data = pickle.load(f)
                self.model = model_data['model']
                self.vectorizer = model_data['vectorizer']
            print(f"Model loaded from {model_path}")
        except Exception as e:
            raise Exception(f"Failed to load model: {str(e)}")
    
    def analyze_symptoms(self, symptoms_data):
        """
        Analyze symptoms and return diagnosis and recommendations
        
        Parameters:
        symptoms_data: dict with symptom_description, duration, and location info
        
        Returns:
        dict with diagnosis, confidence, condition_category, hospitals, medications
        """
        # Process symptoms text
        symptom_description = symptoms_data.get('symptom_description', '')
        processed_text = self.preprocess_text(symptom_description)
        
        # Get duration information
        duration = symptoms_data.get('duration', 'more_than_2_weeks')
        duration_category = self.duration_map.get(duration, 'Subacute')
        
        # Get location information
        current_city = symptoms_data.get('current_city', '')
        birth_place = symptoms_data.get('birth_place', '')
        
        # Predict condition
        X_test = self.vectorizer.transform([processed_text])
        condition, confidence = self._predict_condition(X_test)
        
        # Get recommendations based on condition, duration, and location
        hospitals = self._recommend_hospitals(condition, current_city)
        medications = self._recommend_medications(condition, duration_category)
        
        # Validate and format the response
        formatted_response = {
            'diagnosis': condition if condition else 'Unknown Condition',
            'confidence': f"{confidence:.2f}%",
            'condition_category': duration_category,
            'hospitals': self._format_hospital_data(hospitals),
            'medications': self._format_medication_data(medications),
            'advice': self._get_condition_advice(condition, duration_category)
        }
        
        return formatted_response
    
    def _predict_condition(self, X_test):
        """Predict condition and return with confidence percentage"""
        # Get prediction probabilities
        probs = self.model.predict_proba(X_test)[0]
        conditions = self.model.classes_
        
        # Get the highest probability and corresponding condition
        max_prob_index = np.argmax(probs)
        max_prob = probs[max_prob_index]
        predicted_condition = conditions[max_prob_index]
        
        # Convert to percentage
        confidence = max_prob * 100
        
        return predicted_condition, confidence
    
    def _recommend_hospitals(self, condition, current_city):
        """Recommend hospitals based on condition and user location"""
        # First, try to find specialty hospitals for the condition
        condition_hospitals = self.hospitals[
            self.hospitals['specialties'].str.contains(condition, case=False, na=False)
        ]
        
        # Then, try to find hospitals in the user's location or nearby
        location_hospitals = self.hospitals[
            (self.hospitals['location'].str.contains(current_city, case=False, na=False)) |
            (self.hospitals['county'].str.contains(current_city, case=False, na=False))
        ]
        
        # If we have both condition and location matches, prioritize those
        if not condition_hospitals.empty and not location_hospitals.empty:
            merged_hospitals = pd.merge(condition_hospitals, location_hospitals, how='inner')
            if not merged_hospitals.empty:
                recommended = merged_hospitals
            else:
                # If no overlap, prioritize condition matches
                recommended = condition_hospitals
        # If we only have condition matches
        elif not condition_hospitals.empty:
            recommended = condition_hospitals
        # If we only have location matches
        elif not location_hospitals.empty:
            recommended = location_hospitals
        # If no matches, use all hospitals
        else:
            recommended = self.hospitals
        
        # Sort by rating and return top 3
        recommended = recommended.sort_values('rating', ascending=False)
        return recommended.head(3).to_dict('records')
    
    def _recommend_medications(self, condition, duration_category):
        """Recommend medications based on condition and duration category"""
        # Filter medications for the condition and appropriate for the duration
        condition_meds = self.medications[
            (self.medications['condition'].str.lower() == condition.lower()) & 
            (self.medications['duration_category'] == duration_category)
        ]
        
        # If no exact matches, try just matching the condition
        if condition_meds.empty:
            condition_meds = self.medications[
                (self.medications['condition'].str.lower() == condition.lower())
            ]
        
        # If still no matches, provide general recommendations based on duration
        if condition_meds.empty:
            condition_meds = self.medications[
                (self.medications['duration_category'] == duration_category)
            ].head(2)
        
        # If still empty, return any medications
        if condition_meds.empty:
            condition_meds = self.medications.head(2)
            
        return condition_meds.to_dict('records')
    
    def _format_medication_data(self, medications):
        """Ensure medication data is properly formatted with all required fields"""
        formatted_medications = []
        for med in medications:
            formatted_med = {
                'name': med.get('name', 'General Medication'),
                'dosage': med.get('dosage', 'As prescribed'),
                'instructions': med.get('instructions', 'Take as directed by physician'),
                'frequency': med.get('frequency', 'As needed'),
                'side_effects': med.get('side_effects', 'Consult doctor for side effects')
            }
            formatted_medications.append(formatted_med)
        
        # If no medications were found, provide a default recommendation
        if not formatted_medications:
            formatted_medications = [{
                'name': 'General Treatment',
                'dosage': 'As prescribed',
                'instructions': 'Consult with healthcare provider',
                'frequency': 'As directed',
                'side_effects': 'Varies based on individual'
            }]
        
        return formatted_medications

    def _format_hospital_data(self, hospitals):
        """Ensure hospital data is properly formatted with all required fields"""
        formatted_hospitals = []
        for hospital in hospitals:
            formatted_hospital = {
                'name': hospital.get('name', 'General Hospital'),
                'specialties': hospital.get('specialties', 'General Medicine'),
                'location': hospital.get('location', 'Kenya'),
                'county': hospital.get('county', 'Unknown'),
                'contact': hospital.get('contact', 'Contact not available'),
                'rating': hospital.get('rating', 3.0)
            }
            formatted_hospitals.append(formatted_hospital)
        
        # If no hospitals were found, provide a default recommendation
        if not formatted_hospitals:
            formatted_hospitals = [{
                'name': 'Kenyatta National Hospital',
                'specialties': 'General Medicine',
                'location': 'Nairobi',
                'county': 'Nairobi',
                'contact': '+254 20 2726300',
                'rating': 4.5
            }]
        
        return formatted_hospitals
    
    def _get_condition_advice(self, condition, duration_category):
        """Provide general advice for the diagnosed condition based on duration"""
        # Base advice for conditions
        base_advice = {
            'Psoriasis': "Keep skin moisturized, avoid triggers like stress and alcohol, get sunlight exposure carefully, follow prescribed medication, avoid scratching affected areas.",
            'Varicose': "Exercise regularly, elevate legs when resting, avoid standing for long periods, wear compression stockings, maintain a healthy weight.",
            'Typhoid': "Stay hydrated, eat bland foods, get plenty of rest, complete the full course of antibiotics, follow hygiene practices, avoid preparing food for others until cleared."
        }
        
        # Duration-specific advice
        duration_advice = {
            'Acute': "This appears to be a recent onset. Rest and monitor symptoms closely. Seek immediate medical attention if symptoms worsen.",
            'Subacute': "You've been experiencing these symptoms for some time. It's important to consult with a healthcare provider for proper evaluation.",
            'Chronic': "These long-lasting symptoms require thorough medical evaluation. Consider seeing a specialist for ongoing management."
        }
        
        condition_advice = base_advice.get(condition, "Consult with a healthcare professional for personalized advice regarding your condition.")
        timing_advice = duration_advice.get(duration_category, "")
        
        return f"{condition_advice} {timing_advice}"

# Script to process symptom data from command line
def process_from_cli():
    """Process symptom data from command line arguments"""
    try:
        if len(sys.argv) < 2:
            raise Exception("No input file provided")

        # Read and validate input data
        try:
            with open(sys.argv[1], 'r') as f:
                input_data = json.load(f)
        except json.JSONDecodeError:
            raise Exception(f"Invalid JSON in {sys.argv[1]}")
        except FileNotFoundError:
            raise Exception(f"Input file {sys.argv[1]} not found")
            
        if not input_data.get('symptom_description'):
            raise Exception("No symptom description provided in input data")

        system = MedicalDiagnosisSystem()
        result = system.analyze_symptoms(input_data)
        
        # Validate result before output
        if not result.get('diagnosis'):
            raise Exception("Failed to generate diagnosis")
            
        print(json.dumps(result, indent=2))

    except Exception as e:
        error_result = {
            "error": str(e),
            "status": "error"
        }
        print(json.dumps(error_result))
        sys.exit(1)

# PHP API endpoint for symptom analysis
def analyze_symptoms_api(symptom_description, duration="more_than_2_weeks", current_city="", birth_place=""):
    """
    API function for PHP to call
    
    Parameters:
    symptom_description: str - User-provided symptom text
    duration: str - How long symptoms have been experienced
    current_city: str - User's current location
    birth_place: str - User's birth place
    
    Returns:
    dict - Analysis results
    """
    try:
        system = MedicalDiagnosisSystem()
        
        # Format input data
        input_data = {
            'symptom_description': symptom_description,
            'duration': duration,
            'current_city': current_city,
            'birth_place': birth_place
        }
        
        # Analyze symptoms
        result = system.analyze_symptoms(input_data)
        return result
        
    except Exception as e:
        return {
            "error": str(e),
            "status": "error"
        }

# Main execution code
if __name__ == "__main__":
    process_from_cli()