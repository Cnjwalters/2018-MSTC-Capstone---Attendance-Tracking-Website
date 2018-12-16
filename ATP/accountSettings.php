<?php
// Start the session.
session_start();

// If the session vars aren't set, try to set them with a cookie.
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'];
    }
}

require_once'header.php';
require_once('connectvars.php');
require_once('navbar.php');

// Pull the account information and display it for the user.
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$accountInfo = mysqli_query($dbc, "SELECT * FROM userinfo WHERE username = '" . $_SESSION['username'] . "'");
$accountInfoArr = mysqli_fetch_array($accountInfo);
?>
<!-- Create the account settings form. -->
<div id="content">
    <div class="container">
        <div class="jumbotron">
            <h2 align="center">
                Account Settings
            </h2>
            <hr>
            <form method="post" action="accountSettings.php" id="frmAccountSettings" class="form-horizontal">
                    <!-- User Name -->
                    <h3>User Name:</h3>
                    <div class="col-sm-10">
                        <label type="text" id="username">
                            <?php echo $accountInfoArr['username'];?>
                        </label>
                    </div>
                    <br /><br />

                    <!-- Employee ID -->
                    <h3>Employee ID:</h3>
                    <div class="col-sm-10">
                        <label type="number" id="employeeId">
                            <?php echo $accountInfoArr['employeeId'];?>
                        </label>
                    </div>
                    <br /><br />

                    <!-- First Name -->
                    <h3>First Name:</h3>
                    <div class="col-sm-10">
                        <label type="text" id="firstName">
                            <?php echo $accountInfoArr['firstName'];?>
                        </label>
                    </div>
                    <br /><br />

                    <!-- Last Name -->
                    <h3>Last Name:</h3>
                    <div class="col-sm-10">
                        <input type="text" maxlength="50" class="form-control" id="lastName"
                               value="<?php echo $accountInfoArr['lastName'];?>" name="lastName">
                        <button type="lastnamesubmit" name="lastnamesubmit" class="btn btn-default">Change Last Name</button>
                    </div>
                    <br /><br /><br /><br />

                    <!-- Email -->
                    <h3>Email:</h3>
                    <div class="col-sm-10">
                        <label type="text" id="email">
                            <?php echo $accountInfoArr['email'];?>
                        </label>
                    </div>

                    <br /><br />
            </form>
            <hr>
            <h3 align="center">
                <a href="passwordupdate.php"style="color: dodgerblue;">Changing your Password?</a>
            </h3>
        </div>
    </div>
</div>

<?php
// Check if the name change button was pressed.
if(isset($_POST['lastnamesubmit']))
{
    $lastName = $_POST['lastName'];
    // Validate the last name.
    if (preg_match("/^[\p{L} '-]+$/", $lastName))
    {
        mysqli_query($dbc, "UPDATE userinfo SET lastName = '$lastName' WHERE username = '" . $accountInfoArr['username'] . "'");
        ?>
        <script type="text/javascript">
            setTimeout(function () { swal("Success!", "Your last name has been successfully changed!", "success");}, 0);
            setTimeout(function loginRedir() {
                window.location.href = "http://www.mstc-atp.com/accountSettings.php";
            }, 1500);
        </script>
        <?php
    }

    else
    {
        ?>
        <!-- Show name change error. -->
        <script type="text/javascript">
        setTimeout(function () { swal("Uh oh..!", "Your last name contains an invalid character. Please use letters only.", "error");}, 0);
        </script>
        <?php
    }


}
require_once 'footer.php';
?>