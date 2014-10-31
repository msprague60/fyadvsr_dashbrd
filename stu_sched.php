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
$term_desc = 'Fall 2014';

if(isset($uname))
{
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);
	
	// mms hardcode a pidm for testing
	//	$stu_pidm = 30654230;
        $stu_pidm = $student_info[0]['PIDM'];

        $schd_info = array();
	$local_oracle->getStudentSchedule($schd_info, $stu_pidm, $term_code);

        $days = $schd_info[0]['DAYS1'];
        if ($schd_info[0]['DAYS2'] != "") {
	  $days .= "<br>".$schd_info[0]['DAYS2'];
	}
        if ($schd_info[0]['DAYS3'] != "") {
	  $days .= "<br>".$schd_info[0]['DAYS3'];
	}

        $startend = $schd_info[0]['STARTEND1'];
        if ($schd_info[0]['STARTEND2'] != "") {
	  $days .= "<br>".$schd_info[0]['STARTEND2'];
	}
        if ($schd_info[0]['STARTEND3'] != "") {
	  $days .= "<br>".$schd_info[0]['STARTEND3'];
	}

        $loc = $schd_info[0]['LOC1'];
        if ($schd_info[0]['LOC2'] != "") {
	  $days .= "<br>".$schd_info[0]['LOC2'];
	}
        if ($schd_info[0]['LOC3'] != "") {
	  $days .= "<br>".$schd_info[0]['LOC3'];
	}


	$data = array();
	$urls = array();
	$sort = array();
        $columns = array ('crn' => 'CRN',
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
          if ($key == 'credit_hours' || $key == 'days') {
	    $t_columns[$key]['width'] = '50';
	  }
	  else {
	    $t_columns[$key]['width'] = '150';
	  }
	}

  
    $yui = new yuitable("stu_schd");
    $yui->setColumns($t_columns);
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
	</div>
	<div id="results">
	<?
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
	<div id="tests">
	<?
                $yui->table($schd_info,$urls,$sort,0,$term_desc);
		$yui->printFooter(); 
	?>
	</div>
</center>