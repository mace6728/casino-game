<?php
// register.php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  // verify login
  if (empty($username) || empty($password)) {
    echo "<script>alert('Please fill in all fields.'); window.location.href = 'register.html';</script>";
    exit;
  }

  // check if the user has already existed
  $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo "<script>alert('User already exists. Please choose another username.'); window.location.href = 'register.html';</script>";
    $stmt->close();
    exit;
  }
  $stmt->close();

  // hashed password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // register a new account 
  $stmt = $conn->prepare("INSERT INTO users (username, password, chips) VALUES (?, ?, 1000)");
  $stmt->bind_param("ss", $username, $hashed_password);

  if ($stmt->execute()) {
    echo "<script>
      alert('Register successful!');
      window.location.href = './login.html';
    </script>";
  } else {
    echo "<script>alert('Registration failed. Please try again.'); window.location.href = 'register.html';</script>";
  }

  $stmt->close();
  $conn->close();
}
