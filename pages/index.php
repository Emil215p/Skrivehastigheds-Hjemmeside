<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Skrive Hastighedstest</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="../css/shared.css">
  </head>
  <body>
  <div class="container">
      <header>
        <h1>Skrive Hastighedstest</h1>
      </header>
      <main>
        <section id="test-area">
          <div id="text-display"></div>
          <button id="reset-btn" type="button">Genstart Test</button>
        </section>
      </main>
    </div>
    <div id="progress"><div id="timer-display"></div></div>
    <div id="result-modal" class="modal">
      <div class="modal-content">
        <span id="modal-close" class="close">&times;</span>
        <h2>Test Resultater</h2>
        <p id="modal-wpm"></p>
        <p id="modal-accuracy"></p>
        <form id="result-form">
          <input type="text" id="name-input" placeholder="Indtast dit navn" required>
          <button type="submit">Gem resultat</button>
        </form>
        <p id="result-feedback"></p>
      </div>
    </div>
    <div class="nav-buttons">
      <a href="../pages/leaderboard.php" class="nav-left">Leaderboard</a>
    </div>
    <script src="../scripts/script.js"></script>
  </body>
</html>