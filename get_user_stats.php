<?php
header('Content-Type: application/json');

$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'No id specified.']));
}

$id = $conn->real_escape_string($_GET['id']);

// Fetch the specific record based on the unique id
$sql = "SELECT navn, wpm, errors, præcision, raw, created_at 
        FROM resultater 
        WHERE id = '$id' LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Record not found.']);
}

$conn->close();
?>
