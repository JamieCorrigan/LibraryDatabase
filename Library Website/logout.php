<?php
    session_start();
    session_unset();     // Remove variables
    session_destroy();   // Destroy session

    //Redirect to login
    header("Location: login.php");
    exit();
?>