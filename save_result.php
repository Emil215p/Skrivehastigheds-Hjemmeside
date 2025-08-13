<?php
header('Content-Type: text/plain; charset=utf-8');

if (isset($_SERVER['REQUEST_METHOD'])) {
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
} else {
    error_log("Request Method: undefined");
}

error_log("POST Data: " . print_r($_POST, true));

require 'db/db_conn.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == "POST") {
    $name = $_POST['name'] ?? '';
    $wpm = isset($_POST['wpm']) ? intval($_POST['wpm']) : 0;
    $accuracy = isset($_POST['accuracy']) ? intval($_POST['accuracy']) : 0;
    $raw = isset($_POST['raw']) ? intval($_POST['raw']) : 0;
    $errors = isset($_POST['errors']) ? intval($_POST['errors']) : 0;

    if (!empty($name) && $wpm > 0) {
        $stmt = $conn->prepare("INSERT INTO resultater (navn, wpm, præcision, raw, errors) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiii", $name, $wpm, $accuracy, $raw, $errors);
        
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