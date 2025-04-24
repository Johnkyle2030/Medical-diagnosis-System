<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - Diagnosis System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f6f8f9 0%, #e5ebee 100%);
            font-family: 'Inter', sans-serif;
        }
        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 50px;
        }
        .registration-header {
            background: linear-gradient(to right, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .registration-form {
            padding: 30px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border-color: #e1e5eb;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .btn-primary {
            background: linear-gradient(to right, #4e73df 0%, #224abe 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1);
        }
        .side-image {
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%234e73df" fill-opacity="1" d="M0,160L48,176C96,192,192,224,288,229.3C384,235,480,213,576,197.3C672,181,768,171,864,181.3C960,192,1056,224,1152,229.3C1248,235,1344,213,1392,202.7L1440,192L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0L192,0L96,0L0,0Z"></path></svg>') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .side-image img {
            max-width: 300px;
            opacity: 0.9;
        }
        #password-requirements {
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .requirement {
            color: red;
        }
        .requirement.valid {
            color: green;
        }
        #message {
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .login-link {
            color: #4e73df;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .login-link:hover {
            color: #224abe;
            text-decoration: underline;
        }
        .error-message {
            color: #f55252;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .loader-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.98);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .loader {
            display: inline-block;
            position: relative;
            width: 80px;
            height: 80px;
        }
        .loader div {
            position: absolute;
            width: 16px;
            background: #4e73df;
            animation: loader 1.2s cubic-bezier(0, 0.5, 0.5, 1) infinite;
        }
        .loader div:nth-child(1) {
            left: 8px;
            animation-delay: -0.24s;
        }
        .loader div:nth-child(2) {
            left: 32px;
            animation-delay: -0.12s;
        }
        .loader div:nth-child(3) {
            left: 56px;
            animation-delay: 0;
        }
        @keyframes loader {
            0% {
                top: 8px;
                height: 64px;
            }
            50%, 100% {
                top: 24px;
                height: 32px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row registration-container">
            <div class="col-md-5 d-none d-md-flex side-image">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGRhdGEtbmFtZT0iTGF5ZXIgMSIgdmlld0JveD0iMCAwIDUxMiA1MTIiPjxwYXRoIGZpbGw9IiM0ZTczZGYiIGQ9Ik00MTQuNiAxNjguM2ExMC42IDEwLjYgMCAwIDAtMTAuNiAxMC42djExNy41aC0xMTcuNWExMC42IDEwLjYgMCAwIDAtMTAuNiAxMC42djMxLjhhMTAuNiAxMC42IDAgMCAwIDEwLjYgMTAuNmgxMTcuNXYxMTcuNWExMC42IDEwLjYgMCAwIDAgMTAuNiAxMC42aDMxLjhhMTAuNiAxMC42IDAgMCAwIDEwLjYtMTAuNnYtMTE3LjVoMTE3LjVhMTAuNiAxMC42IDAgMCAwIDEwLjYtMTAuNnYtMzEuOGExMC42IDEwLjYgMCAwIDAtMTAuNi0xMC42aC0xMTcuNXYtMTE3LjVhMTAuNiAxMC42IDAgMCAwLTEwLjYtMTAuNmgtMzEuOHoiLz48Y2lyY2xlIGN4PSIyNTYiIGN5PSIyNTYiIHI9IjIwNS40IiBmaWxsPSJub25lIiBzdHJva2U9IiM0ZTczZGYiIHN0cm9rZS1taXRlcmxpbWl0PSIxMCIgc3Ryb2tlLXdpZHRoPSIzMCIvPjwvc3ZnPg==" alt="Registration Illustration">
            </div>
            <div class="col-md-7 registration-form">
                <div class="registration-header">
                    <h2>Patient Registration</h2>
                    <p class="text-white-50">Create your patient account</p>
                </div>
                <form method="post" action="register_process.php" class="p-4" id="registrationForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fname" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="fname" name="fname" 
                                   onkeydown="return alphaOnly(event);" required placeholder="Enter first name"/>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lname" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lname" name="lname" 
                                   onkeydown="return alphaOnly(event);" required placeholder="Enter last name"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" 
                                   name="date_of_birth" onchange="calculateAge()" required>
                        </div>
                        <div class="col-md-6 mb-3">
    <label for="birth_place" class="form-label">Birth Place</label>
    <select class="form-control" id="birth_place" name="birth_place" required>
        <option value="" disabled selected>Select your county</option>
        <option value="Nairobi">Nairobi</option>
        <option value="Mombasa">Mombasa</option>
        <option value="Kisumu">Kisumu</option>
        <option value="Nakuru">Nakuru</option>
        <option value="Kiambu">Kiambu</option>
        <option value="Machakos">Machakos</option>
        <option value="Uasin Gishu">Uasin Gishu</option>
        <option value="Kericho">Kericho</option>
        <option value="Nyeri">Nyeri</option>
        <option value="Bungoma">Bungoma</option>
        <option value="Kakamega">Kakamega</option>
        <option value="Meru">Meru</option>
        <option value="Kitui">Kitui</option>
        <option value="Homabay">Homabay</option>
        <option value="Kisii">Kisii</option>
    </select>
    <div id="birthPlaceError" class="error-message"></div>
</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" 
                                   name="email" required placeholder="Enter email address">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="contact" 
                                   name="contact" minlength="10" maxlength="13" required 
                                   placeholder="Enter phone number" 
                                   onkeypress="return validatePhoneNumber(event)">
                            <div id="contactError" class="error-message"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" 
                                   name="password" onkeyup='checkPasswordStrength(); check();' required placeholder="Create password">
                            <div id="password-requirements">
                                <div id="length" class="requirement">At least 8 characters long</div>
                                <div id="capital" class="requirement">At least one capital letter</div>
                                <div id="small" class="requirement">At least one small letter</div>
                                <div id="number" class="requirement">At least one number</div>
                                <div id="symbol" class="requirement">At least one symbol (!@#$%^&*)</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cpassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="cpassword" 
                                   name="cpassword" onkeyup='check();' required placeholder="Confirm password">
                            <span id='message'></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="gender" id="male" value="Male" checked>
                                    <label class="form-check-label" for="male">Male</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="female" value="Female">
                                    <label class="form-check-label" for="female">Female</label>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="currentcity" class="form-label">Current City/Town</label>
                            <select class="form-control" id="birth_place" name="birth_place" required>
                              <option value="" disabled selected>Select your county</option>
                             <option value="Nairobi">Nairobi</option>
                            <option value="Mombasa">Mombasa</option>
                             <option value="Kisumu">Kisumu</option>
                             <option value="Nakuru">Nakuru</option>
                             <option value="Kiambu">Kiambu</option>
                             <option value="Machakos">Machakos</option>
                             <option value="Uasin Gishu">Uasin Gishu</option>
                             <option value="Kericho">Kericho</option>
                             <option value="Nyeri">Nyeri</option>
                             <option value="Bungoma">Bungoma</option>
                             <option value="Kakamega">Kakamega</option>
                            <option value="Meru">Meru</option>
                            <option value="Kitui">Kitui</option>
                            <option value="Homabay">Homabay</option>
                            <option value="Kisii">Kisii</option>
                           </select>
                                   
                                 <div id="currentCityError" class="error-message"></div>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" name="patsub1" class="btn btn-primary w-100">Register</button>
                        <p class="mt-3">
                            Already have an account? 
                            <a href="index1.php" class="login-link">Login here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

 <!-- Loader -->
 <div class="loader-container" id="loader">
        <div class="loader">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
     <script>
        document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registrationForm");
    const loader = document.getElementById("loader");

    // Name validation (for both first name and last name)
    function validateName(input) {
    const nameRegex = /^[A-Za-z]+$/; // Allows only letters, no length restrictions
    const errorElement = document.getElementById(`${input.id}Error`);
    
    if (!nameRegex.test(input.value.trim())) {
        input.classList.add('is-invalid');
        errorElement.textContent = "Name must contain only letters";
        return false;
    }
    
    input.classList.remove('is-invalid');
    errorElement.textContent = "";
    return true;
}

    // Email validation
    function validateEmail(input) {
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const errorElement = document.getElementById(`${input.id}Error`);
        
        if (!emailRegex.test(input.value.trim())) {
            input.classList.add('is-invalid');
            errorElement.textContent = "Please enter a valid email address";
            return false;
        }
        input.classList.remove('is-invalid');
        errorElement.textContent = "";
        return true;
    }

    // Phone number validation with both format and input restrictions
    function validatePhoneNumber(input) {
        const phoneRegex = /^\+?[0-9]{10,13}$/;
        const errorElement = document.getElementById(`${input.id}Error`);
        const value = input.value.trim();
        
        if (!phoneRegex.test(value)) {
            input.classList.add('is-invalid');
            errorElement.textContent = "Phone number must be 10-13 digits (optional + prefix)";
            return false;
        }
        input.classList.remove('is-invalid');
        errorElement.textContent = "";
        return true;
    }

    // Prevent non-numeric input for phone number
    document.getElementById("contact").addEventListener("keypress", function(e) {
        const char = String.fromCharCode(e.which);
        if (!/[0-9+]/.test(char) || (this.value.includes('+') && char === '+') || 
            (this.value.length === 0 && char !== '+' && char !== '0')) {
            e.preventDefault();
        }
    });

    // Date of birth validation with age calculation
    function validateDateOfBirth(input) {
        const dob = new Date(input.value);
        const today = new Date();
        const errorElement = document.getElementById(`${input.id}Error`);
        
        if (dob > today) {
            input.classList.add('is-invalid');
            errorElement.textContent = "Date of birth cannot be in the future";
            return false;
        }

        const age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        input.classList.remove('is-invalid');
        errorElement.textContent = "";
        return true;
    }

    // Enhanced password validation with strength indicators
    function validatePassword() {
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("cpassword");
        const message = document.getElementById("message");
        
        const requirements = {
            length: password.value.length >= 8,
            capital: /[A-Z]/.test(password.value),
            small: /[a-z]/.test(password.value),
            number: /[0-9]/.test(password.value),
            symbol: /[!@#$%^&*]/.test(password.value)
        };

        // Update requirement indicators
        Object.keys(requirements).forEach(req => {
            const element = document.getElementById(req);
            if (requirements[req]) {
                element.classList.add("valid");
                element.classList.remove("requirement");
            } else {
                element.classList.add("requirement");
                element.classList.remove("valid");
            }
        });

        // Check if all requirements are met
        const isValidPassword = Object.values(requirements).every(Boolean);
        
        // Check password match if confirm password has value
        if (confirmPassword.value) {
            if (password.value === confirmPassword.value && isValidPassword) {
                message.style.color = "#5dd05d";
                message.textContent = "Passwords Matched";
                confirmPassword.classList.remove('is-invalid');
                return true;
            } else {
                message.style.color = "#f55252";
                message.textContent = "Passwords Not Matching";
                confirmPassword.classList.add('is-invalid');
                return false;
            }
        }

        return isValidPassword;
    }

    // Real-time validation for inputs
    document.getElementById("fname").addEventListener("input", (e) => validateName(e.target));
    document.getElementById("lname").addEventListener("input", (e) => validateName(e.target));
    document.getElementById("email").addEventListener("input", (e) => validateEmail(e.target));
    document.getElementById("contact").addEventListener("input", (e) => validatePhoneNumber(e.target));
    document.getElementById("date_of_birth").addEventListener("change", (e) => validateDateOfBirth(e.target));
    document.getElementById("password").addEventListener("input", validatePassword);
    document.getElementById("cpassword").addEventListener("input", validatePassword);

    // Clear invalid state on select change
    document.getElementById("birth_place").addEventListener("change", function() {
        this.classList.remove('is-invalid');
    });
    
    document.getElementById("currentcity").addEventListener("change", function() {
        this.classList.remove('is-invalid');
    });

    // Enhanced form submission with validation, loading effect, and delays
    form.addEventListener("submit", function(event) {
        event.preventDefault();
        
        // Validate all fields
        const isValidFirstName = validateName(document.getElementById("fname"));
        const isValidLastName = validateName(document.getElementById("lname"));
        const isValidEmail = validateEmail(document.getElementById("email"));
        const isValidPhone = validatePhoneNumber(document.getElementById("contact"));
        const isValidDOB = validateDateOfBirth(document.getElementById("date_of_birth"));
        const isValidPassword = validatePassword();
        const birthPlace = document.getElementById("birth_place").value;
        const currentCity = document.getElementById("currentcity").value;

        // Check select fields
        if (!birthPlace) {
            document.getElementById("birth_place").classList.add('is-invalid');
        }
        if (!currentCity) {
            document.getElementById("currentcity").classList.add('is-invalid');
        }

        // Validate all fields before submission
        if (!isValidFirstName || !isValidLastName || !isValidEmail || !isValidPhone || 
            !isValidDOB || !isValidPassword || !birthPlace || !currentCity) {
            alert("Please fix all errors before submitting the form.");
            return;
        }

        // Show loader and hide form
        form.style.display = "none";
        loader.style.display = "flex";

        // Add delay before form submission (2 seconds)
        setTimeout(() => {
            const formData = new FormData(this);
            fetch("register_process.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(() => {
                // Add delay before redirect (1 second)
                setTimeout(() => {
                    window.location.href = "welcome.php";
                }, 1000);
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred during registration. Please try again.");
                form.style.display = "block";
                loader.style.display = "none";
            });
        }, 2000);
    });
});
     </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>