<?php
include 'connect.php';

function spinWheel() {
    return rand(0, 36); // Simulate spinning the roulette wheel
}

function calculatePayout($betType, $betNumber, $winningNumber) {
    if ($betType == 'number' && $betNumber == $winningNumber) {
        return 35; // Payout for a straight-up bet
    }
    // Add more bet types and their payouts here
    return 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['user_id'];
    $betType = $_POST['bet_type'];
    $betAmount = $_POST['bet_amount'];
    $betNumber = $_POST['bet_number'] ?? null;

    $winningNumber = spinWheel();
    $payoutMultiplier = calculatePayout($betType, $betNumber, $winningNumber);
    $payout = $betAmount * $payoutMultiplier;

    $stmt = $conn->prepare("INSERT INTO roulette_bets (user_id, bet_type, bet_amount, bet_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdi", $userId, $betType, $betAmount, $betNumber);
    $stmt->execute();

    echo json_encode([
        'winning_number' => $winningNumber,
        'payout' => $payout
    ]);
}
?>