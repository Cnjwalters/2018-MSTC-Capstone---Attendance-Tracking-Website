<?php

session_start();

// Expire the session if user is inactive for 30 minutes or more.
$expireAfter = 45;

// Check to see if our "last action" session variable has been set.
if(isset($_SESSION['last_action'])){

    // Figure out how many seconds have passed since the user was last active.
    $secondsInactive = time() - $_SESSION['last_action'];

    // Convert our minutes into seconds.
    $expireAfterSeconds = $expireAfter * 60;
    // Check to see if they have been inactive for too long.
    if($secondsInactive >= $expireAfterSeconds){
        //User has been inactive for too long.
        //Redirect to logout.
        header('Location: logout.php');
    }
}

// Assign the current timestamp as the user's latest activity.
$_SESSION['last_action'] = time();

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

if (isset ($_SESSION['user_id']))
{
    // Get the user's first and last name to display in the welcome banner.
    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $name = mysqli_query($dbc, "SELECT firstName, lastName FROM userinfo WHERE employeeId = " .
        $_SESSION['user_id']);
    $nameArr = mysqli_fetch_array($name);
    ?>
    <div id ="content">
<div class="container" id="studentContainer">
    <div align="center" class="jumbotron" id="studentTable">
        <h2>
            Welcome, <?php echo ucwords($nameArr['firstName']) . " " . ucwords($nameArr['lastName'])?>
        </h2><hr>

        <br />
        <br />

        <?php
        // The next banner is conditional. Find out if the user has any classes registered to determine which message should be displayed.
        $class = mysqli_query($dbc, "SELECT * FROM courseinfo WHERE employeeID = " . $_SESSION['user_id']);
        $classQuery = "SELECT * FROM courseinfo WHERE employeeID = " . $_SESSION['user_id'] . " ORDER BY courseName ASC";
        $classData = mysqli_query($dbc, $classQuery);

        if (mysqli_num_rows($classData) > 0)
        {
            // The user has classes registered. Let's link to attendance tracking!
            echo '<h4>Select a Class Below to Begin Taking Attendance</h4>';
            echo '<br /><br />';
            echo '<ul class="List-group">';
            while($classRow = mysqli_fetch_array($classData))
            {
                echo '<a href="takeattendance.php?courseid=' . $classRow['courseID'] .
                    '"><li class="list-group-item" id="userClassList"><p>' . $classRow['courseName'] . '</p></a></li>';
                echo '<br />';
            }
        }

        else
        {
            // The user does not have any classes registered. Let's get them registered!
            echo '<h3>It appears that you do not have any classes registered!</h3>';
            echo '<br /><br />';
            echo '<h4>Let\'s register some classes! Click <a href="classSettings.php" style="color: dodgerblue;">here</a> to begin registering!</h4>';
        }


        ?>

    </div>
</div>
<?php
}

if (!isset($_SESSION['user_id'])) {
    echo '
    <div id="content">
    <div class="container">
   <div align="center" class ="jumbotron">
   <h1>Welcome!</h1>
   <h2>Please <a href="login.php" class="userLoginLinks" style="color: dodgerblue;">Login</a> or <a href="signup.php" class="userLoginLinks" style="color: dodgerblue;">Sign Up</a></h2>
    </div>
    </div>
    </div>';
}

require_once 'footer.php';
?>