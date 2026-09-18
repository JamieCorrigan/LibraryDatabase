<?php
    session_start();

    $host = "localhost";
    $dbname = "librarydb";
    $user = "root";
    $pass = "";

    //Connect to db
    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //Details from login.php
    $username = $_POST['Username'];
    $password = $_POST['Password'];

    //Check against db
    $sql = "SELECT * FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if ($password === $row['Password']) {

            //Store info for index.php
            $_SESSION['Username'] = $row['Username'];
            $_SESSION['Password'] = $row['Password'];

            header("Location: index.php");
            exit();

        } 
        //Wrong password entered
        else {
            echo "Invalid password";
        }

    } 
    //Wrong username entered
    else {
        echo "User not found";
    }

    $conn->close();
?>
