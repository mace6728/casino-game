<!-- index.php -->
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Roulette Game</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>

  <!-- 導航欄 -->
  <nav class="navbar">
    <ul>
      <li><a href="index.php">Home</a></li>
      <li><a href="login.html">Login</a></li>
      <li><a href="register.html">Register</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>

  <div>
    <h1 class="title">Roulette Game</h1>
    <h3 id="username_display">Username:</h3>
    <h3 id="user_chips">Chips:</h3>

    <!-- 指針 -->
    <div class="pointer"></div>

    <div class="circle"></div>

    <form id="betForm">
      <input type="hidden" name="user_id" id="user_id" />
      <input type="hidden" name="bet_type" id="bet_type" />
      <input type="hidden" name="bet_number" id="bet_number" />
      <label for="bet_amount">Bet Amount:</label>
      <input type="number" name="bet_amount" id="bet_amount" required min="1" />
      <button type="submit">Start</button>
    </form>

    <div class="betting-board">
      <h2>Place Your Bets</h2>
      <div class="bet-options">
        <button class="bet-btn" data-bet="red">Red</button>
        <button class="bet-btn" data-bet="black">Black</button>
        <button class="bet-btn" data-bet="even">Even</button>
        <button class="bet-btn" data-bet="odd">Odd</button>
        <button class="bet-btn" data-bet="big">Big (19-36)</button>
        <button class="bet-btn" data-bet="small">Small (1-18)</button>
        <button class="bet-btn" data-bet="exact">Exact Number</button>
      </div>
    </div>

    <!-- 新增具體數字選項容器 -->
    <div class="exact-number-options" style="display: none">
      <h3>Select a Number</h3>
      <div class="numbers-grid">
        <!-- 使用 JavaScript 動態生成數字按鈕 -->
      </div>
    </div>

    <div id="result"></div>
  </div>
  <script src="script.js"></script>
</body>

</html>