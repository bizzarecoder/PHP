<?php
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

$error_message = '';

// Handle admin login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        $query = "SELECT * FROM admin_users WHERE email = ?";
        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                if ($password === $admin['password']) { // For demonstration, use plain text
                    header("Location: index.php"); // Redirect to home page
                    exit();
                } else {
                    $error_message = 'Incorrect password.';
                }
            } else {
                $error_message = 'No admin found with this email.';
            }
        } else {
            $error_message = 'Error preparing the SQL statement.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - eSchool</title>
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

        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
        }

        footer {
            background-color: #2c2c2c;
            color: #fff;
            text-align: center;
            padding: 20px 0;
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
            <a href="feedback.php">Feedback</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <div class="login-container">
        <h2>Admin Login</h2>
        <form action="" method="POST">
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="admin_login">Login</button>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 eSchool. All Rights Reserved.</p>
    </footer>
</body>
</html> 