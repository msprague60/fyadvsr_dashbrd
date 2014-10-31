<?php


require_once "config.php";

//http://us.php.net/session_destroy

// Initialize the session.
// If you are using session_name("something"), don't forget session_start();!

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Finally, destroy the session.
session_destroy();

//If MAINTENANCE_MODE is on don't show the login page when a non-admin is automatically logged out
if(!MAINTENANCE_MODE)
{
	include_once(LOGIN_PAGE);
} 

?>