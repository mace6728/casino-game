<?php
// get_username.php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['username']) && isset($_SESSION['chips'])) {
    echo json_encode([
        'username' => $_SESSION['username'],
        'chips' => $_SESSION['chips']
    ]);
} else {
    echo json_encode(['username' => null, 'chips' => 0]);
}
?>