<?php

  // Allows Dean's Office to load first year advisor data spreadsheet into Banner table sgradvr.

include_once "config.php";

printPageTitle("Upload First Year Advisor Assignments","Please upload an Excel CSV file containing advisor assignments");

printNavigation($my_role, $roles, $role_titles, $master_navigations,1);

// Make sure that the user is allowed to do admin functions

if ($my_role != ADMIN_ID) {
  print "You are not allowed access to this link.\n";
  include "logout.php";
  exit;
 }

$submit = $_POST['submit'];
if ($submit != "") {
  if (isset($_FILES['advinfo'])){
    $file = safeString($_FILES['advinfo'][tmp_name],1);
    readParseCSV($file,$data);
    $error = "";
    for ($i = 1; $i < sizeof($data); $i++) {
      $incomplete = 0;
      if (trim($data[$i][0]) == "") {
	  $incomplete = 1;
	}
      if (trim($data[$i][1]) == "") {
	  $incomplete = 1;
	}
      if ($incomplete == 1) {
	$string = join(",",$data[$i]);
        $rn = $i + 1;
	$error .= "row $rn is incomplete: " . $string . "<br>\n";
      }
    }

    //    print_r($data);
    
    // mms hardcode term for now
    $term = '201409';

    if ($error == "") {
      
      $sql = "merge into sgradvr s
              using (select :sid student_id,
                            :aid advisor_id,
                            :term term_code from dual) v
              on (s.sgradvr_pidm = (select distinct spriden_pidm from spriden where spriden_id = v.student_id)
              and s.sgradvr_advr_code = 'FYAA'
              and s.sgradvr_term_code_eff = v.term_code)
              when matched then update 
                         set s.sgradvr_advr_pidm = (select distinct spriden_pidm from spriden where spriden_id = v.advisor_id),
                             s.sgradvr_activity_date = sysdate,
                             s.sgradvr_prim_ind = 'Y'
              when not matched then insert 
                             (s.sgradvr_pidm,
                              s.sgradvr_term_code_eff,
                              s.sgradvr_advr_pidm,
                              s.sgradvr_advr_code,
                              s.sgradvr_prim_ind,
                              s.sgradvr_activity_date)
                       values((select distinct spriden_pidm from spriden where spriden_id = v.student_id),
                              v.term_code,
                              (select distinct spriden_pidm from spriden where spriden_id = v.advisor_id),
                              'FYAA',
                              'Y',
                              sysdate)";


      for ($i = 0; $i < sizeof($data); $i++) {  

                $stmt = oci_parse($oracle_link1,$sql) or die ("Error in parsing SQL in advisor table insertion " );

                oci_bind_by_name($stmt, ":sid",$data[$i][0], -1) or die ("Error in binding sid in advisor table insertion");
                oci_bind_by_name($stmt, ":aid",$data[$i][1], -1) or die ("Error in binding aid in advisor table insertion");
                oci_bind_by_name($stmt, ":term",$term, -1) or die ("Error in binding term in advisor table insertion");

	      oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing insertion sql in advisor table "); 
	      print "Successfully inserted advisor $i<br>\n";
	      }
      print "</center>\n";
      oci_commit($oracle_link1);

    //   $sql = "begin szkbreg.p_regs; end;";
    //   $stmt = oci_parse($oracle_link1,$sql) or die ("Error in parsing SQL to execute szkbregs.p_reg " );
    //   oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing SQL to execute szkbregs.p_reg "); 
      print "Successfully uploaded data to Banner.<br>\n";

    }

  }

 }
?>

<form enctype="multipart/form-data" method=post action="">
<center>
<table>
<tr><td colspan=2>
<b>Please note that this upload will insert or update information in the Banner advisor table.</b>
<?php
if ($error != "") {
  print "<tr><td colspan=2><font color=\"#ff0000\">$error</font></td>\n</tr>\n";
}

print "<tr>\n<td>\n";
//print "Choose the Schedule CSV File (Remember that this will enter data in Production Banner. Are you sure?):\n";
print "Choose the Schedule CSV File:\n";
print "</td>\n<td>\n<input type=file  name=\"advinfo\"\n";
print "</td>\n</tr>\n";

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


