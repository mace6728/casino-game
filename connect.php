<?php
// connect.php
$servername = "localhost";
$username = "casino";
$password = "roullete_bets";
$dbname = "users";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>