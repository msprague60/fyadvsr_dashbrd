<?php

$no_navs = 1;
$skip_grapics = 1;
$skip_header = 1;

include_once('config.php');

$advisor = $_SESSION['username'];
//$advisor = 'avelench';
// mms hardcode term for now
$term = '201409';

$name = strtolower($_GET['name']);

$temp1 = array();
$local_oracle->getPidmByUser($temp1, $advisor);

$adv_pidm = $temp1[0]['PIDM'];

if(strlen($name) < 3)
{
	echo "You must type at least 3 characters";
	exit;
} else {
	$info = array();
        if ($my_role == ADMIN_ID) {
	  $local_oracle1->AdminFirstYearStudentsLookup($info,$name,$term);
	}
	else {
	  $local_oracle1->FirstYearStudentsLookup($info,$adv_pidm,$name,$term);
	}
	//	print_r($info);

	foreach($info as $student)
	{
		/* $parts = explode('@', $student['EMAIL']);
		echo "<a href=\"javascript:getStudentResult('$parts[0]', '$report')\">".$student['LAST_NAME'].", ".$student['FIRST_NAME'] . " (" . $student['EMAIL'] . ")" . " - " . $student['ID'];
		echo "<br>"; */
		
			$parts = explode('@', $student['EMAIL']);
			echo "<a href=\"gen_info.php?name=$parts[0]\">".$student['LAST_NAME'].", ".$student['FIRST_NAME'] . " (" . $student['EMAIL'] . ")" . " - " . $student['ID'];
			echo "<br>"; 
	}
}