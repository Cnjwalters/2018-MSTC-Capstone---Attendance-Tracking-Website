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

// Make initial connection to database.
$dbc = new PDO('mysql:host=localhost;dbname=attendancetracking', DB_USER, DB_PASSWORD);
$dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get the courseID from the get var.
$courseID = $_GET['courseID'];

// Create a date picker to select the date of attendance
$newDate = new DateTime();
$today = $newDate->format('Y-m-d');

// Create date jumbotron.
echo '<div id="content">';
echo '<div class="container" id="dateContainer">';
echo '<div class ="jumbotron" id="attendanceDatePicker">';
echo '<form method="post" action="editattendance.php?courseID=' . $courseID . '&attendanceDate=' . $_POST['attendanceDate'] . '">';
echo '<div class="form-group">
                    <label class="control-label col-sm-4" for="attendanceDate">Edit Past Date:</label>
                    <div class="col-sm-8">';
echo "<input type='date' value='$today' onblur='validateAttendanceDate();' max='$today' id='attendanceDate' 
           name='attendanceDate' placeholder='$today'>";
echo '<button type="submit" name="dateSubmit" class="btn btn-default">Submit Date</button>';
echo '</div></div></form>';
echo '</div></div></div><br/><br/>';

$attendanceDate = $_POST['attendanceDate'];
// If the date submit is clicked, add a popup asking if they really want to edit the past date.
if (isset($_POST['dateSubmit'])) {
    echo '
<div class="overlay" id="overlay">
  <div class="popup" id="popup">
  <form method="post" action="editattendance.php?courseID=' . $courseID . '&attendanceDate=' . $attendanceDate .'">
    <p>Are you sure you want to edit the attendance record for ' . $_POST['attendanceDate'] . '</p>
    <div class="text-right">
      <input class="btn btn-cancel" onclick="closePopup()" value="Cancel" type="button">
      <input type="submit" id="popupSubmit" name="popupSubmit" value="Ok" onclick="popOk()" class="btn btn-primary">
      </form>
    </div>
  </div>
</div>';
}

