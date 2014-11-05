<?php

$no_navs = 1;
$skip_grapics = 1;
$skip_header = 1;

include_once('config.php');

$name = strtolower($_GET['name']);
$report = strtolower($_GET['report']);

if(strlen($name) < 3)
{
	echo "You must type at least 3 characters";
	exit;
} else {
	$info = array();
	$oracle->enteringStudentsLookup($name,$info,'sdspers.last_name', 'N');
	
	foreach($info as $student)
	{
		/* $parts = explode('@', $student['EMAIL']);
		echo "<a href=\"javascript:getStudentResult('$parts[0]', '$report')\">".$student['LAST_NAME'].", ".$student['FIRST_NAME'] . " (" . $student['EMAIL'] . ")" . " - " . $student['ID'];
		echo "<br>"; */
		
		if($report == '2')
		{
			$parts = explode('@', $student['EMAIL']);
			echo "<a href=\"advising_report.php?name=$parts[0]\">".$student['LAST_NAME'].", ".$student['FIRST_NAME'] . " (" . $student['EMAIL'] . ")" . " - " . $student['ID'];
			echo "<br>"; 
		}
		
		if($report == '3')
		{
			$parts = explode('@', $student['EMAIL']);
			echo "<a href=\"exam_signup_report.php?name=$parts[0]\">".$student['LAST_NAME'].", ".$student['FIRST_NAME'] . " (" . $student['EMAIL'] . ")" . " - " . $student['ID'];
			echo "<br>";
		}
	}
}