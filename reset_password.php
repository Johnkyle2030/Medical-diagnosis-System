<?php
session_start();
require_once 'db_connection.php';

$token = $_GET['token'] ?? '';
$message = '';
$message_type = '';
$valid_token = false;

// Verify token
if ($token) {
    $stmt = $conn->prepare("SELECT id FROM patients WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $valid_token = true;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $update_stmt = $conn->prepare("UPDATE patients SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $update_stmt->bind_param("ss", $hashed_password, $token);
        
        if ($update_stmt->execute()) {
            $message = "Password successfully reset. You can now login with your new password.";
            $message_type = "success";
        } else {
            $message = "Error resetting password";
            $message_type = "error";
        }
        $update_stmt->close();
    } else {
        $message = "Passwords do not match";
        $message_type = "error";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <?php if (!$token || !$valid_token): ?>
            <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-700 mb-4">Invalid or Expired Link</h2>
                    <p class="text-gray-600 mb-6">This password reset link is invalid or has expired. Please request a new one.</p>
                    <a 
                        href="forgot_password.php" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline inline-block"
                    >
                        Request New Link
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
                <div class="mb-6 text-center">
                    <h2 class="text-2xl font-bold text-gray-700">Reset Password</h2>
                    <p class="mt-2 text-gray-600">Enter your new password</p>
                    
                    <?php if ($message): ?>
                        <div class="mt-4 p-3 rounded <?php 
                            echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; 
                        ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        New Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-lock text-gray-400"></i>
                        </span>
                        <input 
                            class="shadow appearance-none border rounded w-full py-2 px-3 pl-10 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            id="password" 
                            name="password" 
                            type="password" 
                            placeholder="Enter new password" 
                            required
                            minlength="8"
                        >
                    </div>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                        Confirm Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-lock text-gray-400"></i>
                        </span>
                        <input 
                            class="shadow appearance-none border rounded w-full py-2 px-3 pl-10 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            id="confirm_password" 
                            name="confirm_password" 
                            type="password" 
                            placeholder="Confirm new password" 
                            required
                            minlength="8"
                        >
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <button 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" 
                        type="submit"
                    >
                        Set New Password
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Add password visibility toggle if needed
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>