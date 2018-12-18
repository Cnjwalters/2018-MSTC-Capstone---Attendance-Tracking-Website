<head>
    <title>Attendance Tracker</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="StyleSheets/sideNavStyle.css">
    <link rel="stylesheet" href="StyleSheets/bodyStyle.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/malihu-custom-scrollbar-plugin/3.1.5/jquery.mCustomScrollbar.min.css">

    <!-- Sweet Alerts for Attendance tracking success/fail messages. -->
    <script src="package/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill"></script>

    <!-- Sweet Alerts CSS -->
    <link rel="stylesheet" href="package/dist/sweetalert2.min.css"
</head>
<body>

<?php
require_once('connectvars.php');
require_once('navbar.php');

// Connect to the database.
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)
or die("Unable to connect to database");

$disable = 'false';

if (isset($_POST['submit'])) {
    // Grab the profile data from the POST
    $username = mysqli_real_escape_string($dbc, trim($_POST['username']));
    $employeeId = mysqli_real_escape_string($dbc, trim($_POST['employeeId']));
    $firstName = mysqli_real_escape_string($dbc, trim($_POST['firstName']));
    $lastName = mysqli_real_escape_string($dbc, trim($_POST['lastName']));
    $email = mysqli_real_escape_string($dbc, trim($_POST['email']));
    $password1 = mysqli_real_escape_string($dbc, trim($_POST['password1']));
    $password2 = mysqli_real_escape_string($dbc, trim($_POST['password2']));

    $num_length = strlen((string)$employeeId);

    if ($num_length != 8) {
        echo '<div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                Employee ID must be 8 characters and numeric only.
                </div></div> \';';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                Email is invalid.
                </div></div> \';';
    }

    if ($password1 != $password2)
    {
        ?>
        <!-- Show password match error. -->
        <script type="text/javascript">
            setTimeout(function () {swal("Hold On!", "Passwords do not match! Please re-enter" +
                " passwords.", "error");}, 0);
        </script>
    <?php
    }

    // Check to make sure form is completely filled out.
    if (!empty($username) && !empty($employeeId) && $num_length == 8 && !empty($firstName) && !empty($lastName) &&
        !empty($email) && !empty($password1) && !empty($password2) && ($password1 == $password2))
    {
        // First, check that the username, employeeId, and email are unique.
        $data = mysqli_query($dbc, "SELECT * FROM userinfo WHERE username = '$username'");
        if (mysqli_num_rows($data) == 0)
        {
            // The username is unique, now let's check employee ID.
            $data = mysqli_query($dbc, "SELECT * FROM userinfo WHERE employeeId = '$employeeId'");
            if (mysqli_num_rows($data) == 0)
            {
                // The username and employeeId are unique, now lastly check the user's email.
                $data = mysqli_query($dbc, "SELECT * FROM userinfo WHERE email = '$email'");
                if (mysqli_num_rows($data) == 0)
                {
    $disable = 'true';
                    // Everything's good and unique - allow the account creation to happen!
                    $query = "INSERT INTO userinfo(username, employeeId, firstName, lastName, email, password, confirmation) " .
                    "VALUES ('$username','$employeeId','$firstName','$lastName','$email', sha1('$password1'), 0)";
                    mysqli_query($dbc, $query);

                    // Create an encrypted string to pass into the email and database for security.
                    $encNumRand = sha1(rand(1111111111,9999999999));
                    $encKey = sha1("nkfop8a!2D28snl3kkK" . $encNumRand . "h2@kl;3lasd");

                    // Verify there aren't multiple keys for confirming this user within the database.
                    $encData = mysqli_query($dbc, "SELECT * FROM encuserconfirm WHERE username = '$username'");
                    if (mysqli_num_rows($encData) == 0)
                    {
                        // There is no record for this encrypted key so insert one.
                        mysqli_query($dbc, "INSERT INTO encuserconfirm (username, encryption) " .
                            "VALUES ('$username','$encKey')");
                    }

                    // There is a record for this encrypted key. Remove it and insert the new one.
                    else
                    {
                        mysqli_query($dbc, "UPDATE encuserconfirm SET encryption = '$encKey' WHERE username = " .
                            "'$username'");
                    }

                    // Create and send the confirmation Email
                    $msg = "Congratulations! Your account with mstc-atp.com has been successfully created!\n" .
                        "Please click the link below to confirm your account!\n" .
                        "Please Note: If the link is not clickable, please copy and paste it into your internet browser.\n" .
                        "\nwww.mstc-atp.com/emailconfirmation.php?key=" . $encKey;
                    //mail($email, "Account Confirmation", $msg);

                    // Confirm success with the user.
                    ?>
                    <script type="text/javascript">
                        setTimeout(function () { swal("Almost Done!", "We will send an email to \''<?php echo $email;?>'\' soon!" +
                            " Open it up and follow the instructions to activate your account.\n \n" +
                            " Note: Please make sure to check your spam " +
                            "folder if you don't see the email in your inbox.", "success");}, 0);
                        setTimeout(function loginRedir() {
                            window.location.href = "http://www.mstc-atp.com/login.php";
                        }, 7502);
                    </script>
                    <?php
                }

                else
                {
                    // An account already exists for this email, so display an error message.
                    ?>
                    <script type="text/javascript">
                        setTimeout(function () {swal("Hold On!", "An account already exists with this email! Please" +
                        " use a different email.", "error");}, 0);
                    </script>
                    <?php
                }
            }

            else
            {
                // An account already exists for this employeeId, so display an error message.
                ?>
                <script type="text/javascript">
                    setTimeout(function () {swal("Hold On!", "An account already exists with this Employee ID! Please" +
                    " use a different Employee ID.", "error");}, 0);
                </script>
                <?php
           }
        }

        else
        {
            // An account already exists for this username, so display an error message.
            ?>
            <script type="text/javascript">
                setTimeout(function () { swal("Hold On!", "An account already exists with this Username! Please" +
                    " use a different Username.", "error");}, 0);
            </script>
            <?php
            $username = "";
        }
    }
}

