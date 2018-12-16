<?php
require_once('header.php');
require_once('connectvars.php');
require_once('navbar.php');

// Insert HTML divs for bootstrap purposes.
echo '<div id="content">';
echo '<div class="container" id="studentContainer">';
echo '<div class ="jumbotron" id="studentTable">';

// Create the form to handle recovering the user's password.
?>
<form method="post" action="forgottenpassword.php" id="frmForgotPassword" class="form-horizontal">
    <div class="form-group">
        <h2 align="center">Recover your Account</h2>
        <hr>
        <h4 align="center">Please enter your account's email below.</h4>
        <br />
        <label class="control-label col-sm-2" for="username">Email:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="email" name="email"
                   value="<?php if (!empty($email)) echo $email; ?>" required>
        </div>
        <br />
        <br />
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" name="submit" class="btn btn-default">Recover Password</button>
        </div>
    </div>
</form>

<?php
echo '</div>';
if (isset($_POST['submit']))
{
    // Pull the entered email from the form.
    $email = $_POST['email'];

    // Check to see if the email exists. If it does, email the user. If it doesn't, pretend to send email.
    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $emailResult = mysqli_query($dbc, "SELECT * FROM userinfo WHERE email = '$email'");
    $row = mysqli_fetch_array($emailResult);
    $username = $row['username'];

    if ($row['email'] == $email)
    {
        // Emails match! Send off the email for the user to change their password!
        // First, create an encrypted string to pass into the email and database for security.
        $encNumRand = sha1(rand(1111111111,9999999999));
        $encKey = sha1("Jjn24Jk$2i2092YnUKL" . $encNumRand . "kliJ2nHnn2hg#g@");

        // Verify there aren't multiple keys for this forgotten password request.
        $encData = mysqli_query($dbc, "SELECT * FROM encforgottenpassword WHERE username = '$username'");
        if (mysqli_num_rows($encData) == 0)
        {
            // There is no record for this encypted key so insert one.
            mysqli_query($dbc, "INSERT INTO encforgottenpassword (username, encryption) " .
                "VALUES ('$username', '$encKey')");
        }

        // There is a record for this encrypted key so update it.
        else
        {
            mysqli_query($dbc, "UPDATE encforgottenpassword SET encryption = '$encKey' WHERE username = " .
                "'$username'");
        }

        $msg = "Reset your password?\n \n" .
            "If you requested a password reset for " . $username . ", click the link below. If you " .
            "didn't make this request, ignore this email. \n \n" .
            "www.mstc-atp.com/passwordchange.php?key=" . $encKey;

        mail($row['email'], "Password Reset Request", $msg);
        ?>
        <script type="text/javascript">
        setTimeout(function () { swal("Email Sent!", "If your email matches our records, you will receive an email shortly!", "success");}, 0);
        setTimeout(function loginRedir() {
            window.location.href = "http://www.mstc-atp.com/login.php";
        }, 3550);
        </script>
        <?php
    }

    // Entered email does not match what's on file - pretend to send email.
    else
    {
        ?>
        <script type="text/javascript">
            setTimeout(function () { swal("Email Sent!", "If your email matches our records, you will receive an email shortly!", "success");}, 0);
            setTimeout(function loginRedir() {
                window.location.href = "http://www.mstc-atp.com/login.php";
            }, 1550);
        </script>
        <?php
    }

    mysqli_close($dbc);
}
echo '</div></div>';
require_once('footer.php');
?>