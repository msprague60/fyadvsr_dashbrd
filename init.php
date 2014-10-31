<?php

  // Do all the initialization tasks here

  // Remember that the session start should be called before any other output is sent to the browser.

session_start();

if ( !isset($skip_header) ) {
    include_once "includes/header.php";
 }

// Is it time to log the person out?

if(isset($_SESSION['timeout'])) {
  check_timeout();
 }

/* Uncomment the following to restrict the app from being used only from 
   Wellesley network. If you want specific functions protected, copy this to the beginning of those functions.

if (isInternal() == 0) {
  print "Admin functions are allowed only from Wellesley Campus or through VPN. " .
    "<a href=\"https://vpn.wellesley.edu\">Click here to connect to VPN.</a>\n";
 }

*/

// Set the timeout timer to current time.

$_SESSION['timeout']=time();

// Decide whether to send the user to the default page or not.

$do_default = 0;

if(!isset($_SESSION['loggedInSessionFlag'])) {
  include "verify_login.php";
  $do_default = 1;
 }

// Login to mysql DB

$mysql = new mysql_functions(MYSQL_SRV,MYSQL_USER,MYSQL_PWD,MYSQL_NAME,MYSQL_CHARSET);
$mysql1 = new mysql_functions(MYSQL_SRV1,MYSQL_USER1,MYSQL_PWD1,MYSQL_NAME1,MYSQL_CHARSET1);

$mysql_link = $mysql->connectToDb();
$mysql_link1 = $mysql1->connectToDb();

// MYSQL role checker only checks the admin table. 

if (trim($_SESSION['role']) == '') {
  $my_role = $mysql->getRole();
  $_SESSION['role'] = $my_role;
 }
 else {
   $my_role = $_SESSION['role'];
 }

$local_mysql = new local_mysql_functions($mysql_link, $mysql);
$local_mysql1 = new local_mysql_functions($mysql_link1, $mysql1);

// Login to Oracle DB

if (defined('ORACLE_SRV')) {
  
  $oracle = new oracle_functions(ORACLE_SRV,ORACLE_USER,ORACLE_PWD,ORACLE_NAME,ORACLE_CHARSET);
  
  $oracle_link = $oracle->connectToDb();
  

  // Get all relevant info for the person logged in in the Faculty Staff table.
  
  $facstaff_info = array();
  $oracle->facstaffLookup($_SESSION['username'] . "@wellesley.edu",
			  $facstaff_info,'primary_email','Y');

  $local_oracle = new local_oracle_functions($oracle_link, $oracle);

if (defined('ORACLE_SRV1')) {

    $oracle1 = new oracle_functions(ORACLE_SRV1,ORACLE_USER1,ORACLE_PWD1,ORACLE_NAME1,ORACLE_CHARSET1);

    $oracle_link1 = $oracle1->connectToDb();

    $course_class = new courses($oracle_link1,$oracle1,'future');

    $local_oracle1 = new local_oracle_functions($oracle_link1, $oracle1);

        if(empty($facstaff_info))
	  $local_oracle->cwLookup($facstaff_info,$_SESSION['username']);
    
 }

  // If it is a student, do student lookup.

  /*
   $student_info = array();
   $oracle->studentLookup($_SESSION['username'] . "@wellesley.edu",
   $student_info,'primary_email','Y');

  */

  // If you want additional protection of allowing only faculty or staff, uncomment below
  /*
  // If the person is not a faculty or staff, set the role to be null so they are not allowed in.
  
  if (sizeof($facstaff_info) == 0) {
    $my_role = "";
  }

  */
 }


// If the person is not qualified to run the app, kick the person out.

if ($my_role == "") {
  print "<br><br<Br>You do not have access to this application. <a href=\"logout.php\">Click here to logout.</a>";
  $_SESSION = array();
  session_destroy();
  exit;
 }

// Set the Dean role if the user is a Dean.

//if (in_array($_SESSION['username'],$deans)) {
//  $my_role = DEAN_ID;
// }

// This section provides an opportunity for using role based default page redirection.
/*
$role_name = array_keys($roles,$my_role);

$default = DEFAULT_PAGE;
if ($default_pages[$role_name[0]] != "") {
  $default = $default_pages[$role_name[0]];
 }

*/

// If default is to be shown, do so.


//Code for Redirecting based on Maintenance Mode.  Only admins are allowed to access site while in admin mode.
if(MAINTENANCE_MODE)
{ 
	 if($my_role == ADMIN_ID || $_SESSION['proxy'])
	 {
	 	if($do_default == 1)
	 	{
		 	include('checklist.php');
		 	exit;
	 	}
	 } else {
		 include('maintenance.php');
		 exit;
	 }
} else {
	if ($do_default == 1) {
	  include(DEFAULT_PAGE);
	  exit;
	 }
}



?>
