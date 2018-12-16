<?php
require_once('connectvars.php');

if (isset($_SESSION['username'])) {
    $dbc = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $currentuser = $_SESSION['user_id'];
    // Retrieve the course data from MySQL.
    $query = "SELECT * FROM courseinfo WHERE employeeID = '$currentuser' ORDER BY courseName ASC";
    $data = mysqli_query($dbc, $query);

    echo '
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="index.php"><img src="http://www.mstc.edu/sites/all/themes/Mstc/img/logo.png" id="mstcLogo"/></a>
        </div>
        <!-- Set the sidebar items -->
        <ul id="sidebarUL" class="list-unstyled components">
            <p class="programName">Attendance Tracker</p>
            <li>
                <a href="accountSettings.php">'. $_SESSION['username'].' <span class="glyphicon glyphicon-cog"></span></a>
            </li>
            <li>
                <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">My Classes <span class="glyphicon glyphicon-triangle-bottom"></span></a>
                <ul class="collapse list-unstyled" id="homeSubmenu">';
    if (mysqli_num_rows($data) > 0) {

        $i = 0;
        while ($row = mysqli_fetch_array($data)) {
            echo '            
                    <li>
                        <a href="takeattendance.php?courseid=' . $row['courseID'] . '"><p>' . $row['courseName'] . '</p></a>';
        }
    }
        echo'    
    </li>
                </ul>
            </li>
            
            <li>
                <a href="classSettings.php">Class Settings</a>
            </li>
            
             <li>
                <a href="reportClass.php">Report</a>
            </li>
                        
            <li>
                <a href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>';

    mysqli_close($dbc);
}

// If a user is not logged in show this.
else{
    echo '
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <a href="index.php"><img src="http://www.mstc.edu/sites/all/themes/Mstc/img/logo.png" id="mstcLogo"/></a>
        </div>

        <ul class="list-unstyled components">
            <p class="programName">Attendance Tracker</p>
        </ul>
    </nav>';
}
?>