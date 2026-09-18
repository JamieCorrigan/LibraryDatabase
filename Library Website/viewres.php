<!DOCTYPE html>
<html>
    <head>
        <titl>Reserved Books</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <header>
        <nav class="nav">
            <!-- Divs to make the nav bar even for title and right buttons -->
                <div class="nav-left"></div>
                <div class="nav-center">
                    <h1>Lab Library</h1>
                </div>
                <div class='nav-right'>
                    <a href="index.php" class="nav-button"><button><h2>Return</h2></button></a>
                    <a href="logout.php" class="nav-button"><button><h2>Log Out</h2></button></a>
                </div>
        </nav>
    </header>
    <body>
        <div class='FormBox'>
        <?php
            session_start();

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

            // Cancel reservation
            if (isset($_POST['cancel']) && isset($_POST['isbn'])) {
                $isbn = $_POST['isbn'];
                $username = $_SESSION['Username'];
                //Compare to db
                $deleteSql = "DELETE FROM reservations WHERE ISBN = ? AND Username = ?";
                $stmt = $conn->prepare($deleteSql);
                $stmt->bind_param("ss", $isbn, $username);

                //Return result
                if ($stmt->execute()) {
                    $msg = "Reservation canceled successfully.";
                } else {
                    $msg = "Error canceling reservation: " . $conn->error;
                }

                // Redirect
                header("Location: viewres.php?msg=" . urlencode($msg));
                exit();
            }

            // Display message if redirected from cancel
            if (isset($_GET['msg'])) {
                echo "<p style='color:blue;'>" . htmlspecialchars($_GET['msg']) . "</p>";
            }

            // User reservations
            $sql = "
                SELECT b.ISBN, b.BookTitle, b.Author, c.CategoryDescription, r.ReservedDate
                FROM reservations r
                JOIN books b ON r.ISBN = b.ISBN
                JOIN category c ON b.CategoryID = c.CategoryID
                WHERE r.Username = ?
                ORDER BY r.ReservedDate DESC
            ";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_SESSION['Username']);
            $stmt->execute();
            $result = $stmt->get_result();
        ?>
        <h2>My Reserved Books</h2>
        <p>Welcome</p>
        <br><br>
        <!-- Table to show current reserved books -->
        <?php if ($result->num_rows > 0): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Reservation Date</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlentities($row['BookTitle']); ?></td>
                    <td><?php echo htmlentities($row['Author']); ?></td>
                    <td><?php echo htmlentities($row['CategoryDescription']); ?></td>
                    <td><?php echo htmlentities($row['ReservedDate']); ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="isbn" value="<?php echo htmlentities($row['ISBN']); ?>">
                            <button type="submit" name="cancel" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            <!-- If no books currently reserved -->
        <?php else: ?>
            <p>You have not reserved any books yet.</p>
        <?php endif; ?>
        </div>

        <footer>
            <h3>Thank you for using our website</h3>
        </footer>

    </body>
</html>

<?php
$conn->close();
?>
