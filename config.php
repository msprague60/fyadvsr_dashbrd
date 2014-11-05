<?php

  // Application creator: Mary Sprague

ini_set('include_path', '/var/apache/wellesley');
include_once "global_config.php";
include_once "local_mysql_functions.php";
include_once "local_oracle_functions.php";
include_once "local_utilities.php";

define ("LOGIN_PAGE", "verify_login.php");
define ("LOGFILE","/var/phpapps/logs/fyadvsr_dashbrd/log");
define ("DEFAULT_PAGE", "gen_info.php");
define ("MAXNAVIGATIONCOLS",3);
define ("FEEDBACK", "jokeefe");
define("MAINTENANCE_MODE",false);

// This defines the role id for allowing any user who logged in successfully 
// to access the system. IMPORTANT: DO NOT REMOVE THIS LINE. Instead set it to ""

define ("DEFAULT_ID" , "1");


// Idle Timeout of 20 minutes is defined in global config. 
// Override it here by uncommenting. Second parameter is in seconds, so this is for 3 hours.

 
define ('APPLICATION_SESSION_TIMEOUT','3600');


// Get all MySQL information from the data file.

if (!file_exists("/var/secure/fyadvsr_dashbrd/mysql")) {
  print "Error: unable to find MYSQL information.\n";
  exit;
 }

$infoDB=file("/var/secure/fyadvsr_dashbrd/mysql");

for ($i = 0; $i <sizeof($infoDB); $i++) {
  $infoDB[$i] = trim($infoDB[$i]);
 }

define ("MYSQL_SRV", $infoDB[0]);
define ("MYSQL_USER", $infoDB[1]);
define ("MYSQL_PWD", $infoDB[2]);
define ("MYSQL_NAME", $infoDB[3]);
define ("MYSQL_CHARSET",$infoDB[4]);

if (file_exists("/var/secure/fyadvsr_dashbrd/mysql1")) {

$infoDB=file("/var/secure/fyadvsr_dashbrd/mysql1");

for ($i = 0; $i <sizeof($infoDB); $i++) {
  $infoDB[$i] = trim($infoDB[$i]);
 }

define ("MYSQL_SRV1", $infoDB[0]);
define ("MYSQL_USER1", $infoDB[1]);
define ("MYSQL_PWD1", $infoDB[2]);
define ("MYSQL_NAME1", $infoDB[3]);
define ("MYSQL_CHARSET1",$infoDB[4]);
 }

if (file_exists("/var/secure/fyadvsr_dashbrd/oracle")) {

  // Get all Oracle information from the data file.
  
  $infoDB = array();
  $infoDB = file("/var/secure/fyadvsr_dashbrd/oracle");
  
  for ($i = 0; $i <sizeof($infoDB); $i++) {
    $infoDB[$i] = trim($infoDB[$i]);
  }
  
  define ("ORACLE_SRV", $infoDB[0]);
  define ("ORACLE_USER", $infoDB[1]);
  define ("ORACLE_PWD", $infoDB[2]);
  define ("ORACLE_NAME", $infoDB[3]);
  define ("ORACLE_CHARSET",$infoDB[4]);
 }
 
if (file_exists("/var/secure/fyadvsr_dashbrd/oracle1")) {

  // Get all Oracle information from the data file.
  
  $infoDB = array();
  $infoDB = file("/var/secure/fyadvsr_dashbrd/oracle1");
  
  for ($i = 0; $i <sizeof($infoDB); $i++) {
    $infoDB[$i] = trim($infoDB[$i]);
  }
  
  define ("ORACLE_SRV1", $infoDB[0]);
  define ("ORACLE_USER1", $infoDB[1]);
  define ("ORACLE_PWD1", $infoDB[2]);
  define ("ORACLE_NAME1", $infoDB[3]);
  define ("ORACLE_CHARSET1",$infoDB[4]);
 }

 // Get Security Crypto Code

 if(file_exists("/var/secure/fyadvsr_dashbrd/crypto"))
 {
 	$info = array();
 	$info = file("/var/secure/fyadvsr_dashbrd/crypto");

 	for($i = 0; $i < sizeof($info); $i++)
 	{
 	$info[$i] = trim($info[$i]);
 	}
 
 	define('SECPHRASE', $info[0]);
 	}


define ("APP_TITLE", "First Year Advisor Dashboard");

$proxies = array('msprague','jokeefe');

/* Define Roles and Role Titles. Leave ADMIN_ID alone. Example roles are commented.*/
// Please note that if you define a DEFAULT_ID that is not null, like 1, you need to define roles 
// and other role related information below.


$roles = array (
                'advisor' => "1",
		'admin' => ADMIN_ID
		);


// Define names for each of the roles. Examples are commented.

$role_titles = array (
		      /* 'facstaff' => 'Faculty/Staff',
		       'chair' => 'Chair',
		       'dean' => 'Dean',
		      */
                      'advisor' => 'Advisor',
		      'admin' => 'Administrator'
		      );

// Define Navigations. For each role, create an array which is a sequence of 
// name and url. Then they are associated with appropriate role id's in master navigations.


$admin_navigations[$i]['name'] = 'Manage Roles';
$admin_navigations[$i]['url'] = 'admin.php';

$generic_navigations[$i]['name'] = 'Logout';
$generic_navigations[$i]['url'] = 'logout.php';

$advisor_navigations[$i]['name'] = 'Logout';
$advisor_navigations[$i]['url'] = 'logout.php';

$i++;
$admin_navigations[$i]['name'] = 'Upload Advisors';
$admin_navigations[$i]['url'] = 'upload_advisors_banner.php';

$advisor_navigations[$i]['name'] = 'Advising Homepage';
$advisor_navigations[$i]['url'] = 'gen_info.php';

$generic_navigations[$i]['name'] = 'Advising Homepage';
$generic_navigations[$i]['url'] = 'gen_info.php';

$i++;
$admin_navigations[$i]['name'] = 'Advising Homepage';
$admin_navigations[$i]['url'] = 'gen_info.php';

$advisor_navigations[$i]['name'] = 'View Mid Sememster Reports';
$advisor_navigations[$i]['url'] = 'https://sparrow.wellesley.edu/mid_semester_rpt/ms_stu_report.php';

$i++;
$admin_navigations[$i]['name'] = 'Advising Dashboard';
$admin_navigations[$i]['url'] = 'all_info.php';

$advisor_navigations[$i]['name'] = 'Advising Dashboard';
$advisor_navigations[$i]['url'] = 'all_info.php';

$i++;
$admin_navigations[$i]['name'] = 'View Mid Sememster Reports';
$admin_navigations[$i]['url'] = 'https://sparrow.wellesley.edu/mid_semester_rpt/ms_stu_report.php';

$i++;
$admin_navigations[$i]['name'] = 'Switch Roles';
$admin_navigations[$i]['url'] = 'switch_roles.php';

$i++;
$admin_navigations[$i]['name'] = 'Logout';
$admin_navigations[$i]['url'] = 'logout.php';

// $master_navigations will be used later, and it contains an array that says which array contains
// navigations for which role.

$master_navigations = array (
                             'advisor' => $advisor_navigations,
			     'admin' => $admin_navigations
			     );

error_reporting(E_ERROR);

// If you want to redirect the users to different start pages depending on their role, do it here.

/*
$default_pages = array ('facstaff' => 'show_form.php',
			'chair' => 'list_submissions.php',
			'dean' => 'list_submissions.php',
			'admin' => 'list_submissions.php'
			);
*/
// App specific definitions

//define('PDFDIR' , "/var/phpapps/data/oir/senior_survey/2009-2010");

include_once "init.php";
?>
