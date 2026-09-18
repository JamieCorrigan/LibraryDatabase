<?php
    session_start();

    //User check
    if (!isset($_SESSION['Username'])) {
        header("Location: login.php");
        exit();
    }

    $host = "localhost";
    $dbname = "librarydb";
    $user = "root";
    $pass = "";

    // Connect to database
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $isbn = $_POST['isbn'];
    $username = $_SESSION['Username'];

    // Check if already reserved
    $checkSql = "SELECT * FROM reservations WHERE Username = ? AND ISBN = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $username, $isbn);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Already reserved
        header("Location: index.php?msg=You have already reserved this book.");
        exit();
    }

    // Insert reservation
    $insertSql = "INSERT INTO reservations (Username, ISBN, ReservedDate) VALUES (?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ss", $username, $isbn);

    //Return result
    if ($insertStmt->execute()) {
        header("Location: index.php?msg=Book reserved successfully.");
    } else {
        header("Location: index.php?msg=Error reserving the book.");
    }

    $checkStmt->close();
    $insertStmt->close();
    $conn->close();
?>
