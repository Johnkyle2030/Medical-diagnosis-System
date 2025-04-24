<?php
session_start();
require_once 'db_connection.php';

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM patients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token in database
        $update_stmt = $conn->prepare("UPDATE patients SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $token, $expires, $email);
        
        if ($update_stmt->execute()) {
            // In a real application, you would send this via email
            // For demonstration, we'll just show the reset link
            $reset_link = "reset_password.php?token=" . $token;
            $message = "Password reset link has been sent to your email";
            $message_type = "success";
        } else {
            $message = "Error generating reset link";
            $message_type = "error";
        }
        $update_stmt->close();
    } else {
        // For security, show the same message even if email doesn't exist
        $message = "If an account exists with this email, a password reset link will be sent";
        $message_type = "info";
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
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <form action="forgot_password.php" method="POST" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-700">Forgot Password</h2>
                <p class="mt-2 text-gray-600">Enter your email address to reset your password</p>
                
                <?php if ($message): ?>
                    <div class="mt-4 p-3 rounded <?php 
                        echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 
                            ($message_type === 'error' ? 'bg-red-100 text-red-700' : 
                            'bg-blue-100 text-blue-700'); 
                    ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email Address
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
            
            <div class="flex items-center justify-between">
                <button 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                    type="submit"
                >
                    Reset Password
                </button>
            </div>
            
            <div class="text-center mt-6">
                <a 
                    class="inline-block font-bold text-sm text-blue-500 hover:text-blue-800" 
                    href="index1.php"
                >
                    Back to Login
                </a>
            </div>
        </form>
    </div>
</body>
</html>