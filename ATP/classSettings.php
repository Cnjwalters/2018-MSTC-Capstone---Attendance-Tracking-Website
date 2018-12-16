<?php
// Start the session.
session_start();

// Expire the session if user is inactive for 30 minutes or more.
$expireAfter = 45;

// Check to see if our "last action" session variable has been set.
if(isset($_SESSION['last_action'])){

    //Figure out how many seconds have passed
    //since the user was last active.
    $secondsInactive = time() - $_SESSION['last_action'];

    //Convert our minutes into seconds.
    $expireAfterSeconds = $expireAfter * 60;

    //Check to see if they have been inactive for too long.
    if($secondsInactive >= $expireAfterSeconds){
        //User has been inactive for too long.
        //Kill their session.
        header('Location: logout.php');
    }

}

//Assign the current timestamp as the user's latest activity.
$_SESSION['last_action'] = time();

// If the session vars aren't set, try to set them with a cookie.
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'];
    }
}

// Set the required pages.
require_once('header.php');
require_once('connectvars.php');
require_once('navbar.php');

// Make initial connection to database.
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$currentuser = $_SESSION['user_id'];

// Retrieve the game data from MySQL
$query = "SELECT * FROM courseinfo WHERE employeeID = '$currentuser' ORDER BY courseName ASC";
$data = mysqli_query($dbc, $query);

// Insert HTML divs for bootstrap purposes.
echo '<div id ="content">';
echo '<div class="container" id="classesContainer">';
echo '<div class ="jumbotron" id="classes">';
echo '<h3 align="center">Current classes</h3>';
if (mysqli_num_rows($data) > 0) {

    $i = 0;
    echo '<ul class="List-group">';
    while ($row = mysqli_fetch_array($data)) {
        echo '<a href="manageClass.php?courseid=' . $row['courseID'] . '"><li class="list-group-item" id="userClassList"> <p>' . $row['courseName'] . '</p></a></li>';
    }
}
echo '</ul><hr>';
?>  <!-- Form to add a new course. -->
    <h3 align="center">Add new course</h3>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form-horizontal">
        <div class="form-group">
            <label class="control-label col-sm-2" for="username">Course name:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="courseName" placeholder="Course Name" name="courseName"
                       required>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-2" for="courseID">Course ID:</label>
            <div class="col-sm-10">
                <input type="number" maxlength="8" class="form-control" id="courseId" placeholder="Course ID"
                       name="courseNumber" required>
            </div>
        </div>
        <input type="submit" name="submit" class="btn btn-primary" value="Add Course">
    </form>
<?php
if (isset($_POST['submit'])) {
    // Set the post vars
    $courseName = $_POST['courseName'];
    $courseNumber = $_POST['courseNumber'];
    $flag = false;
    $num_length = strlen((string)$courseNumber);

    $data = mysqli_query($dbc, "SELECT * FROM courseinfo WHERE courseID = '$courseNumber'");
    if (!mysqli_num_rows($data) == 0) {
        echo '<br><div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                There is already a course with that ID. Please ensure you\'re typing the correct number.
                </div></div>';
        $flag = true;
    }

    if(!preg_match('/^[-a-zA-Z0-9 \']+$/', $courseName)) {
        echo '<br><div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                Course name must include letters, numbers, spaces, hyphens and apostrophes only.
                </div></div>';
        $flag = true;
    }

    if ($num_length != 8) {
        echo '<br><div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                Course ID must be 8 characters and numeric only.
                </div></div>';
        $flag = true;
    }

    if($flag == false) {
        $queryInsert = "INSERT INTO courseinfo(courseID, employeeID, courseName) VALUES ('$courseNumber', '$currentuser', '$courseName')";
        mysqli_query($dbc, $queryInsert);


    $instructorInfo = mysqli_query($dbc, "SELECT username FROM attendancetracking.userinfo WHERE employeeid = $currentuser")
    or die("Error querying attendancetracking database. Could not fetch username.");
    $instructorInfoArray = mysqli_fetch_array($instructorInfo);
    $instructorName = $instructorInfoArray['username'];

    // Create the query for if table does not exist.
    $createNewTable = 'CREATE TABLE IF NOT EXISTS atp' . $instructorName . $courseNumber . '(
                    StudentID int(8) NOT NULL,
                    FirstName varchar(50) NOT NULL,
                    LastName varchar(62) NOT NULL,
                    StudentComment TEXT NULL,
                    PRIMARY KEY(studentID))';

    // Execute the creation query. If the table does exist, nothing will happen, so just proceed to column logic.
    mysqli_query($dbc, $createNewTable);
    echo "<meta http-equiv='refresh' content='0'>";
    }
}
echo '</div>';
echo '</div>';
echo '</div>';

require_once('footer.php');
?>