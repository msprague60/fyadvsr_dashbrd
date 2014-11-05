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
 //if($my_role != ADMIN_ID && $my_role != ADMIN_ACAD)
 //{
 //	echo "<center>You are not allowed to access this page.<br><a href='logout.php'>Logout Here</a></center>";
 //	exit;
 //}

if ($no_navs == "") {
	printNavigation1($my_role, $roles, $role_titles, $master_navigations,1);
}

printPageTitle("Adviser Notes");

 //Set the column width for textareas based on the browser being used
 $col_width = 92;
 
 if(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox'))
 {
     $col_width = 73;
 }
 
// hardcode term for now
//$advisor = 'avelench';
$advisor = $_SESSION['username'];
$current_term = '201409';

//$advisor = $_SESSION['username'];
$uname = $_GET['name'];

$adv_comments = $_POST['adv_comments']?$_POST['adv_comments']:$_GET['adv_comments'];
//$adv_comments = $_GET['adv_comments'];
$adv_comments = trim($adv_comments);

//echo 'uanme='.$uname;
//echo 'adv_comments='.$adv_comments;

$apidm = array();
$local_oracle->getPidmByUser($apidm, $advisor);
$adv_pidm = $apidm[0]['PIDM'];

//                echo 'adv_pidm='.$adv_pidm;

if(isset($uname))
{
	$student_info = array();
	$local_oracle->getGeneralData($student_info, $uname);
        $stu_pidm = $student_info[0]['PIDM'];

	//                echo 'stu_pidm='.$stu_pidm;
	//                echo 'adv_pidm='.$adv_pidm;
	//                echo 'current_term='.$current_term;
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

	    $local_oracle1->SaveAdvComments($stu_pidm,$adv_pidm,$current_term,$adv_comments);
	    $adv_comments = '';
	  }

$temp1 = array();
$old_adv_comments = array();
$concat_adv_comments = "";
 $local_oracle1->getAdvComments($temp1,$stu_pidm,$adv_pidm,$current_term);
 //echo 'temp1=';
 //print_r($temp1);

 	for($i = 0; $i<sizeof($temp1);$i++)
 	{
 	array_push($old_adv_comments, $temp1[$i]);
 	}
//echo 'old_adv_comments=';
//print_r($old_adv_comments);
 
 	for($i = 0; $i<sizeof($old_adv_comments); $i++)
 	{
	  $concat_adv_comments .= trim($old_adv_comments[$i]['ADV_COMMENTS']);
	  $concat_adv_comments .= '<br>';
	}

	//   echo "concat_adv_comments=".$concat_adv_comments;

	
	$data = array();
	$urls = array();
	$sort = array();
	$columns = array('one'=>'one', 'two'=>'two');
	$i = 0;
	
	$data[$i]['one'] = "<b>Banner ID</b>";
	$data[$i]['two'] = "<b>Student Name</b>";
	$i++;
	
	$data[$i]['one'] = $student_info[0]['ID'];
	$data[$i]['two'] = $student_info[0]['CURRENT_NAME'];
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

#overall_comments_block
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

</script>

<!-- HTML LAYOUT -->
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
			echo "<td><a href=\"stu_sched.php?name=$uname\">"."Schedules</td>";
                         ?>
			</tr>
		</table>
	</div>
	<div id="results">
	<?
			  //                        $yui->table($data, $urls, $sort, 0);
			  //                        $yui->printFooter(); 
 	echo "<table class='column_table' id='student_info_block'>";
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
	<div id="entry">
	<form name='advisor_comments' method='POST' action=''>
		<table width='100%'>
		    <tr>
		        <td align=left>
		            <input type='submit' name='submit' value='Save' />
		        </td>
		    </tr>
		</table>
        <?
 	echo "<table class='column_table' id='adv_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='center'>Add Comments/Notes</th>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td align='center'>
 		                <textarea class='mceEditorSmall' id='adv_comments' name='adv_comments' cols=$col_width rows=20 onkeyup='textCounter(\"adv_comments\",\"overall_comment_count\",\"2000\")' maxlength='2000'>".htmlentities($adv_comments)."</textarea>
 		            </td>";
 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
        ?>
	</form>
	</div>
        <div>
        <?
 	echo "<table class='column_table' id='concat_adv_comments_block' cellpadding='2px'>";
 		echo "<thead>";
 		echo "<tr class='table_head'>";
 			echo "<th align='center'>Comments/Notes</th>";
 		echo "</tr>";
 		echo "</thead>";
 		echo "<tbody>";;
 		echo "<tr>";
 		    echo "<td align='center'>
 		                <textarea class='mceEditor' id='concat_adv_comments' name='concat_comments' cols=$col_width rows=5 onkeyup='textCounter(\"concat_comments\",\"concat_comment_count\",\"6000\")' maxlength='6000'>".htmlentities($concat_adv_comments)."</textarea>
 		            </td>";
 		echo "</tr>";
 		echo "</tbody>";
 	echo "</table>";
        ?>
	</div>

</center>

