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

$error_message = '';
$success_message = '';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get course ID from request
$course_id = $_GET['course_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['purchase'])) {
    if ($course_id) {
        // Get user ID from session
        $user_email = $_SESSION['user'];
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $user_email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Insert purchase record
        $query = "INSERT INTO course_purchases (user_id, course_id) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $user_id, $course_id);

        if ($stmt->execute()) {
            $success_message = "Course purchased successfully!";
        } else {
            $error_message = "Failed to purchase course. Please try again.";
        }
    } else {
        $error_message = "Invalid course.";
    }
}

// Get course title from URL
$courseTitle = isset($_GET['title']) ? $_GET['title'] : '';

// Detailed descriptions for each course
$courseDetails = [
    'Web Development' => [
        'description' => 'Learn HTML, CSS, and JavaScript to build modern web applications. This course covers everything from the basics to advanced topics, including responsive design, web accessibility, and performance optimization. You will also learn about popular frameworks like React and Angular.',
        'price' => 1
    ],
    // Add other courses here...
];

$course = $courseDetails[$courseTitle] ?? null;

if (!$course) {
    die('Course not found.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($courseTitle); ?> - Course Details</title>
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
            padding: 20px 40px;
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
            margin: 0 20px;
            transition: color 0.3s;
        }

        .navbar a:hover {
            color: #ff6f61;
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

        .course-details {
            max-width: 700px;
            margin: 0 auto;
            text-align: left;
            background-color: #333;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .course-details p {
            color: #b0b0b0;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .course-details .price {
            font-size: 1.4em;
            color: #ff6f61;
            margin-bottom: 30px;
        }

        .course-details button {
            width: 100%;
            padding: 20px;
            background-color: #ff6f61;
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        .course-details button:hover {
            background-color: #e65c54;
            transform: scale(1.05);
        }

        footer {
            background-color: #2c2c2c;
            color: #fff;
            text-align: center;
            padding: 30px 0;
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

    <!-- Course Details Section -->
    <section class="section">
        <h3><?php echo htmlspecialchars($courseTitle); ?> - Course Details</h3>
        <div class="course-details">
            <p><?php echo htmlspecialchars($course['description']); ?></p>
            <div class="price">Price: ₹<?php echo htmlspecialchars($course['price']); ?></div>
            <form method="POST">
                <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
                <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
                <button type="submit" name="purchase">Purchase Course</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 eSchool. All Rights Reserved.</p>
    </footer>

    <!-- Razorpay Integration -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "YOUR_RAZORPAY_KEY", // Enter the Key ID generated from the Dashboard
            "amount": "<?php echo $course['price'] * 100; ?>", // Amount is in currency subunits. Default currency is INR. Hence, 100 refers to 1 INR
            "currency": "INR",
            "name": "eSchool",
            "description": "<?php echo htmlspecialchars($courseTitle); ?>",
            "image": "https://example.com/your_logo",
            "handler": function (response){
                alert("Payment successful. Payment ID: " + response.razorpay_payment_id);
                // Here you can write code to save the payment details to your database
            },
            "prefill": {
                "name": "Your Name",
                "email": "email@example.com",
                "contact": "9999999999"
            },
            "theme": {
                "color": "#ff6f61"
            }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('rzp-button1').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }
    </script>
</body>
</html> 