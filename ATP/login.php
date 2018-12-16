<?php
require_once('connectvars.php');
require_once ('navbar.php');

// Start the session
session_start();
require_once ('header.php');

// Clear the error message
$error_msg = "";

// If the user isn't logged in, try to log them in.
if (!isset($_SESSION['user_id'])) {
    if (isset($_POST['submit'])) {
        // Connect to the database
        $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        // Grab the user-entered log-in data.
        $user_username = mysqli_real_escape_string($dbc, trim($_POST['username']));
        $user_password = mysqli_real_escape_string($dbc, trim($_POST['password']));

        if (!empty($user_username) && !empty($user_password)) {
            // Look up the username and password in the database.
            $query = "SELECT employeeId, username, confirmation FROM userinfo WHERE username = '$user_username' AND " .
                "password = sha1('$user_password')";
            $data = mysqli_query($dbc, $query);

            if (mysqli_num_rows($data) == 1)
            {
                // The user's record has been found. Now, verify that their account has been confirmed.
                $row = mysqli_fetch_array($data);
                if ($row['confirmation'] != 1)
                {
                    echo '<script type="text/javascript">';
                    echo 'setTimeout(function () { swal("Sorry,", "You must confirm your account before logging in.", "error");';
                    echo '}, 0);</script>';
                }

                else
                {
                    // The log-in is OK so set the user ID and username session vars (and cookies), and redirect to the home page.
                    $_SESSION['user_id'] = $row['employeeId'];
                    $_SESSION['username'] = $row['username'];

                    // Expires in 30 days.
                    setcookie('user_id', $row['employeeId'], time() + (60 * 60 * 24 * 30));
                    setcookie('username', $row['username'], time() + (60 * 60 * 24 * 30));

                    // Redirect the now logged-in user to the index page.
                    $home_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php';
                    header('Location: ' . $home_url);
                }
            }

            else
            {

                echo '<script type="text/javascript">';
                echo 'setTimeout(function () { swal("Uh oh", "You must enter a valid username and password to log in.", "error");';
                echo '}, 0);</script>';

            }
        }
        else {
            // The username/password are incorrect so set an error message.
            echo '<script type="text/javascript">';
            echo 'setTimeout(function () { swal("Uh oh", "You must enter a valid username and password to log in.", "error");';
            echo '}, 0);</script>';
        }
    }
}

// If the session var is empty, show any error message and the log-in form; otherwise confirm the log-in.
if (empty($_SESSION['user_id'])) {
    echo '<p class="error">' . $error_msg . '</p>';
    ?>
    <div id="content">
    <div class="container">
        <div class="jumbotron">
            <h3 align="center">Log In</h3>
            <hr>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-2" for="username">Username:</label>
                    <div class="col-sm-10">
                        <input maxlength="20" type="text" class="form-control" id="username" placeholder="Username" name="username" value="<?php if (!empty($user_username)) echo $user_username; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="password">Password:</label>
                    <div class="col-sm-10">
                        <input maxlength="40" type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="submit" name="submit" class="btn btn-default">Log In</button>

                        <button onclick="location.href='signup.php'" class="btn btn-default">Sign Up</a></button>
                        <br /><br />
                        <a href="forgottenpassword.php" style="color: dodgerblue;">Forgot your password?</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
    <?php
}
else {
    // Confirm the successful log-in.
    echo('<p class="login">You are logged in as ' . $_SESSION['username'] . '.</p>');
}

require_once ('footer.php');
?>