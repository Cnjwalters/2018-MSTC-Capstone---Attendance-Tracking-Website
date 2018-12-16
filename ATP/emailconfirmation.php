<?php
require_once('header.php');
require_once('connectvars.php');
require_once('navbar.php');

// Get the encrypted key from the URL and use it to find the correct user information.
$key = $_GET['key'];
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$userInfo = mysqli_query($dbc, "SELECT * FROM encuserconfirm WHERE encryption = '$key'");
$userInfoRow = mysqli_fetch_array($userInfo);
$userName = $userInfoRow['username'];

// Now, remove the record from the encrypted link table for security purposes.
mysqli_query($dbc, "DELETE FROM encuserconfirm WHERE encryption = '$key'")
    or die("Error removing encryption from encrypted link table.");

// Lastly, confirm the user so they may now login and close the database connection.
mysqli_query($dbc, "UPDATE userinfo SET confirmation = 1 WHERE username = '$userName'")
    or die("Error confirming account.");
mysqli_close($dbc);

// Insert HTML divs for bootstrap purposes.
echo'<div id="content">';
echo '<div class="container" id="studentContainer">';
echo '<div class ="jumbotron" id="studentTable">';

echo '<h2 align="center">Congratulations!</h2>';
echo '<hr>';
echo '<h3 align="center">Your account is now confirmed!</h3>';
echo '<h4 align="center">Login <a href="login.php" style="color: dodgerblue;">Here</a> or you will be redirected ' .
    'to the login screen shortly.';

echo '<script type="text/javascript">setTimeout(function loginRedir() {window.location.href = ' .
    '"http://www.mstc-atp.com/login.php";}, 3000);</script>';
echo '</div></div></div>';
?>

