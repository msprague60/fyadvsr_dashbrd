<?php


// Handle the admins.and other roles.

include_once "config.php";

printPageTitle("Manage Administrators and Reviewers","Please make changes and hit submit. Do not add any user to both. Also, your username will not appear. You cannot remove yourself from the reviewer or admin list. Leave Regular User data blank if you want to open this up to any validated user.");
printNavigation1($my_role, $roles, $role_titles, $master_navigations);

// Make sure that the user is allowed to do amin functions

if ($my_role != ADMIN_ID) {
  print "You are not allowed access to this link.\n";
  include "logout.php";
  exit;
 }


$submit = $_POST['submit'];

// If submitted, insert the roles and associated members.

if ($submit != "") {
  foreach ($roles as $r) {
    $memberlist = $_POST['role_' . $r];
    $mysql->insertUsersAdmins($r, $memberlist);
  }
  printStatus("Successfully updated the information.");
 }

// Sort the roles by values and get the members for each

$sroles = $roles;
asort($sroles);

foreach ($sroles as $key=>$value) {
  $members[$value] = array();
}

$mysql->getAdminData( $members);

?>
<form method=post action="admin.php">
<center>
<table>

<?php

 // print the title for each role and the corresposnding text area with members.
 // Dean role is special. skip it.

foreach ($sroles as $key=> $value) {
  if ($key == 'dean') {
    continue;
  }

  print "<tr>\n<td>\n";
  print $role_titles[$key] . "\n";
  print "</td>\n<td>\n<textarea name=\"role_" . $value. "\"  cols=80 rows=5>\n";
  print implode($members[$value],",");
  print "</textarea>\n</td>\n</tr>\n";
}
?>

<tr>
<td colspan=2 align=center>
<input type="submit" name="submit" value="submit"></input>
</td></tr>
</table></center>
</form>

<?php
include_once "includes/footer.php";
?>





