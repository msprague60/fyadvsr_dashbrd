<?php

//Handles Display setup of page
if (!isset($_POST['no_navs'])) {
	$_POST['no_navs'] = "";
}

if (!isset($_POST['skip_graphics'])) {
	$_POST['skip_graphics'] = "";
}

if (!isset($no_navs)) {
	$no_navs = ($_POST['no_navs']?$_POST['no_navs']:$_GET['no_navs']);
}

if (!isset($skip_graphics)) {
	$skip_graphics = ($_POST['skip_graphics']?$_POST['skip_graphics']:$_GET['skip_graphics']);
}

include('config.php');

?>
<script type="text/javascript" src='js/reports.js'></script>
<style>
#formtable th {display:none};
</style>
<?php 

//Kick out non-admin users
 //if($my_role != ADMIN_ID && $my_role != 1)
 //{
 //	echo "<center>You are not allowed to access this page.<br><a href='logout.php'>Logout Here</a></center>";
 //	exit;
 //}

if ($no_navs == "") {
	printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);
}

printPageTitle("Advisee Lookup","<center>Use the form below to view general information about a specific student you are advising.</center>");

//$advisor = 'avelench';
// mms hardcode term for now
$term_code = '201409';

$my_role = $_SESSION['my_role'];
$advisor = $_SESSION['username'];

$uname = $_POST['advisee_selected']?$_POST['advisee_selected']:$_GET['advisee_selected'];
if ($uname == '') {
  $uname = $_GET['name'];
 }

$a_selected = $uname;
//echo "uname=".$uname;
//echo "my_role=".$my_role;

$temp1 = array();
$local_oracle->getPidmByUser($temp1, $advisor);
$adv_pidm = $temp1[0]['PIDM'];

$advisees = array();
if ($my_role == ADMIN_ID) {
$local_oracle1->getAllStudents($advisees, $term_code);
 }
 else {
$local_oracle1->getStudentsbyAdvisor($advisees, $adv_pidm, $term_code);
 }

if(isset($uname))
{
	
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);

        $local_mysql1->getPreferredPronoun($pronoun,$uname);

        $temp2 = array();
        $local_oracle1->getAdvisor($temp2,$uname,$term_code);
        $stu_advisor = $temp2[0]['ADVISOR_NAME'];

	//	echo 'pronoun='.$pronoun;

	foreach($values as $key=>$value)
	{
		${$key} = $value;
	}
	
	$data = array();
	$urls = array();
	$sort = array();
	$columns = array('one'=>'one', 'two'=>'two');
	$i = 0;
	
	$data[$i]['one'] = "<b>Banner ID</b>";
	$data[$i]['two'] = $student_info[0]['ID'];
	$i++;
	
	$data[$i]['one'] = "<b>Student Name</b>";
	$data[$i]['two'] = $student_info[0]['CURRENT_NAME'];
	$i++;
	
	$data[$i]['one'] = "<b>Preferred First Name</b>";
	$data[$i]['two'] = $student_info[0]['PREF_FIRST_NAME'];
	$i++;
	
	$data[$i]['one'] = "<b>Home Address</b>";
	$data[$i]['two'] = $student_info[0]['HOME_STREET_LINE1'];

        if ($student_info[0]['HOME_STREET_LINE2'] != "")
	  {
	    $data[$i]['two'] = $data[$i]['two'] . "<br>" . $student_info[0]['HOME_STREET_LINE2'];
	  }
        if ($student_info[0]['HOME_STREET_LINE3'] != "")
	  {
	    $data[$i]['two'] = $data[$i]['two'] . "<br>" . $student_info[0]['HOME_STREET_LINE3'];
	  }
	$i++;
	
	//	$data[$i]['one'] = " ";
	//	$data[$i]['two'] = $student_info[0]['HOME_STREET_LINE2'];
	//	$i++;
	
	//	$data[$i]['one'] = " ";
	//	$data[$i]['two'] = $student_info[0]['HOME_STREET_LINE3'];
	//	$i++;

	$data[$i]['one'] = " ";
	$data[$i]['two'] = $student_info[0]['HOME_CITY'];
	$i++;

	$data[$i]['one'] = " ";
	$data[$i]['two'] = $student_info[0]['HOME_STATE'] . ", " . $student_info[0]['HOME_ZIP'];
	$i++;
	
	$data[$i]['one'] = " ";
	$data[$i]['two'] = $student_info[0]['HOME_NATION_DESC'];
	$i++;

	$data[$i]['one'] = "<b>Wellesley Email</b>";
	$data[$i]['two'] = $student_info[0]['WEL_EMAIL'];
	$i++;

	$data[$i]['one'] = "<b>Alternate Email</b>";
	$data[$i]['two'] = $student_info[0]['GEN_EMAIL'];
	$i++;

	$data[$i]['one'] = "<b>Home Phone</b>";
	$data[$i]['two'] = $student_info[0]['HOME_PHONE_NUMBER'];
	$i++;

	$data[$i]['one'] = "<b>Cell Phone</b>";
	$data[$i]['two'] = $student_info[0]['CELL_PHONE_NUMBER'];
	$i++;

	$data[$i]['one'] = "<b>Citizen Code</b>";
	$data[$i]['two'] = $student_info[0]['CITIZEN_CODE'];
	$i++;
	
	$data[$i]['one'] = "<b>High School</b>";
	$data[$i]['two'] = $student_info[0]['HIGH_SCHOOL'];
	$i++;

	$data[$i]['one'] = "<b>Address</b>";
	$data[$i]['two'] = $student_info[0]['HS_STREET_LINE_1'];
	$i++;
	
        if ($student_info[0]['HS_STREET_LINE_2'] != "")
	  {
	    $data[$i]['two'] = $data[$i]['two'] . "<br>" . $student_info[0]['HS_STREET_LINE_2'];
	  }
        if ($student_info[0]['HS_STREET_LINE_3'] != "")
	  {
	    $data[$i]['two'] = $data[$i]['two'] . "<br>" . $student_info[0]['HS_STREET_LINE_3'];
	  }

	//	$data[$i]['one'] = " ";
	//	$data[$i]['two'] = $student_info[0]['HS_STREET_LINE_2'];
	//	$i++;
	
	//	$data[$i]['one'] = " ";
	//	$data[$i]['two'] = $student_info[0]['HS_STREET_LINE_3'];
	//	$i++;

	$data[$i]['one'] = " ";
	$data[$i]['two'] = $student_info[0]['HS_CITY'];
	$i++;

	$data[$i]['one'] = " ";
	$data[$i]['two'] = $student_info[0]['HS_STATE_CODE'] . ", " . $student_info[0]['HS_ZIP'];
	$i++;
	
	$data[$i]['one'] = " ";
	$data[$i]['two'] = $student_info[0]['HS_NATION_DESCRIPTION'];
	$i++;

	$data[$i]['one'] = "<b>Preferred Pronoun</b>";
	$data[$i]['two'] = $pronoun;
	$i++;
	
	$data[$i]['one'] = "<b>Intended Major 1</b>";
	$data[$i]['two'] = $student_info[0]['INTENDED_MAJOR1'];
	$i++;
	
	$data[$i]['one'] = "<b>Intended Major 2</b>";
	$data[$i]['two'] = $student_info[0]['INTENDED_MAJOR2'];
	$i++;
	
	$data[$i]['one'] = "<b>Birthdate</b>";
	$data[$i]['two'] = $student_info[0]['DATE_OF_BIRTH'];
	$i++;
	
	$data[$i]['one'] = "<b>Academic Advisor</b>";
	$data[$i]['two'] = $stu_advisor;
	$i++;
	
	$t_columns = array();
	foreach($columns as $key=>$value)
	{
		$t_columns[$key]['key'] = $value;
		$t_columns[$key]['lable'] = $value;
		$t_columns[$key]['width'] = '100';
		$t_columns[$key]['sortable'] = 'false';
	}
	
	$t_columns['two']['width'] = '500';

	$yui = new yuitable('formtable');
	$yui->setColumns($t_columns);

	
}

