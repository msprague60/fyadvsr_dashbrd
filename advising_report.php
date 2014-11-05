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

//Kick out unauthorized users
 //if($my_role != ADMIN_ID && $my_role != 1)
 //{
 //	echo "<center>You are not allowed to access this page.<br><a href='logout.php'>Logout Here</a></center>";
 //	exit;
 //}

if ($no_navs == "") {
	printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);
}

printPageTitle("Advising Questionnaire Information");

$uname = $_GET['name'];

//echo 'uname='.$uname;

if(isset($uname))
{
	$id = '';
	$local_mysql1->getIdByUser('advising', $uname, $id);
	
	$values = array();
	$local_mysql1->getAllData('advising', $values, $id);
	
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);
	
	$housing = array();
	$local_oracle->getStudentHousing($uname."@wellesley.edu", $housing);
	
	foreach($values as $key=>$value)
	{
		${$key} = $value;
	}
	
	$data = array();
	$urls = array();
	$sort = array();
	$columns = array('one'=>'one', 'two'=>'two');
	$i = 0;
	
	$data[$i]['one'] = "<b>Student Name</b>";
	$data[$i]['two'] = $student_info[0]['CURRENT_NAME'];
	$i++;
	
	$data[$i]['one'] = "<b>Building</b>";
	$data[$i]['two'] = $housing[0]['BLDG_CODE'];
	$i++;
	
	$data[$i]['one'] = "<b>Room Number</b>";
	$data[$i]['two'] = $housing[0]['ROOM_NUMBER'];
	$i++;
	
	$data[$i]['one'] = "<b>Preferred Name</b>";
	$data[$i]['two'] = $preferred_name;
	$i++;

	$data[$i]['one'] = "<b>Preferred Pronoun</b>";
	$data[$i]['two'] = $pronoun;
	$i++;
	
	$data[$i]['one'] = "<b>Most Interesting High School Subjects</b>";
	$data[$i]['two'] = $high_school_subjs;
	$i++;
	
	$data[$i]['one'] = "<b>Areas of Academic Exploration</b>";
	$data[$i]['two'] = $areas_of_study;
	$i++;
	
	$data[$i]['one'] = "<b>Two Course Choices</b>";
	$data[$i]['two'] = $two_courses;
	$i++;
		
	$data[$i]['one'] = "<b>Top Two Skills</b>";
	$data[$i]['two'] = $skills;
	$i++;

	$data[$i]['one'] = "<b>Transition</b>";
	$data[$i]['two'] = $transition;
	$i++;
	
	$data[$i]['one'] = "<b>Other Info</b>";
	$data[$i]['two'] = $other_info;
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
	<div id="menu">
		<table>
			<tr>
                        <td><br></td>
                        </tr>
			<tr>
                        <?
			echo "<td><a href=\"gen_info.php?name=$uname\">"."General Information</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"test_scores.php?name=$uname\">"."Test Scores</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"fy_classes.php?name=$uname\">"."FY Courses</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"stu_sched.php?name=$uname\">"."Schedules</td>";
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