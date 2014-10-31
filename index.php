<?php

  // Just a placeholder. Send it to verify_login

include "config.php";
include "verify_login.php";

// This section provides an opportunity for using role based default page redirection.
/*
$role_name = array_keys($roles,$my_role);

$default = DEFAULT_PAGE;
if ($default_pages[$role_name[0]] != "") {
  $default = $default_pages[$role_name[0]];
 }

*/

if(isset($_SESSION['loggedInSessionFlag'])) {
  include(DEFAULT_PAGE);
 } 

?>