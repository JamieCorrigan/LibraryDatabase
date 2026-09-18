<html>
    <head>
    <link rel="stylesheet" href="style.css">
    </head>
    <body>

        <?php
            $message = ""; // Success or error message
            $messageColor = "blue"; // Colour success

            //Connect to db
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "librarydb";

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                //Get inputs from user
                $Username = $_POST['Username'];
                $Password  = $_POST['Password'];
                $Password2 = $_POST['Password2'];
                $FirstName = $_POST['FirstName'];
                $Surname = $_POST['Surname'];
                $AddressLine1 = $_POST['AddressLine1'];
                $AddressLine2 = $_POST['AddressLine2'];
                $City = $_POST['City'];
                $Telephone = $_POST['Telephone'];
                $Mobile = $_POST['Mobile'];

                //Compare to current usernames
                $sql = "SELECT * FROM users WHERE Username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $Username);
                $stmt->execute();
                $result = $stmt->get_result();

                //Check different parameters
                if ($result->num_rows > 0){
                    $message = "Username already exists.";
                    $messageColor = "red";
                } 
                elseif (!is_numeric($Mobile) || strlen($Mobile) != 10) {
                    $message = "Error: Phone number must be a 10 digit number.";
                    $messageColor = "red";
                } 
                elseif (strlen($Password) != 6) {
                    $message = "Error: Password must be 6 digits.";
                    $messageColor = "red";
                } 
                elseif ($Password != $Password2) {
                    $message = "Error: Password Confirmation failure.";
                    $messageColor = "red";
                } 
                else {
                    //Insert into database
                    $sql = "INSERT INTO users (Username, Password, FirstName, Surname, AddressLine1, AddressLine2, City, Telephone, Mobile)
                            VALUES ('$Username', '$Password', '$FirstName', '$Surname', '$AddressLine1', '$AddressLine2', '$City', '$Telephone', '$Mobile')";
                    if ($conn->query($sql) === TRUE) {
                        $message = "User added successfully.";
                        $messageColor = "blue";
                    } 
                    else {
                        $message = "Error: " . $conn->error;
                        $messageColor = "red";
                    }
                }
            }

            $conn->close();
        ?>

        <div class='Formbox'>
            <?php
            // Form for user to fill out
            if (!empty($message)) {
                echo "<p style='color:$messageColor; font-weight:bold;'>$message</p>";
            }
            ?>
            <h2>Register new details:</h2>

            <form method="post" action="">
                <label for="Username">Username</label>
                <input type="varchar" id="Username" name="Username" placeholder="Enter unique username" required>
                <br><br>

                <label for="Password">Password:</label>
                <input type="varchar" id="Password" name="Password" placeholder="Must be 6 characters" required>
                <br><br>

                <label for="Password2">Password Check:</label>
                <input type="varchar" id="Password2" name="Password2" required>
                <br><br>

                <label for="FirstName">Firstname:</label>
                <input type="varchar" id="FirstName" name="FirstName" required>
                <br><br>

                <label for="Surname">Surname</label>
                <input type="varchar" id="Surname" name="Surname" required>
                <br><br>

                <label for="AddressLine1">Address Line 1</label>
                <input type="varchar" id="AddressLine1" name="AddressLine1" required>
                <br><br>

                <label for="AddressLine2">Address Line 2</label>
                <input type="varchar" id="AddressLine2" name="AddressLine2" required>
                <br><br>

                <label for="City">City</label>
                <input type="varchar" id="City" name="City" required>
                <br><br>

                <label for="Telephone">Telephone</label>
                <input type="number" id="Telephone" name="Telephone" required>
                <br><br>

                <label for="Mobile">Mobile</label>
                <input type="number" id="Mobile" name="Mobile" placeholder="Must be 10 digits" required>
                <br><br>

                <input type="submit" value="Submit">

                <a href="login.php">
                    Back to Login
                </a>
            </form>
        </div>

    </body>
</html>
