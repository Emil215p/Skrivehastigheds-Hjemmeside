<?php
// admin.php
// Database connection settings
$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Received POST with data: " . print_r($_POST, true));

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $sentence = trim($_POST['sentence'] ?? '');
            if (!empty($sentence)) {
                $stmt = $conn->prepare("INSERT INTO tekster (`tekst`, active) VALUES (?, 0)");
                $stmt->bind_param("s", $sentence);
                if ($stmt->execute()) {
                    error_log("Sentence added successfully.");
                } else {
                    error_log("Error adding sentence: " . $stmt->error);
                }
                $stmt->close();
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            if ($id >= 0) {
                $stmt = $conn->prepare("DELETE FROM tekster WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    error_log("Sentence with id $id deleted.");
                } else {
                    error_log("Error deleting sentence: " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Invalid id received for delete: " . print_r($_POST['id'], true));
            }
        } elseif ($action === 'enable') {
            $id = intval($_POST['id'] ?? 0);
            if ($id >= 0) {
                // Disable all sentences first
                if (!$conn->query("UPDATE tekster SET active = 0")) {
                    error_log("Error disabling sentences: " . $conn->error);
                }
                // Enable the selected sentence
                $stmt = $conn->prepare("UPDATE tekster SET active = 1 WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) {
                    error_log("Sentence with id $id enabled.");
                } else {
                    error_log("Error enabling sentence: " . $stmt->error);
                }
                $stmt->close();
            } else {
                error_log("Invalid id received for enable: " . print_r($_POST['id'], true));
            }
        }
    } else {
        error_log("No action specified in POST data.");
    }
    header("Location: admin.php");
    exit;
}

// Fetch all sentences
$result = $conn->query("SELECT * FROM tekster ORDER BY id DESC");
$sentences = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sentences[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Administrations Panel</title>
  <style>
    /* Inspired by index.php's style.css */
    :root {
      --background-color: #1c1c1c;
      --container-bg: #121212;
      --current-word-bg: #2a2a2a;
      --text-color: #f0f0f0;
      --accent-color: #7b5fe4;
      --font-family: 'Roboto Mono', monospace;
    }
    
    * {
      box-sizing: border-box;
    }
    
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: var(--container-bg);
      color: var(--text-color);
      font-family: var(--font-family);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .container {
      width: 70vw;
      padding: 1rem;
      text-align: center;
      background: var(--background-color);
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    
    h1 {
      font-size: 2.5rem;
      margin-bottom: 1rem;
    }
    
    form {
      margin-bottom: 1rem;
    }
    
    input[type="text"] {
      width: 40%;
      padding: 0.5rem;
      background: var(--current-word-bg);
      border: 1px solid var(--accent-color);
      border-radius: 4px;
      color: var(--text-color);
      font-family: var(--font-family);
    }
    
    button {
      background: var(--accent-color);
      border: none;
      padding: 0.8rem 1rem;
      font-size: 1rem;
      color: var(--text-color);
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.1s ease;
      margin: 0 0.3rem;
    }
    
    button:hover {
      background: #5a6fb2;
      transform: translateY(-2px);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 2.5rem;
    }
    
    th, td {
      padding: 0.8rem;
      border-bottom: 1px solid var(--current-word-bg);
      text-align: left;
    }
    
    .active {
      color: green;
      font-weight: bold;
    }

    #add-button {
        margin-top: .75vh;
        width: 15%;
    }
    
    td form {
      display: inline;
    }

    .actions {
        gap: 0.75rem;
        display: flex;
        flex-direction: column;
    }

    .actions form {
        margin-bottom: 0;
    }
  </style>
</head>
<body>
<div class="container">
    <h1>Administrations Panel</h1>
    <form method="post" action="admin.php">
        <input type="hidden" name="action" value="add">
        <input type="text" name="sentence" placeholder="Indtast sætning" required>
        <button id="add-button" type="submit">Tilføj sætning</button>
    </form>
    
    <!-- List all sentences with options to enable or delete -->
    <table>
      <tr>
        <th>ID</th>
        <th>Sætning</th>
        <th>Status</th>
        <th>Handling</th>
      </tr>
      <?php foreach ($sentences as $sentence): ?>
      <tr>
        <td><?php echo $sentence['id']; ?></td>
        <td><?php echo htmlspecialchars($sentence['tekst']); ?></td>
        <td><?php echo $sentence['active'] == 1 ? '<span class="active">Aktiv</span>' : 'Inaktiv'; ?></td>
        <td class="actions">
            <?php if ($sentence['active'] != 1): ?>
            <form method="post" action="admin.php">
                <input type="hidden" name="action" value="enable">
                <input type="hidden" name="id" value="<?php echo $sentence['id']; ?>">
                <button type="submit">Aktivere</button>
            </form>
            <?php endif; ?>
            <form method="post" action="admin.php" onsubmit="return confirm('Er du helt sikker på at du vil slette denne sætning?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $sentence['id']; ?>">
                <button type="submit">Slet</button>
            </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
</div>
</body>
</html>
