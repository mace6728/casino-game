<?php
// login.php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        echo "<script>alert('Username and password cannot be empty.'); window.location.href = 'login.html';</script>";
        exit;
    }

    // Query user
    $stmt = $conn->prepare("SELECT id, password, chips FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hashed_password, $chips);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['chips'] = $chips;

            header("Location: index.php");
            exit;
        } else {
            echo "<script>alert('Invalid password.'); window.location.href = 'login.html';</script>";
        }
    } else {
        echo "<script>alert('User does not exist.'); window.location.href = 'login.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>