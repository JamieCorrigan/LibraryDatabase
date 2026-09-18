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

    // Connect to db
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Result from reserve.php
    $reservationMessage = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : "";

    // Pagination variables
    $limit = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Library</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <header>
            <nav class="nav">
                <!-- Divs to make the nav bar even for title and right buttons -->
                    <div class="nav-left"></div>
                    <div class="nav-center">
                        <h1>Lab Library</h1>
                    </div>
                    <div class='nav-right'>
                    <a href="viewres.php" class="nav-button"><button><h2>Reserved</h2></button></a>
                    <a href="logout.php" class="nav-button"><button><h2>Log Out</h2></button></a>
                    </div>
            </nav>
        </header>

        <!-- Main box -->
        <div class ='FormBox'>
            <h2>Welcome</h2>
            <p>Please enter information</p>

            <!-- Show reserve.php message -->
            <?php
                if ($reservationMessage !== "") {
                    echo "<p style='color:blue; font-weight:bold;'>$reservationMessage</p>";
                }
            ?>

            <!-- Search Form -->
            <form method="GET">
                <input type="text" name="search" placeholder="Search for book" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit">Search</button>
            </form>
            <br>

            <!-- Category Dropdown -->
            <form method="GET">
                <label for="category">Select Category:</label>
                <select name="category" id="category">
                    <option value="">-- Choose a Category --</option>
                    <option value="all">-- View All Books --</option>
                    <?php
                    // Show category as the name rather than the number in books table
                    $catQuery = "SELECT CategoryID, CategoryDescription FROM category ORDER BY CategoryID";
                    $catResult = $conn->query($catQuery);
                    while ($row = $catResult->fetch_assoc()) {
                        $selected = (isset($_GET['category']) && $_GET['category'] == $row['CategoryID']) ? "selected" : "";
                        echo "<option value='" . $row['CategoryID'] . "' $selected>" . htmlentities($row['CategoryDescription']) . "</option>";
                    }
                    ?>
                </select>
                <button type="submit">Search</button>
            </form>

            <?php
                // Which query
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                $category = isset($_GET['category']) ? $_GET['category'] : '';

                $params = [];
                $sqlWhere = "";
                $sqlCountWhere = "";

                // Build search conditions
                if ($search !== '') {
                    $sqlWhere = "WHERE BookTitle LIKE ? OR Author LIKE ?";
                    $sqlCountWhere = $sqlWhere;
                    $params[] = $search . "%";
                    $params[] = $search . "%";
                } elseif ($category !== '' && $category !== 'all') {
                    $sqlWhere = "WHERE b.CategoryID = ?";
                    $sqlCountWhere = $sqlWhere;
                    $params[] = $category;
                }

                // Count total books
                if ($category === 'all' || ($search === '' && $category === '')) {
                    $countSql = "SELECT COUNT(*) AS total FROM books";
                    $stmt = $conn->prepare($countSql);
                } else {
                    $countSql = "SELECT COUNT(*) AS total FROM books b $sqlCountWhere";
                    $stmt = $conn->prepare($countSql);
                    if (!empty($params)) {
                        if ($search !== '') {
                            $stmt->bind_param("ss", $params[0], $params[1]);
                        } else {
                            $stmt->bind_param("s", $params[0]);
                        }
                    }
                }
                $stmt->execute();
                $totalRows = $stmt->get_result()->fetch_assoc()['total'];
                $totalPages = ceil($totalRows / $limit);
                $stmt->close();

                // Get books
                $sql = "SELECT b.ISBN, b.BookTitle, b.Author, b.Reserved, c.CategoryDescription
                        FROM books b
                        LEFT JOIN category c ON b.CategoryID = c.CategoryID ";
                if ($sqlWhere) $sql .= $sqlWhere . " ";
                $sql .= "LIMIT ?, ?";
                $stmt = $conn->prepare($sql);

                if (!empty($params)) {
                    if ($search !== '') {
                        $stmt->bind_param("ssii", $params[0], $params[1], $offset, $limit);
                    } else {
                        $stmt->bind_param("sii", $params[0], $offset, $limit);
                    }
                } else {
                    $stmt->bind_param("ii", $offset, $limit);
                }

                $stmt->execute();
                $result = $stmt->get_result();

                //Books table display
                if ($result->num_rows > 0) {
                    echo "<h3>Books List</h3>";
                    echo "<table border='1'>";
                    echo "<tr><th>Title</th><th>Author</th><th>Reserve</th></tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlentities($row['BookTitle']) . "</td>";
                        echo "<td>" . htmlentities($row['Author']) . "</td>";
                        echo "<td>";
                        if ($row['Reserved'] === 'Y') {
                            echo "<button disabled>Reserved</button>";
                        } else {
                            echo "<form method='POST' action='reserve.php'>
                                    <input type='hidden' name='isbn' value='" . htmlentities($row['ISBN']) . "'>
                                    <button type='submit'>Reserve</button>
                                </form>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "No books found.";
                }

                // Pagination links
                echo "<center>";
                for ($i = 1; $i <= $totalPages; $i++) {
                    $link = "?";
                    if ($search !== '') $link .= "search=" . urlencode($search) . "&";
                    if ($category !== '') $link .= "category=" . urlencode($category) . "&";
                    $link .= "page=$i";

                    if ($i == $page) {
                        echo "<span><b>$i</b></span> ";
                    } else {
                        echo "<a href='$link'>$i</a> ";
                    }
                }
                echo "</center>";

                $stmt->close();
                $conn->close();
            ?>

        </div>
        <footer>
            <h3>Thank you for using our website</h3>
        </footer>

    </body>
</html>
