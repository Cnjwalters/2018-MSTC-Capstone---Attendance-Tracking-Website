<?php

session_start();

// If the session vars aren't set, try to set them with a cookie
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

// Grab data from database based on selected class's id.
$classNameQuery = "SELECT courseName FROM courseinfo WHERE courseID =".$courseID;
$classNameData = mysqli_query($dbc,$classNameQuery)
or die('Could not query class name.');
$instructorQuery = "SELECT username FROM userinfo WHERE employeeId =". $currentuser;
$instructorData = mysqli_query($dbc,$instructorQuery)
or die('Could not query');
$row = mysqli_fetch_array($instructorData);
$atpTABLE = 'atp'. $row['username'].$courseID;
$studentQuery = "SELECT StudentID, FirstName, LastName FROM ". $atpTABLE." WHERE studentID = ".$studentID;
$studentData = mysqli_query($dbc, $studentQuery)
or die('Could not query student');

$studentRow = mysqli_fetch_array($studentData);
$classRow = mysqli_fetch_array($classNameData);
$firstName = $studentRow['FirstName'];
$lastName = $studentRow['LastName'];
$studentID = $studentRow['StudentID'];
$className = $classRow['courseName'];
$review_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/manageClass.php?courseid=';

if (isset($_POST['submit']))
{
        // Remove necessary student information from database after student deletion is selected.
        $removeATPQuery="DELETE FROM ".$atpTABLE." WHERE StudentID = " .$studentID;
        $removeEnrollment = "DELETE FROM enrollment WHERE courseID = ".$courseID." and studentID = " .$studentID;
        $removeEnrollData = mysqli_query($dbc,$removeEnrollment)
        or die('Could not remove enrollment data');
        $removeATPData = mysqli_query($dbc,$removeATPQuery)
        or die('Could not remove ATP data');

        // Show success messasge.
        if ($removeATPQuery && $removeEnrollment){
            echo '<script type="text/javascript">';
            echo 'setTimeout(function () { swal("Success!", "", "success");';
            echo '}, 0);</script>';


?>
            <!-- Redirect -->
           <script type="text/javascript">
                setTimeout(function loginRedir() {
                    window.location.href = "<?php echo $review_url.$courseID;?>";
                }, 1000);
            </script>';
<?php
        }
}


if (mysqli_num_rows($studentData) > 0)
{

    echo '<div id ="content">';
    echo '<div class="container" id="classesContainer">';
    echo '<div class ="jumbotron" id="classes">';
    ?>
    <h3 align="center">Remove Student</h3>
    <h4 align="center">Are you sure you want to remove this student?</h4>
    <form enctype="multipart/form-data" method="post" action="removeStudent.php?<?php echo"studentId=$studentID&amp;courseId=$courseID"?>" class="form-horizontal">
        <div class="form-group">
            <label class="control-label col-sm-2" for="studentID">Enrolled Course:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="studentID" name="studentID"
                       value="<?php if (!empty($className)) echo $className; ?>" disabled>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="studentID">Student ID:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="studentID" name="studentID"
                       value="<?php if (!empty($studentID)) echo $studentID; ?>" disabled>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="studentFirstName">First Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="studentFirstName" name="studentFirstName"
                       value="<?php if (!empty($firstName)) echo $firstName; ?>" disabled>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="studentLastName">Last Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="studentLastName" name="studentLastName"
                       value="<?php if (!empty($lastName)) echo $lastName; ?>" disabled>
            </div>
        </div>
        <br>
        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#OkModalNorm">Yes, remove</button>
        <a href="manageClass.php?courseid=<?php echo$courseID?>" name="cancel" class="btn btn-danger" role="button">Cancel</a>
    </form>
    <?php
    echo'</div>';
    echo'</div>';
    echo'</div>';
}
else{
    echo '<div id ="content">';
    echo '<div class="container" id="classesContainer">';
    echo '<div class ="jumbotron" id="classes">';
    echo 'Looks like there was no student with that ID in that class';
    echo'</div>';
    echo'</div>';
    echo'</div>';
}
require_once ('footer.php');
?>

<div class="modal fade" id="OkModalNorm" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close"
                        data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h3 class="modal-title" id="myModalLabel">
                    WARNING
                </h3>
                <br>
                <h4>
                    Removing a student from this class will also remove any attendance records for the class.
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form role="form" id="modalForm" method="post">
                    <h5>Ok to remove student?</h5>
                    <div class="form-group">
                        <input type="submit" name="submit" class="btn btn-success" value="Yes, remove">
                        <a href="manageClass.php?courseid=<?php echo$courseID?>" name="cancel" class="btn btn-danger" role="button">Cancel</a>
                    </div>
                </form>


            </div>

        </div>
    </div>
</div>
</div>
