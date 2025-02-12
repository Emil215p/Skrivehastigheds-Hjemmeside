<?php
// Fjern HTML-tags da dette script kaldes via AJAX
header('Content-Type: text/plain; charset=utf-8');

// Tjek REQUEST_METHOD med isset()
if (isset($_SERVER['REQUEST_METHOD'])) {
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
} else {
    error_log("Request Method: undefined");
}

error_log("POST Data: " . print_r($_POST, true));

$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

// Opret forbindelse
$conn = new mysqli($servername, $username, $password, $dbname);

// Tjek forbindelse
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Tjek kun POST hvis REQUEST_METHOD er defineret
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "POST") {
    // Hent POST-data
    $name = $_POST['name'] ?? '';
    $wpm = isset($_POST['wpm']) ? intval($_POST['wpm']) : 0;
    $accuracy = isset($_POST['accuracy']) ? intval($_POST['accuracy']) : 0;

    if (!empty($name) && $wpm > 0) {
        $stmt = $conn->prepare("INSERT INTO resultater (navn, wpm, præcision) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $name, $wpm, $accuracy);
        
        if ($stmt->execute()) {
            echo "Resultat gemt!";
        } else {
            echo "Fejl: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Ugyldige data. Navn og WPM skal udfyldes.";
    }
} else {
    echo "Kun POST-anmodninger accepteres.";
}

$conn->close();
?>