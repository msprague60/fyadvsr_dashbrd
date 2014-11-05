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
if($my_role != ADMIN_ID && $my_role != ADMIN_ACAD)
{
	echo "<center>You are not allowed to access this page.<br><a href='logout.php'>Logout Here</a></center>";
	exit;
}

if ($no_navs == "") {
	printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);
}

printPageTitle("Advisee Lookup","<center>Use the form below to view general information about a specific student you are advising.</center>");

// hardcode advisor for now
$advisor = 'avelench';

//$advisor = $_SESSION['username'];
$uname = $_GET['name'];

$adv_pidm = '';
$local_oracle->getPidmByUser($adv_pidm, $advisor);

if(isset($uname))
{
	
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);

        $local_mysql1->getPreferredPronoun($pronoun,$uname);
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
	
	$t_columns = array();
	foreach($columns as $key=>$value)
	{
		$t_columns[$key]['key'] = $value;
		$t_columns[$key]['lable'] = $value;
		$t_columns[$key]['width'] = '100';
		$t_columns[$key]['sortable'] = 'false';
	}
	
	$t_columns['two']['width'] = '500';
	//	$yui = new yuitable('formtable');
	//	$yui->setColumns($t_columns);

	$mdata = array();
	$murls = array();
	$msort = array();
	//	$columns = array('one'=>'one', 'two'=>'two');
	$mcolumns = array('one'=>'one');
	$i = 0;
	
	$murls[$i] = "advising_report.php?name=".$uname;
	$mdata[$i] = "<b>Advising Information</b>";
	$i++;
	
	$m_columns = array();
	foreach($mcolumns as $key=>$value)
	{
		$m_columns[$key]['key'] = $value;
		$t_columns[$key]['lable'] = $value;
		$t_columns[$key]['width'] = '100';
		$t_columns[$key]['sortable'] = 'false';
	}
	
	//	$yui = new yuitable('formtable');
	//	$yui->setColumns($t_columns);

	
}

?>

<center>
	<div id="entry">
	<form name='student_search' method='POST' action=''>
		<table>
			<tr>
				<td>Start Entering the last name of a Student:</td>
				<td><input type="text" name="name" value="" onKeyUp="fyearLookup(this.value)"/></td>
			</tr>
		</table>
	</form>
	</div>
										<!--	<div id="student_menu"> -->
  //	<?
  //												$yui = new yuitable('menutable');
  //									                        $yui->setColumns($m_columns);
  //									                        $yui->table($mdata, $murls, $msort, 0);
  //									                        $yui->printFooter(); 
  //										?>
  <!--   </div>  -->
	<div id="student_results"> </div>
	<div id="results">
	<?
			$yui = new yuitable('formtable');
                        $yui->setColumns($t_columns);
                        $yui->table($data, $urls, $sort, 0);
                        $yui->printFooter(); 
	?>
	</div>
</center>