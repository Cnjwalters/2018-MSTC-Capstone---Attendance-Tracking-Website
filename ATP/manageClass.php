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
        //Kill their session.
        header('Location: logout.php');
    }

}

// Assign the current timestamp as the user's latest activity.
$_SESSION['last_action'] = time();

require_once('connectvars.php');
require_once('header.php');
require_once('navbar.php');

// If the session vars aren't set, try to set them with a cookie.
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'];
    }
}
$currentuser = $_SESSION['user_id'];
$courseID = $_GET['courseid'];

$dbc = new PDO('mysql:host=localhost;dbname=attendancetracking', DB_USER, DB_PASSWORD);
$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obtain the logged in user's username.
$instructorQuery = "SELECT username FROM attendancetracking.userinfo WHERE employeeid = $currentuser";
$instructorQuery_stmt = $dbc->prepare($instructorQuery);
$instructorQuery_stmt->execute();
$instructorRow = $instructorQuery_stmt->fetch(PDO::FETCH_ASSOC);

$classTable = 'atp' . $instructorRow['username'] . $courseID;

// Grab all dats for the current class.
$query = "select * from $classTable s join enrollment e on s.StudentID = e.studentID join courseinfo c on c.courseID = e.courseID WHERE c.courseID = $courseID";
$query_stmt = $dbc->prepare($query);
$query_stmt->execute();

$courseNameQuery = "SELECT courseName FROM courseinfo WHERE courseID = " . $courseID;
$query_stmt_coursename = $dbc->prepare($courseNameQuery);
$query_stmt_coursename->execute();

$row = $query_stmt->fetch(PDO::FETCH_ASSOC);
$coursenamerow = $query_stmt_coursename->fetch(PDO::FETCH_ASSOC);

$review_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/manageClass.php?courseid=';

// Script for hidden div to allow edits to be made to class name.
?>
<script>
function toggleEditDiv() {
    var x = document.getElementById("myDIV");
    if (x.style.display === "block") {
        x.style.display = "none";
    } else {
        x.style.display = "block";
    }
}
</script>
<?php
    // Insert HTML divs for bootstrap purposes.
echo'<div id ="content">';
    echo '<div class="container" id="classesContainer">';
    echo '<div class ="jumbotron" id="classes">';
    echo'<h2 align="center">'.$coursenamerow['courseName'].'&nbsp;<button  style="border: none; background-color: inherit;" onclick="toggleEditDiv()"><span class="glyphicon glyphicon-edit"></span></button>
&nbsp;<a href="removeClass.php?courseId='. $_GET['courseid'] .'"><span class="glyphicon glyphicon-trash"></span></a></h2>';
    echo'<h4 align="center">('.$courseID.')</h4>';
?>
<div id="myDIV" type="hidden">
<form method="post" action="manageClass.php?courseid=<?php echo $_GET['courseid']; ?>" class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-sm-2" for="courseName">Course Name:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="courseName" placeholder="Course Name" name="courseName"
             value="<?php if (!empty($coursenamerow['courseName'])) echo $coursenamerow['courseName'] ?>" required>
        </div>
    </div>
    <br>
    <input type="submit" name="editCourse" class="btn btn-primary" value="Edit Course">
</form>
</div>
<?php

if(isset($_POST['editCourse'])){
    $connect = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $courseName = $_POST['courseName'];


    // Update the course name.
    $editQuery = "UPDATE courseinfo SET courseName= '$courseName' WHERE courseID = ". $courseID;
    $editData = mysqli_query($connect,$editQuery)
    or die('Could not make edit');


    if ($editData){
        // Show successful name change.
        echo '<script type="text/javascript">';
        echo 'setTimeout(function () { swal("Success!", "", "success");';
        echo '}, 0);</script>';
        ?>
        <script type="text/javascript">
            setTimeout(function loginRedir() {
                window.location.href = "<?php echo $review_url.$courseID;?>";
            }, 1000);
        </script>
        <?php

    }
}

    echo '<hr><h3 align="center">Add new student</h3>
<form method="post" action="manageClass.php?courseid=' . $_GET['courseid'] . '" class="form-horizontal">
    <div class="form-group">
        <label class="control-label col-sm-2" for="studentFirstName">First Name:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="studentFirstName"  placeholder="First Name" name="studentFirstName" required>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-sm-2" for="studentLastName">Last Name:</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="studentLastName" placeholder="Last Name" name="studentLastName" required>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-sm-2" for="studentID">Student ID:</label>
        <div class="col-sm-10">
            <input type="number"  title="ID must be exactly 8 digits" class="form-control" id="studentId" placeholder="Student ID" name="studentID" required>
        </div>
    </div>
    <br>
    <input type="submit" name="addStudent" class="btn btn-primary" value="Add Student">
