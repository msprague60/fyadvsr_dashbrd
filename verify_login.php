<?php

include_once "config.php";

// Check to see if the session variable is set or not.

if(!isset($_SESSION['loggedInSessionFlag'])) {

  
  $luminis = new login("","",LOGFILE);

  $results = $luminis->luminisSSO();
  if ($results == "") {
    return;
  }

  // If the username and password are not set, display the login form
  
  if(!isset($_POST['j_username']) ||  !isset($_POST['j_password']))
    {
      //display login form then stop here
      
      include ("includes/login_form.php");
      exit;
    }
  
  $_POST['j_username'] = trim($_POST['j_username']);
  $_POST['j_password'] = trim($_POST['j_password']);
  
  
  
  // If either the username or the password is blank,
  // throw an error
  
  if($_POST['j_username']==""  ||  $_POST['j_password']=="")
    {
      //display login form then stop here
      $errorMsg = "You must enter text in both the<br>username and password fields.";
      include ("includes/login_form.php");
      exit;
      
    }

  // Login as the user him/her self.

  $login = new login($_POST['j_username'],
		     $_POST['j_password'],
		     LOGFILE
		     );
  
  $errorMsg = $login->login();

  // If that fails, try proxies

  if ($errorMsg != "") {
    $errorMsg = $login->login($proxies);
  }


  if ($errorMsg == "") {
    return;
  }
  else {
    include ("includes/login_form.php");
    exit;
  }
 }
 else {
  include(DEFAULT_PAGE);
  exit;
 }

?>
