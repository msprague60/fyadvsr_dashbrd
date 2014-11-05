
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

//Print Title and Navigations
if ($no_navs != "1") {
	printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);
 }
 printPageTitle("Student Degree Requirements Detail");
 
 //Set Student PIDM, Reader Username, and Region
 $reader = $_SESSION['username'];
// $reader = $_POST['reader']?$_POST['reader']:$_GET['reader'];

// hardcode for now
// $current_term = '201402';
// $exp_grad_term = '201402';
 //$pidm = 30623941;

//echo 'pidm = ' . $pidm;
//echo 'reader = ' . $reader;
 $message = "<center><br>There was a problem with the input you supplied.</center>";
 
 //  DATA VALIDATION CHECKS FOR PIDM AND READER
 
     //PIDM should only be numeric
//   if(preg_match('/[a-zA-Z]/', $pidm))
//     {
//         echo $message;
//         exit;
//     }

     //PIDM should not contain any special characters
//     if(preg_match('[~`!@#$%^&*()_-+=\[\]{}\|\\:;\"\'<,>.]/', $pidm))
//     {
//         echo $message;
//         exit;
//     }

     //PIDM should only be 8 characters long
//     if(strlen($pidm) != 8 )
//     {
//         echo $message;
//         exit;
//     }

     //Reader should not have any special characters
     if(preg_match('[~`!@#$%^&*()_-+=\[\]{}\|\\:;\"\'<,>.]/', $reader))
     {
         echo $message;
         exit;
     }

     //Reader should be 8 characters or less
     if(strlen($reader) > 8)
     {
         echo $message;
         exit;
     }

 //Get Reader Info for Insert/Update Queries
 $reader_info = array();

 $oracle1->facstaffLookup($_SESSION['username'] . "@wellesley.edu",
   $reader_info,'primary_email');

 if(empty($reader_info))
   $local_oracle1->cwLookup($reader_info,$_SESSION['username']);
 
 $reader_pidm = $reader_info[0]['PIDM'];
 
//echo 'reader pidm = ' . $reader_pidm;
 //Get Reader Role
 $my_role = $mysql->fetchRole($reader, $mysql_link);
 
 //All readers should have a role. If a role is not found for the username then kick them out.
 if($my_role == '')
 {
     echo "<center><br>You are not permitted to access this application.</center>";
     exit;
 }
 
 //Get Reader Preferences for Blocks
// $preferences = array();
// $local_mysql->getPreferences($reader, $preferences);
 
 //Set Readonly & Disabled Flags
 $readOnly = "";
 $disabled = "";
 //if($my_role != '100' && $my_role != '2')
 //{
 	$readOnly = 'readonly="readonly"';
 	$disabled = 'disabled="disabled"';
