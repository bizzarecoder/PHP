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

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_email = $_SESSION['user'];
$query = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Fetch purchased courses
$query = "SELECT courses.* FROM courses
          JOIN course_purchases ON courses.id = course_purchases.course_id
          WHERE course_purchases.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$purchased_courses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - eSchool</title>
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

        .content {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #333;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .course-list {
            list-style: none;
            padding: 0;
        }

        .course-list li {
            background-color: #444;
            margin: 10px 0;
            padding: 15px;
            border-radius: 5px;
            text-align: left;
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
            <a href="my_courses.php">My Courses</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="content">
        <h1>My Courses</h1>
        <?php if (empty($purchased_courses)): ?>
            <p>You haven't purchased any courses yet.</p>
        <?php else: ?>
            <ul class="course-list">
                <?php foreach ($purchased_courses as $course): ?>
                    <li>
                        <h2><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html> 