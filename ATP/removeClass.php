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
$currentuser = $_SESSION['user_id'];
$courseID = $_GET['courseId'];

// Make initial connection to database.
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$classNameQuery = "SELECT courseName FROM courseinfo WHERE courseID =".$courseID;
$classNameData = mysqli_query($dbc,$classNameQuery)
or die('Could not query class name.');

$instructorQuery = "SELECT username FROM userinfo WHERE employeeId =". $currentuser;
$instructorData = mysqli_query($dbc,$instructorQuery)
or die('Could not query');

$row = mysqli_fetch_array($instructorData);
$atpTABLE = 'atp'. $row['username'].$courseID;

$classRow = mysqli_fetch_array($classNameData);
$className = $classRow['courseName'];
$review_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/classSettings.php';

if (isset($_POST['submit']))
{
    // Remove necessary database components for removing a class.
    $removeATPQuery="DROP TABLE ".$atpTABLE;
    $removeCourseInfoQuery="DELETE FROM courseinfo WHERE courseID = ".$courseID." AND employeeID = ".$currentuser;
    $removeEnrollment = "DELETE FROM enrollment WHERE courseID = ".$courseID;

    $removeEnrollData = mysqli_query($dbc,$removeEnrollment)
    or die('Could not remove enrollment data');
    $removeATPData = mysqli_query($dbc,$removeATPQuery)
    or die('Could not remove ATP data');
    $removeCourseInfoData = mysqli_query($dbc,$removeCourseInfoQuery)
    or die('Could not remove courseinfo data.');

    // Show successful removal message.
    if ($removeATPQuery && $removeEnrollment){
        echo '<script type="text/javascript">';
        echo 'setTimeout(function () { swal("Success!", "", "success");';
        echo '}, 0);</script>';


        ?>
        <!-- Redirect to class settings page. -->
        <script type="text/javascript">
            setTimeout(function loginRedir() {
                window.location.href = "<?php echo $review_url;?>";
            }, 1000);
        </script>';
        <?php
    }
}


if (mysqli_num_rows($instructorData) > 0)
{

    echo '<div id ="content">';
    echo '<div class="container" id="classesContainer">';
    echo '<div class ="jumbotron" id="classes">';
    ?>
    <h3 align="center">Remove Class</h3>
    <h4 align="center">Are you sure you want to remove this class?</h4>
    <form enctype="multipart/form-data" method="post" action="removeClass.php?<?php echo"courseId=$courseID"?>" class="form-horizontal">
        <div class="form-group">
            <label class="control-label col-sm-2" for="courseID">Course ID:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="courseID" name="courseID"
                       value="<?php if (!empty($courseID)) echo $courseID; ?>" disabled>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="courseName">Course Name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="courseName" name="courseName"
                       value="<?php if (!empty($className)) echo $className; ?>" disabled>
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
    echo 'Looks like there was no course with that ID.';
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
                    Removing a class will remove all records of this class and can not be undone.
                </h4>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <form role="form" id="modalForm" method="post">
                    <h5>Ok to remove class?</h5>
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
