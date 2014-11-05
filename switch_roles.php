<?php


// Handle the admins.and other roles.

include_once "config.php";

printPageTitle("Switch Roles","If you have been assigned to multiple roles in the application, you can use this to switch roles");

printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);

// Make sure that the user is allowed to do amin functions



$submit = $_POST['submit'];

// If submitted, insert the roles and associated members.

if ($submit != "") {
  $role = $_POST['role'];
  //  echo "role=".$role;
  if (trim($role) != '') {
    if ($role == DEFAULT_ID) {
      $_SESSION['role'] = $role;
      printStatus("Successfully updated the information.");
    }
    else {
      if ($mysql->isValidRole($role) == 1) {
	$_SESSION['role'] = $role;
	printStatus("Successfully updated the information.");
      }
      else {
	printStatus("Role switching failed");
      }
    }
  }

 }

// Sort the roles by values and get the members for each

$data = array();
$mysql->getAllMyRoles($data);

$my_role = $_SESSION['my_role'];
//echo "my_role=".$my_role;

if (sizeof($data) == 1) {
  print "<center><font=\"#cc0000\">You have only one role, so you cannot switch to other roles.</font>\n</center>\n";
  exit;
 }
 else {
   print "<form action='' method=post>\n";
   print "<center>\n";
   print "Please choose a role to switch to";
   print "<table>\n";
   $r_roles = array();
   foreach ($roles as $key=>$value) {
     $r_roles[$value] = $key;
   }
   foreach ($data as $d) {
     $checked = "";
     if ($my_role == $d) {
       $checked = " checked ";
     }
     print "<tr>\n";
     print "<td>\n<input $checked type=radio name=role value=$d>$r_roles[$d]</input></td>\n";
     print "</tr>\n";
   }
   print "</table>\n";
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





