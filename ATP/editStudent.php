<?php

session_start();

// Expire the session if user is inactive for 30 minutes or more.
$expireAfter = 45;

// Check to see if our "last action" session variable has been set.
if (isset($_SESSION['last_action'])) {

    // Figure out how many seconds have passed since the user was last active.
    $secondsInactive = time() - $_SESSION['last_action'];

    // Convert our minutes into seconds.
    $expireAfterSeconds = $expireAfter * 60;

    // Check to see if they have been inactive for too long.
    if ($secondsInactive >= $expireAfterSeconds) {
        //User has been inactive for too long.
        //Kill their session.
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

require_once('header.php');
require_once('connectvars.php');
require_once('navbar.php');
$currentuser = $_SESSION['user_id'];
$studentID = $_GET['studentId'];
$courseID = $_GET['courseId'];

// Make initial connection to database.
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$instructorQuery = "SELECT username FROM userinfo WHERE employeeId =" . $currentuser;
$instructorData = mysqli_query($dbc, $instructorQuery)
or die('Could not query');

$row = mysqli_fetch_array($instructorData);
$atpTABLE = 'atp' . $row['username'] . $courseID;

$studentQuery = "SELECT StudentID, FirstName, LastName FROM " . $atpTABLE . " WHERE studentID = " . $studentID;
$studentData = mysqli_query($dbc, $studentQuery)
or die('Could not query student');

$studentRow = mysqli_fetch_array($studentData);
$firstName = $studentRow['FirstName'];
$lastName = $studentRow['LastName'];
$review_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/manageClass.php?courseid=';

if (isset($_POST['submit'])) {
    $firstName = $_POST['studentFirstName'];
    $lastName = $_POST['studentLastName'];
    $flag = false;


    if (!preg_match('/^[-a-zA-Z \']+$/', $lastName)) {
        ?>
        <script type="text/javascript">
            setTimeout(function () {
                swal("Hold On!", "The last name must only contain letters, spaces, hyphens and apostrophes! Please re-enter", "error");
            }, 0);
        </script>
        <?php
        $flag = true;
    }

    if (!preg_match('/^[-a-zA-Z \']+$/', $firstName)) {

        ?>
        <script type="text/javascript">
            setTimeout(function () {
                swal("Hold On!", "The first name must only contain letters, spaces, hyphens and apostrophes! Please re-enter", "error");
            }, 0);
        </script>
        <?php
        $flag = true;
    }

    if ($flag == false) {
        $editQuery = "UPDATE " . $atpTABLE . " SET FirstName= '$firstName', LastName= '$lastName' WHERE StudentID = " . $studentID;
        $editData = mysqli_query($dbc, $editQuery)
        or die('Could not make edit');

        if ($editData) {
            echo '<script type="text/javascript">';
            echo 'setTimeout(function () { swal("Success!", "", "success");';
            echo '}, 0);</script>';
            ?>
            <script type="text/javascript">
                setTimeout(function loginRedir() {
                    window.location.href = "<?php echo $review_url . $courseID;?>";
                }, 1000);
            </script>';
            <?php
        }
    }
}


if (mysqli_num_rows($studentData) > 0)
{

    // Create the edit student form.

    echo '<div id ="content">';
    echo '<div class="container" id="classesContainer">';
    echo '<div class ="jumbotron" id="classes">';
    ?>

    <h3 align="center">Edit Student</h3>
    <form enctype="multipart/form-data" method="post"
          action="editStudent.php?<?php echo "studentId=$studentID&amp;courseId=$courseID" ?>" class="form-horizontal">
        <div class="form-group">
            <label class="control-label col-sm-2" for="studentFirstName">First Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="studentFirstName" placeholder="First Name"
                       name="studentFirstName"
                       value="<?php if (!empty($firstName)) echo $firstName; ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="studentLastName">Last Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="studentLastName" placeholder="Last Name"
                       name="studentLastName"
                       value="<?php if (!empty($lastName)) echo $lastName; ?>" required>
            </div>
        </div>
        <br>
        <input type="submit" name="submit" class="btn btn-success" value="Save Edit">
        <a href="manageClass.php?courseid=<?php echo $courseID ?>" name="cancel" class="btn btn-danger" role="button">Cancel</a>
    </form>
    <?php
    echo '</div>';
    echo '</div>';
    echo '</div>';
} else {
    echo '<div id ="content">';
    echo '<div class="container" id="classesContainer">';
    echo '<div class ="jumbotron" id="classes">';
    echo 'Looks like there was no student with that ID in that class';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
require_once('footer.php');
?>