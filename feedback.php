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
        header("Location: feedback.php"); // Redirect to feedback page
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - eSchool</title>
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
            <a href="feedback.php">Feedback</a>
            <a href="login.php">Login</a>
        </div>
    </nav>

    <!-- Feedback Section -->
    <section class="section feedback">
        <h3>Feedback</h3>
        <p>We value your feedback. Please let us know your thoughts and suggestions.</p>
        <form action="" method="POST">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <textarea name="message" placeholder="Your Feedback" rows="5" required></textarea>
            <button type="submit" name="feedback_submit">Submit Feedback</button>
        </form>

        <div class="feedback-list">
            <h4>Recent Feedbacks</h4>
            <?php
            // Display feedbacks from the database
            foreach ($feedbacks as $feedback) {
                echo "<div class='feedback-item'>";
                echo "<strong>" . htmlspecialchars($feedback['name']) . ":</strong> ";
                echo "<p>" . htmlspecialchars($feedback['feedback']) . "</p>";
                echo "</div>";
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