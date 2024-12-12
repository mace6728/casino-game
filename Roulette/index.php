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
      <li><a href="../lobby/index.php">Lobby</a></li>
    </ul>
  </nav>

  <div>
    <h1 class="title">Roulette Game</h1>
    <h3 id="username_display">Username:</h3>
    <h3 id="user_chips">Chips:</h3>

    <!-- 指針 -->
    <div class="pointer"></div>
    <div class="small-square1"></div>
    <div class="small-square2"></div>
    <div class="small-square3"></div>
    <div class="small-square4"></div>
    <div class="small-square5"></div>
    <div class="small-square6"></div>
    <div class="circle"></div>

    <form id="betForm">
      <input type="hidden" name="user_id" id="user_id" />
      <input type="hidden" name="bet_type" id="bet_type" />
      <input type="hidden" name="bet_number" id="bet_number" />
      <label for="bet_amount">Bet Amount:</label>
      <input type="number" name="bet_amount" id="bet_amount"  required min="1" />
      <button type="submit" class="bet-btn">Start</button>
    </form>

    <div class="betting-board">
      <h2>Place Your Bets</h2>
      <div class="bet-options">
        <button class="button" data-bet="red">Red</button>
        <button class="button" data-bet="black">Black</button>
        <button class="button" data-bet="even">Even</button>
        <button class="button" data-bet="odd">Odd</button>
        <button class="button" data-bet="big">Big (19-36)</button>
        <button class="button" data-bet="small">Small (1-18)</button>
        <button class="button" data-bet="exact">Exact Number</button>
      </div>
    </div>

    <div class="exact-number-options" style="display: none">
      <h3>Select a Number</h3>
      <div class="numbers-grid">
      </div>
    </div>

    <div id="result"></div>
    <h3 id="payout"></h3>
  </div>
  <script src="script.js"></script>
</body>

</html>