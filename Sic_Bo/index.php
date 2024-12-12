<?php
session_start();

if (!isset($_SESSION['username'])) {
  die("<a href=\"../Roulette/login.html\" style=\"font-size:200%;\">Please Login!</a>");
  // die("<script>header(\"Location: ../Roulette/login.html\");</script>");
  exit;
}

$username = $_SESSION['username'];
// 資料庫連線
$conn = new mysqli('localhost', 'root', '', 'casino'); // 調整資料庫設定
if ($conn->connect_error) {
    die('Database connection failed');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'onload') {
    // 從資料庫取得用戶的 Chips
    $stmt = $conn->prepare('SELECT chips FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $_SESSION['playerChips'] = $row['chips'];
      echo json_encode(['playerChips' => $row['chips']]);
    }

    $stmt->close();   
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rollDice') {
  

    $bets = json_decode($_POST['bets'], true);

    $dice = [];
    $dice[0] = rand(1, 6);
    $dice[1] = rand(1, 6);
    $dice[2] = rand(1, 6);
    $totalPoints = $dice[0] + $dice[1] + $dice[2];

    if($_SESSION['playerChips'] == 0){
      echo json_encode([
        'dice' => $dice,
        'playerChips' => $_SESSION['playerChips'],
        "winningZones" => []
      ]);
      exit;
    }

    $payouts = [];
    foreach($bets as $key => $value){
      $payouts[$key] = 0;
    }

    // Triple
    if (count(array_unique($dice)) === 1) {
        $number = $dice[0];
        $payouts["Triple"] = $bets["Triple"] * 31;
        $payouts["Triple_$number"] = $bets["Triple_$number"] * 181;
    }
    else{
      if($totalPoints <= 10){
        $payouts["Small"] = $bets["Small"] * 2;
      }
      else{
        $payouts["Big"] = $bets["Big"] * 2;
      }
    }
    
    // Double
    foreach (array_count_values($dice) as $num => $count) {
        if ($count >= 2) {
            $payouts["Double_$num"] = $bets["Double_$num"] * 11;
        }
    }
    
    if($totalPoints === 4 || $totalPoints === 17){
      $payouts["Total$totalPoints"] = $bets["Total$totalPoints"] * 61;
    }
    elseif($totalPoints === 5 || $totalPoints === 16){
      $payouts["Total$totalPoints"] = $bets["Total$totalPoints"] * 31;
    }
    elseif($totalPoints === 6 || $totalPoints === 15){
      $payouts["Total$totalPoints"] = $bets["Total$totalPoints"] * 18;
    }
    elseif($totalPoints === 7 || $totalPoints === 14){
      $payouts["Total$totalPoints"] = $bets["Total$totalPoints"] * 13;
    }
    elseif($totalPoints === 8 || $totalPoints === 13){
      $payouts["Total$totalPoints"] = $bets["Total$totalPoints"] * 9;
    }
    elseif($totalPoints >= 9 && $totalPoints <= 12){
      $payouts["Total$totalPoints"] = $bets["Total$totalPoints"] * 7;
    }
    //combo
    foreach ($bets as $combo => $amount) {
        $combo_numbers = explode('_', str_replace('and', '', $combo));
        if (in_array($combo_numbers[0], $dice) && in_array($combo_numbers[1], $dice)) {
            $payouts[$combo] = $amount * 6;
        }
    }

    foreach (array_count_values($dice) as $num => $count) {
        if ($count == 3) {
            $payouts["point$num"] = $bets["point$num"] * 4;
        }
        elseif($count == 2){
          $payouts["point$num"] = $bets["point$num"] * 3;
        }
        elseif($count == 1){
          $payouts["point$num"] = $bets["point$num"] * 2;
        }
    }
    
    $winAmount = array_sum($payouts) - array_sum($bets);
    $_SESSION['playerChips'] += $winAmount;
    if($_SESSION['playerChips'] < 0){
      $_SESSION['playerChips'] = 0;
    }


    $stmt = $conn->prepare('UPDATE users SET chips = ? WHERE username = ?');
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param('is', $_SESSION['playerChips'], $_SESSION['username']);
    $stmt->execute();
    $stmt->close();


    echo json_encode([
        'dice' => $dice,
        'playerChips' => $_SESSION['playerChips'],
        "winningZones" => array_keys(array_filter($payouts, fn($value) => $value > 0))
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sic Bo</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div id="chipsboard">
      <div>Chips: <span id="player-chips">1000</span></div>
      <div>Bet: <span id="current-bet">0</span></div>    
  </div>
  <div class="dice-container">
    <div class="container">
      <div id='dice1' class="dice dice-one">
        <div id="dice-one-side-one" class='side one'>
          <div class="dot one-1"></div>
        </div>
        <div id="dice-one-side-two" class='side two'>
          <div class="dot two-1"></div>
          <div class="dot two-2"></div>
        </div>
        <div id="dice-one-side-three" class='side three'>
          <div class="dot three-1"></div>
          <div class="dot three-2"></div>
          <div class="dot three-3"></div>
        </div>
        <div id="dice-one-side-four" class='side four'>
          <div class="dot four-1"></div>
          <div class="dot four-2"></div>
          <div class="dot four-3"></div>
          <div class="dot four-4"></div>
        </div>
        <div id="dice-one-side-five" class='side five'>
          <div class="dot five-1"></div>
          <div class="dot five-2"></div>
          <div class="dot five-3"></div>
          <div class="dot five-4"></div>
          <div class="dot five-5"></div>
        </div>
        <div id="dice-one-side-six" class='side six'>
          <div class="dot six-1"></div>
          <div class="dot six-2"></div>
          <div class="dot six-3"></div>
          <div class="dot six-4"></div>
          <div class="dot six-5"></div>
          <div class="dot six-6"></div>
        </div>
      </div>
    </div>
    <div class="container">
      <div id='dice2' class="dice dice-two">
        <div id="dice-two-side-one" class='side one'>
          <div class="dot one-1"></div>
        </div>
        <div id="dice-two-side-two" class='side two'>
          <div class="dot two-1"></div>
          <div class="dot two-2"></div>
        </div>
        <div id="dice-two-side-three" class='side three'>
          <div class="dot three-1"></div>
          <div class="dot three-2"></div>
          <div class="dot three-3"></div>
        </div>
        <div id="dice-two-side-four" class='side four'>
          <div class="dot four-1"></div>
          <div class="dot four-2"></div>
          <div class="dot four-3"></div>
          <div class="dot four-4"></div>
        </div>
        <div id="dice-two-side-five" class='side five'>
          <div class="dot five-1"></div>
          <div class="dot five-2"></div>
          <div class="dot five-3"></div>
          <div class="dot five-4"></div>
          <div class="dot five-5"></div>
        </div>
        <div id="dice-two-side-six" class='side six'>
          <div class="dot six-1"></div>
          <div class="dot six-2"></div>
          <div class="dot six-3"></div>
          <div class="dot six-4"></div>
          <div class="dot six-5"></div>
          <div class="dot six-6"></div>
        </div>
      </div> 
    </div>
    <div class="container">
      <div id='dice3' class="dice dice-three">
        <div id="dice-three-side-one" class='side one'>
          <div class="dot one-1"></div>
        </div>
        <div id="dice-three-side-two" class='side two'>
          <div class="dot two-1"></div>
          <div class="dot two-2"></div>
        </div>
        <div id="dice-three-side-three" class='side three'>
          <div class="dot three-1"></div>
          <div class="dot three-2"></div>
          <div class="dot three-3"></div>
        </div>
        <div id="dice-three-side-four" class='side four'>
          <div class="dot four-1"></div>
          <div class="dot four-2"></div>
          <div class="dot four-3"></div>
          <div class="dot four-4"></div>
        </div>
        <div id="dice-three-side-five" class='side five'>
          <div class="dot five-1"></div>
          <div class="dot five-2"></div>
          <div class="dot five-3"></div>
          <div class="dot five-4"></div>
          <div class="dot five-5"></div>
        </div>
        <div id="dice-three-side-six" class='side six'>
          <div class="dot six-1"></div>
          <div class="dot six-2"></div>
          <div class="dot six-3"></div>
          <div class="dot six-4"></div>
          <div class="dot six-5"></div>
          <div class="dot six-6"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="game-container">
    <img src="background.png" alt="Sic Bo Table" class="game-table">
    
    <div class="bet-area small" onclick="openModal('Small')" id="Small"><img src="chips.png" alt="Chip" class="chip" id="chip-Small" style="display: none;"><div class="bet" id="bet-Small" style="display: none;"></div></div>
    <div class="bet-area big" onclick="openModal('Big')" id="Big"><img src="chips.png" alt="Chip" class="chip" id="chip-Big" style="display: none;"><div class="bet" id="bet-Big" style="display: none;"></div></div>
    
    <div class="bet-area triple" onclick="openModal('Triple')" id="Triple"><img src="chips.png" alt="Chip" class="chip" id="chip-Triple" style="display: none;"><div class="bet" id="bet-Triple" style="display: none;"></div></div>

    <div class="bet-area triple1" onclick="openModal('Triple_1')" id="Triple_1"><img src="chips.png" alt="Chip" class="chip" id="chip-Triple_1" style="display: none;"><div class="bet" id="bet-Triple_1" style="display: none;"></div></div>
    <div class="bet-area triple2" onclick="openModal('Triple_2')" id="Triple_2"><img src="chips.png" alt="Chip" class="chip" id="chip-Triple_2" style="display: none;"><div class="bet" id="bet-Triple_2" style="display: none;"></div></div>
    <div class="bet-area triple3" onclick="openModal('Triple_3')" id="Triple_3"><img src="chips.png" alt="Chip" class="chip" id="chip-Triple_3" style="display: none;"><div class="bet" id="bet-Triple_3" style="display: none;"></div></div>

    <div class="bet-area triple4" onclick="openModal('Triple_4')" id="Triple_4"><img src="chips.png" alt="Chip" class="chip" id="chip-Triple_4" style="display: none;"><div class="bet" id="bet-Triple_4" style="display: none;"></div></div>
    <div class="bet-area triple5" onclick="openModal('Triple_5')" id="Triple_5"><img src="chips.png" alt="Chip" class="chip" id="chip-Triple_5" style="display: none;"><div class="bet" id="bet-Triple_5" style="display: none;"></div></div>
    <div class="bet-area triple6" onclick="openModal('Triple_6')" id="Triple_6"><img src="chips.png" alt="Chip" class="chip" id="chip-Triple_6" style="display: none;"><div class="bet" id="bet-Triple_6" style="display: none;"></div></div>

    <div class="bet-area double1" onclick="openModal('Double_1')" id="Double_1"><img src="chips.png" alt="Chip" class="chip" id="chip-Double_1" style="display: none;"><div class="bet" id="bet-Double_1" style="display: none;"></div></div>
    <div class="bet-area double2" onclick="openModal('Double_2')" id="Double_2"><img src="chips.png" alt="Chip" class="chip" id="chip-Double_2" style="display: none;"><div class="bet" id="bet-Double_2" style="display: none;"></div></div>
    <div class="bet-area double3" onclick="openModal('Double_3')" id="Double_3"><img src="chips.png" alt="Chip" class="chip" id="chip-Double_3" style="display: none;"><div class="bet" id="bet-Double_3" style="display: none;"></div></div>

    <div class="bet-area double4" onclick="openModal('Double_4')" id="Double_4"><img src="chips.png" alt="Chip" class="chip" id="chip-Double_4" style="display: none;"><div class="bet" id="bet-Double_4" style="display: none;"></div></div>
    <div class="bet-area double5" onclick="openModal('Double_5')" id="Double_5"><img src="chips.png" alt="Chip" class="chip" id="chip-Double_5" style="display: none;"><div class="bet" id="bet-Double_5" style="display: none;"></div></div>
    <div class="bet-area double6" onclick="openModal('Double_6')" id="Double_6"><img src="chips.png" alt="Chip" class="chip" id="chip-Double_6" style="display: none;"><div class="bet" id="bet-Double_6" style="display: none;"></div></div>

    <div class="bet-area total4" onclick="openModal('Total4')" id="Total4"><img src="chips.png" alt="Chip" class="chip" id="chip-Total4" style="display: none;"><div class="bet" id="bet-Total4" style="display: none;"></div></div>
    <div class="bet-area total5" onclick="openModal('Total5')" id="Total5"><img src="chips.png" alt="Chip" class="chip" id="chip-Total5" style="display: none;"><div class="bet" id="bet-Total5" style="display: none;"></div></div>
    <div class="bet-area total6" onclick="openModal('Total6')" id="Total6"><img src="chips.png" alt="Chip" class="chip" id="chip-Total6" style="display: none;"><div class="bet" id="bet-Total6" style="display: none;"></div></div>
    <div class="bet-area seven" onclick="openModal('Total7')" id="Total7"><img src="chips.png" alt="Chip" class="chip" id="chip-Total7" style="display: none;"><div class="bet" id="bet-Total7" style="display: none;"></div></div>
    <div class="bet-area eight" onclick="openModal('Total8')" id="Total8"><img src="chips.png" alt="Chip" class="chip" id="chip-Total8" style="display: none;"><div class="bet" id="bet-Total8" style="display: none;"></div></div>
    <div class="bet-area nine" onclick="openModal('Total9')" id="Total9"><img src="chips.png" alt="Chip" class="chip" id="chip-Total9" style="display: none;"><div class="bet" id="bet-Total9" style="display: none;"></div></div>
    <div class="bet-area ten" onclick="openModal('Total10')" id="Total10"><img src="chips.png" alt="Chip" class="chip" id="chip-Total10" style="display: none;"><div class="bet" id="bet-Total10" style="display: none;"></div></div>
    <div class="bet-area eleven" onclick="openModal('Total11')" id="Total11"><img src="chips.png" alt="Chip" class="chip" id="chip-Total11" style="display: none;"><div class="bet" id="bet-Total11" style="display: none;"></div></div>
    <div class="bet-area twelve" onclick="openModal('Total12')" id="Total12"><img src="chips.png" alt="Chip" class="chip" id="chip-Total12" style="display: none;"><div class="bet" id="bet-Total12" style="display: none;"></div></div>
    <div class="bet-area thirteen" onclick="openModal('Total13')" id="Total13"><img src="chips.png" alt="Chip" class="chip" id="chip-Total13" style="display: none;"><div class="bet" id="bet-Total13" style="display: none;"></div></div>
    <div class="bet-area fourteen" onclick="openModal('Total14')" id="Total14"><img src="chips.png" alt="Chip" class="chip" id="chip-Total14" style="display: none;"><div class="bet" id="bet-Total14" style="display: none;"></div></div>
    <div class="bet-area fifteen" onclick="openModal('Total15')" id="Total15"><img src="chips.png" alt="Chip" class="chip" id="chip-Total15" style="display: none;"><div class="bet" id="bet-Total15" style="display: none;"></div></div>
    <div class="bet-area sixteen" onclick="openModal('Total16')" id="Total16"><img src="chips.png" alt="Chip" class="chip" id="chip-Total16" style="display: none;"><div class="bet" id="bet-Total16" style="display: none;"></div></div>
    <div class="bet-area seventeen" onclick="openModal('Total17')" id="Total17"><img src="chips.png" alt="Chip" class="chip" id="chip-Total17" style="display: none;"><div class="bet" id="bet-Total17" style="display: none;"></div></div>

    <div class="bet-area and12" onclick="openModal('and1_2')" id="and1_2"><img src="chips.png" alt="Chip" class="chip" id="chip-and1_2" style="display: none;"><div class="bet" id="bet-and1_2" style="display: none;"></div></div>
    <div class="bet-area and13" onclick="openModal('and1_3')" id="and1_3"><img src="chips.png" alt="Chip" class="chip" id="chip-and1_3" style="display: none;"><div class="bet" id="bet-and1_3" style="display: none;"></div></div>
    <div class="bet-area and14" onclick="openModal('and1_4')" id="and1_4"><img src="chips.png" alt="Chip" class="chip" id="chip-and1_4" style="display: none;"><div class="bet" id="bet-and1_4" style="display: none;"></div></div>
    <div class="bet-area and15" onclick="openModal('and1_5')" id="and1_5"><img src="chips.png" alt="Chip" class="chip" id="chip-and1_5" style="display: none;"><div class="bet" id="bet-and1_5" style="display: none;"></div></div>
    <div class="bet-area and16" onclick="openModal('and1_6')" id="and1_6"><img src="chips.png" alt="Chip" class="chip" id="chip-and1_6" style="display: none;"><div class="bet" id="bet-and1_6" style="display: none;"></div></div>
    <div class="bet-area and23" onclick="openModal('and2_3')" id="and2_3"><img src="chips.png" alt="Chip" class="chip" id="chip-and2_3" style="display: none;"><div class="bet" id="bet-and2_3" style="display: none;"></div></div>
    <div class="bet-area and24" onclick="openModal('and2_4')" id="and2_4"><img src="chips.png" alt="Chip" class="chip" id="chip-and2_4" style="display: none;"><div class="bet" id="bet-and2_4" style="display: none;"></div></div>
    <div class="bet-area and25" onclick="openModal('and2_5')" id="and2_5"><img src="chips.png" alt="Chip" class="chip" id="chip-and2_5" style="display: none;"><div class="bet" id="bet-and2_5" style="display: none;"></div></div>
    <div class="bet-area and26" onclick="openModal('and2_6')" id="and2_6"><img src="chips.png" alt="Chip" class="chip" id="chip-and2_6" style="display: none;"><div class="bet" id="bet-and2_6" style="display: none;"></div></div>
    <div class="bet-area and34" onclick="openModal('and3_4')" id="and3_4"><img src="chips.png" alt="Chip" class="chip" id="chip-and3_4" style="display: none;"><div class="bet" id="bet-and3_4" style="display: none;"></div></div>
    <div class="bet-area and35" onclick="openModal('and3_5')" id="and3_5"><img src="chips.png" alt="Chip" class="chip" id="chip-and3_5" style="display: none;"><div class="bet" id="bet-and3_5" style="display: none;"></div></div>
    <div class="bet-area and36" onclick="openModal('and3_6')" id="and3_6"><img src="chips.png" alt="Chip" class="chip" id="chip-and3_6" style="display: none;"><div class="bet" id="bet-and3_6" style="display: none;"></div></div>
    <div class="bet-area and45" onclick="openModal('and4_5')" id="and4_5"><img src="chips.png" alt="Chip" class="chip" id="chip-and4_5" style="display: none;"><div class="bet" id="bet-and4_5" style="display: none;"></div></div>
    <div class="bet-area and46" onclick="openModal('and4_6')" id="and4_6"><img src="chips.png" alt="Chip" class="chip" id="chip-and4_6" style="display: none;"><div class="bet" id="bet-and4_6" style="display: none;"></div></div>
    <div class="bet-area and56" onclick="openModal('and5_6')" id="and5_6"><img src="chips.png" alt="Chip" class="chip" id="chip-and5_6" style="display: none;"><div class="bet" id="bet-and5_6" style="display: none;"></div></div>

    <div class="bet-area point1" onclick="openModal('point1')" id="point1"><img src="chips.png" alt="Chip" class="chip" id="chip-point1" style="display: none;"><div class="bet" id="bet-point1" style="display: none;"></div></div>
    <div class="bet-area point2" onclick="openModal('point2')" id="point2"><img src="chips.png" alt="Chip" class="chip" id="chip-point2" style="display: none;"><div class="bet" id="bet-point2" style="display: none;"></div></div>
    <div class="bet-area point3" onclick="openModal('point3')" id="point3"><img src="chips.png" alt="Chip" class="chip" id="chip-point3" style="display: none;"><div class="bet" id="bet-point3" style="display: none;"></div></div>
    <div class="bet-area point4" onclick="openModal('point4')" id="point4"><img src="chips.png" alt="Chip" class="chip" id="chip-point4" style="display: none;"><div class="bet" id="bet-point4" style="display: none;"></div></div>
    <div class="bet-area point5" onclick="openModal('point5')" id="point5"><img src="chips.png" alt="Chip" class="chip" id="chip-point5" style="display: none;"><div class="bet" id="bet-point5" style="display: none;"></div></div>
    <div class="bet-area point6" onclick="openModal('point6')" id="point6"><img src="chips.png" alt="Chip" class="chip" id="chip-point6" style="display: none;"><div class="bet" id="bet-point6" style="display: none;"></div></div>
    
  </div>

  <div class="overlay" id="overlay"></div>

  <div class="modal" id="modal">
    <h1 id="whichArea">Adjust Your Bet</h1>
    <h2 id="odds">1 wins 1</h2>
    <div>
      <button onclick="adjustBet(-1)">-</button>
      <input type="text" id="bet-amount" value="0" readonly>
      <button onclick="adjustBet(1)">+</button>
    </div>
    <button onclick="saveBet()">Save</button>
    <button id="clear-btn" onclick="clearBet()">Clear</button>
    <button onclick="closeModal()">Cancel</button>
  </div>

  <div id="rollBtn">
    <button id="roll-button" onclick="rollDice()"><img src="roll_the_dice.png" style="width: 100%;"></button>
  </div>

  <button class="quick-bet-btn" onclick="openQuickBetModal()">Quick Bet</button>

  <div class="back">
    <a href="../lobby/index.php"><img src="backbutton.png" alt="back to lobby" style="width:100%;"></a>
  </div>

  <div class="modal" id="quick-bet-modal">
    <h1>Quick Bet</h1>
    <div>
      <input type="text" id="quick-bet-amount" value="0">
    </div>
    <button onclick="QuickBet()">Save</button>
    <button onclick="closeQuickBetModal()">Cancel</button>
  </div>

  <script src="script.js"></script>
</body>
</html>