mysqli_close($dbc);
?>

<!-- Create the sign up form. -->
<div id="content">
    <div class="container">
        <div class="jumbotron">
            <h3 align="center">Sign Up</h3>
            <form method="post" action="signup.php" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-2" for="username">Username:</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="username" placeholder="Username" name="username"
                               value="<?php if (!empty($username)) echo $username; ?>" required>
                    </div>
                </div>
            </div>
            <!-- Employee ID -->
            <div class="form-group">
                <label class="control-label col-sm-2" for="employeeId">Employee ID:</label><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-placement="top" title="Employee ID must be 8 numeric characters."></span>
                <div class="col-sm-10">
                    <input type="number" maxlength="8" class="form-control" id="employeeId" placeholder="Employee ID"
                           name="employeeId" value="<?php if (!empty($employeeId)) echo $employeeId; ?>" required>
                </div>
            </div>
            <!-- First name -->
            <div class="form-group">
                <label class="control-label col-sm-2" for="firstName">First Name:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="firstName" placeholder="First Name" name="firstName"
                           value="<?php if (!empty($firstName)) echo $firstName; ?>" required>
                </div>
            </div>
            <!-- last name -->
            <div class="form-group">
                <label class="control-label col-sm-2" for="lastName">Last Name:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="lastName" placeholder="Last Name" name="lastName"
                           value="<?php if (!empty($lastName)) echo $lastName; ?>" required>
                </div>
            </div>
            <!-- Email address -->
            <div class="form-group">
                <label class="control-label col-sm-2" for="email">Email Address:</label><span class="glyphicon glyphicon-question-sign" data-toggle="tooltip" data-placement="top" title="Offical MSTC instructor email only."></span>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="email" placeholder="john.doe@mstc.edu" name="email"
                           value="<?php if (!empty($email)) echo $email; ?>" required>
                </div>
            </div>
            <!-- Password one -->
            <div class="form-group">
                <label class="control-label col-sm-2" for="password1">Password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="password1" placeholder="Enter password"
                           name="password1" required>
                </div>
            </div>
            <!-- Password two -->
            <div class="form-group">
                <label class="control-label col-sm-2" for="password2">Re-Enter Password:</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="password2" placeholder="Re Enter password"
                           name="password2" required>
                </div>
            </div>
            <!-- Submit button -->
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" name="submit" class="btn btn-default" <?php if ($disable =='true'){echo 'disabled';}?>>Create Account</button>
                    <br /><br />
                </div>
            </form>
            <a href="login.php" style="color: dodgerblue;">Already have an account? Click here to login.</a>
        </div>
    </div>
</div>
<!-- Script to show tool tip. -->
<script>
    $(document).ready(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<?php
require 'footer.php';
?>
