<?php
// Database Connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'eschool_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Database Connection Failed: ' . $conn->connect_error);
}

// Fetch existing feedbacks from users
$feedbacks = [];
$feedbackQuery = "SELECT name, feedback FROM users WHERE feedback IS NOT NULL ORDER BY id DESC LIMIT 5";
$feedbackResult = $conn->query($feedbackQuery);

if ($feedbackResult === false) {
    echo "Error: " . $conn->error; // Output the error message
} else {
    if ($feedbackResult->num_rows > 0) {
        while ($row = $feedbackResult->fetch_assoc()) {
            $feedbacks[] = $row;
        }
    }
}

// Handle new feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feedback_submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Update feedback in the users table
    $updateFeedbackQuery = "UPDATE users SET feedback = '$message' WHERE email = '$email' AND name = '$name'";
    if ($conn->query($updateFeedbackQuery) === TRUE) {
        header("Location: index.php#feedback"); // Redirect to feedback section
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// Get all images from the 'images' directory
$imageDir = 'images/';
$images = array_diff(scandir($imageDir), array('.', '..'));

// Function to get a random image
function getRandomImage($images, $imageDir) {
    return $imageDir . $images[array_rand($images)];
}

session_start(); // Start the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - eSchool</title>
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

        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #fff;
            overflow: hidden;
            margin-bottom: 0;
        }

        .hero video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .hero h1 {
            font-size: 3.5em;
            margin: 0;
            animation: fadeIn 2s ease-in-out;
        }

        .hero p {
            font-size: 1.5em;
            margin-top: 10px;
            animation: fadeIn 2s ease-in-out 0.5s;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff6f61;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn:hover {
            background-color: #e65c54;
            transform: scale(1.05);
        }

        .section {
            padding: 100px 30px;
            text-align: center;
            background-color: #2c2c2c;
            margin-top: 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }

        .section h3 {
            font-size: 2.8em;
            margin-bottom: 30px;
            color: #ff6f61;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .course-card {
            background-color: #333;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
        }

        .course-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
        }

        .course-card h3 {
            font-size: 1.5em;
            color: #ff6f61;
            margin: 10px 0;
        }

        .course-card p {
            color: #b0b0b0;
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .course-card .price {
            font-size: 1.2em;
            color: #ff6f61;
            margin-bottom: 20px;
        }

        .course-card a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff6f61;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .course-card a:hover {
            background-color: #e65c54;
        }

        .feedback form {
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
            background-color: #333;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .feedback input, .feedback textarea {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #444;
            color: #f0f0f0;
            font-size: 1em;
            transition: border-color 0.3s, background-color 0.3s;
        }

        .feedback input:focus, .feedback textarea:focus {
            border-color: #ff6f61;
            background-color: #555;
        }

        .feedback button {
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

        .feedback button:hover {
            background-color: #e65c54;
            transform: scale(1.05);
        }

        .feedback-list {
            margin-top: 40px;
            text-align: left;
        }

        .feedback-item {
            background-color: #333;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 15px;
            color: #b0b0b0;
            transition: background-color 0.3s;
        }

        .feedback-item:hover {
            background-color: #444;
        }

        .contact {
            background-color: #2c2c2c;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 40px 0;
            color: #f0f0f0;
            transition: background-color 0.3s, transform 0.3s;
        }

        .contact:hover {
            background-color: #3a3a3a;
            transform: scale(1.02);
        }

        .contact h3 {
            margin-bottom: 20px;
            color: #ff6f61;
        }

        .contact p {
            color: #b0b0b0;
        }

        .contact .social-links a {
            color: #ff6f61;
            margin: 0 10px;
            text-decoration: none;
            font-size: 1.2em;
            transition: color 0.3s;
        }

        .contact .social-links a:hover {
            color: #e65c54;
        }

        footer {
            background-color: #2c2c2c;
            color: #fff;
            text-align: center;
            padding: 30px 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

            .section {
                padding: 60px 15px;
            }

            .feedback form {
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
            <?php if (isset($_SESSION['user'])): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
            <a href="my_courses.php">My Courses</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero">
        <video autoplay muted loop>
            <source src="video.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-content">
            <h1>Welcome to eSchool</h1>
            <p>Learn from the Best Courses Online</p>
            <a href="#courses" class="btn">Explore Courses</a>
        </div>
    </header>

    <!-- Popular Courses Section -->
    <section class="section">
        <h3>Popular Courses</h3>
        <div class="course-grid">
            <?php
            // Dummy data for popular courses
            $popularCourses = [
                ['title' => 'Web Development', 'description' => 'Learn HTML, CSS, and JavaScript to build modern web applications.', 'price' => 1],
                ['title' => 'Data Science', 'description' => 'Explore data analysis, visualization, and machine learning techniques.', 'price' => 1],
                ['title' => 'Digital Marketing', 'description' => 'Master SEO, social media marketing, and online advertising.', 'price' => 1],
                // Add more popular courses as needed
            ];

            foreach ($popularCourses as $course) {
                echo "<div class='course-card'>";
                echo "<img src='" . getRandomImage($images, $imageDir) . "' alt='Course Image'>";
                echo "<h3>" . $course['title'] . "</h3>";
                echo "<p>" . $course['description'] . "</p>";
                echo "<div class='price'>Price: ₹" . $course['price'] . "</div>";
                echo "<a href='course_details.php?title=" . urlencode($course['title']) . "' class='btn'>Enroll Now</a>";
                echo "</div>";
            }
            ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section contact">
        <h3>Contact Us</h3>
        <p>For any inquiries, please email us at contact@eschool.com or call us at (123) 456-7890.</p>
        <p>Address: 123 eSchool Lane, Education City, ED 12345</p>
        <p>Office Hours: Monday - Friday, 9 AM - 5 PM</p>
        <div class="social-links">
            <a href="https://facebook.com" target="_blank">Facebook</a>
            <a href="https://twitter.com" target="_blank">Twitter</a>
            <a href="https://instagram.com" target="_blank">Instagram</a>
            <a href="https://linkedin.com" target="_blank">LinkedIn</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 eSchool. All Rights Reserved.</p>
    </footer>

    <script>
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