// }
 
 //Set the column width for textareas based on the browser being used
 $col_width = 92;
 
 if(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox'))
 {
     $col_width = 73;
 }
 
 //30702085
 //30623140


//Simpe test to make sure we're able to insert/update data
//$table_data = array();
//$local_oracle->getAllTableData('wc_nolijweb.adm_vote_data', '',$table_data);
 
 //Set Layout Width based on User Input (default 800px
 $width = (isset($_GET['width'])?$_GET['width']:'1000');
 $column_width = $width/2;
 
 //Sets activity date for Upsert Queries
 $activity_date = date('d-M-y');
 
 $fgen_vals = array (
 		'YES' => 'Y',
 		'NO' => 'N',
 );
 
// need array of terms?
 
 $submit = $_POST['submit'];
 
//   echo "pidm before=".$pidm;   
//   echo "banner_id before=".$banner_id;   
 $pidm = $_POST['pidm']?$_POST['pidm']:$_GET['pidm'];
 $banner_id = $_POST['banner_id']?$_POST['banner_id']:$_GET['banner_id'];
//  echo "pidm after get=".$pidm;   
//  echo "banner_id after get=".$banner_id;

if($banner_id != '')
  {
    $stu_info = array();
    $local_oracle->getPidmfromBID($stu_info,$banner_id);
    $pidm = $stu_info[0]['STU_PIDM'];
  }
//echo 'pidm after banner_id ='.$pidm;

 //Submission Logic
 if(trim($submit) != '')
 {
   // 	echo 'Here we are - submit is not null'; 	
 // $pidm = $_POST['cterm_code']?$_POST['cterm_code']:$_GET['cterm_code'];
 // $pidm = $_POST['gterm_code']?$_POST['gterm_code']:$_GET['gterm_code'];
  $former_pidm = $pidm;
  //  $former_cterm = $cterm_code;
  //  $former_gterm = $gterm_code;
 // $former_dept = $dept;
  //  echo "former_pidm=".$former_pidm;
  //  echo "former_cterm=".$former_cterm;
  //  echo "former_gterm=".$former_gterm;
 // echo "previous_pidm=".$previous_pidm;
 // echo "next_pidm=".$next_pidm;
     //Write all post information to variables
     //Trim values so that users don't input too much data
 	foreach($_POST as $key=>$value)
 	{
 		${$key} = $value;
		//                echo "key=".${$key};
		//                echo "value=".$value;
 	}

 //If Previous or Next PIDM is set then we update the pidm
	if(($advr != $advisor_selected or $deg_status != $deg_outcome_status_selected) and $pidm != $former_pidm) 
	  {
	    $pidm = "";
	  }
 
 if(trim($previous_pidm) != "")
 	$pidm = $previous_pidm;
 
 if(trim($next_pidm) != "")
 	$pidm = $next_pidm;

 //   echo 'new pidm='.$pidm;

$reg_comments = trim($reg_comments);
// echo 'reg_comments='.$reg_comments;
$dean_comments = trim($dean_comments);
// echo 'dean_comments='.$dean_comments;
$major = trim($major);
// echo 'major='.$major;
 // $deg_outcome_status = $trim($deg_outcome_status);
 // echo 'deg_outcome_status='.$deg_outcome_status;
	//print_r($appr_courses);
//if($current_term <> $former_cterm or $exp_grad_term <> $former_gterm)
//	  {
//	    $pidm = "";
//	  }
//	echo 'pidm here='.$pidm;
// only save if user hit Save; don't save if user is scrolling through students; only save comments if they have changed
// echo 'made it here first';

 if($pidm == $former_pidm )
   {
     //            echo "former_pidm=".$former_pidm." and new_pidm=".$pidm;
 	//Cut Reg Office and Deans comments down to 3000 characters
 	if(strlen($reg_comments) > 400)
 	{
 		$reg_comments = substr($reg_comments, 0, 400);
 	}
	//		echo 'reg_comments length =' . strlen($reg_comments);
	//		echo 'reg_comments= ' . $reg_comments;
	//	        echo 'current_term= ' . $current_term;
	if(strlen($reg_comments) > 0 )
	  {
	    $reg_comments .= $_SESSION['username'] . " " . date("m/d/Y") . "<br>";
		//		echo 'reg_comments='.$reg_comments;
		//                echo 'pidm='.$pidm;
		//                echo 'current_term='.$current_term;
	    $local_oracle->SaveRegComments($pidm,$current_term,$reg_comments);
	    $reg_comments = '';
	  }
 	if(strlen($dean_comments) > 400)
 	{
 		$dean_comments = substr($dean_comments, 0, 400);
 	}
     
	//	echo 'dean_comments length =' . strlen($dean_comments);
	//	echo 'dean_comments1= ' . $dean_comments;
	//	        echo 'current_term= ' . $current_term;


        if (strlen($dean_comments) > 0 )
	{
	  $dean_comments .= $_SESSION['username'] . " " . date("m/d/Y")."<br>";
	  //	echo 'dean_comments2= ' . $dean_comments;
	  $local_oracle->SaveDeanComments($pidm,$current_term,$dean_comments);
	  $dean_comments = '';
	}    
	//	echo 'pidm='.$pidm;
	//	        echo 'deg_outcome_status='.$deg_outcome_status;
	$deg_outcome_rec = array();
	$local_oracle->getDegOutcomeRec($deg_outcome_rec,$pidm);
	//        echo 'deg_outcome_status from Banner='.$deg_outcome_rec[0]['SHRDGMR_DEGS_CODE'];
    if ($deg_outcome_rec[0]['SHRDGMR_DEGS_CODE'] != $deg_outcome_status and $deg_outcome_status != '')
      {
	  $seq_no = $deg_outcome_rec[0]['SHRDGMR_SEQ_NO'];
	  $local_oracle->updateDegStatus($pidm,$seq_no,$deg_outcome_status,$reader);
		// 	    	$local_oracle->insertRecord('shrdgmr', $array_to_insert, $table_columns);
 	}

   }
 }
//echo 'made it here';
 //Get all graduates to use in Previous and Next buttons
 $grads = array();
 $previous = '';
 $next = '';
 $temp = array();
 // mms hard code current_term and exp_grad_term for now
if($current_term == "")
  {
    $current_term = '201402';
  }

if($exp_grad_term == "")
  {
    $exp_grad_term = '201402';
  }

 // mms hard code current_term and exp_grad_term for now
// $current_term = '201402';
//echo 'current_term ='.$current_term;
//echo 'exp_grad_term ='.$exp_grad_term;
// $exp_grad_term = '201402';
$c_selected = $current_term;
$g_selected = $exp_grad_term;
$o_selected = $deg_outcome_status_selected;
$a_selected = $advisor_selected;
//echo 'a_selected='.$a_selected;
//echo 'advisor='.$advisor;
$y_checked = '';
$n_checked = '';
$u_checked = '';
$c_checked = '';

//echo 'deg_outcome_status='.$deg_outcome_status;
//echo 'deg_outcome_status_selected='.$deg_outcome_status_selected;

$local_oracle->getAllGrads($temp, $current_term, $exp_grad_term, $advisor_selected, $deg_outcome_status_selected);
//echo 'temp=';
//print_r ($temp);
for($i = 0; $i<sizeof($temp);$i++)
  {
    array_push($grads, $temp[$i]);
  }
 
if($pidm == "")
  {
    $pidm = $grads[0]['STU_PIDM'];
  }

 	for($i = 0; $i<sizeof($grads); $i++)
 	{

	  if($grads[$i]['STU_PIDM'] == $pidm)
	    {
 	//Set Previous Pidm
 		if($i == 0)
 		{
 		$previous = $grads[sizeof($grads)-1]['STU_PIDM'];
 		} else {
 		$previous = $grads[$i-1]['STU_PIDM'];
 		}
 	
  		//Set Next Pidm
  		if($i == sizeof($grads)-1)
  		{
  		$next = $grads[0]['STU_PIDM'];
  		} else {
  		$next = $grads[$i+1]['STU_PIDM'];
  		}
 	}
	}
//	  echo 'pidm now ='.$pidm;

$temp1 = array();
$old_reg_comments = array();
$concat_reg_comments = "";
$local_oracle->getRegComments($temp1,$pidm,$current_term);
//echo 'temp1=';
//print_r($temp1);

 	for($i = 0; $i<sizeof($temp1);$i++)
 	{
 	array_push($old_reg_comments, $temp1[$i]);
 	}
//echo 'old_reg_comments=';
//print_r($old_reg_comments);
 
 	for($i = 0; $i<sizeof($old_reg_comments); $i++)
 	{
	  $concat_reg_comments .= trim($old_reg_comments[$i]['REG_COMMENTS']);
	}

//   echo "concat_reg_comments=".$concat_reg_comments;
$temp2 = array();
$old_dean_comments = array();
$concat_dean_comments = "";
$local_oracle->getDeanComments($temp2,$pidm,$current_term);
//print_r($temp2);

 	for($i = 0; $i<sizeof($temp2);$i++)
 	{
 	array_push($old_dean_comments, $temp2[$i]);
 	}
//print_r($old_dean_comments);
 
 	for($i = 0; $i<sizeof($old_dean_comments); $i++)
 	{
	  $concat_dean_comments .= trim($old_dean_comments[$i]['DEAN_COMMENTS']);
	}

//   echo "concat_dean_comments=".$concat_dean_comments;

//echo 'next= '.$next;
//echo 'previous= '.$previous;
//print_r($grads);
 //Grab Form Data From Oracle
 
$temp_terms = array();
$local_oracle->getTerms($temp_terms);

//$terms = array();

foreach ($temp_terms as $term)
{
  $tc = $term['STVTERM_CODE'];
  $td = $term['STVTERM_DESC'];
  //  echo 'tc='.$tc.' td='.$td;
  $terms[$td] = $tc;
}
//print_r($terms);
	// $last = sizeof($term_codes) - 1;
 
 $stu_mart_info = array();
 $local_oracle1->martIDsLookup($stu_mart_info,$pidm,$current_term,$exp_grad_term);
//print_r($stu_mart_info); 
 $stu_banner_info = array();
 $local_oracle->getGradBannerData($stu_banner_info,$pidm,$current_term);
 $deg_outcome_status = $stu_banner_info[0]['OUTCOME_STATUS'];
$outcome_status_list = array();
$local_oracle->getOutcomeStatusList($outcome_status_list);
$advisor_info = array();
//echo "outcome status=";
//print_r($outcome_status_list);
$local_oracle->getAdvisor($advisor_info,$pidm);
$advisor = $advisor_info[0]['ADVISOR'];
//echo "advisor=".$advisor;
$advisor_list = array();
$local_oracle->getAllAdvisors($advisor_list);
//echo "advisor list=";
//print_r($advisor_list);
// echo 'deg_outcome_status take 2='.$deg_outcome_status;
//echo 'current_term='.$current_term;
//echo 'exp_grad_term='.$exp_grad_term;
//print_r($stu_banner_info);
 $banner_deg_aud_info = array();
$local_oracle->bannerDegreeAuditLookup($banner_deg_aud_info,$pidm, $current_term, $exp_grad_term);
//echo 'banner_deg_aud_info=';
//print_r($banner_deg_aud_info);
 $mart_deg_aud_info = array();
 $local_oracle1->martDegreeAuditLookup($mart_deg_aud_info,$pidm,$current_term,$exp_grad_term);
//echo 'mart_deg_aud_info=';
//print_r($mart_deg_aud_info);
 $rule18_yn = array();
 $local_oracle->get18Rule($rule18_yn,$pidm);
//echo '18 rule=';
//print_r($rule18_yn);
 $unknown_dept = array();
 $local_oracle->getUnknownDept($unknown_dept,$pidm);
//echo 'unknown dept=';
//print_r($unknown_dept);
 $units_courses = array();
$local_oracle->eighteenUnitsLookup($units_courses,$pidm);
$num_courses = sizeof($units_courses);
//echo 'units_courses=';
//print_r($units_courses);
 $current_courses = array();
 $local_oracle->getCurrentCourses($current_courses,$pidm,$current_term);
//echo 'current_courses=';
//print_r($current_courses);
$units_outside_info = array();
$local_oracle->getUnitsOutside($units_outside_info,$pidm);
$units_outside = $units_outside_info[0]['UNITS_OUTSIDE'];
$total_units = $units_outside_info[0]['TOTAL_UNITS'];

$units_by_dept = array();
$local_oracle->get18RuleByDept($units_by_dept,$pidm);

 //$reg_comment_info = array();
// $local_oracle->getRegComments($reg_comment_info,$pidm,$current_term);
//print_r($reg_comment_info);
// $reg_comments = $reg_comment_info[0]['REG_COMMENTS'];
//print_r($reg_comment_info); 
 //$dean_comment_info = array();
 //$local_oracle->getDeanComments($dean_comment_info,$pidm,$current_term);
// $dean_comments = $dean_comment_info[0]['DEAN_COMMENTS'];
//print_r($dean_comment_info);
 //Clear previous class variables in case we have a deletion
 
 // example of displaying data based on role
 // if($my_role == ADMIN_ID || $my_role == LEADER) // LOAD VOTE 1, 2 & 3
 // else if($my_role == READER3) { // LOAD VOTE 3

 
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
  function printStudentInfoBlock()
 {
 	global $stu_mart_info, $stu_banner_info;
 	global $my_role, $advisor;
 	
 	echo "<table class='column_table' id='student_info_block'>";
 		echo "<tr>";
 			echo "<td align='right'><span style='font-size:14px'><b>Banner ID:</b></span></td>";
 			echo "<td><span style='font-size:14px'>".$stu_mart_info[0]['BANNER_ID']."</span></td>";
 			echo "<td align='right'><span style='font-size:14px'><b>Name:</b></td>";
 			echo "<td><span style='font-size:14px'>".$stu_mart_info[0]['STU_NAME']."</span></td>";
 			echo "<td align='right'><span style='font-size:14px'><b>Advisor:</b></td>";
 			echo "<td><span style='font-size:14px'>".$advisor."</span></td>";
			// 			echo "<td align='right'><span style='font-size:14px'><b>Current Term:</b></span></td>";
      			// 			echo "<td><span style='font-size:14px'>".$stu_mart_info[0]['TERM_DESC']."</span></td>";
			// 			echo "<td align='right'><span style='font-size:14px'><b>Expected Grad Term:</b></td>";
			//		echo "<td><span style='font-size:14px'>".$stu_banner_info[0]['EXP_GRAD_TERM']."</td>";
 			echo "<td align='right'><span style='font-size:14px'><b>Confidential:</b></span></td>";
                        if ($stu_mart_info[0]['CONFID_IND'] == 'Y')
 			echo "<td><span style='font-size:14px'><font color=red><b>".$stu_mart_info[0]['CONFID_IND']."</b></font></span></td>";
                        else
 			echo "<td><span style='font-size:14px'>".$stu_mart_info[0]['CONFID_IND']."</span></td>";
 		echo "</tr>";
 	echo "</table>";
 }

 function printStudentDetailBlock()
 {
 	global $stu_mart_info, $stu_banner_info;
 	global $my_role, $deg_outcome_status;
 	
 	echo "<table class='column_table' id='student_detail_block' cellpadding='2px' style='border:1px solid black;'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left' colspan=3>Student Status</th>";
 				echo "<td align='right' colspan=3>(<a href='javascript:showHideBlock(\"demographics\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Class:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_mart_info[0]['CLASS_DESC']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Admit Term:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_mart_info[0]['ADMIT_TERM']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Entered As:</b></td>";
                        if ($stu_mart_info[0]['TRANSFER_YN'] != '')
			  {
         			echo "<td style='border:1px solid black;'>Transfer</td>";
                          }
                        else if ($stu_mart_info[0]['TRAD_YN'] != '')
			  {
         			echo "<td style='border:1px solid black;'>Traditional</td>";
                          }
                        else if ($stu_mart_info[0]['DAVIS_YN'] != '')
			  {
         			echo "<td style='border:1px solid black;'>Davis</td>";
                          }

 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Student Status:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_mart_info[0]['STU_STAT_CODE']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Student Type:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_mart_info[0]['STU_TYPE_DESC']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Level:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_mart_info[0]['STU_LEVEL_CODE']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Major 1:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['MAJOR1_CODE']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Major 2:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['MAJOR2_CODE']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Minor:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['MINOR_CODE']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Diploma Name:</b></td>";
 			echo "<td style='border:1px solid black;' colspan=3>".$stu_banner_info[0]['DIPLOMA_NAME']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Outcome status:</b></td>";
			//$p_data[$i][2] = "<input name=personal[banner_id] size=20 placeholder='B12345678' value='$personal[banner_id]'> <br>";
			if($my_role == ADMIN_ID or $my_role == '100')
			  {
 			echo "<td class='no_edit' style='border:1px solid black;'><input name=deg_outcome_status size = 3 value='".$deg_outcome_status."'></td>";
			  }
			else
			  {
 			echo "<td style='border:1px solid black;' colspan=2>".$deg_outcome_status."</td>";
			  }
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Expected Grad Date:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['EXP_GRAD_DATE']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Expected Grad Term:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['EXP_GRAD_TERM']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Expected Grad Year:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['EXP_GRAD_YEAR']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Degree Sequence Number:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['DEGREE_SEQ_NO']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Grad Term:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['GRAD_TERM']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Grad Academic Year:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['GRAD_ACAD_YEAR']."</td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' align='right'><b>Grad Status Code:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['GRAD_STATUS_CODE']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Grad Date:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['GRAD_DATE']."</td>";
 			echo "<td style='border:1px solid black;' align='right'><b>Marched Previously:</b></td>";
 			echo "<td style='border:1px solid black;'>".$stu_banner_info[0]['MARCHED_PREVIOUSLY']."</td>";
 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printDegreeAuditBlock()
 {
 	global $mart_deg_aud_info, $banner_deg_aud_info;
 	global $my_role, $total_units, $num_courses;
 	
 	echo "<table class='column_table' id='degree_audit_block' cellpadding='2px' style='border:1px solid black;'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left' colspan=3>Degree Requirements Status</th>";
 				echo "<td align='right' colspan=3>(<a href='javascript:showHideBlock(\"decision\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>PE Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['PE_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>Icreds Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['ICREDS_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>GPA Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['GPA_SATISFIED']."</td>";

 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;' ><b>Units Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['UNITS_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>Writing Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['WRITING_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>MC Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['MC_SATISFIED']."</td>";

 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>Lab Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['LAB_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>Level 300 Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['LEVEL_300_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>FL Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['FL_SATISFIED']."</td>";

 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>QR Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['QR_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>ARS LL Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['ARS_LL_SATISFIED']."</td>";
 			echo "<td style='border:1px solid black;'><b>MM NPS Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['MM_NPS_SATISFIED']."</td>";

 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>SBA EC REP HS Satisfied:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['SBA_EC_REP_HS_SATISFIED']."</td>";
                        echo "<td colspan=4 style='border:1px solid black;'>  </td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>Cum Units:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['CUM_UNITS']."</td>";
 			echo "<td style='border:1px solid black;'><b>Total Term Credits:</b></td>";
 			echo "<td style='border:1px solid black;'>".$mart_deg_aud_info[0]['TOTAL_TERM_CREDITS']."</td>";
                        echo "<td colspan=2 style='border:1px solid black;'>  </td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>Total Number of Courses:</b></td>";
 			echo "<td style='border:1px solid black;'>".$num_courses."</td>";
 			echo "<td style='border:1px solid black;'><b>Total Number of Units:</b></td>";
 			echo "<td style='border:1px solid black;'>".$total_units."</td>";
                        echo "<td colspan=2 style='border:1px solid black;'>  </td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>Number of .25 Courses:</b></td>";
 			echo "<td style='border:1px solid black;'>".$banner_deg_aud_info[0]['NUM_COURSES_25']."</td>";
 			echo "<td style='border:1px solid black;'><b>Number of .25 Units:</b></td>";
 			echo "<td style='border:1px solid black;'>".$banner_deg_aud_info[0]['FRACT_UNITS_25']."</td>";
                        echo "<td colspan=2 style='border:1px solid black;'>  </td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>Number of .5 Courses:</b></td>";
 			echo "<td style='border:1px solid black;'>".$banner_deg_aud_info[0]['NUM_COURSES_50']."</td>";
 			echo "<td style='border:1px solid black;'><b>Number of .5 Units:</b></td>";
 			echo "<td style='border:1px solid black;'>".$banner_deg_aud_info[0]['FRACT_UNITS_50']."</td>";
                        echo "<td colspan=2 style='border:1px solid black;'>  </td>";
 		echo "</tr>";
 		echo "<tr>";
 			echo "<td style='border:1px solid black;'><b>Total Earned Units Counted:</b></td>";
 			echo "<td colspan=2 style='border:1px solid black;'>".$banner_deg_aud_info[0]['TOTAL_EARNED_UNITS']."</td>";
                        echo "<td colspan=3 style='border:1px solid black;'>  </td>";
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printCurrentCoursesBlock()
 {
 	global $current_courses;
 	global $my_role;
 	
 	echo "<table class='column_table' id='current_courses_block' style='border:1px solid black;' cellpadding='2px'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
 				echo "<th align='left' colspan=3>Current Courses</th>";
 				echo "<td align='right' colspan=3>(<a href='javascript:showHideBlock(\"courses\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
                		echo "<tr>";
 		        	echo "<td style='border:1px solid black;'><b>CRN:</b></td>";
 			        echo "<td style='border:1px solid black;'><b>Course Name:</b></td>";
 			        echo "<td colspan=2 style='border:1px solid black;'><b>Course Attributes:</b></td>";
      		                echo "</tr>";
                	for($i = 0; $i<sizeof($current_courses);$i++)
 	                  { 
      	                	echo "<tr>";
		        	echo "<td style='border:1px solid black;'>".$current_courses[$i]['CRN']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$current_courses[$i]['COURSE_NAME']."</td>";
 		        	echo "<td colspan=2 style='border:1px solid black;'>".$current_courses[$i]['CRN_ATTR1_DESC']."</td>";
      		                echo "</tr>";
                                if ($current_courses[$i]['CRN_ATTR2_DESC'] != '') {
      	                	echo "<tr>";
                                echo "<td>  </td>";
                                echo "<td>  </td>";
 		        	echo "<td colspan=2 style='border:1px solid black;'>".$current_courses[$i]['CRN_ATTR2_DESC']."</td>";
      		                echo "</tr>";
				}
                                if ($current_courses[$i]['CRN_ATTR3_DESC'] != '') {
      	                	echo "<tr>";
                                echo "<td>  </td>";
                                echo "<td>  </td>";
 		        	echo "<td colspan=2 style='border:1px solid black;' >".$current_courses[$i]['CRN_ATTR3_DESC']."</td>";
      		                echo "</tr>";
				}
                                if ($current_courses[$i]['CRN_ATTR4_DESC'] != '') {
      	                	echo "<tr>";
                                echo "<td>  </td>";
                                echo "<td>  </td>";
 		        	echo "<td colspan=2 style='border:1px solid black;'>".$current_courses[$i]['CRN_ATTR4_DESC']."</td>";
      		                echo "</tr>";
				}
                                if ($current_courses[$i]['CRN_ATTR5_DESC'] != '') {
      	                	echo "<tr>";
                                echo "<td>  </td>";
                                echo "<td>  </td>";
 		        	echo "<td colspan=2 style='border:1px solid black;'>".$current_courses[$i]['CRN_ATTR5_DESC']."</td>";
      		                echo "</tr>";
				}
                                if ($current_courses[$i]['CRN_ATTR6_DESC'] != '') {
      	                	echo "<tr>";
				//                                echo "<td> ' ' </td>";
                                echo "<td>  </td>";
                                echo "<td>  </td>";
 		        	echo "<td colspan=2 style='border:1px solid black;'>".$current_courses[$i]['CRN_ATTR6_DESC']."</td>";
      		                echo "</tr>";
				}
                                if ($current_courses[$i]['CRN_ATTR7_DESC'] != '') {
      	                	echo "<tr>";
                                echo "<td> ' ' </td>";
                                echo "<td> ' ' </td>";
 		        	echo "<td colspan=2 style='border:1px solid black;'>".$current_courses[$i]['CRN_ATTR7_DESC']."</td>";
      		                echo "</tr>";
				}
			  }
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function print18UnitsBlock()
 {
   global $units_courses, $rule18_yn, $unknown_dept, $units_by_dept;
   global $my_role, $units_outside, $total_units;
 	
 	echo "<table class='column_table' id='eighteen_units_block' style='border:1px solid black;' cellpadding='2px'>";
 		echo "<thead>";
 			echo "<tr class='table_head'>";
			if ($unknown_dept[0]['UNKNOWN_DEPT'] == 'U') {
 				echo "<th align='left' colspan=3>Satisfied 18 Units Requirement: Unknown</th>";
			}
                        else {
 				echo "<th align='left' colspan=3>Satisfied 18 Units Requirement: ".$rule18_yn[0]['EIGHTEEN_UNITS_SATISFIED']."</th>";
			  // 				echo "<th align='left' colspan=3>Satisfied 18 Units Requirement: ".$rule18_yn."</th>";
			}
         		echo "<td align='right' colspan=3>(<a href='javascript:showHideBlock(\"units\")'>Show/Hide</a>)</td>";
 			echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";
       		echo "<tr>";
         	echo "<td style='border:1px solid black;'><b>Term</b></td>";
         	echo "<td style='border:1px solid black;'><b>CRN</b></td>";
         	echo "<td style='border:1px solid black;'><b>Dept</b></td>";
         	echo "<td style='border:1px solid black;'><b>Course</b></td>";
         	echo "<td style='border:1px solid black;'><b>Title</b></td>";
         	echo "<td style='border:1px solid black;'><b>Units</b></td>";
      	       	echo "</tr>";
                	for($i = 0; $i<sizeof($units_courses);$i++)
 	                  {
                		echo "<tr>";
				// 		        	echo "<td class='no_edit'>".$units_courses[$i]['TERM_CODE']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_courses[$i]['TERM_DESC']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_courses[$i]['CRN']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_courses[$i]['AUDIT_DEPT']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_courses[$i]['SUBJ_CODE']." ".$units_courses[$i]['CRSE_NUMB']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_courses[$i]['CRSE_TITLE']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_courses[$i]['NUM_UNITS']."</td>";
      	                	echo "</tr>";

			  }
       		echo "<tr>";
         	echo "<td </td>";
      	       	echo "</tr>";
       		echo "<tr>";
         	echo "<td style='border:1px solid black;'><b>Dept</b></td>";
         	echo "<td style='border:1px solid black;'><b>Courses</b></td>";
         	echo "<td style='border:1px solid black;'><b>Units</b></td>";
      	       	echo "</tr>";
                	for($i = 0; $i<sizeof($units_by_dept);$i++)
 	                  {
                		echo "<tr>";
 		        	echo "<td style='border:1px solid black;'>".$units_by_dept[$i]['AUDIT_DEPT']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_by_dept[$i]['NUM_COURSES_DEPT']."</td>";
 		        	echo "<td style='border:1px solid black;'>".$units_by_dept[$i]['NUM_UNITS_DEPT']."</td>";
      	                	echo "</tr>";

			  }
       		echo "<tr>";
         	echo "<td style='border:1px solid black;' colspan=2><b>Units Outside: </b></td>";
		//         	echo "<td </td>";
        	echo "<td style='border:1px solid black;'>".$units_outside."</td>";
               	echo "</tr>";
       		echo "<tr>";
         	echo "<td style='border:1px solid black;' colspan=2><b>Total Units: </b></td>";
		//         	echo "<td </td>";
        	echo "<td style='border:1px solid black;'>".$total_units."</td>";
               	echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printRegAddCommentsBlock()
 {
 	global $reg_comments;
 	global $my_role, $col_width;
 	
 	echo "<table class='column_table' id='reg_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='left'>Add Registrar Comments/Notes</th>";
 			echo "<td align='right'>(<a href='javascript:showHideBlock(\"overall\")'>Show/Hide</a>)</td>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td colspan=2 align='center'>
 		                <textarea class='mceEditorSmall' id='reg_comments' name='reg_comments' cols=$col_width rows=20 onkeyup='textCounter(\"reg_comments\",\"overall_comment_count\",\"4000\")' maxlength='4000'>".htmlentities($reg_comments)."</textarea>
 		            </td>";
 		echo "</tr>";
// 		echo "<tr>";
// 		    echo "<td colspan=2>";
// 		        echo "<span id='overall_comment_count' style='color:red'>4000 characters remaining</span>";
// 		    echo "</td>";
// 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
 }
 
 function printRegCommentsBlock()
 {
   global $reg_comments, $concat_reg_comments;
 	global $my_role, $col_width;
 	
 	echo "<table class='column_table' id='concat_reg_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='left'>Registrar Comments/Notes</th>";
 			echo "<td align='right'>(<a href='javascript:showHideBlock(\"rconcat\")'>Show/Hide</a>)</td>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td colspan=2 align='center'>
 		                <textarea class='mceEditor' id='concat_reg_comments' name='concat_comments' cols=$col_width rows=5 onkeyup='textCounter(\"concat_comments\",\"concat_comment_count\",\"400\")' maxlength='400'>".htmlentities($concat_reg_comments)."</textarea>
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

 function printDeanAddCommentsBlock()
 {
 	global $dean_comments;
 	global $my_role, $col_width;
 	
 	echo "<table class='column_table' id='dean_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='left'>Add Deans Comments/Notes</th>";
 			echo "<td align='right'>(<a href='javascript:showHideBlock(\"secondary\")'>Show/Hide</a>)</td>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td colspan=2 align='center'>
 		                <textarea class='mceEditorSmall' id='dean_comments' name='dean_comments' cols=$col_width rows=20 onkeyup='textCounter(\"dean_comments\",\"dean_comment_count\",\"4000\")' maxlength='4000'>".htmlentities($dean_comments)."</textarea>
 		            </td>";
 		echo "</tr>";
// 		echo "<tr>";
// 		    echo "<td colspan=2>";
// 		        echo "<span id='dean_comment_count' style='color:red'>4000 characters remaining</span>";
// 		    echo "</td>";
// 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
 }

 function printDeanCommentsBlock()
 {
   global $dean_comments, $concat_dean_comments;
 	global $my_role, $col_width;
 	
 	echo "<table class='column_table' id='concat_dean_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='left'>Dean Comments/Notes</th>";
 			echo "<td align='right'>(<a href='javascript:showHideBlock(\"dconcat\")'>Show/Hide</a>)</td>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td colspan=2 align='center'>
 		                <textarea class='mceEditor' id='concat_dean_comments' name='concat_comments' cols=$col_width rows=5 onkeyup='textCounter(\"concat_comments\",\"concat_comment_count\",\"400\")' maxlength='400'>".htmlentities($concat_dean_comments)."</textarea>
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
#concat_dean_comments_block
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
	var decision = 0;
	var demographics = 0;
	var courses = 0;
	var units = 0;
	var reader = 0;
	var overall = 0;
	var secondary = 0;
	var family = 0;
	var reqtest = 0;
	var opttest = 0;
	var selftest = 0;
	var extracurricular = 0;
	var contacts = 0;
	var student_address = 0;
	var show_senior = 0;
	var show_excur = 0;
	var show_sibling = 0;
	var offset = 0;
	var last_special = 0;

window.onload = function()
{
  showHideBlock('units');
  textCounter('reg_comments', 'overall_comment_count', '4000');
  textCounter('dean_comments', 'dean_comment_count', '4000');
}

	//Preferences from MySQL Database
//	var vote_pref = "<?php echo $preferences['voting'];?>";

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
	  case 'decision':		  
		  if(decision == 0)
		  {
			  $('#degree_audit_block tbody').hide();
			  decision = 1;
		  } else {
			  $('#degree_audit_block tbody').show();
			  decision = 0;
		  }
		  break;
		  
	  case 'courses':
		  if(courses == 0)
		  {
			  $('#current_courses_block tbody').hide();
			  courses  = 1;
		  } else {
			  $('#current_courses_block tbody').show();
			  courses = 0;
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
		  
	  case 'demographics':
		  if(demographics == 0)
		  {
			  $('#student_detail_block tbody').hide();
			  demographics = 1;
		  } else {
			  $('#student_detail_block tbody').show();
			  demographics = 0;
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

	  case 'dconcat':
		  if(overall == 0)
		  {
			  $('#concat_dean_comments_block tbody').hide();
			  overall = 1;
		  } else {
			  $('#concat_dean_comments_block tbody').show();
			  overall = 0;
		  }
		  break;

	  case 'secondary':
		  if(secondary == 0)
		  {
			  $('#dean_comments_block tbody').hide();
			  secondary = 1;
		  } else {
			  $('#dean_comments_block tbody').show();
			  secondary = 0;
		  }
		  break;	 

	  }
  }

  function showSenior()
  {
	  if(show_senior == 0)
	  {
		  $('.hidden_senior').show();
		  show_senior = 1;
	  } else {
		  $('.hidden_senior').hide();
		  show_senior = 0;
	  }
  }

  function showExcur()
  {
	  if(show_excur == 0)
	  {
		  $('.hidden_excur').show();
		  show_excur= 1;
	  } else {
		  $('.hidden_excur').hide();
		  show_excur = 0;
	  }
  }

  function showSibling()
  {
	  if(show_sibling == 0)
	  {
		  $('.hidden_sibling').show();
		  show_sibling= 1;
	  } else {
		  $('.hidden_sibling').hide();
		  show_sibling = 0;
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

  function calculatePercentile()
  {
	  var rank = $('#rank').val();
	  var class_size = $('#class_size').val();
	  var percentile = '';

	  if(rank!='' && class_size!='' && !isNaN(rank) && !isNaN(class_size))
	  {
		  percentile = Math.round((1-(rank/class_size))*100);
	  }
	  
	  $('#percentile').val(percentile);
  }

  function showVoteCodes()
  {
	  if($('#vote_codes').is(':visible'))
	  {
		  $('#vote_codes').hide();
	  } else {
		  $('#vote_codes').show();
	  }
  }

  function goToPrevious(id)
  {
	  $('#previous_pidm').val(id);
	  $('#submit').click();
  }
  
  function goToNext(id)
  {
	  $('#next_pidm').val(id);
	  $('#submit').click();
  }

  function uncheckW()
  {
	$('#id_w_value').attr('checked', false);
  }

  function uncheckU()
  {
	$('#id_u_value').attr('checked', false);
  }
  
</script>

<!-- HTML LAYOUT -->
<div id="container">

<span><a href='#' onclick="javascript:goToPrevious('<?php echo $previous; ?>')">Previous Student</a> | <a href='#' onclick="javascript:goToNext('<?php echo $next?>')">Next Student</a></span> 
<br><br>
<form id='grad_audit_form' name='grad_audit_form_form' method='POST' action='audit_detail.php'>
	<div id="top_container">
		<?php 
  //  echo 'Hello top container!';
  printTermSelectionBlock();
  printStudentInfoBlock(); 
		?>
		<table width='100%'>
		    <tr>
		        <td align=left>
		            <input type='submit' name='submit' value='Save' />
		        </td>
		    </tr>
		</table>
	</div>
	
	<div class='clear'></div>
	
	<div id="left_container">
		<?php 

		 //	        echo 'Hello World';
                printStudentDetailBlock(); 
                printDegreeAuditBlock();
                print18UnitsBlock();
                printCurrentCoursesBlock();
		?>
	</div>
	
	<div id="right_container">
		<?php 
	  //	    	echo 'Hello Again';
	if($my_role == ADMIN_ID or $my_role == '2')
	  {
                printRegAddCommentsBlock(); 
                printRegCommentsBlock(); 
                printDeanCommentsBlock(); 
	  }
	if($my_role == '3')
	  {
                printDeanAddCommentsBlock(); 
                printDeanCommentsBlock(); 
                printRegCommentsBlock(); 
	  }
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
	<center><input type='submit' id='submit' name='submit' value='Save'/></center>
	</form>
	
</div>
