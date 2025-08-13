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
    header("Location: ../pages/admin.php");
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
      --outline-color:rgb(65, 65, 65);
      --text-color: #f0f0f0;
      --accent-color: #f97316;
      --font-family: 'Roboto Mono', monospace;
    }
    
    * {
      box-sizing: border-box;
    }
    
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      background: rgb(10, 10, 10);
      color: var(--text-color);
      font-family: var(--font-family);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    html::-webkit-scrollbar {
      display: none;
    }

    html {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    
    .container {
      width: 60vw;
      padding: 2vw;
      background: var(--background-color);
      margin-top: 4vh;
      margin-bottom: 4vh;
      border-radius: 0.6vw;
      box-shadow: 0 2vw 8vw rgba(0,0,0,0.3);
      text-align: center;
      border: 0.1vw solid var(--outline-color);
      box-shadow:rgba(249, 116, 22, 0.4) 0 0 6vw -1.5vw;
    }
    
    h1 {
      font-size: 2vw;
      margin-bottom: 1.5vh;
    }
    
    form {
      margin-bottom: 1.5vh;
      display: flex;
      gap: 1vw;
      justify-content: center;
    }
    
    input[type="text"] {
      width: 16vw;
      padding: 0.5vw;
      background: var(--current-word-bg);
      border: 0.1vw solid var(--accent-color);
      border-radius: 0.3vw;
      font-size: 0.7vw;
      color: var(--text-color);
      height: 5vh;
      font-family: var(--font-family);
      margin-right: 0.5vw;
      transition: box-shadow 0.3s ease;
    }

    input[type="text"]:focus {
      outline: none;
      box-shadow: 0 0 .5vw 0.01vw var(--accent-color);
    }

    table td {
      vertical-align: middle;
    }

    #submit {
      height: 5vh;
      margin: 0;
      border: 0.1vw solid #f97316;
      box-shadow: 0 0 2vw -.75vw #f97316;
      transition: background 0.3s ease, transform 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }

    #submit:hover {
      color: #000;
      background-color: #fff;
      border-color: #000;
      transform: translateY(-0.3vh);
    }

    #aktiver {
      background-color:rgb(62, 233, 76);
    }
    
    #slet {
      background-color:rgb(233, 62, 62);
    }

    button {
      background: var(--accent-color);
      border: 0.1vw solid rgba(255, 255, 255, 0);
      padding: 0.8vh 1vw;
      font-size: 0.85vw;
      color: var(--text-color);
      border-radius: .3vw;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease, border 0.2s ease;
      margin: 0.3vh 0;
    }

    #aktiver-grå {
      background: rgba(62, 233, 76, 0.2);
      color: rgba(255, 255, 255, 0.5);
    }

    #aktiver-grå:hover {
      border-color: rgba(255, 255, 255, 0);
      cursor: not-allowed;
    }
    
    button:hover {
      background: #5a6fb2;
      transform: translateY(-0.2vh);
      border-color: #fff;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 4.5vh;
      font-size: 0.85vw;
    }
    
    th, td {
      padding: 0.8vw;
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
      gap: 0.5vh;
    }
    
    .actions form {
      margin: 0;
      display: inline-block;
    }

    .sentence-row {
      border-top: 0.1vw solid rgb(53, 53, 53);
    }
  </style>
  <link rel="stylesheet" href="shared.css">
</head>
<body>
<div class="container">
    <h1>Administrationspanel</h1>
    <form method="post" action="admin.php">
        <input type="hidden" name="action" value="add">
        <input type="text" name="sentence" maxlength="500" placeholder="Indtast en sætning" title="Indtast en sætning" required oninvalid="this.setCustomValidity('Indtast en sætning')">
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
      <tr class="sentence-row">
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
            <?php if ($sentence['active'] >= 1): ?>
            <form>
                <button type="submit" id="aktiver-grå" disabled>Aktiver</button>
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
  <a href="../pages/index.php" class="nav-right">Forside</a>
</div>
</body>
</html>