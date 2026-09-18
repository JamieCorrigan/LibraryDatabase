<?php
session_start();

    // Check if logged in
    if (isset($_SESSION['username'])) {
        header("Location: index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>

    <!-- Login form -->
    <div class='FormBox'>
        <h2>Login</h2>
        <form action="process.php" method="POST">
            Username: <input type="text" name="Username" required><br><br>
            Password: <input type="password" name="Password" required><br><br>
            <input type="submit" value="Login">
        </form>

        <!-- Register redirect -->
        <h3>No account? Register <a href="register.php">here.<href>

    </div>
    </body>
</html>