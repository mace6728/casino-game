<?php
    $dbname = 'your_database_name'; 
    $dbusername = 'your_database_username'; 
    $dbpassword = 'your_database_password'; 

    try {
        $database = new PDO("mysql:host=localhost;dbname=$dbname", $dbusername, $dbpassword);
        $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Could not connect to database: " . $e->getMessage());
    }

    if(array_key_exists('username', $_GET)){
        $username = decodeURIComponent($_GET['username']);
        try {
            $query = "SELECT money FROM login WHERE username = :username";
            $stmt = $database->prepare($query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                echo $result['money']; 
            } else {
                echo "0";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    if(array_key_exists('username', $_POST) && array_key_exists('bank', $_POST)){
        $username = decodeURIComponent($_POST['username']);
        $bank = intval(decodeURIComponent($_POST['bank']));

        try {
            $updateQuery = "UPDATE login SET money = :newBankAmount WHERE username = :username";
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
