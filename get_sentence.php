<?php
// get_sentence.php
header('Content-Type: text/plain; charset=utf-8');

// Database connection settings
$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the active sentence; there should be only one
$result = $conn->query("SELECT text FROM tekster WHERE active = 1 LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    echo $row['text'];
} else {
    echo "No active sentence found.";
}
$conn->close();
?>
