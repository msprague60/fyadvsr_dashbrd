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

printPageTitle("Student Schedule");

$uname = $_GET['name'];

//echo 'uname='.$uname;

//hardcode term for now
$term_code = '201409';
$spring_term = '201502';

if(isset($uname))
{
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);
	
	// mms hardcode a pidm for testing
	//	$stu_pidm = 30654230;
        $stu_pidm = $student_info[0]['PIDM'];

        $schd1_info = array();
        $schd2_info = array();
	$local_oracle1->getStudentSchedule($schd1_info, $stu_pidm, $term_code);
	$local_oracle1->getStudentSchedule($schd2_info, $stu_pidm, $spring_term);

	$urls = array();
	$sort = array();
        $columns = array ('term_desc' => 'Semester',
			  'course' => 'Course Name',
			  'title' => 'Course Title',
			  'credit_hours' => 'Units',
                          'days' => 'Days',
                          'startend' => 'Time',
                          'loc' => 'Room',
                          'instructors' => 'Instructors'
			  );
    
        $t_columns = array();

        foreach ($columns as $key=>$value) {
	  $t_columns[$key]['key'] = $value;
          $t_columns[$key]['label'] = $value;
          $t_columns[$key]['sortable'] = "true";
          if ($key == 'credit_hours' || $key == 'days' || $key == 'crn') {
	    $t_columns[$key]['width'] = '50';
	  }
          else if ($key == 'term_desc') {
	    $t_columns[$key]['width'] = '80';
	  }
	  else {
	    $t_columns[$key]['width'] = '150';
	  }
	}

  
    $yui1 = new yuitable("stu_schd1");
    $yui1->setColumns($t_columns);

    if(sizeof($schd2_info) > 0) {
    $yui2 = new yuitable("stu_schd2");
    $yui2->setColumns($t_columns);
    }

    //    print "<br>\n";
	
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
			echo "<td><a href=\"advising_report.php?name=$uname\">"."Advising Information</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"test_scores.php?name=$uname\">"."Test Scores</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"fy_classes.php?name=$uname\">"."FY Courses</td>";
                        echo "<td> </td>";
			echo "<td><a href=\"advisor_comments.php?name=$uname\">"."Advisor Comments</td>";
                         ?>
			</tr>
		</table>
                <br><br>
	</div>
	<div id="results">
	<?php
 	echo "<table class='column_table' id='student_info_block'>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:16px'><b> </b></span></td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:16px'><b>Banner ID:</b></span></td>";
 			echo "<td><span style='font-size:16px'>".$student_info[0]['ID']."</span></td>";
 			echo "<td align='right'><span style='font-size:16px'><b>Name:</b></td>";
 			echo "<td><span style='font-size:16px'>".$student_info[0]['CURRENT_NAME']."</span></td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:16px'><b> </b></span></td>";
 		echo "</tr>";
 	echo "</table>";

	?>
	</div>
        <div id="fall_title">
	<?php
 	echo "<table class='column_table' id='fall_title_block'>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:16px'><b> </b></span></td>";
 		echo "</tr>";
 		echo "<tr>";
        	echo "<td><span style='font-size:16px'><b>".$schd1_info[0]['TERM_DESC']."</b></span></td>";
//		echo "<td><span style='font-size:16px'><b>Fall 2014</b></span></td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:16px'><b> </b></span></td>";
 		echo "</tr>";
 	echo "</table>";

	?>
        </div>
	<div id="fall_sched">
	<?php
	$yui1->table($schd1_info,$urls,$sort,0,$sort_text);
        $yui1->printFooter();
	?>
	</div>
        <div id="spring_title">
	<?php
 	echo "<table class='column_table' id='spring_title_block'>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:16px'><b> </b></span></td>";
 		echo "</tr>";
 		echo "<tr>";
        	echo "<td><span style='font-size:16px'><b>".$schd2_info[0]['TERM_DESC']."</b></span></td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:16px'><b> </b></span></td>";
 		echo "</tr>";
 	echo "</table>";

	?>
        </div>
	<div id="spring_sched">
	<?php
	$yui2->table($schd2_info,$urls,$sort,0,$sort_text);
	$yui2->printFooter(); 
	?>
	</div>
</center>