<?php
header('Content-Type: application/json');

require 'db/db_conn.php';

if (!isset($_GET['id'])) {
    die(json_encode(['error' => 'No id specified.']));
}

$id = $conn->real_escape_string($_GET['id']);

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
