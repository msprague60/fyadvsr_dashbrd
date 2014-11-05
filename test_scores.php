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

printPageTitle("Student Test Scores");

$uname = $_GET['name'];

//echo 'uname='.$uname;

if(isset($uname))
{
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);
	
        $stu_pidm = $student_info[0]['PIDM'];

        $test_info = array();
	$local_oracle->getStudentTestScores($test_info, $stu_pidm);

	$data = array();
	$urls = array();
	$sort = array();
        $columns = array ('test_description' => 'Test',
		      'test_score' => 'Score',
		      'test_date' => 'Date'
		      );
    
        $t_columns = array();

        foreach ($columns as $key=>$value) {
	  $t_columns[$key]['key'] = $value;
          $t_columns[$key]['label'] = $value;
          $t_columns[$key]['sortable'] = "true";
          if ($key == 'test_description') {
	    $t_columns[$key]['width'] = '120';
	  }
	  else {
	    $t_columns[$key]['width'] = '75';
	  }
	}

    $yui = new yuitable("test_scores");
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
                $yui->table($test_info,$urls,$sort,0,$sort_text);
		$yui->printFooter(); 
	?>
	</div>
</center>