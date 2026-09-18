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

    // Atomically claim the book: only succeeds if it's currently unreserved
    $updateSql = "UPDATE books SET Reserved = 'Y' WHERE ISBN = ? AND Reserved = 'N'";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("s", $isbn);
    $updateStmt->execute();

    if ($updateStmt->affected_rows === 0) {
        // Either the book doesn't exist, or someone already reserved it first
        header("Location: index.php?msg=" . urlencode("Sorry, this book is already reserved."));
        exit();
    }

    // We successfully claimed it — now record who reserved it
    $insertSql = "INSERT INTO reservations (username, ISBN, ReservedDate) VALUES (?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ss", $username, $isbn);

    if ($insertStmt->execute()) {
        header("Location: index.php?msg=" . urlencode("Book reserved successfully."));
    } else {
        // Roll back the flag if the insert somehow fails
        $rollback = $conn->prepare("UPDATE books SET Reserved = 'N' WHERE ISBN = ?");
        $rollback->bind_param("s", $isbn);
        $rollback->execute();
        $rollback->close();
        header("Location: index.php?msg=" . urlencode("Error reserving the book."));
    }

    $updateStmt->close();
    $insertStmt->close();
    $conn->close();
?>