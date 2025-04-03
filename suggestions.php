<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'eschool_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die('Database Connection Failed: ' . $conn->connect_error);
}

$query = $_GET['query'] ?? '';

if ($query) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE email LIKE CONCAT('%', ?, '%') LIMIT 5");
    $stmt->bind_param('s', $query);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['email'];
    }

    echo json_encode($suggestions);
}
?> 