</form>';

    if (isset($_POST['addStudent'])){
        $studentFirst = $_POST['studentFirstName'];
        $studentLast = $_POST['studentLastName'];
        $studentID = $_POST['studentID'];
        $flag = false;
        $num_length = strlen((string)$studentID);
        if(!preg_match('/^[-a-zA-Z\']+$/', $studentFirst)) {
            echo '<br><div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                First name must include letters, hyphens and apostrophes only.
                </div></div>';
            $flag = true;
        }

        if(!preg_match('/^[-a-zA-Z\']+$/', $studentLast)) {
            echo '<br><div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                Last name must include letters, hyphens and apostrophes only.
                </div></div>';
            $flag = true;
        }

        if ($num_length != 8) {
            echo '<br><div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                Student ID must be 8 characters and numeric only.
                </div></div>';
            $flag = true;
        }

        $enroll_query = "Select * from enrollment where studentID ='$studentID' and courseID = $courseID";
        $enroll_query_stmt = $dbc->prepare($enroll_query);
        $enroll_query_stmt->execute();

        $query = "Select * from studentinfo where studentID ='$studentID'";
        $query_stmt = $dbc->prepare($query);
        $query_stmt->execute();
        if ($query_stmt->rowCount() == 0 && $flag == false) {
            $query = "INSERT INTO studentinfo (studentID, studentFirstName, studentLastName)
 values (:studentID,:studentFirstName,:studentLastName);
 INSERT INTO enrollment (courseID, studentID) VALUES (:courseID, :studentID);
 INSERT INTO " . $classTable . "(StudentID, FirstName, LastName) VALUES (:studentID, :studentFirstName, :studentLastName);";
            $query_stmt = $dbc->prepare($query);
            $query_stmt->bindParam(":studentFirstName", $studentFirst, PDO::PARAM_STR, 50);
            $query_stmt->bindParam(":studentLastName", $studentLast, PDO::PARAM_STR, 62);
            $query_stmt->bindParam(":studentID", $studentID, PDO::PARAM_INT, 8);
            $query_stmt->bindParam(":courseID", $courseID, PDO::PARAM_INT, 8);
            $query_stmt->execute();
        }
        else if ($query_stmt->rowCount() > 0 && $enroll_query_stmt->rowCount() == 0 && $flag == false) {
            $query = "INSERT INTO enrollment (courseID, studentID) VALUES (:courseID, :studentID); 
INSERT INTO " . $classTable . "(StudentID, FirstName, LastName) VALUES (:studentID, :studentFirstName, :studentLastName);";
            $query_stmt = $dbc->prepare($query);
            $query_stmt->bindParam(":studentFirstName", $studentFirst, PDO::PARAM_STR, 50);
            $query_stmt->bindParam(":studentLastName", $studentLast, PDO::PARAM_STR, 62);
            $query_stmt->bindParam(":studentID", $studentID, PDO::PARAM_INT, 8);
            $query_stmt->bindParam(":courseID", $courseID, PDO::PARAM_INT, 8);
            $query_stmt->execute();
        }
        else if ($enroll_query_stmt->rowCount() > 0 && $flag == false){
            echo '<br><div class="container"><div class="alert alert-danger alert-dismissible fade in">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                Looks like a student with that ID is already in that class.
                </div></div>';
        }
//        else{
//            ?>
<!--            <script type="text/javascript">-->
<!--                setTimeout(function () {-->
<!--                    swal("Hold On!", "An error has occurred while adding this student", "error");-->
<!--                }, 0);-->
<!--            </script>-->
<!--            --><?php
//        }
    }

    echo '<hr><h3 align="center">Import students from file</h3>
    <form class="form-horizontal" action="importStudents.php?courseid=' . $_GET['courseid'] . '" method="post" name="upload_excel" enctype="multipart/form-data">
                        <!-- File Button -->
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="filebutton">Select File:</label>
                            <div class="col-sm-10">
                                <input type="file" name="file" id="file" class="btn btn-primary">
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div class="form-group">
                            <div class="col-md-4">
                            <!--<input type="submit" id="submit" name="Import" class="btn btn-primary" data-loading-text="Loading...">-->
                                <button type="submit" id="submit" name="Import" class="btn btn-primary" data-loading-text="Loading...">Import</button>
                            </div>
                        </div>
                        </form>
                        <hr>';

    // Create a table to contain all the students. In addition, this will hold the radio buttons for attendance values.
    echo '<table class="table table-condensed">';
    echo '<thead><h3>Current Students</h3><tr>

    <th class="editLinks" style="width: 80px"></th>
    <th class="firstname">First Name</th>
        <th class="lastname">Last Name</th>
        <th class="studentid">Student ID</th>
    </tr> 
    </thead>';
// Loop through the gathered data array of students, formatting the HTML with the student data.
if ($query_stmt->rowCount() > 0) {
    $query = "select * from $classTable s join enrollment e on s.StudentID = e.studentID join courseinfo c on c.courseID = e.courseID WHERE c.courseID = $courseID ORDER BY s.LastName ASC";
    $query_stmt = $dbc->prepare($query);
    $query_stmt->execute();
    // Populate the table with student information. Also, increment the $i by each iteration.
    while ($row = $query_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tbody><tr>';
        echo'<td><p><a href="editStudent.php?studentId=' . $row['StudentID'] .'&amp;courseId='. $_GET['courseid'] .'"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;&nbsp;&nbsp;
<a href="removeStudent.php?studentId=' . $row['StudentID'] .'&amp;courseId='. $_GET['courseid'] .'""><span class="glyphicon glyphicon-trash"></span></a></p></td>';
        echo '<td><p>' . $row['FirstName'] . '</p></td>';
        echo '<td><p>' . $row['LastName'] . '</p></td>';
        echo '<td><p>' . $row['StudentID'] . '</p></td></tr>';
    }
    // Close off the table now that the table has all of the students within the respective class.
    echo '</table>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

}
require_once('footer.php');
?>