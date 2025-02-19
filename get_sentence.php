<?php
header('Content-Type: text/plain; charset=utf-8');

$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT tekst FROM tekster WHERE active = 1 LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    echo $row['tekst'];
} else {
    echo "Ingen aktiv sætning fundet.";
}
$conn->close();
?>
