<?php
// roulette.php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $betType = $_POST['bet_type'];
    $betAmount = intval($_POST['bet_amount']);
    $betNumber = $_POST['bet_number'] ?? null;

    // 檢查用戶是否有足夠的籌碼
    $stmt = $conn->prepare("SELECT chips FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($currentChips);
    $stmt->fetch();
    $stmt->close();

    if ($betAmount > $currentChips) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient chips']);
        exit;
    }

    // 扣除籌碼
    $stmt = $conn->prepare("UPDATE users SET chips = chips - ? WHERE id = ?");
    $stmt->bind_param("ii", $betAmount, $userId);
    $stmt->execute();
    $stmt->close();

    // 旋轉輪盤
    $winningNumber = spinWheel();
    $payoutMultiplier = calculatePayout($betType, $betNumber, $winningNumber);
    $payout = $betAmount * $payoutMultiplier;

    // 更新用戶籌碼
    if ($payout > 0) {
        $stmt = $conn->prepare("UPDATE users SET chips = chips + ? WHERE id = ?");
        $stmt->bind_param("ii", $payout, $userId);
        $stmt->execute();
        $stmt->close();

        // 更新會話中的籌碼
        $_SESSION['chips'] += $payout;
    }

    // 插入下注記錄
    $stmt = $conn->prepare("INSERT INTO roulette_bets (user_id, bet_type, bet_number, bet_amount, winning_number, payout) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issisi", $userId, $betType, $betNumber, $betAmount, $winningNumber, $payout);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'status' => 'success',
        'winning_number' => $winningNumber,
        'payout' => $payout
    ]);
}

function spinWheel() {
    $numbers = ['0', '28', '9', '26', '30', '11', '7', '20', '32', '17', '5', '22', '34', '15', '3', '24', '36', '13', '1', '00', '27', '10', '25', '29', '12', '8', '19', '31', '18', '6', '21', '33', '16', '4', '23', '35', '14', '2'];
    return $numbers[array_rand($numbers)];
}

function calculatePayout($betType, $betNumber, $winningNumber) {
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

function getColor($number) {
    $red = ['1','3','5','7','9','12','14','16','18','19','21','23','25','27','30','32','34','36'];
    if ($number == '0' || $number == '00') return 'green';
    return in_array($number, $red) ? 'red' : 'black';
}

function getEvenOdd($number) {
    if ($number == '0' || $number == '00') return 'none';
    $num = intval($number);
    return ($num % 2 == 0) ? 'even' : 'odd';
}

function getBigSmall($number) {
    if ($number == '0' || $number == '00') return 'none';
    $num = intval($number);
    return ($num >= 19 && $num <= 36) ? 'big' : 'small';
}
?>