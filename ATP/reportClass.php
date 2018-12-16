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

// If the session vars aren't set, try to set them with a cookie.
if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['user_id']) && isset($_COOKIE['username'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
        $_SESSION['username'] = $_COOKIE['username'];
    }
}
$currentuser = $_SESSION['user_id'];
$currentSelectedClass = $_GET['courseid'];

require_once('connectvars.php');
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Grab the selected class's information.
$instructorName = "select username from userinfo where employeeID = $currentuser";
$instruct_result = mysqli_query($dbc, $instructorName);
$nameRow = mysqli_fetch_array($instruct_result);
$currentUsername = $nameRow['username'];
$currentSelectedClassTable = '';
$currentSelectedClassName ='';
if (isset($_GET['courseid'])) {
    $className_query = "SELECT courseName FROM courseinfo WHERE courseID =" . $currentSelectedClass;
    $className_result = mysqli_query($dbc, $className_query)
    or die("could not find class.");

    $currentSelectedClassTable = "atp" . $currentUsername . $currentSelectedClass;
    $sql_query = "SELECT * FROM " . $currentSelectedClassTable;
    $resultset = mysqli_query($dbc, $sql_query) or die("database error:" . mysqli_error($dbc));
}
$student_records = array();
while ($rows = mysqli_fetch_assoc($resultset)) {
    $student_records[] = $rows;
}

$classNameRow = mysqli_fetch_assoc($className_result);
$currentSelectedClassName = $classNameRow['courseName'];
$cleanClassName=preg_replace('/\s+/', '',$currentSelectedClassName);

require_once('header.php');
require_once('navbar.php');

// Make initial connection to database.
$dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Retrieve the game data from MySQL
$query = "SELECT * FROM courseinfo WHERE employeeId = '$currentuser' ORDER BY courseName ASC";
$data = mysqli_query($dbc, $query);

// Get database structure for selected class.
if (isset($_GET['courseid'])) {
    $colQuery = "SELECT `COLUMN_NAME` 
FROM `INFORMATION_SCHEMA`.`COLUMNS` 
WHERE `TABLE_SCHEMA`='attendancetracking' 
    AND `TABLE_NAME`=" . "'$currentSelectedClassTable'";
    $colResult = mysqli_query($dbc, $colQuery)
    or die("Cannot query on that selected class.");

    $ATP_col_names = array();
    while ($colRow = mysqli_fetch_array($colResult)) {
        $ATP_col_names[] = $colRow;
    }
}

// Insert HTML divs for bootstrap purposes.
echo'<div id="content">';
echo '<div class="container" id="classesContainer">';
echo '<div class ="jumbotron" id="classes">';
echo $ATP_col_names[0][1];
echo '<h3 align="center">Select a class to run a report on</h3><hr>';
if (mysqli_num_rows($data) > 0) {
    echo '<ul class="List-group">';
    while ($row = mysqli_fetch_array($data)) {
        echo '<a href="reportClass.php?courseid=' . $row['courseID'] . '"><li class="list-group-item" id="userClassList"> <p>' . $row['courseName'] . '</p></a></li>';
    }
}
echo '</ul>';

$newColName = array();
$k = 0;

?>

<script>
    function exportTableToExcel(tableID, filename = ''){
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

        // Specify file name.
        filename = filename?filename+'.xls':'excel_data.xls';

        // Create download link element.
        downloadLink = document.createElement("a");

        document.body.appendChild(downloadLink);

        if(navigator.msSaveOrOpenBlob){
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob( blob, filename);
        }else{
            // Create a link to the file.
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

            // Setting the file name.
            downloadLink.download = document.getElementById("className").innerHTML;

            // Triggering the function
            downloadLink.click();.
        }
    }
</script>
<hr>
    <!-- Create the reporting form -->
    <div class="container">
        <h3 hidden id ="className"><?php echo $cleanClassName?></h3>
        <h3 style="text-align: center">Export attendance data to Excel</h3>
        <br>
        <br>
        <h3> Current selected class:  <?php echo '<h4>'. $currentSelectedClassName . '</h4></h3>';?>
        <div class="well-sm col-sm-12">
            <div class="btn-group pull-right">
                <button class="btn btn-primary" onclick="exportTableToExcel('reportTable')">Export Table Data To Excel File</button>

            </div>
        </div>
        <div class = "container" style="overflow-x:auto;">
        <table id="reportTable" class="table table-striped table-bordered" border="2">
            <tr>
                <?php foreach ($ATP_col_names as $colName) {
                    $i = 0; ?>
                    <th id="<?php echo $colName[$i];?>"><?php array_push($newColName, $colName[$i]);
                        echo $colName[$i]; ?></th>
                    <?php $i++;
                } ?>
            </tr>
            <tbody>
            <tr>
                <?php foreach ($student_records as $student) { ?>
                    <?php

                        for ($j = 0; $j < count($newColName); $j++) {
                            ?>

                          <td <?php if ($student[$newColName[$j]] == 'absent'){echo'style = "background-color: #ff9999;"'; }
                          else if($student[$newColName[$j]] == 'present'){echo'style = "background-color: #85e085;"';}?>> <?php echo $student[$newColName[$j]]; echo'</td>';?>
                         <?php
                        }
                    echo'</tr>';
                } ?>
            </tbody>
        </table>
        </div>

<?php
echo '</div>';
echo '</div>';
echo '</div>';

require_once('footer.php');
?>