"use strict";
var $ = function (id) {
    return document.getElementById(id).innerHTML;
}
window.onload = function () {
    $("attendanceDate").onblur = validateAttendanceDate();
}
window.setTimeout(function() {add_stops(49.89995, -97.14124)}, 5000);
function validateAttendanceDate() {
    var patternForMmDdYyyy = /^\d{4}-\d{2}-\d{2}$/;   //Regular express for yyyy-mm-dd date format
    var ptr = $("attendanceDate");         //Pointer to the date input
    var err = $("dateerr");         //Pointer to the date error marker
    err.style.visibility = "hidden";
    if (ptr.value === "") {
        err.style.visibility = "visible";
        err.title = "Please enter a sample date.";
        alert("Please enter a sample date.");
        var today = new Date();
        $('.attendanceDate').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            endDate: "today",
            maxDate: today
        });
    } else {
        if (isDate(ptr.value)) {
            var enteredDate = ptr.value;
            //To simplify comparison, all dates should be strings in yyyy-mm-dd format
            if(!enteredDate.match(patternForMmDdYyyy)) {
                enteredDate = new Date(enteredDate).format("yyyy-mm-dd");
                ptr.value = enteredDate;  //reformat user entry
            }//end if
        } else {
            err.style.visibility = "visible";
            err.title = "That is not a recognizable date format.";
            alert("That is not a recognizable date format.");
        }//end if
    }//else OK (date is option)
    return err.style.visibility === "hidden";
}//end

// --------------
// Date Popup
// -------------

function closePopup() {
    history.back();
}

function popOk() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('popup').style.display = 'none';
}