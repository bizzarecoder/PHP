<?php
session_start(); // Start the session

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'eschool_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Database Connection Failed: ' . $conn->connect_error);
}

require 'vendor/autoload.php'; // Include PHPMailer's autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_message = '';
$success_message = '';

// Function to send password reset email
function sendPasswordResetEmail($toEmail, $resetLink) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // SMTP username
        $mail->Password = 'your-email-password'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'eSchool');
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "Click the following link to reset your password: <a href='$resetLink'>$resetLink</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Function to generate a password reset token
function generateResetToken($email) {
    global $conn;
    $token = bin2hex(random_bytes(16));
    $query = "INSERT INTO password_resets (email, token) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    return "http://localhost/eschool/reset_password.php?token=$token";
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user['email']; // Store user email in session
                header("Location: index.php"); // Redirect to home page
                exit(); // Ensure no further code is executed
            } else {
                $error_message = 'Incorrect password.';
            }
        } else {
            $error_message = 'No user found with this email.';
        }
    }
}

// Handle forgot password form submission
if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'] ?? '';

    if (!empty($email)) {
        // Check if email exists in users table
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Email exists, generate reset token and send email
            $resetLink = generateResetToken($email);
            if (sendPasswordResetEmail($email, $resetLink)) {
                $success_message = "A password reset link has been sent to $email.";
            } else {
                $error_message = "Failed to send reset link. Please try again.";
            }
        } else {
            $error_message = "No user found with this email.";
        }
    } else {
        $error_message = "Please enter your email to reset your password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - eSchool</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #1e1e1e;
            color: #f0f0f0;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background-color: #2c2c2c;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #ff6f61;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: #333;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #ff6f61;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #b0b0b0;
        }

        .form-group input {
            width: 93%;
            padding: 12px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #444;
            color: #f0f0f0;
            font-size: 1em;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .form-group input:focus {
            border-color: #ff6f61;
            background-color: #555;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #ff6f61;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        button:hover {
            background-color: #e65c54;
            transform: scale(1.05);
        }

        .signup-prompt {
            margin-top: 20px;
            font-size: 0.9em;
            color: #b0b0b0;
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #333;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            color: #fff;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        footer {
            background-color: #2c2c2c;
            color: #fff;
            text-align: center;
            padding: 20px 0;
        }

        /* Improved Modal Styles */
        .modal-content h3 {
            color: #ff6f61;
            margin-bottom: 20px;
        }

        .modal-content input[type="email"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #555;
            border-radius: 5px;
            background-color: #444;
            color: #fff;
        }

        .modal-content button {
            width: 100%;
            padding: 10px;
            background-color: #ff6f61;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .modal-content button:hover {
            background-color: #e65c54;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .navbar a {
                margin: 10px 0;
            }

            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">eSchool</a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="courses.php">Courses</a>
            <a href="feedback.php" onclick="return checkLoginStatus();">Feedback</a>
            <a href="login.php" id="loginLink">Login</a>
        </div>
    </nav>

    <div class="login-container">
        <h2>Login</h2>
        <form id="loginForm" action="" method="POST" onsubmit="return validateLogin()">
            <div class="error" id="loginError"><?php echo htmlspecialchars($error_message); ?></div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login">Login</button>
        </form>
        <div class="signup-prompt">
            Don't have an account? <a href="register.php">Sign up</a>
        </div>
        <div class="forgot-password">
            <a href="#" onclick="document.getElementById('forgot-password-form').style.display='block'; return false;">Forgot Password?</a>
        </div>
    </div>

    <!-- Forgot Password Form -->
    <div id="forgot-password-form" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('forgot-password-form').style.display='none'">&times;</span>
            <h3>Forgot Password</h3>
            <form action="" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit" name="forgot_password">Send Reset Link</button>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <?php if (!empty($success_message)): ?>
        <div id="successModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('successModal').style.display='none'">&times;</span>
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        </div>
        <script>
            document.getElementById('successModal').style.display = 'block';
        </script>
    <?php endif; ?>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 eSchool. All Rights Reserved. <a href="admin_login.php" style="color: #ff6f61;">Admin Login</a></p>
    </footer>

    <script>
        // Check login status and update navbar
        document.addEventListener('DOMContentLoaded', function() {
            const loginLink = document.getElementById('loginLink');
            <?php if (isset($_SESSION['user'])): ?>
                loginLink.textContent = 'Logout';
                loginLink.href = 'logout.php';
            <?php endif; ?>
        });

        function validateLogin() {
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const errorDiv = document.getElementById('loginError');

            errorDiv.textContent = ''; // Clear previous errors

            if (!email || !password) {
                errorDiv.textContent = 'Please enter both email and password.';
                return false;
            }

            if (!validateEmail(email)) {
                errorDiv.textContent = 'Please enter a valid email address.';
                return false;
            }

            return true;
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function checkLoginStatus() {
            <?php if (!isset($_SESSION['user'])): ?>
                alert('Please log in to access this feature.');
                return false;
            <?php endif; ?>
            return true;
        }
    </script>
</body>
</html> 