?>

<center>
	<div id="entry">
        <?
        if ($my_role == ADMIN_ID) {
           echo "<form name='student_search' method='POST' action=''>";
           echo "<table>";
           echo "<tr>";
           echo "<td>Start Entering the last name of a student, or select from the drop down list below:</td>";
           echo "<td><input type=\"text\" name=\"name\" value=\"\" onKeyUp=\"fyearLookup(this.value)\"/></td>";
           echo "</tr>";
           echo "</table>";
           echo "</form>";
        }
        ?>
	</div>
	<div id="student_results"></div>
        <div id="student_dropdown">
	<form name='student_list' method='POST' action=''>
        <?
 	echo "<table class='column_table' id='student_selection_block'>";
	echo "<tr>";

        echo "<td align='right'><span style='font-size:14px'><b>Advisee:</b><select id=\"advisee_selected\" name=\"advisee_selected\" onChange=\"this.form.submit();\">";

	for ($i = 0; $i< sizeof($advisees) ; $i++) 
        {
	  $selected = "";
	  if ($a_selected == $advisees[$i]['NAME']) 
          {
	    $selected = " selected ";
	  }
          echo "<option value=\"".$advisees[$i]['NAME']."\" $selected >".
          $advisees[$i]['LAST_NAME'].", ".$advisees[$i]['FIRST_NAME']." (".$advisees[$i]['EMAIL'].") - ".$advisees[$i]['ID']."</option>\n";
	}
	echo "</select>\n";
	echo "</tr>";

	echo "<tr>";
	echo "<td align='right'><br></span></td>";
	echo "<tr>";
       	echo "<td align='right'>  </span></td>";
        echo "<td align=left>";
        echo "<input type='submit' name='submit' value='Select' />";
        echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align='right'><br></span></td>";
	echo "</tr>";
 	echo "</table>";
        ?>
	</form>
        </div>
	<div id="menu">
		<table>
			<tr>
                        <td><br></td>
                        </tr>
			<tr>
                        <?
			echo "<td><a href=\"advising_report.php?name=$uname\">"."Advising Information</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"test_scores.php?name=$uname\">"."Test Scores</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"fy_classes.php?name=$uname\">"."FY Courses</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"stu_sched.php?name=$uname\">"."Schedule</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"advisor_comments.php?name=$uname\">"."Advisor Comments</td>";
                         ?>
			</tr>
		</table>
	</div>
	<div id="results">
	<?
                        $yui->table($data, $urls, $sort, 0);
                        $yui->printFooter(); 
	?>
	</div>
</center>