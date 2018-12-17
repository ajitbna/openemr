





<?php
// Copyright (C) 2009, 2017 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");

// Validation for non-unique external patient identifier.
$alertmsg = '';
if (!empty($_POST["form_pubpid"])) {
    $form_pubpid = trim($_POST["form_pubpid"]);
    $result = sqlQuery("SELECT count(*) AS count FROM patient_data WHERE " .
    "pubpid = ?", array($form_pubpid));
    if ($result['count']) {
        // Error, not unique.
        $alertmsg = xl('Warning: Patient ID is not unique!');
    }
}

require_once("$srcdir/pid.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

// here, we lock the patient data table while we find the most recent max PID
// other interfaces can still read the data during this lock, however
// sqlStatement("lock tables patient_data read");

$result = sqlQuery("SELECT MAX(pid)+1 AS pid FROM patient_data");

$newpid = 1;

if ($result['pid'] > 1) {
    $newpid = $result['pid'];
}

setpid($newpid);

if (empty($pid)) {
  // sqlStatement("unlock tables");
    die("Internal error: setpid(" .text($newpid) . ") failed!");
}

// Update patient_data and employer_data:
//
$newdata = array();
$newdata['patient_data' ] = array();
$newdata['employer_data'] = array();
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND (uor > 0 OR field_id = 'pubpid') AND field_id != '' " .
  "ORDER BY group_id, seq");
while ($frow = sqlFetchArray($fres)) {
    $data_type = $frow['data_type'];
    $field_id  = $frow['field_id'];
  // $value     = '';
    $colname   = $field_id;
    $tblname   = 'patient_data';
    if (strpos($field_id, 'em_') === 0) {
        $colname = substr($field_id, 3);
        $tblname = 'employer_data';
    }

  //get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
  
    if (isset($_POST["form_$field_id"]) || $field_id == "pubpid") {
        $value = get_layout_form_value($frow);
        if ($field_id == 'pubpid' && empty($value)) {
            $value = $pid;
        }

        $newdata[$tblname][$colname] = $value;

    }

}



$patient_id = filter_input(INPUT_GET, 'patient_id');
$category_id = filter_input(INPUT_GET, 'parent_id');





/*

if(isset($_FILES['form_profile_image'])){

        $target_dir = "../../images/patient_profile_image/";
    $target_file = $target_dir . basename($_FILES["form_profile_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    // Check if image file is a actual image or fake image
    if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["form_profile_image"]["tmp_name"]);
        if($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }
    // Check if file already exists
    if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }
    // Check file size
    if ($_FILES["form_profile_image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["form_profile_image"]["tmp_name"], $target_file)) {
           // echo "The file ". basename( $_FILES["form_profile_image"]["name"]). " has been uploaded.";
            $newdata['patient_data']['profile_image']=$_FILES["form_profile_image"]["name"];
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}   */
$patient_id=updatePatientData($pid, $newdata['patient_data'], true);

updateEmployerData($pid, $newdata['employer_data'], true);

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../../library/documents.php");

 
$category_id = 10;


if (!empty($_FILES)) {

    
    $name     = $_FILES['form_profile_image']['name'];
    $type     = $_FILES['form_profile_image']['type'];
    $tmp_name = $_FILES['form_profile_image']['tmp_name'];
    $size     = $_FILES['form_profile_image']['size'];
    $owner    = $GLOBALS['userauthorized'];

    addNewDocument($name, $type, $tmp_name, $error, $size, $owner, $patient_id, $category_id);
}
$i1dob = DateToYYYYMMDD(filter_input(INPUT_POST, "i1subscriber_DOB"));
$i1date = DateToYYYYMMDD(filter_input(INPUT_POST, "i1effective_date"));

// sqlStatement("unlock tables");
// end table lock

newHistoryData($pid);
newInsuranceData(
    $pid,
    "primary",
    filter_input(INPUT_POST, "i1provider"),
    filter_input(INPUT_POST, "i1policy_number"),
    filter_input(INPUT_POST, "i1group_number"),
    filter_input(INPUT_POST, "i1plan_name"),
    filter_input(INPUT_POST, "i1subscriber_lname"),
    filter_input(INPUT_POST, "i1subscriber_mname"),
    filter_input(INPUT_POST, "i1subscriber_fname"),
    filter_input(INPUT_POST, "form_i1subscriber_relationship"),
    filter_input(INPUT_POST, "i1subscriber_ss"),
    $i1dob,
    filter_input(INPUT_POST, "i1subscriber_street"),
    filter_input(INPUT_POST, "i1subscriber_postal_code"),
    filter_input(INPUT_POST, "i1subscriber_city"),
    filter_input(INPUT_POST, "form_i1subscriber_state"),
    filter_input(INPUT_POST, "form_i1subscriber_country"),
    filter_input(INPUT_POST, "i1subscriber_phone"),
    filter_input(INPUT_POST, "i1subscriber_employer"),
    filter_input(INPUT_POST, "i1subscriber_employer_street"),
    filter_input(INPUT_POST, "i1subscriber_employer_city"),
    filter_input(INPUT_POST, "i1subscriber_employer_postal_code"),
    filter_input(INPUT_POST, "form_i1subscriber_employer_state"),
    filter_input(INPUT_POST, "form_i1subscriber_employer_country"),
    filter_input(INPUT_POST, 'i1copay'),
    filter_input(INPUT_POST, 'form_i1subscriber_sex'),
    $i1date,
    filter_input(INPUT_POST, 'i1accept_assignment')
);


