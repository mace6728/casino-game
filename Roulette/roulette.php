<?php
// roulette.php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize POST data
    $username = $_SESSION['username'] ?? null;
    $betType = $_POST['bet_type'] ?? null;
    $betAmount = isset($_POST['bet_amount']) ? intval($_POST['bet_amount']) : 0;
    $betNumber = $_POST['bet_number'] ?? null;
    $winningNumber = $_POST['winning_number'] ?? null;

    // Validate required fields
    if (!$username || !$betType || !$betAmount || !$winningNumber) {
        echo json_encode(['error' => 'Missing required fields.']);
        exit;
    }

    // Deduct chips
    $stmt = $conn->prepare("UPDATE users SET chips = chips - ? WHERE username = ?");
    if (!$stmt) {
        error_log("Prepare failed (Deduct Chips): " . $conn->error);
        echo json_encode(['error' => 'Database error.']);
        exit;
    }

    $stmt->bind_param("is", $betAmount, $username);
    if (!$stmt->execute()) {
        error_log("Execute failed (Deduct Chips): " . $stmt->error);
        echo json_encode(['error' => 'Database execution error.']);
        $stmt->close();
        exit;
    }
    $stmt->close();
    $_SESSION['chips'] -= $betAmount;

    // Calculate payout
    $payoutMultiplier = calculatePayout($betType, $betNumber, $winningNumber);
    $payout = $betAmount * $payoutMultiplier;

    // Log payout details
    error_log("Payout Multiplier: " . $payoutMultiplier);
    error_log("Bet Type: " . $betType);
    error_log("Bet Number: " . $betNumber);
    error_log("Winning Number: " . $winningNumber);
    error_log("Bet Amount: " . $betAmount);
    error_log("Payout: " . $payout);

    // Update chips if payout > 0
    if ($payout > 0) {
        $stmt = $conn->prepare("UPDATE users SET chips = chips + ? WHERE username = ?");
        if (!$stmt) {
            error_log("Prepare failed (Add Chips): " . $conn->error);
            echo json_encode(['error' => 'Database error.']);
            exit;
        }
        $stmt->bind_param("is", $payout, $username);
        if (!$stmt->execute()) {
            error_log("Execute failed (Add Chips): " . $stmt->error);
            echo json_encode(['error' => 'Database execution error.']);
            $stmt->close();
            exit;
        }
        $stmt->close();
    }

    // Update session chips
    error_log("Chips before: " . $_SESSION['chips']);
    $_SESSION['chips'] += $payout;
    error_log("Chips after: " . $_SESSION['chips']);

    // Insert bet record
    $stmt = $conn->prepare("INSERT INTO roulette_bets (username, bet_type, bet_number, bet_amount, winning_number, payout) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log("Prepare failed (Insert Bet): " . $conn->error);
        echo json_encode(['error' => 'Database error.']);
        exit;
    }

    $stmt->bind_param("sssisi", $username, $betType, $betNumber, $betAmount, $winningNumber, $payout);
    if (!$stmt->execute()) {
        error_log("Execute failed (Insert Bet): " . $stmt->error);
        echo json_encode(['error' => 'Database execution error.']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Prepare response
    $response = array(
        'chips' => isset($_SESSION['chips']) ? $_SESSION['chips'] : 1000
    );
    echo json_encode($response);
}




function calculatePayout($betType, $betNumber, $winningNumber)
{
    // 定義贏的屬性
    $winningColor = getColor($winningNumber);
    $winningEvenOdd = getEvenOdd($winningNumber);
    $winningBigSmall = getBigSmall($winningNumber);

    switch ($betType) {
        case 'red':
        case 'black':
            return ($betType === $winningColor) ? 2 : 0;
        case 'even':
        case 'odd':
            return ($betType === $winningEvenOdd) ? 2 : 0;
        case 'big':
        case 'small':
            return ($betType === $winningBigSmall) ? 2 : 0;
        case 'exact':
            return ($betNumber === $winningNumber) ? 35 : 0;
        default:
            return 0;
    }
}

function getColor($number)
{
    $red = ['1', '3', '5', '7', '9', '12', '14', '16', '18', '19', '21', '23', '25', '27', '30', '32', '34', '36'];
    if ($number == '0' || $number == '00') return 'green';
    return in_array($number, $red) ? 'red' : 'black';
}

function getEvenOdd($number)
{
    if ($number == '0' || $number == '00') return 'none';
    $num = intval($number);
    return ($num % 2 == 0) ? 'even' : 'odd';
}

function getBigSmall($number)
{
    if ($number == '0' || $number == '00') return 'none';
    $num = intval($number);
    return ($num >= 19 && $num <= 36) ? 'big' : 'small';
}
?>