</div>
<?php
require_once('connectvars.php');

if (isset($_SESSION['username'])) {
    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $currentuser = $_SESSION['user_id'];

    // Retrieve the course data from MySQL.
    $query = "SELECT * FROM courseinfo WHERE employeeID = '$currentuser' ORDER BY courseName ASC";
    $data = mysqli_query($dbc, $query);

    echo '
    <!-- Sidebar -->
        <div class="footer">
            <a href="index.php">Home |</a>
                <a href="accountSettings.php">Account Settings |</a>    
                <a href="classSettings.php">Class Settings |</a>
                <a href="reportClass.php">Report |</a>
                <a href="logout.php">Logout</a>
                </div>';

    mysqli_close($dbc);
}

// If a user is not logged in show this.
else{
    echo '
    <!-- Sidebar -->
        <div class="footer">
            <a href="index.php">Home |</a>
            <a href="signup.php">Signup |</a>
            <a href="login.php">Login</a>
        </div>';
}
?>
</body>
</html>

