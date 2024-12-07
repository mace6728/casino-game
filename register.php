<?php
// register.php
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  // 驗證輸入
  if (empty($username) || empty($password)) {
    echo "<script>alert('Please fill in all fields.'); window.location.href = 'register.html';</script>";
    exit;
  }

  // 檢查用戶是否已存在
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

  // 哈希密碼
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // 插入新用戶
  $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
  $stmt->bind_param("ss", $username, $hashed_password);

  if ($stmt->execute()) {
    echo "<script>
      localStorage.removeItem('logoutButtonDisabled');
      alert('Login successful!');
      window.location.href = 'index.html';
    </script>";
  } else {
    echo "<script>alert('Registration failed. Please try again.'); window.location.href = 'register.html';</script>";
  }

  $stmt->close();
  $conn->close();
}
