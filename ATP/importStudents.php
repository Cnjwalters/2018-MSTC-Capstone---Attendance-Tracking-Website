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

require_once 'header.php';
require_once('connectvars.php');
require_once('navbar.php');

$courseID = $_GET['courseid'];
$userID = $_SESSION['user_id'];
$review_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/classSettings.php';

if (isset($_POST["Import"])) {
// Connect to the database
    $dbc = new PDO('mysql:host=localhost;dbname=attendancetracking', DB_USER, DB_PASSWORD);
    $dbc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtain the logged in user's username.
    $instructorQuery = "SELECT username FROM attendancetracking.userinfo WHERE employeeid = $currentuser";
    $instructorQuery_stmt = $dbc->prepare($instructorQuery);
    $instructorQuery_stmt->execute();
    $instructorRow = $instructorQuery_stmt->fetch(PDO::FETCH_ASSOC);

    $filename = $_FILES["file"]["tmp_name"];
    $checkfile = pathinfo($_FILES["file"]["name"]);


    // The nested array to hold all the arrays.
    $the_big_array = [];

    // Open the file for reading.
    if (($h = fopen("{$filename}", "r")) !== FALSE) {

        // Each line in the file is converted into an individual array that we call $data.
        // The items of the array are comma separated.
        while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {

            // Each individual array is being pushed into the nested array.
            $the_big_array[] = $data;

        }

        // Close the file.
        fclose($h);
    }

    // Flags for loop process.
    $flagOneOrMore = false;

    // Loop through the array with the imported students' data and insert into database.
    for ($i = 1; $i < count($the_big_array); $i++) {
        $var1 = $the_big_array[$i][2];
        $var2 = $the_big_array[$i][1];
        $var3 = $the_big_array[$i][0];
        $enroll_query = "Select * from enrollment where studentID ='$var1' and courseID = $courseID";
        $enroll_query_stmt = $dbc->prepare($enroll_query);
        $enroll_query_stmt->execute();

        $classTable = 'atp' . $instructorRow['username'] . $courseID;

        $query = "Select * from studentinfo where studentID ='$var1';";
        $query_stmt = $dbc->prepare($query);
        $query_stmt->execute();
        if ($query_stmt->rowCount() == 0) {
            $query = "INSERT INTO studentinfo (studentID, studentFirstName, studentLastName)

 values (:studentID,:studentFirstName,:studentLastName);
 INSERT INTO enrollment (courseID, studentID) VALUES (:courseID, :studentID); 
 INSERT INTO " . $classTable . "(StudentID, FirstName, LastName) VALUES (:studentID, :studentFirstName, :studentLastName);";
                $query_stmt = $dbc->prepare($query);
                $query_stmt->bindParam(":studentFirstName", $var2, PDO::PARAM_STR, 50);
                $query_stmt->bindParam(":studentLastName", $var3, PDO::PARAM_STR, 62);
                $query_stmt->bindParam(":studentID", $var1, PDO::PARAM_INT, 8);
                $query_stmt->bindParam(":courseID", $courseID, PDO::PARAM_INT, 8);
                $query_stmt->execute();
            } else if ($query_stmt->rowCount() > 0 && $enroll_query_stmt->rowCount() == 0) {
                $query = "INSERT INTO enrollment (courseID, studentID) VALUES (:courseID, :studentID); 
INSERT INTO " . $classTable . "(StudentID, FirstName, LastName) VALUES (:studentID, :studentFirstName, :studentLastName);";
                $query_stmt = $dbc->prepare($query);
                $query_stmt->bindParam(":studentFirstName", $var2, PDO::PARAM_STR, 50);
                $query_stmt->bindParam(":studentLastName", $var3, PDO::PARAM_STR, 62);
                $query_stmt->bindParam(":studentID", $var1, PDO::PARAM_INT, 8);
                $query_stmt->bindParam(":courseID", $courseID, PDO::PARAM_INT, 8);
                $query_stmt->execute();
            } else {
                $flagOneOrMore = true;
            }

            if ($query_stmt) {
                ?>
                <script type="text/javascript">
                    setTimeout(function () {
                        swal("Success!", "All students were successfully added to this class!.", "success");
                    }, 0);
                    setTimeout(function loginRedir() {
                        window.location.href = "<?php echo $review_url;?>";
                    }, 3500);
                </script>
                <?php

            } else {
                ?>
                <script type="text/javascript">
                    setTimeout(function () {
                        swal("Error", "One or more students have not been added to this class due to already being in this class.", "error");
                    }, 0);
                    setTimeout(function loginRedir() {
                        window.location.href = "<?php echo $review_url;?>";
                    }, 3500);
                </script>
                <?php
            }
        }

        if ($query_stmt) {
            ?>
            <!-- Show success message on successful import. -->
            <script type="text/javascript">
                setTimeout(function () {
                    swal("Success!", "All students were successfully added to this class!.", "success");
                }, 0);
                setTimeout(function loginRedir() {
                    window.location.href = "<?php echo $review_url;?>";
                }, 3500);
            </script>
            <?php

        } else {

            ?>
            <!-- If failed show error message. -->
            <script type="text/javascript">
                setTimeout(function () {
                    swal("Error", "One or more students have not been added to this class due to already being in this class.", "error");
                }, 0);
                setTimeout(function loginRedir() {
                    window.location.href = "<?php echo $review_url;?>";
                }, 3500);
            </script>
            <?php
        }
    }

require_once 'footer.php';
?>