<?php
header('Content-Type: text/plain; charset=utf-8');

require '../db/db_conn.php';

$result = $conn->query("SELECT tekst FROM tekster WHERE active = 1 LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    echo $row['tekst'];
} else {
    echo "Ingen aktiv sætning fundet.";
}
$conn->close();