$i2dob = DateToYYYYMMDD(filter_input(INPUT_POST, "i2subscriber_DOB"));
$i2date = DateToYYYYMMDD(filter_input(INPUT_POST, "i2effective_date"));

newInsuranceData(
    $pid,
    "secondary",
    filter_input(INPUT_POST, "i2provider"),
    filter_input(INPUT_POST, "i2policy_number"),
    filter_input(INPUT_POST, "i2group_number"),
    filter_input(INPUT_POST, "i2plan_name"),
    filter_input(INPUT_POST, "i2subscriber_lname"),
    filter_input(INPUT_POST, "i2subscriber_mname"),
    filter_input(INPUT_POST, "i2subscriber_fname"),
    filter_input(INPUT_POST, "form_i2subscriber_relationship"),
    filter_input(INPUT_POST, "i2subscriber_ss"),
    $i2dob,
    filter_input(INPUT_POST, "i2subscriber_street"),
    filter_input(INPUT_POST, "i2subscriber_postal_code"),
    filter_input(INPUT_POST, "i2subscriber_city"),
    filter_input(INPUT_POST, "form_i2subscriber_state"),
    filter_input(INPUT_POST, "form_i2subscriber_country"),
    filter_input(INPUT_POST, "i2subscriber_phone"),
    filter_input(INPUT_POST, "i2subscriber_employer"),
    filter_input(INPUT_POST, "i2subscriber_employer_street"),
    filter_input(INPUT_POST, "i2subscriber_employer_city"),
    filter_input(INPUT_POST, "i2subscriber_employer_postal_code"),
    filter_input(INPUT_POST, "form_i2subscriber_employer_state"),
    filter_input(INPUT_POST, "form_i2subscriber_employer_country"),
    filter_input(INPUT_POST, 'i2copay'),
    filter_input(INPUT_POST, 'form_i2subscriber_sex'),
    $i2date,
    filter_input(INPUT_POST, 'i2accept_assignment')
);

$i3dob  = DateToYYYYMMDD(filter_input(INPUT_POST, "i3subscriber_DOB"));
$i3date = DateToYYYYMMDD(filter_input(INPUT_POST, "i3effective_date"));

newInsuranceData(
    $pid,
    "tertiary",
    filter_input(INPUT_POST, "i3provider"),
    filter_input(INPUT_POST, "i3policy_number"),
    filter_input(INPUT_POST, "i3group_number"),
    filter_input(INPUT_POST, "i3plan_name"),
    filter_input(INPUT_POST, "i3subscriber_lname"),
    filter_input(INPUT_POST, "i3subscriber_mname"),
    filter_input(INPUT_POST, "i3subscriber_fname"),
    filter_input(INPUT_POST, "form_i3subscriber_relationship"),
    filter_input(INPUT_POST, "i3subscriber_ss"),
    $i3dob,
    filter_input(INPUT_POST, "i3subscriber_street"),
    filter_input(INPUT_POST, "i3subscriber_postal_code"),
    filter_input(INPUT_POST, "i3subscriber_city"),
    filter_input(INPUT_POST, "form_i3subscriber_state"),
    filter_input(INPUT_POST, "form_i3subscriber_country"),
    filter_input(INPUT_POST, "i3subscriber_phone"),
    filter_input(INPUT_POST, "i3subscriber_employer"),
    filter_input(INPUT_POST, "i3subscriber_employer_street"),
    filter_input(INPUT_POST, "i3subscriber_employer_city"),
    filter_input(INPUT_POST, "i3subscriber_employer_postal_code"),
    filter_input(INPUT_POST, "form_i3subscriber_employer_state"),
    filter_input(INPUT_POST, "form_i3subscriber_employer_country"),
    filter_input(INPUT_POST, 'i3copay'),
    filter_input(INPUT_POST, 'form_i3subscriber_sex'),
    $i3date,
    filter_input(INPUT_POST, 'i3accept_assignment')
);
?>
<html>
<body>
<script language="Javascript">
<?php
if ($alertmsg) {
    echo "alert('" . addslashes($alertmsg) . "');\n";
}

  echo "window.location='$rootdir/patient_file/summary/demographics.php?" .
    "set_pid=" . attr($pid) . "&is_new=1';\n";
?>
</script>

</body>
</html>

