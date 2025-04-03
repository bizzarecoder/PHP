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

// Get all images from the 'images' directory
$imageDir = 'images/';
$images = [];

if (is_dir($imageDir)) {
    $images = array_diff(scandir($imageDir), array('.', '..'));
} else {
    echo "Warning: The directory 'images' does not exist.";
}

// Function to get a random image
function getRandomImage($images, $imageDir) {
    if (empty($images)) {
        return 'default.jpg'; // Use a default image if no images are found
    }
    return $imageDir . $images[array_rand($images)];
}

// Fetch courses from the database
$query = "SELECT * FROM courses";
$result = $conn->query($query);

if (!$result) {
    die('Error fetching courses: ' . $conn->error);
}

$courses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - eSchool</title>
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

        .section {
            padding: 80px 20px;
            text-align: center;
            background-color: #2c2c2c;
            margin-top: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }

        .section h3 {
            font-size: 2.5em;
            margin-bottom: 20px;
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
            height: 450px; /* Adjusted height for price */
        }

        .course-card img {
            width: 100%;
            height: 200px; /* Fixed height for images */
            object-fit: cover;
            border-radius: 5px;
        }

        .course-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
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
            margin-bottom: 10px;
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

        footer {
            background-color: #2c2c2c;
            color: #fff;
            text-align: center;
            padding: 20px 0;
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
            <a href="my_courses.php">My Courses</a>
        </div>
    </nav>

    <!-- Courses Section -->
    <section class="section">
        <h3>All Courses</h3>
        <div class="course-grid">
            <?php
            if (empty($courses)) {
                echo "<p>No courses available at the moment.</p>";
            } else {
                foreach ($courses as $course) {
                    echo "<div class='course-card'>";
                    echo "<img src='" . getRandomImage($images, $imageDir) . "' alt='Course Image'>";
                    echo "<h3>" . htmlspecialchars($course['title']) . "</h3>";
                    echo "<p>" . htmlspecialchars($course['description']) . "</p>";
                    echo "<div class='price'>Price: ₹" . htmlspecialchars($course['price']) . "</div>";
                    echo "<a href='course_details.php?course_id=" . htmlspecialchars($course['id']) . "' class='btn'>Enroll Now</a>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 eSchool. All Rights Reserved.</p>
    </footer>
</body>
</html>