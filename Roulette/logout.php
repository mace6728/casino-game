<?php
// clear all the session and local storage we use
session_start();
$_SESSION = []; 
session_unset();
session_destroy();
echo '<script type="text/javascript">
    localStorage.clear();
    console.log("Username stored in localStorage.");
    window.location.href = "./login.html";
    </script>';
exit;
?>