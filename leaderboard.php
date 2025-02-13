<?php
// Database connection settings (use the same credentials as in save_result.php)
$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get all results and calculate the points (wpm * præcision)
// Sorting the results by points in descending order
$sql = "SELECT navn, wpm, præcision, (wpm * præcision) AS points 
        FROM resultater 
        ORDER BY points DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Leaderboard</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --background-color: #1c1c1c;
      --container-bg: #121212;
      --accent-color: #7b5fe4;
      --text-color: #f0f0f0;
      --text-display-color: #777777;
    }
    body {
      background: var(--container-bg);
      color: var(--text-color);
      font-family: 'Roboto Mono', monospace;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    header {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    header h1 {
      font-size: 2.5rem;
      margin-bottom: 1.5rem;
    }
    .container {
      width: 70vw;
      max-width: 90vw;
      padding: .25rem;
      margin-bottom: 10vh;
    }
    .leaderboard {
      background: var(--background-color);
      border-radius: 8px;
      overflow: hidden;
    }
    .leaderboard-header, .leaderboard-row {
      display: grid;
      grid-template-columns: 10% 40% 15% 15% 20%;
      padding: 1.25rem 1rem;
      align-items: center;
    }
    .leaderboard-header {
      background: var(--accent-color);
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.1rem;
    }
    .leaderboard-row {
      border-bottom: 1px solid #2a2a2a;
      transition: background 0.2s ease;
      font-size: 1.25vw;
    }
    .leaderboard-row:hover {
      background: #1f1f1f;
    }
    .leaderboard-row:last-child {
      border-bottom: none;
    }
    .col {
      text-align: center;
    }
    .name {
      text-align: left;
      padding-left: 0.2vw;
      text-overflow: ellipsis;
      overflow: hidden;
      max-width: 50vw;
    }
    .leaderboard-list {
      max-height: 70vh;
      overflow-y: auto;
    }
    /* Scrollbar styling for Webkit browsers */
    .leaderboard-list::-webkit-scrollbar {
      width: 8px;
    }
    .leaderboard-list::-webkit-scrollbar-track {
      background: #1c1c1c;
    }
    .leaderboard-list::-webkit-scrollbar-thumb {
      background: var(--accent-color);
      border-radius: 4px;
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <h1>Leaderboard</h1>
  </header>
  <div class="leaderboard">
    <div class="leaderboard-header">
      <div class="col rank">#</div>
      <div class="col name">Navn</div>
      <div class="col wpm">WPM</div>
      <div class="col precision">Præcision</div>
      <div class="col points">Points</div>
    </div>
    <div class="leaderboard-list">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php $rank = 1; while($row = $result->fetch_assoc()): ?>
          <div class="leaderboard-row">
            <div class="col rank"><?php echo $rank++; ?></div>
            <div class="col name"><?php echo htmlspecialchars($row['navn']); ?></div>
            <div class="col wpm"><?php echo htmlspecialchars($row['wpm']); ?></div>
            <div class="col precision"><?php echo htmlspecialchars($row['præcision']); ?>%</div>
            <div class="col points"><?php echo htmlspecialchars($row['points']); ?></div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="leaderboard-row">
          <div class="col" style="grid-column: 1 / -1;">No results found.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$conn->close();
?>
</body>
</html>
