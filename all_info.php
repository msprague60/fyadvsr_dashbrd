
<?php

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

//Script Includes
include('config.php');

?>
<script type="text/javascript" src='js/reports.js'></script>
<style>
#formtable th {display:none};
</style>
<?php 

//Print Title and Navigations
if ($no_navs != "1") {
	printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);
 }
printPageTitle("Advisee Lookup","<center>Use the form below to view information about a specific student you are advising.</center>");

$term_code = '201409';
// mms hardcode term description for now
$term_desc = 'Fall 2014';
$spring_code = '201502';

$my_role = $_SESSION['my_role'];
$advisor = $_SESSION['username'];

//echo "advisor=".$advisor;

$uname = $_POST['advisee_selected']?$_POST['advisee_selected']:$_GET['advisee_selected'];
if ($uname == '') {
  $uname = $_GET['name'];
 }

$adv_comments = $_POST['adv_comments']?$_POST['adv_comments']:$_GET['adv_comments'];
//$adv_comments = $_GET['adv_comments'];
$adv_comments = trim($adv_comments);
	
$a_selected = $uname;
//echo "uname=".$uname;
//echo "my_role=".$my_role;

$temp1 = array();
$local_oracle->getPidmByUser($temp1, $advisor);
$adv_pidm = $temp1[0]['PIDM'];

//echo "adv_pidm=".$adv_pidm;

$advisees = array();
if ($my_role == ADMIN_ID) {
$local_oracle1->getAllStudents($advisees, $term_code);
 }
 else {
$local_oracle1->getStudentsbyAdvisor($advisees, $adv_pidm, $term_code);
 }

//echo 'advisees=';
//print_r($advisees);

if(isset($uname))
{
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);

        $local_mysql1->getPreferredPronoun($pronoun,$uname);

	$id = '';
	$local_mysql1->getIdByUser('advising', $uname, $id);
	
	$values = array();
	$local_mysql1->getAllData('advising', $values, $id);
	//        print_r($values);
	
        $temp2 = array();
        $local_oracle1->getAdvisor($temp2,$uname,$term_code);
        $stu_advisor = $temp2[0]['ADVISOR_NAME'];

	$housing = array();
	$local_oracle->getStudentHousing($uname."@wellesley.edu", $housing);
	
        $stu_pidm = $student_info[0]['PIDM'];

        $test_info = array();
	$local_oracle->getStudentTestScores($test_info, $stu_pidm);

        $sched_info = array();
	$local_oracle->getStudentSchedule($sched_info, $stu_pidm, $term_code);

        $fyclass_info = array();
	$local_oracle->getFYCourses($fyclass_info, $stu_pidm);
	//	print_r($fyclass_info);
	$spring = array();
	$local_mysql->getMyEnrollments($spring, $uname, $spring_code);
	//	print_r($spring);
	$j = sizeof($fyclass_info);
	for ($i = 0; $i < sizeof($spring); $i++) {
	  $crns = array($spring[$i]['crn']);
	  $courses = array();
	  $course_class->getCoursesByCRN($courses , $spring[$i]['term'], $crns, $additional);
	  //	  print_r($courses);
	  $fyclass_info[$j]['CRN'] = $courses[0]['CRN'];
	  $fyclass_info[$j]['TERM_DESC'] = 'Spring 2015';
	  $fyclass_info[$j]['COURSE'] = $courses[0]['SUBJ_CODE'] . ' ' .
	    $courses[0]['CRSE_NUMBER'] . ' ' .
	    $courses[0]['SECTION_NUMBER'];
	  $fyclass_info[$j]['TITLE'] = $courses[0]['LONG_TITLE'];
	  $fyclass_info[$j]['CREDIT_HOURS'] = $courses[0]['CREDIT_HOURS'];
	  $fyclass_info[$j]['DAYS'] = '';
	  $fylass_info[$j]['STARTEND'] = '';
	  $fyclass_info[$j]['LOC'] = '';	  
	  $fyclass_info[$j]['INSTRUCTORS'] = '';
	  $j++;
	}
	//	echo 'fyclass_info=';
	//	print_r($fyclass_info);
	if(strlen($adv_comments) > 2000)
	  {
	    $adv_comments = substr($adv_comments, 0, 2000);
	  }
	//		echo 'adv_comments length =' . strlen($adv_comments);
	//		echo 'adv_comments= ' . $adv_comments;

	if(strlen($adv_comments) > 0 )
	  {
	    $adv_comments .= $_SESSION['username'] . " " . date("m/d/Y") . "<br>";
		//		echo 'adv_comments='.$adv_comments;

	    $local_oracle1->SaveAdvComments($stu_pidm,$adv_pidm,$term_code,$adv_comments);
	    $adv_comments = '';
	  }

       $temp3 = array();
       $old_adv_comments = array();
       $concat_adv_comments = "";
       $local_oracle1->getAdvComments($temp3,$stu_pidm,$adv_pidm,$term_code);
       //echo 'temp3=';
       //print_r($temp3);

       for($i = 0; $i<sizeof($temp3);$i++)
 	{
 	array_push($old_adv_comments, $temp3[$i]);
 	}
