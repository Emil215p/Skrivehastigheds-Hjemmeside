<?php
$servername = "172.16.3.24";
$username = "root";
$password = "test";
$dbname = "skptyping";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Added 'id' in case you want to use it later for unique identification
$sql = "SELECT id, navn, raw, errors, wpm, præcision, (wpm * præcision) AS points 
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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --background-color: #1c1c1c;
      --container-bg: #121212;
      --accent-color: #f97316;
      --text-color: #f0f0f0;
      --text-display-color: #777777;
      --outline-color: rgb(65, 65, 65);
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
      margin-bottom: 1.5vh;
    }
    header h1 {
      font-size: 2vw;
      margin-bottom: 1.5vh;
    }
    .container {
      width: 50vw;
      padding: .25vw;
      margin-bottom: 10vh;
    }
    .leaderboard {
      background: var(--background-color);
      border-radius: 0.6vw;
      overflow: hidden;
      border: 0.1vw solid rgb(70, 70, 70);
      box-shadow: rgba(249, 116, 22, 0.35) 0 0 5vw -1.5vw;
    }
    .leaderboard-header, .leaderboard-row {
      display: grid;
      grid-template-columns: 10% 40% 15% 15% 20%;
      padding: 2.25vh 1.5vw;
      align-items: center;
    }
    .leaderboard-header {
      background: var(--accent-color);
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.1vw;
    }
    .leaderboard-row {
      border-bottom: 0.1vw solid rgb(53, 53, 53);
      border-top: 0.1vw solid rgb(53, 53, 53);
      transition: background 0.2s ease;
      font-size: 1.25vw;
      cursor: pointer;
      background: rgb(22, 22, 22);
    }
    .leaderboard-row:nth-child(even) {
      background: rgb(28, 28, 28);
    }
    .leaderboard-row:hover {
      background: rgb(36, 36, 36);
    }
    .leaderboard-row:last-child {
      border-bottom: none;
    }
    .col {
      text-align: center;
      font-size: 1vw;
    }
    .name {
      text-align: left;
      padding-left: 0.2vw;
      text-overflow: ellipsis;
      overflow: hidden;
      max-width: 50vw;
    }
    .points {
      padding-left: 15vw;
      text-align: center;
    }
    .leaderboard-list {
      max-height: 70vh;
      overflow-y: auto;
    }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 1000;
    }
    .modal-content {
      background: var(--container-bg);
      margin: 7.5% auto;
      padding: 2vw;
      width: 40vw;
      border-radius: 0.6vw;
      height: 35vh;
      position: relative;
      border: 0.1vw solid var(--outline-color);
      box-shadow: 0 0 2.5vw rgba(255, 255, 255, 0.1);
    }
    .close {
      position: absolute;
      right: 1vw;
      top: 1vh;
      color: #aaa;
      font-size: 2vw;
      cursor: pointer;
      transition: color 0.25s ease;
    }
    .close:hover {
      color: #f97316;
    }
    .user-stats {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 2vw;
      margin: 2vw 0;
    }
    .stat-item {
      background: var(--background-color);
      padding: 1vw;
      border-radius: 0.5vw;
      text-align: center;
      height: 7.5vh;
      width: 17.25vw;
      display: flex;
      flex-direction: column;
      border: 0.1vw solid rgb(42, 42, 42);
    }
    h2 {
      font-size: 1.4vw;
      margin: 0 0 1.5vh;
    }
    .stat-value {
      display: block;
      font-size: 2vw;
      color: var(--accent-color);
      filter: drop-shadow(0 0.5vh 0.05vw rgba(0, 0, 0, 0.79)) drop-shadow(0 0.5vh 0.3vw rgba(0, 0, 0, 0.56));
    }
    #errors {
      color:rgb(226, 20, 20);
    }
    #raw {
      color: #148ce2;
    }
    .stat-label {
      font-size: 0.9vw;
      color: var(--text-display-color);
    }
  </style>
  <link rel="stylesheet" href="shared.css">
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
      <div class="col points">Points</div>
    </div>
    <div class="leaderboard-list">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php $rank = 1; while($row = $result->fetch_assoc()): ?>
          <!-- Each row carries a unique record id -->
          <div class="leaderboard-row" data-id="<?php echo htmlspecialchars($row['id']); ?>">
            <div class="col rank"><?php echo $rank++; ?></div>
            <div class="col name"><?php echo htmlspecialchars($row['navn']); ?></div>
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
<div class="nav-buttons">
  <a href="index.php" class="nav-left">Forside</a>
</div>
<div id="userModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 id="userName"></h2>
    <div class="user-stats">
      <div class="stat-item">
        <span class="stat-value" id="wpm"></span>
        <span class="stat-label">WPM</span>
      </div>
      <div class="stat-item">
        <span class="stat-value" id="accuracy"></span>
        <span class="stat-label">Præcision</span>
      </div>
      <div class="stat-item">
        <span class="stat-value" id="errors"></span>
        <span class="stat-label">Fejl</span>
      </div>
      <div class="stat-item">
        <span class="stat-value" id="raw"></span>
        <span class="stat-label">Input</span>
      </div>
    </div>
  </div>
</div>
<?php
$conn->close();
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('userModal');
  const closeBtn = document.querySelector('.close');

  document.querySelectorAll('.leaderboard-row').forEach(row => {
    row.addEventListener('click', async () => {
      // Retrieve the unique record id
      const id = row.getAttribute('data-id');
      const response = await fetch(`get_user_stats.php?id=${encodeURIComponent(id)}`);
      const data = await response.json();
      
      updateModal(data);
      modal.style.display = 'block';
    });
  });

  closeBtn.onclick = () => modal.style.display = 'none';
  window.onclick = (event) => {
    if (event.target === modal) modal.style.display = 'none';
  };

  function updateModal(data) {
    if(data.error) {
      alert(data.error);
      return;
    }
    // Populate the modal with the record's details
    document.getElementById('userName').textContent = data.navn;
    document.getElementById('wpm').textContent = data.wpm;
    document.getElementById('accuracy').textContent = data.præcision + '%';
    document.getElementById('errors').textContent = data.errors;
    document.getElementById('raw').textContent = data.raw;
  }
});
</script>
</body>
</html>
