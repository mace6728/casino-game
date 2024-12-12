<?php
    $dbname = 'casino'; 
    $dbusername = 'root'; 
    $dbpassword = '';

    session_start();

    try {
        $database = new PDO("mysql:host=localhost;dbname=$dbname", $dbusername, $dbpassword);
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Could not connect to database: " . $e->getMessage());
    }

    if(array_key_exists('username', $_GET)){
        $username = $_GET['username'];
        try {
            $query = "SELECT chips FROM users WHERE username = :username";
            $stmt = $database->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                echo json_encode(['bank' => $result['chips']]);
                $_SESSION['username'] = $username;
                $_SESSION['chips'] = $result['chips'];

            } else {
                echo json_encode(['bank' => 0]);
            }

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if(array_key_exists('username', $_POST) && array_key_exists('bank', $_POST)){
        $username = $_POST['username'];
        $bank = intval($_POST['bank']);
        $_SESSION['username'] = $username;
        $_SESSION['chips'] = $result['chips'];

        try {
            $updateQuery = "UPDATE users SET chips = :newBankAmount WHERE username = :username";
            $updateStmt = $database->prepare($updateQuery);
            $updateStmt->bindParam(':newBankAmount', $bank, PDO::PARAM_INT);
            $updateStmt->bindParam(':username', $username, PDO::PARAM_STR);
            $updateStmt->execute();

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    $database = null;
?>
