<?php
$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
                if (!$conn->query("UPDATE tekster SET active = 0")) {
                    error_log("Error disabling sentences: " . $conn->error);
                }
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
  <title>Administrationspanel</title>
  <style>
    :root {
      --background-color:rgb(20, 20, 20);
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
      max-width: 70vw;
      padding: 2rem;
      background: var(--background-color);
      border-radius: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.3);
      text-align: center;
      border: 1px solid var(--current-word-bg);
      box-shadow:rgba(155, 128, 253, 0.25) 0 0 100px -30px;
    }
    
    h1 {
      font-size: 2vw;
      margin-bottom: 1.5rem;
    }
    
    form {
      margin-bottom: 1.5rem;
    }
    
    input[type="text"] {
      width: 40%;
      padding: 0.5rem;
      background: var(--current-word-bg);
      border: 1px solid var(--accent-color);
      border-radius: 6px;
      font-size: 0.7vw;
      color: var(--text-color);
      height: 3rem;
      font-family: var(--font-family);
      margin-right: 0.5rem;
      transition: box-shadow 0.2s ease;
    }

    input[type="text"]:focus {
      outline: none;
      box-shadow: 0 0 5px 1px var(--accent-color);
    }

    table td {
      vertical-align: middle;
    }

    #submit {
      height: 3rem;
      border: 1px solid #7b5fe4;
      transition: background 0.3s ease, transform 0.2s ease, border-color 0.2s ease;
    }

    #submit:hover {
      background-color: #7b5fe4;
      border-color: #fff;
      transform: translateY(-2px);
    }

    #aktiver {
      background-color:rgb(62, 233, 76);
    }
    
    #slet {
      background-color:rgb(233, 62, 62);
    }

    button {
      background: var(--accent-color);
      border: 1px solid rgba(255, 255, 255, 0);
      padding: 0.8rem 1rem;
      font-size: 0.85vw;
      color: var(--text-color);
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease, border 0.2s ease;
      margin: 0.3rem 0;
    }
    
    button:hover {
      background: #5a6fb2;
      transform: translateY(-2px);
      border-color: #fff;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4.5vh;
      font-size: 0.85vw;
    }
    
    th, td {
      padding: 0.8rem;
      border-bottom: 1px solid var(--current-word-bg);
      text-align: left;
      vertical-align: top;
    }

    .sentence-text {
      max-width: 40vw;
      word-break: break-word;
      white-space: pre-wrap;
    }
    
    .active {
      color: rgb(62, 233, 76) ;
      font-weight: bold;
    }
    
    .actions {
      gap: 0.5rem;
    }
    
    .actions form {
      margin: 0;
      display: inline-block;
    }
  </style>
  <link rel="stylesheet" href="shared.css">
</head>
<body>
<div class="container">
    <h1>Administrationspanel</h1>
    <form method="post" action="admin.php">
        <input type="hidden" name="action" value="add">
        <input type="text" name="sentence" placeholder="Indtast en sætning" title="Indtast en sætning" required oninvalid="this.setCustomValidity('Indtast en sætning')">
        <button type="submit" id="submit">Tilføj sætning</button>
    </form>
    
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
        <td class="sentence-text"><?php echo htmlspecialchars($sentence['tekst']); ?></td>
        <td><?php echo $sentence['active'] == 1 ? '<span class="active">Aktiv</span>' : 'Inaktiv'; ?></td>
        <td class="actions">
            <?php if ($sentence['active'] != 1): ?>
            <form method="post" action="admin.php">
                <input type="hidden" name="action" value="enable">
                <input type="hidden" name="id" value="<?php echo $sentence['id']; ?>">
                <button type="submit" id="aktiver">Aktiver</button>
            </form>
            <?php endif; ?>
            <form method="post" action="admin.php" onsubmit="return confirm('Er du helt sikker på at du vil slette denne sætning?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $sentence['id']; ?>">
                <button type="submit" id="slet">Slet</button>
            </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
</div>
<div class="nav-buttons">
  <a href="leaderboard.php" class="nav-left">Leaderboard</a>
  <a href="index.php" class="nav-right">Forside</a>
</div>
</body>
</html>