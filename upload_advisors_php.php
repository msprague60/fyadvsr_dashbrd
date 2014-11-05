<?php


// Handle the tags

include_once "config.php";

printPageTitle("First Year Advisors","Please prepare a CSV file with the email usernames of the students and the advisor and upload it.");

printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);

// Make sure that the user is allowed to do amin functions

if ($my_role != ADMIN_ID) {
  print "You are not allowed access to this link.\n";
  include "logout.php";
  exit;
 }

$submit = $_POST['submit'];

// If submitted, insert the roles and associated members.


if ($submit != "") {

  if(isset($_FILES['advisors'])){
    $file = safeString($_FILES['advisors'][tmp_name],1);
    readParseCSV($file,$data);
    $error = "";
    for ($i = 1; $i < sizeof($data); $i++) {
      if ( (trim($data[$i][0]) == "")||
	   (trim($data[$i][1]) == "")
	   ) {
	$error .= "row: #$i is incomplete: " . $data[$i][0] . "," .
	  $data[$i][1] .
	  "<br>\n";
      }
    }

    $ndata = array();
    for ($i = 1; $i < sizeof($data); $i++) {
      if ( (trim($data[$i][0]) != "")&&
	   (trim($data[$i][1]) != "")
	   ) {
	$ndata[$i-1][0] = $data[$i][0];
	$ndata[$i-1][1] = $data[$i][1];
      }
    }
    // mms hardcode semester for now
    $semester = 'Fall 2014';

    insertAdvisors($mysql, $mysql_link1, $ndata, $semester);
    
    
    if ($error != "") {
      printStatus("Uploaded records except the ones listed below with errors");
      print "<center><font color=\"#ff0000\">$error</font></center>\n";
    }
    else {
      printStatus("Successfully uploaded advisors file.");
    }
    
    getAdvisors($mysql, $mysql_link1, $data);
    printAdvisors($data);
    exit;
  }
 }

?>
<form enctype="multipart/form-data" method=post action="">
<center>
<table>
<?php
if ($error != "") {
  print "<tr><td colspan=2><font color=\"#ff0000\">$error</font></td>\n</tr>\n";
}


 // print the tags

print "<tr>\n<td>\n";
print "Choose the Advisors CSV File:\n";
print "</td>\n<td>\n<input type=file  name=\"advisors\"\n";
print "</td>\n</tr>\n";

?>

<tr>
<td colspan=2 align=center>
<input type="submit" name="submit" value="submit"></input>
</td></tr>
</table></center>
</form>

<?php

print "<center>\n";
print "Current Data in the Table\n";
getAdvisors($mysql, $mysql_link1, $data);
printAdvisors($data);
print "</center>\n";

include_once "includes/footer.php";

function insertAdvisors($mysql, $mysql_link1, $data, $semester) {
  
  $sql = "delete from advisors ";
  $stmt = $mysql_link1->prepare($sql) or die ("Error in preparing DB statement in deleting entries in advisors table");
  $stmt->execute() or die ("Error executing a sql statement in deleting from the advisors table");
  $stmt->close();
  
  $sql = "insert into advisors values (?,?,?)";
  $stmt = $mysql_link1->prepare($sql) or die ("Error in preparing DB statement in inserting into advisors table");
  $k = 0;

  foreach ($data as $d) {
    $stmt->bind_param("sss", $d[0], $d[1], 
		      $semester);	      
    $stmt->execute() or die ("Error executing a sql statement in inserting into advisors table");
  }
  $stmt->close();
}

  // Get all data from Advisors table

function getAdvisors($mysql, $mysql_link1, &$data) {
  $data = array();
  $sql = "select * from advisors ";
  $result = $mysql_link1->query($sql) or die ("Error accessing info from advisors database table");
  $i = 0;
  while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    foreach ($row as $key => $value) {
      $data[$i][$key] = $value;
    }
    $i++;
  }
}


function printAdvisors($data) {
  
  if (sizeof($data) == 0) {
    print "<br>\nNo data Found";
    return;
  }
  
  $columns = array_keys($data[0]);
  
  $t_columns = array();
  
  foreach ($columns as $key=>$value) {
    $t_columns[$key]['key'] = $value;
    $t_columns[$key]['label'] = $value;
    $t_columns[$key]['sortable'] = "true";
    $t_columns[$key]['width'] = '100';
    $t_columns[$key]['className'] = '';
  }
  
  $yui = new yuitable();
  $yui->setColumns($t_columns);
  print "<center>\n";
  
  $yui->table($data, $urls, $sort);
  $yui->printFooter();
  
  print "</center>\n";
  
}


?>