//echo 'old_adv_comments=';
//print_r($old_adv_comments);
 
 	for($i = 0; $i<sizeof($old_adv_comments); $i++)
 	{
	  $concat_adv_comments .= trim($old_adv_comments[$i]['ADV_COMMENTS']);
	  $concat_adv_comments .= '<br>';
	}
//	echo 'pronoun='.$pronoun;

	foreach($values as $key=>$value)
	{
		${$key} = $value;
	}
 }
	
 //All readers should have a role. If a role is not found for the username then kick them out.
// if($my_role == '')
// {
//     echo "<center><br>You are not permitted to access this application.</center>";
//     exit;
// }
 
 	$readOnly = 'readonly="readonly"';
 	$disabled = 'disabled="disabled"';
 
 //Set the column width for textareas based on the browser being used
 $col_width = 92;
 
 if(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox'))
 {
     $col_width = 73;
 }
 
 //Set Layout Width based on User Input (default 800px
 $width = (isset($_GET['width'])?$_GET['width']:'1000');
 $column_width = $width/2;
 
 //Sets activity date for Upsert Queries
 $activity_date = date('d-M-y');
 
 $fgen_vals = array (
 		'YES' => 'Y',
 		'NO' => 'N',
 );
 
 
 //Functions for displaying different chunks of table data. We do this so we can move them around easily in the layout
 function printTermSelectionBlock()
 {
   global $terms, $temp_terms, $depts, $outcome_status_list, $advisor_list;
   global $my_role, $generate_dept_sel, $c_selected, $g_selected, $d_selected, $o_selected, $a_selected;
   global $advisor;
 	
 	echo "<table class='column_table' id='term_selection_block'>";
	echo "<tr>";
                        $additional = " onchange=\"reload_report(this.form)\" "; 
        echo "<td align='right'><span style='font-size:14px'><b>Current Term:</b><select id=\"current_term\" name=\"current_term\" onChange=\"this.form.submit();\">";
	if ($c_selected == "") 
        {
	  $c_selected = '201402';
	}
	for ($i = 0; $i< sizeof($temp_terms) ; $i++) 
        {
	  $selected = "";
	  if ($c_selected == $temp_terms[$i]['STVTERM_CODE']) 
          {
	    $selected = " selected ";
	  }
          echo "<option value=\"" . $temp_terms[$i]['STVTERM_CODE'] . "\" $selected >" .
          $temp_terms[$i]['STVTERM_DESC'] . "</option>\n";
	}
	echo "</select>\n";

	//	echo 'c_selected='.$c_selected;
        echo "<td align='right'><span style='font-size:14px'><b>Expected Grad Term:</b><select id=\"exp_grad_term\" name=\"exp_grad_term\" onChange=\"this.form.submit();\">";
	if ($g_selected == "") 
        {
	  $g_selected = '201402';
	}

	for ($i = 0; $i< sizeof($temp_terms) ; $i++) 
        {
	  $selected = "";
	  if ($g_selected == $temp_terms[$i]['STVTERM_CODE']) 
          {
	    $selected = " selected ";
	  }
          echo "<option value=\"" . $temp_terms[$i]['STVTERM_CODE'] . "\" $selected >" .
          $temp_terms[$i]['STVTERM_DESC'] . "</option>\n";
	}
	echo "</select>\n";

        echo "<td align='right'><span style='font-size:14px'><b>Degree Outcome Status:</b><select id=\"deg_outcome_status_selected\" name=\"deg_outcome_status_selected\" onChange=\"this.form.submit();\">";
	if ($o_selected == "") 
        {
	  $o_selected = 'All';
	    $selected = " selected ";
	}

        echo "<option value=\"All\" $selected >All</option>\n";

	for ($i = 0; $i< sizeof($outcome_status_list) ; $i++) 
        {
	  $selected = "";
	  if ($o_selected == $outcome_status_list[$i]['STVDEGS_CODE']) 
          {
	    $selected = " selected ";
	  }
          echo "<option value=\"" . $outcome_status_list[$i]['STVDEGS_CODE'] . "\" $selected >" .
          $outcome_status_list[$i]['STVDEGS_DESC'] . "</option>\n";
	}
	echo "</select>\n";

	if ($a_selected == "") 
        {
	  $a_selected = 'All';
	    $selected = " selected ";
	}

        echo "<td align='right'><span style='font-size:14px'><b>Advisor:</b><select id=\"advisor_selected\" name=\"advisor_selected\" onChange=\"this.form.submit();\">";

        echo "<option value=\"All\" $selected >All</option>\n";

	for ($i = 0; $i< sizeof($advisor_list) ; $i++) 
        {
	  $selected = "";
	  if ($a_selected == $advisor_list[$i]['ADVISOR_PIDM']) 
          {
	    $selected = " selected ";
	  }
          echo "<option value=\"" . $advisor_list[$i]['ADVISOR_PIDM'] . "\" $selected >" .
          $advisor_list[$i]['ADVISOR_NAME'] . "</option>\n";
	}
	echo "</select>\n";
	//	echo 'g_selected='.$g_selected;
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
 }

 function printStudentSearchBlock()
 {
            echo "<form name='student_search' method='POST' action=''>";
           echo "<table>";
           echo "<tr>";
           echo "<td>Start Entering the last name of a student, or select from the drop down list below:</td>";
           echo "<td><input type=\"text\" name=\"name\" value=\"\" onKeyUp=\"fyearLookup(this.value)\"/></td>";
           echo "</tr>";
           echo "</table>";
           echo "</form>";
 }

 function printStudentSelectionBlock()
 {
   global $advisees;
   global $my_role, $a_selected;
 	
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
 }

  function printStudentInfoBlock()
 {
 	global $student_info;
 	global $my_role, $pronoun, $stu_advisor;
 	
 	echo "<table class='column_table' id='student_info_block' cellpadding='2px' style='border:1px solid black;'>";
		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left' colspan=2>Student Biographical Information</th>";
				// 				echo "<td align='right' colspan=3>(<a href='javascript:showHideBlock(\"demographics\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Banner ID:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['ID']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Student Name:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['CURRENT_NAME']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Preferred First Name:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['PREF_FIRST_NAME']."</td>";
 		echo "</tr>";
                $home_address = $student_info[0]['HOME_STREET_LINE1'];
                if ($student_info[0]['HOME_STREET_LINE2'] != "")
		  {
		    $home_address .= "<br>" . $student_info[0]['HOME_STREET_LINE2'];
		  }
		if ($student_info[0]['HOME_STREET_LINE3'] != "")
		  {
		    $home_address .= "<br>" . $student_info[0]['HOME_STREET_LINE3'];
		  }
                $home_address .= "<br>" . $student_info[0]['HOME_CITY'];
                $home_address .= ", " . $student_info[0]['HOME_STATE'] . " " . $student_info[0]['HOME_ZIP'];
                $home_address .= "<br>" . $student_info[0]['HOME_NATION_DESC'];
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Home Address:</b></td>";
 			echo "<td style='border:1px solid black;'>".$home_address."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Wellesley Email:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['WEL_EMAIL']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Alternate Email:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['GEN_EMAIL']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Home Phone:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['HOME_PHONE_NUMBER']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Cell Phone:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['CELL_PHONE_NUMBER']."</td>";
 		echo "</tr>";
                $hschool = $student_info[0]['HIGH_SCHOOL'];
                if ($student_info[0]['HS_STREET_LINE_1'] != "")
		  {
		    $hschool .= "<br>" . $student_info[0]['HS_STREET_LINE_1'];
		  }
                if ($student_info[0]['HS_STREET_LINE_2'] != "")
		  {
		    $hschool .= "<br>" . $student_info[0]['HS_STREET_LINE_2'];
		  }
		if ($student_info[0]['HS_STREET_LINE_3'] != "")
		  {
		    $hschool .= "<br>" . $student_info[0]['HS_STREET_LINE3_'];
		  }
                $hschool .= "<br>" . $student_info[0]['HS_CITY'];
                $hschool .= ", " . $student_info[0]['HS_STATE_CODE'] . " " . $student_info[0]['HS_ZIP'];
                $hschool .= "<br>" . $student_info[0]['HS_NATION_DESCRIPTION'];
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>High School:</b></td>";
 			echo "<td style='border:1px solid black;'>".$hschool."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Preferred Pronoun:</b></td>";
 			echo "<td style='border:1px solid black;'>".$pronoun."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Intended Major 1:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['INTENDED_MAJOR1']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Intended Major 2:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['INTENDED_MAJOR2']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Birthdate:</b></td>";
 			echo "<td style='border:1px solid black;'>".$student_info[0]['DATE_OF_BIRTH']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Academic Advisor:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_advisor."</td>";
 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
 }

 function printAdvisingInfoBlock()
 {
 	global $housing;
 	global $my_role, $high_school_subjs, $areas_of_study, $two_courses, $skills, $transition, $other_info;
 	
 	echo "<table class='column_table' id='student_advising_block' cellpadding='2px' style='border:1px solid black;'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left'>Advising Survey Information</th>";
 				echo "<td align='right'>(<a href='javascript:showHideBlock(\"survey\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Housing Assignment:</b></td>";
 			echo "<td style='border:1px solid black;'>".$housing[0]['BLDG_CODE']." ".$housing[0]['ROOM_NUMBER']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Most Interesting High School Subjects:</b></td>";
      			echo "<td style='border:1px solid black;'>".$high_school_subjs."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Areas of Academic Exploration:</b></td>";
      			echo "<td style='border:1px solid black;'>".$areas_of_study."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Two Course Choices:</b></td>";
      			echo "<td style='border:1px solid black;'>".$two_courses."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Top Two Skills:</b></td>";
      			echo "<td style='border:1px solid black;'>".$skills."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Transition:</b></td>";
      			echo "<td style='border:1px solid black;'>".$transition."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Other Info:</b></td>";
      			echo "<td style='border:1px solid black;'>".$other_info."</td>";
 		echo "</tr>";
 		echo "<tr>";
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printTestScoresBlock()
 {
 	global $test_info;
 	global $my_role;
 	
 	echo "<table class='column_table' id='test_scores_block' cellpadding='2px' style='border:1px solid black;'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left' colspan=2>Test Scores</th>";
 				echo "<td align='right' colspan=3>(<a href='javascript:showHideBlock(\"test_scores\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
                		echo "<tr>";
 		        	echo "<td style='border:1px solid black;'><b>Test:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Score:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Date:</b></td>";
      		                echo "</tr>";
                	for($i = 0; $i<sizeof($test_info);$i++)
 	                  { 
      	                	echo "<tr>";
		        	echo "<td style='border:1px solid black;'>".$test_info[$i]['TEST_DESCRIPTION']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$test_info[$i]['TEST_SCORE']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$test_info[$i]['TEST_DATE']."</td>";
      		                echo "</tr>";
			  }
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printStudentScheduleBlock()
 {
 	global $sched_info;
 	global $my_role,$term_desc;
 	
 	echo "<table class='column_table' id='student_schedule_block' style='border:1px solid black;' cellpadding='2px'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left' colspan=4>Student Schedule ".$term_desc."</th>";
 				echo "<td align='right' colspan=4>(<a href='javascript:showHideBlock(\"schedule\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
                		echo "<tr>";
 		        	echo "<td style='border:1px solid black;'><b>CRN:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Course Name:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Course Title:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Credit Hours:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Days:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Time:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Room:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Instructors:</b></td>";
      		                echo "</tr>";
                	for($i = 0; $i<sizeof($sched_info);$i++)
 	                  { 
      	                	echo "<tr>";
		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['CRN']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['COURSE']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['TITLE']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['CREDIT_HOURS']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['DAYS']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['STARTEND']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['LOC']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$sched_info[$i]['INSTRUCTORS']."</td>";
      		                echo "</tr>";
			  }
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printFYClassesBlock()
 {
 	global $fyclass_info;
 	global $my_role;
 	
 	echo "<table class='column_table' id='first_year_classes_block' style='border:1px solid black;' cellpadding='2px'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left' colspan=4>Student First Year Seminar and Writing Classes</th>";
 				echo "<td align='right' colspan=4>(<a href='javascript:showHideBlock(\"fyclasses\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
                		echo "<tr>";
 		        	echo "<td style='border:1px solid black;'><b>Term:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Course Name:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Course Title:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Credit Hours:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Days:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Time:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Room:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Instructors:</b></td>";
      		                echo "</tr>";
                	for($i = 0; $i<sizeof($fyclass_info);$i++)
 	                  { 
      	                	echo "<tr>";
		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['TERM_DESC']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['COURSE']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['TITLE']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['CREDIT_HOURS']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['DAYS']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['STARTEND']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['LOC']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$fyclass_info[$i]['INSTRUCTORS']."</td>";
      		                echo "</tr>";
			  }
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printAddAdvisorCommentsBlock()
 {
 	global $adv_comments;
 	global $my_role, $col_width;
 	
 	echo "<table class='column_table' id='adv_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='left'>Add Comments/Notes</th>";
 			echo "<td align='right'>(<a href='javascript:showHideBlock(\"add_comments\")'>Show/Hide</a>)</td>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td colspan=2 align='center'>
 		                <textarea class='mceEditorSmall' id='adv_comments' name='adv_comments' cols=$col_width rows=20 onkeyup='textCounter(\"adv_comments\",\"adv_comment_count\",\"2000\")' maxlength='2000'>".htmlentities($adv_comments)."</textarea>
 		            </td>";
 		echo "</tr>";
// 		echo "<tr>";
// 		    echo "<td colspan=2>";
// 		        echo "<span id='adv_comment_count' style='color:red'>4000 characters remaining</span>";
// 		    echo "</td>";
// 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
 }

 function printAdvisorCommentsBlock()
 {
   global $adv_comments, $concat_adv_comments;
 	global $my_role, $col_width;
 	
 	echo "<table class='column_table' id='concat_adv_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='left'>Comments/Notes</th>";
 			echo "<td align='right'>(<a href='javascript:showHideBlock(\"aconcat\")'>Show/Hide</a>)</td>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td colspan=2 align='center'>
 		                <textarea class='mceEditor' id='concat_adv_comments' name='concat_comments' cols=$col_width rows=5 onkeyup='textCounter(\"concat_comments\",\"concat_comment_count\",\"6000\")' maxlength='6000'>".htmlentities($concat_adv_comments)."</textarea>
 		            </td>";
 		echo "</tr>";
		// 		echo "<tr>";
		// 		    echo "<td colspan=2>";
		// 		        echo "<span id='chair_comment_count' style='color:red'>4000 characters remaining</span>";
		// 		    echo "</td>";
		// 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
 }
 
 ?>

<!-- Style Script -->
<style>
 
 body{
 	width:100%;
 	height:100%;
    text-align:center;
 }
#container
{
	position:relative;
	width:<?=$width?>px;
	margin-left:auto;
	margin-right:auto;
}

#top_container
{
	width:<?=$width?>px;
}

#left_container
{
	min-width:<?=$column_width?>px;
	float:left;
}

#right_container
{
	min-width:<?=$column_width?>px;
	float:right;
}

#student_info_block
{
	margin-left:5px;
	padding-bottom:20px;
	width:<?=$width?>px;
}

#overall_comments_block, #decision_block, #voting_block, #reader_entered_codes, #demographic_block, #secondary_block,
#family_block, #reqtesting_block, #opttesting_block, #extracurricular_block, #active_contact_block, #student_address_block, #selfreported_block,
#student_detail_block,#reg_comments_block,#degree_audit_block,#dean_comments_block,#current_courses_block,#eighteen_units_block,#concat_reg_comments_block,
#concat_dean_comments_block,#student_info_block,#student_advising_block,#test_scores_block,#adv_comments_block,#concat_adv_comments_block,#student_schedule_block,
#first_year_classes_block
{
	margin-bottom:15px;
	width:<?=$column_width-10?>px;
	border:1px;
	border-style:solid;
	border-color:grey;
	border-spacing:5px;
	text-align:left;
}

.column_table
{
    border-collapse:collapse;
    width:100%;
}

textarea
{
	overflow-y:scroll;
	resize:none;
}

#student_dem_block td
{
	width:12%;
}

.clear
{
	clear:both;
	height:1px;
	overflow:hidden;
}

.table_head
{
	background-color:#E3E3E3;
	padding:5px;
}

.no_edit
{
	background-color:#C2C2C2;
	text-align:center;
}

.no_edit_left
{
	background-color:#C2C2C2;
	text-align:left;
}

.no_edit_left_input
{
    background-color:#C2C2C2;
	text-align:left;
	border: 1px solid #E3E3E3;
    padding:2px;
}

.hidden_senior, .hidden_excur, .hidden_sibling
{
    display:none;
}

#family_info
{
    font-size:12px;
}

.banner
{
    background:#E6CCFF;
    border: 1px solid #E3E3E3;
    padding:2px;
}

#vote_codes
{
	position: absolute;
	text-align:left;
	display:none;
	z-index:100;
	width: 700px;
	padding:3px 3px 3px 3px;
	border-radius:3px;
	border:1px solid;
	border-color:grey;
	background:white;	
	top:5%;
	left:50%;
	margin-left: -350px;
	box-shadow: 0px 0px 10px 3px #888888;
}

.autosave_alert
{
	color:red;
}

</style>

<!-- Javascript -->

<script>
tinyMCE.init({
	  	  theme : "advanced",
              readonly : "1",
	      mode : "specific_textareas",
	      editor_selector: "mceEditor",
	      height:"400",
	      plugins : "paste",
	      theme_advanced_buttons1 : "bold, italic, underline, fontsizeselect, forecolor, backcolor",
	      theme_advanced_buttons1_add : "pastetext,pasteword,selectall",
	      theme_advanced_buttons2 : "",
	      charLimit:4000,
	      setup: function(ed){
		      ed.onKeyDown.add(function(ed, e) {
			      var max, length, str, data, remaining;

			      max = ed.settings.charLimit;
			      length = ed.getContent().length;
			      str = ed.getContent();

			      if(length >= max)
			      {
				      data = str.substr(0,max);
				      ed.setContent(data);
				      $('#overall_comment_count').text("0 characters remaining.");
			      } else {
				      remaining = max - length;
				      $('#overall_comment_count').text(remaining+" characters remaining.");
			      }
		      });
	      }
	  });

	tinyMCE.init({
	  theme : "advanced",
	      mode : "specific_textareas",
	      editor_selector: "mceEditorSmall",
	      height:"125",
	      plugins : "paste",
	      theme_advanced_buttons1 : "bold, italic, underline",
	      theme_advanced_buttons1_add : "pastetext,pasteword,selectall",
	      theme_advanced_buttons2 : ""
	      });

	//Variables for Showing/Hiding Blocks
	var test_scores = 0;
	var survey = 0;
	var schedule = 0;
	var fyclasses = 0;
	var aconcat = 0;
	var add_comments = 0;

window.onload = function()
{
  showHideBlock('units');
  textCounter('reg_comments', 'overall_comment_count', '4000');
  textCounter('dean_comments', 'dean_comment_count', '4000');
}


	function textCounter(field, count, maxlimit)
	{		
		str = document.getElementById(field).value;
		
		if((str.length) >= maxlimit)
		{
			document.getElementById(field).value = str.substr(0, maxlimit);
			document.getElementById(count).innerHTML = maxlimit - (str.length) + ' characters remaining';
		} else {
			document.getElementById(count).innerHTML = maxlimit - (str.length) + ' characters remaining';
		}
		
	}


  function showHideBlock(table)
  {
	  switch(table)
	  {
	  case 'test_scores':		  
		  if(test_scores == 0)
		  {
			  $('#test_scores_block tbody').hide();
			  test_scores = 1;
		  } else {
			  $('#test_scores_block tbody').show();
			  test_scores = 0;
		  }
		  break;
		  
	  case 'schedule':
		  if(schedule == 0)
		  {
			  $('#student_schedule_block tbody').hide();
			  schedule  = 1;
		  } else {
			  $('#student_schedule_block tbody').show();
			  schedule = 0;
		  }
		  break;
		  
	  case 'fyclasses':
		  if(fyclasses == 0)
		  {
			  $('#first_year_classes_block tbody').hide();
			  fyclasses  = 1;
		  } else {
			  $('#first_year_classes_block tbody').show();
			  fyclasses = 0;
		  }
		  break;
		  
	  case 'units':
		  if(units == 0)
		  {
			  $('#eighteen_units_block tbody').hide();
			  units  = 1;
		  } else {
			  $('#eighteen_units_block tbody').show();
			  units = 0;
		  }
		  break;
		  
	  case 'survey':
		  if(survey == 0)
		  {
			  $('#student_advising_block tbody').hide();
			  survey = 1;
		  } else {
			  $('#student_advising_block tbody').show();
			  survey = 0;
		  }
		  break;

	  case 'overall':
		  if(overall == 0)
		  {
			  $('#reg_comments_block tbody').hide();
			  overall = 1;
		  } else {
			  $('#reg_comments_block tbody').show();
			  overall = 0;
		  }
		  break;

	  case 'rconcat':
		  if(overall == 0)
		  {
			  $('#concat_reg_comments_block tbody').hide();
			  overall = 1;
		  } else {
			  $('#concat_reg_comments_block tbody').show();
			  overall = 0;
		  }
		  break;

	  case 'aconcat':
		  if(aconcat == 0)
		  {
			  $('#concat_adv_comments_block tbody').hide();
			  aconcat = 1;
		  } else {
			  $('#concat_adv_comments_block tbody').show();
			  aconcat = 0;
		  }
		  break;

	  case 'add_comments':
		  if(add_comments == 0)
		  {
			  $('#adv_comments_block tbody').hide();
			  add_comments = 1;
		  } else {
			  $('#adv_comments_block tbody').show();
			  add_comments = 0;
		  }
		  break;	 

	  }
  }


  function checkNumeric(id)
  {
	  var test = $('#'+id).val();
	  var regex = /^[0-9]+$/;
	  
	  if(id == 'p_4yr'){label='% 4-yr';}
	  if(id == 'p_2yr'){label='% 2-yr';}
	  if(id == 'ap_off'){label='# AP Offered';}

	if(!new RegExp(regex).test(test) && test!=="")
	{
  		alert('You entered a non-numeric value into the '+label+' textbox. Please only use numbers in this field.');
  		$('#'+id).val('');
	}
  }


</script>

<!-- HTML LAYOUT -->
<div id="container">

<form id='first_year_advising_form' name='first_year_advising_form_form' method='POST' action='all_info.php'>
	<div id="top_container">
		<?php 
  //  echo 'Hello top container!';
  //if ($my_role == ADMIN_ID) {
  //  printStudentSearchBlock();
  //}
  printStudentSelectionBlock();
		?>
		<table width='100%'>
		    <tr>
		        <td align=center>
		            <input type='submit' name='submit' value='Submit Notes' />
		        </td>
		    </tr>
		</table>
	</div>
	
	<div class='clear'></div>
	
	<div id="left_container">
		<?php 
  printStudentInfoBlock(); 
  printStudentScheduleBlock();
  printFYClassesBlock();
  printTestScoresBlock();
		 //	        echo 'Hello World';
		?>
	</div>
	
	<div id="right_container">
		<?php 
  printAddAdvisorCommentsBlock();
  printAdvisorCommentsBlock();
  printAdvisingInfoBlock(); 

	  //	    	echo 'Hello Again';
		 //	if($my_role == ADMIN_ID or $my_role == '2')
		 //	  {
		 //	    //                printRegAddCommentsBlock(); 
		 //	  }
		 //	if($my_role == '3')
		 //	  {
	    //                printDeanAddCommentsBlock(); 
		 //	  }
		 ?>
	</div>
	
	<div class='clear'></div>
	<input type=hidden name='pidm' value='<?php echo $pidm; ?>' />
	<input type=hidden id='previous_pidm' name='previous_pidm' value='' />
	<input type=hidden id='next_pidm' name='next_pidm' value='' />
	<input type=hidden name='reader' value='<?php echo $reader; ?>' />
	<input type=hidden name='cterm_code' value='<?php echo $current_term; ?>' />
	<input type=hidden name='gterm_code' value='<?php echo $exp_grad_term; ?>' />
	<input type=hidden name='deg_status' value='<?php echo $deg_outcome_status; ?>' />
	<input type=hidden name='advr' value='<?php echo $advisor; ?>' />
	<center><input type='submit' id='submit' name='submit' value='Submit Notes'/></center>
	</form>
	
</div>
