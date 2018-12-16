<?php
require_once('header.php');
require_once('connectvars.php');
require_once('navbar.php');

// Get the username from the URL.
$key = $_GET['key'];

// Check to ensure the URL the user was lead to is not expired.
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$userInfo = mysqli_query($dbc, "SELECT * FROM encforgottenpassword WHERE encryption = '$key'")
    or die("Error getting user info using key.");

if (mysqli_num_rows($userInfo) == 0)
{
    ?>
    <script type="text/javascript">
        setTimeout(function () { swal("Uh oh..", "It looks like this link has expired.", "error");}, 0);
        setTimeout(function loginRedir() {
            window.location.href = "http://www.mstc-atp.com/login.php";
        }, 3550);
    </script>
    <?php
}

else
{
    // Insert the jumbotron logic.
    echo '<div id="content">';
    echo '<div class="container" id="studentContainer">';
    echo '<div class ="jumbotron" id="studentTable">';

// Create the form to handle resetting the user's password.
    ?>
    <h3 align="center">Please enter and confirm your new password below</h3>
    <hr>
    <form method="post" action="passwordchange.php?key=<?php echo $key; ?>" id="frmForgotPasswordChange" class="form-horizontal">
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
        if ($_POST['password1'] === $_POST['password2'])
        {
            $password = $_POST['password1'];


            // Get the correct user from the encforgottenpassword table.
            $userInfoRow = mysqli_fetch_array($userInfo);
            $userName = $userInfoRow['username'];

            // Now, remove the record from the encforgottenpassword for security purposes.
            mysqli_query($dbc, "DELETE FROM encforgottenpassword WHERE encryption = '$key'")
            or die("Error removing encryption from encrypted link table.");

            mysqli_query($dbc, "UPDATE userinfo SET password = sha1('" . $password . "') WHERE username = '" .
                $userName . "'");
            mysqli_close($dbc);
            ?>
            <!-- Show success message for password change. -->
            <script type="text/javascript">
                setTimeout(function () { swal("Success!", "Your password has been successfully changed!", "success");}, 0);
                setTimeout(function loginRedir() {
                    window.location.href = "http://www.mstc-atp.com/login.php";
                }, 3000);
            </script>
            <?php
        }

        else
        {
            ?>
            <!-- Show error message when failed password change. -->
            <script type="text/javascript">
                setTimeout(function () {
                    swal("Error", "Please make sure both passwords match.", "error");
                }, 0);

            </script>
            <?php
        }
    }
}

require_once('footer.php');
?>