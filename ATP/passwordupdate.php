<?php
session_start();

// If the session vars aren't set, try to set them with a cookie.
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'];
    }
}

require_once('header.php');
require_once('connectvars.php');
require_once('navbar.php');

// Insert the Jumbotron logic.
echo '<div id="content">';
echo '<div class="container" id="studentContainer">';
echo '<div class ="jumbotron" id="studentTable">';

// Create the form to handle resetting the user's password.
?>
    <h3 align="center">Please enter and confirm your new password below</h3>
    <hr>
    <form method="post" action="passwordupdate.php" id="frmForgotPasswordChange" class="form-horizontal">
        <div class="form-group">

            <br />
            <div class="form-group">
                <label class="control-label col-sm-2" for="password1">New Password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="password1" placeholder="Enter password"
                           name="password1" required>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-sm-2" for="password2">Re-Enter Password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="password2" placeholder="Re Enter password"
                           name="password2" required>
                </div>
            </div>
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" name="submit" class="btn btn-default">Update Password</button>
            </div>
        </div>
    </form>
<?php
if (isset($_POST['submit']))
{
    // If the passwords match change the password.
    if ($_POST['password1'] === $_POST['password2'])
    {
        $password = $_POST['password1'];
        $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        mysqli_query($dbc, "UPDATE userinfo SET password = sha1('" . $password . "') WHERE username = '" .
            $_SESSION['username'] . "'")
            or die("Error updating password.");
        mysqli_close($dbc);
        ?>
        <script type="text/javascript">
            setTimeout(function () { swal("Success!", "Your password has been successfully changed! Please log back in.", "success");}, 0);
            setTimeout(function loginRedir() {
                window.location.href = "http://www.mstc-atp.com/logout.php";
            }, 3000);
        </script>
        <?php
    }

    else
    {
        ?>
        <script type="text/javascript">
            setTimeout(function () {
                swal("Error", "Please make sure both passwords match.", "error");
            }, 0);

        </script>
        <?php
    }
}

require_once('footer.php');
?>