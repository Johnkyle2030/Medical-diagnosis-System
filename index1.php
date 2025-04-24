<?php
session_start();
require_once 'db_connection.php';

// Disable error output before redirect
error_reporting(0);

$login_success = false;
$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize email input
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, fname, password FROM patients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['id'] = $user['id'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['email'] = $email;

            // Set success flag
            $login_success = true;
            
            // Send JSON response for AJAX request
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo json_encode(['success' => true]);
                exit;
            }
            
            // Redirect to welcome page for normal form submission
            header("Location: welcome.php");
            exit;
        } else {
            $login_error = "Invalid email or password";
        }
    } else {
        $login_error = "Invalid email or password";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Loader Styles */
        .loader-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            z-index: 9999;
        }

        .loader {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dot {
            width: 15px;
            height: 15px;
            margin: 0 5px;
            background-color: #007bff;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        .dot:nth-child(3) { animation-delay: 0; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); } 
            40% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <!-- Login Form -->
        <form id="loginForm" action="index1.php" method="POST" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
            <div class="mb-4 text-center">
                <h2 class="text-2xl font-bold text-gray-700">Login</h2>
                <?php if ($login_error): ?>
                    <p class="text-red-500 text-xs italic"><?php echo $login_error; ?></p>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </span>
                    <input 
                        class="shadow appearance-none border rounded w-full py-2 px-3 pl-10 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        id="email" 
                        name="email" 
                        type="email" 
                        placeholder="Enter your email" 
                        required
                    >
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-lock text-gray-400"></i>
                    </span>
                    <input 
                        class="shadow appearance-none border rounded w-full py-2 px-3 pl-10 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        id="password" 
                        name="password" 
                        type="password" 
                        placeholder="Enter your password" 
                        required
                    >
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" onclick="togglePasswordVisibility()">
                        <i id="passwordToggle" class="fas fa-eye-slash text-gray-400"></i>
                    </span>
                </div>
            </div>
            
            <div class="flex items-center justify-between">
    <button 
        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
        type="submit"
    >
        Sign In
    </button>
    <div class="flex flex-col items-center text-center mt-4">
    <a 
        class="inline-block font-bold text-sm text-blue-500 hover:text-blue-800 mb-2" 
        href="forgot_password.php"
    >
        Forgot Password?
    </a>
    <a 
        class="inline-block font-bold text-sm text-blue-500 hover:text-blue-800" 
        href="index.php"
    >
        Create Account
    </a>
</div>
</div>
        </form>
    </div>

    <!-- Loader -->
    <div class="loader-container" id="loader">
        <p>Checking your details, please wait...</p>
        <div class="loader">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            }
        }

        document.getElementById("loginForm").addEventListener("submit", function(event) {
            event.preventDefault();
            
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();

            // Ensure user has entered credentials before showing the loader
            if (email === "" || password === "") {
                return;
            }

            // Hide form and show loader
            document.getElementById("loginForm").style.display = "none";
            document.getElementById("loader").style.display = "flex";

            // Create form data
            const formData = new FormData(this);

            // Send AJAX request
            fetch('index1.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Wait 2 seconds before redirecting
                    setTimeout(() => {
                        window.location.href = 'welcome.php';
                    }, 2000);
                } else {
                    // If login failed, show form again
                    document.getElementById("loader").style.display = "none";
                    document.getElementById("loginForm").style.display = "block";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById("loader").style.display = "none";
                document.getElementById("loginForm").style.display = "block";
            });
        });

        // Prevent loader from appearing on direct page load
        window.onload = function() {
            document.getElementById("loader").style.display = "none";
            document.getElementById("loginForm").style.display = "block";
        };
    </script>
</body>
</html>