if (isset($_POST['popupSubmit'])) {

    // Create query to get all student information.
    $query = "SELECT * FROM studentinfo s JOIN enrollment e ON s.studentID = e.studentID JOIN courseinfo c ON c.courseID = e.courseID WHERE c.courseID = $courseID ORDER BY s.studentLastName ASC";
    $query_stmt = $dbc->prepare($query);
    $query_stmt->execute();

    // Loop through the gathered data array of students, formatting the HTML with the student data.
    if ($query_stmt->rowCount() > 0) {

        // Insert HTML divs for bootstrap purposes.
        echo '<div id="content">';
        echo '<div class="container" id="studentContainer">';
        echo '<div class ="jumbotron" id="studentTable">';
        $row = $query_stmt->fetch(PDO::FETCH_ASSOC);

        echo '<h2 align="center">' . $row['courseName'] . '<a href="manageClass.php?courseid=' . $row['courseID'] . '"><span class="glyphicon glyphicon-cog"></span></a></h2>';
        echo '<h4 align="center">(' . $row['courseID'] . ')</h4>';

        // Query and link student information to the class currently being looked at.
        $query = "SELECT * FROM studentinfo s JOIN enrollment e ON s.studentID = e.studentID JOIN courseinfo c ON " .
            "c.courseID = e.courseID WHERE c.courseID = $courseID ORDER BY s.studentLastName ASC";
        $query_stmt = $dbc->prepare($query);
        $query_stmt->execute();

        // Create a form to handle any attendance value inputs. Passing in courseID var ensures dynamically created forms.
        echo '<form action="editattendance.php?courseid=' . $courseID . '&attendanceDate=' . $attendanceDate . '" method="POST">';

        // Create a variable for error handling/success messages.
        $error = true;

        // Create a table to contain all the students. In addition, this will hold the radio buttons for attendance values.
        echo '<table class="table table-condensed">';
        echo '<thead><tr>

    <th class="present"></th>
    <th class="attendanceinputs"></th>
    <th class="absent"></th>
    <th class="attendanceinputs2"></th>
    <th class="firstname">First Name</th>
        <th class="lastname">Last Name</th>
        <th class="studentid">Student ID</th>
    </tr> 
    </thead>';

        // Populate the table with student information. Also, increment the $i by each iteration.
        $i = 0;
        while ($row = $query_stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tbody id="myTable"><tr></td>';
            //<td><p><input type="radio" class="form-check-input" id="' . $row['studentLastName'] . 'present" name="student' . $i . '" value="1"><span class="glyphicon glyphicon-ok" ></span>&nbsp;&nbsp;
            echo '<td>Present</td>
<td><p><input type="checkbox" id="' . $row['studentID'] . 'present" name="student' . $i . '" value="present" checked hidden></p>
<p><label class="switch"><input type="checkbox" id="' . $row['studentID'] . 'absent" name="student' . $i . '" value="absent"><span class="slider round" ></span></label></p></td>
<td>Absent</td>&nbsp;&nbsp;
            <td><button type="button"  class="btn btn-default btn-lg" data-toggle="modal" data-target="#myModalNorm">...</a></button></td>&nbsp;&nbsp;&nbsp;&nbsp;
            ' . '<td><p>' . $row['studentFirstName'] . '</p></td>';
            echo '<td><p>' . $row['studentLastName'] . '</p></td>';
            echo '<td><p>' . $row['studentID'] . '</p></td></tr>';
            $i = $i + 1;
        }

        // Close off the table now that the table has all of the students within the respective class.
        echo '</table>';
        $dbc = null;
        $radioVal = [];

        // Post method time!
        if (isset($_POST['submit'])) {
            // Grab the attendance values.
            for ($j = 0; $j < $i; $j++) {
                $studentGetter = 'student' . $j;
                $radioVal[$j] = $_POST[$studentGetter];
            }

            $updateSuccess = false;
            $initialSuccess = false;

            // -----------------------------------------------------
            // Begin handling column logic.
            // -----------------------------------------------------

            // Establish connection to database.
            $columnDBC = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            // Obtain the logged in user's username.
            $instructorInfo = mysqli_query($columnDBC, "SELECT username FROM attendancetracking.userinfo WHERE employeeid = $currentuser")
            or die("Error querying attendancetracking database. Could not fetch username.");
            $instructorInfoArray = mysqli_fetch_array($instructorInfo);
            $instructorName = $instructorInfoArray['username'];

            // Obtain all of the studentID's for the students within the class.
            $studentQuery = "SELECT studentinfo.studentID FROM studentinfo JOIN enrollment " .
                "ON studentinfo.studentID = enrollment.studentID JOIN courseinfo " .
                "ON courseinfo.courseID = enrollment.courseID WHERE courseinfo.courseID = " . $courseID . " ORDER BY studentinfo.studentLastName ASC";

            $studentResults = mysqli_query($columnDBC, $studentQuery);

            $studentIDary = [];
            $k = 0;

            while ($studentResultsRow = mysqli_fetch_array($studentResults)) {
                $studentIDary[$k] = $studentResultsRow['studentID'];
                $k = $k + 1;

            }


            // Obtain the current date and create the name of the column attendance data will be stored in.
            $atpColumn = $_GET->format('m_d_Y');

            // Place the table name into a variable.
            $columnTable = 'atp' . $instructorName . $courseID;

            $radioVal = [];
            $studentIDary = [];

            // Find out if the user is inserting new attendance values or updating existing attendance values.
            $columnQuery = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME = '" . $atpColumn . "' 
                        AND TABLE_NAME = '" . $columnTable . "'";
            $columnQueryResult = mysqli_query($columnDBC, $columnQuery);
            if (mysqli_num_rows($columnQueryResult) > 0) {
                echo 'update';
                // The column exists within the classes table. Update query is required.
                for ($x = 0; $x < count($radioVal); $x++) {
                    $atpVal = $radioVal[$x];
                    $atpStudent = $studentIDary[$x];

                    mysqli_query($columnDBC, "UPDATE " . $columnTable . " SET " . $atpColumn . " = '" . $atpVal .
                        "' WHERE studentID = " . $atpStudent);
                }

                // Flip the update flag message to true.
                $updateSuccess = true;
            } else {
                echo 'create';
                echo '<div class="overlay" id="overlay">
  <div class="popup" id="popup">
  <form method="post" action="editattendance.php?courseID=' . $courseID . '&attendanceDate=' . $attendanceDate . '">
    <p>There are no attendance records for ' . $_POST['attendanceDate'] . '. Would you like to add new records?</p>
    <div class="text-right">
      <input class="btn btn-cancel" onclick="closePopup()" value="Cancel" type="button">
      <input type="submit" id="createOk" name="createOk" value="Ok" onclick="popOk()" class="btn btn-primary">
      </form>
    </div>
  </div>
</div>';
                if (isset($_POST['createOk'])) {
                    // The column does not exist within the classes table. Alter table query is required.
                    $alterColumn = mysqli_query($columnDBC, "ALTER TABLE " . $columnTable .
                        " ADD " . $atpColumn . " varchar(10)");

                    for ($x = 0; $x < count($radioVal); $x++) {
                        $atpVal = $radioVal[$x];
                        $atpStudent = $studentIDary[$x];

                        mysqli_query($columnDBC, "UPDATE " . $columnTable . " SET " . $atpColumn . " = '" . $atpVal .
                            "' WHERE studentID = " . $atpStudent);
                    }

                    // Flip the initial success message flag to true.
                    $initialSuccess = true;
                }
            }
            // Determine which success/error message should be displayed, then display it.

            // Update success message.
            if ($updateSuccess == true) {
                echo '<script type="text/javascript">';
                echo 'setTimeout(function () { swal("Success!", "Attendance for ' . date('M, d Y') .
                    ' has been updated!", "success");';
                echo '}, 0);</script>';
            } // Initial success message.
            else if ($initialSuccess == true) {
                echo '<script type="text/javascript">';
                echo 'setTimeout(function () { swal("Success!", "Attendance for ' . date('M, d Y') .
                    ' has been saved!", "success");';
                echo '}, 0);</script>';
            } // Error message.
            else {
                echo '<script type="text/javascript">';
                echo 'setTimeout(function () { swal("Uh oh", "Attendance for ' . date('M, d Y') .
                    ' has not been saved. Please try again.", "error");';
                echo '}, 0);</script>';
            }
        }

        // Submit button. Then, close form.
        echo '<input type="submit" name="submit" value="Submit Attendance">';
        echo '</form>';

        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<!-- Modal -->
<div class="modal fade" id="myModalNorm" tabindex="-1" role="dialog" 
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
                <h4 class="modal-title" id="myModalLabel">
                    Extra Options
                </h4>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body">
                
                <form role="form">
                  <div class="form-group">
                  <label for="excused">Excused</label>
                      <button class="btn btn-default btn-sm"><span class="glyphicon glyphicon-file"></button>
                    <label for="tardy">Tardy</label>
                      <button class="btn btn-default btn-sm"><span class="glyphicon glyphicon-time"></button>
                      <label for="contactStudent">Contact</label>
                      <button class="btn btn-default btn-sm"><span class="glyphicon glyphicon-envelope"></button>
                      <label for="removeStudent">Remove Student</label>
                      <button class="btn btn-default btn-sm"><span class="glyphicon glyphicon-remove-circle"></button>
                  </div>
                  <div class="form-group">
                      <textarea rows="4" cols="75" placeholder="Comment.."></textarea>
                  </div>
                </form>
                
                
            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">
                            Close
                </button>
                <button type="button" class="btn btn-primary">
                    Save changes
                </button>
            </div>
        </div>
    </div>
</div>';

    } else {
        echo '<div class="container" id="studentContainer">';
        echo '<div class ="jumbotron" id="studentTable">';
        $query = "SELECT courseName FROM courseinfo WHERE courseID = " . $courseID;
        $query_stmt = $dbc->prepare($query);
        $query_stmt->execute();
        $row = $query_stmt->fetch(PDO::FETCH_ASSOC);

        echo '<h2 align="center">' . $row['courseName'] . '<a href="manageClass.php?courseid=' . $courseID . '"><span class="glyphicon glyphicon-cog"></span></a></h2>';
        echo '<h4 align="center">(' . $courseID . ')</h4>';

        echo '</div> </div>';
    }

// Close the database connection.
    $dbc = null;
}

require_once('footer.php');
?>