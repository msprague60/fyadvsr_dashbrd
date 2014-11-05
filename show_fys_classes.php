<?php

  // This is to handle the creation of the form.


  // Check to see if  we want to skip navs and graphics.

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

include_once "config.php";
include_once "includes/header.php";

// Add this app specific javascript to toggle states if USA is selected. Also turn on tinyMCE for the textarea.

?>

<style>
#formtable1 thead { display: none; } 
#formtable2 thead { display: none; } 
#formtable3 thead { display: none; } 


#div-1a {
float:left;
width:200px;
}
#div-1b {
float:left;
width:200px;
}
</style>

<script>

tinyMCE.init({
  theme : "advanced",
      mode : "specific_textareas",
      editor_selector: "mceEditor",
      height:"200",
      width:"600",
      plugins : "paste",
      theme_advanced_buttons3_add : "pastetext,pasteword,selectall",
      });

tinyMCE.init({
  theme : "advanced",
      mode : "specific_textareas",
      editor_selector: "mceEditorSmall",
      height:"125",
      plugins : "paste",
      theme_advanced_buttons3_add : "pastetext,pasteword,selectall",
      });

</script>

<script>

function showBoth () {
  $("#"+"formtable2").show("slow");
  $("#"+"formtable3").show("slow");
}


function onCampus () {
  $("#"+"formtable2").hide("slow");
  $("#"+"formtable3").show("slow");
}


function offCampus () {
  $("#"+"formtable3").hide("slow");
  $("#"+"formtable2").show("slow");

}
</script>

<?php

$fys_choices = array ('1st Choice',
		      '2nd Choice',
		      '3rd Choice',
		      '4th Choice',
		      '5th Choice',
		      'Clear the entry'
		      );

$tables = array('prereg',
		'fys',
		);

$url_to_use = "/course_browser/display_single_course_cb.php";


if ($my_role != ADMIN_ID) {
  
  $current_time = strtotime("now");
  
  if ($dates[1] > $current_time) {
    print "System is currently closed. Plese visit us on: " .
      date('m/d/Y',$dates[1]) . " at " . date('H:i',$dates[1]) . " when it will open.";
    print "<br><br><a href=\"logout.php\">Click here to logout</a>\n";
    $_SESSION = array();
    session_destroy();
    include_once "includes/footer.php";
    exit;
  }

  if ($dates[2] < $current_time) {
    print "System closed on: " .
      date('m/d/Y',$dates[2]) . " at " . date('H:i',$dates[2]) ;
    print "<br><br><a href=\"logout.php\">Click here to logout</a>\n";

    $_SESSION = array();
    session_destroy();
    include_once "includes/footer.php";
    exit;
  }

 }

// Print the title and navigations
// Navigation here is role based. Only show navs for the role by indicating 1 at the end.

$explanation = "";

printPageTitle("Pre-Registration For First Year Writing And First Year Seminars",$explanation);

if ($no_navs != "1") {
  $menu_option = 1;
  printNavigation1($my_role, $roles, $role_titles, $master_navigations,$menu_option);
 }

$viewed = $local_mysql->getVideoView($_SESSION['username']);

if ($viewed == 0) {
  print "<center>You have not yet viewed the required introductory video. Please watch it fully before proceeding further by clicking on the Introduction link above.</center>";
  exit;
 }

?>

<center>

<?php


// Get all the admin maintained information

//Add ---Choose One --- as an option to the dropdown


// Check to see if the Admin is coming here fresh to create a new entry.

$passed_id = $_POST['id'];
if (trim($passed_id) == "") {
  $passed_id = $_GET['id'];
 }

// Check to see if an advisor or committee member is trying to create a form. If so, tell them they cannot do it.

// If it is an admin type entry and a tanner id is provided, check the validity. This comes from
// list_submissions


if ( ($my_role == ADMIN_ID) 
     //||     ($my_role == $roles['advisor']) ||
     //($my_role == $roles['committee'])
     )
  {
    $id = safeString($passed_id,1);
    if (trim($id) != "") {
      $return = $local_mysql->isValidId('prereg',$id);
      if ($return != "") {
	print $return;
	include_once "includes/footer.php";
	exit;
      }
    }
  }

 else {

   // Get the  ID of the proposal for the logged in person if it exists.
   
   $id = "";

   $result = array();
   $local_mysql->getId('prereg',$result);
   
   $id = $result[0];
 }

$courses = array();

$additional = "and ( ssrattr_attr_code= 'FYS' ) ";
$course_class->getCoursesbyTerm($courses, $fall_semester,$additional);

$fys_courses_fall_info = array();

for ($i = 0; $i < sizeof($courses); $i++) {
  $key = $courses[$i]['CRN'] . "_" . $courses[$i]['TERM_CODE'];
  $value = $courses[$i]['SUBJ_CODE'] . " " . $courses[$i]['CRSE_NUMBER'] . 
    " " .
    $courses[$i]['REAL_LONG_TITLE'];
  $value = preg_replace('/first-year seminar\:/i','',$value);
  $fys_courses_fall_info[$key] = $value;
  
 }

$courses= array();
$additional = "and ( ssrattr_attr_code= 'FYS' ) ";
$course_class->getCoursesbyTerm($courses, $spring_semester,$additional);

$fys_courses_spring_info = array();

for ($i = 0; $i < sizeof($courses); $i++) {
  $key = $courses[$i]['CRN'] . "_" . $courses[$i]['TERM_CODE'];
  $value = $courses[$i]['SUBJ_CODE'] . " " . $courses[$i]['CRSE_NUMBER'] .
    " " .
    $courses[$i]['REAL_LONG_TITLE'];
  $value = preg_replace('/first-year seminar\:/i','',$value);
  $fys_courses_spring_info[$key] = $value;
 }

$values = array();
$local_mysql->getAllData('prereg', $values,$id);

foreach ($values as $key=>$value) {
  ${$key} = $value;
}

$values = array();
$local_mysql->getAllMultipleDataFromUsername('fys', $values,$username,' order by preference');
$fys_courses = array();
for ($i = 0; $i < sizeof($values); $i++) {
  $v = "s_" . $values[$i]['crn'] . "_" . $values[$i]['term'];
  $fys_courses[$v] = $values[$i]['preference'];
 }


if ($last_name == '') {
  $info = array();
  $oracle->enteringStudentsLookup($username ,
				  $info,'sdsespu.user_name','Y');
  
  $vars = array_keys($info[0]);
  foreach ($vars as $v) 
    {
      if ($v == "ID") {
	$v = 'BANNER_ID';
      }
      ${strtolower($v) } = $info[0]{$v};
    }
 }

$i = 0;

if ($error != "") {
  $data[$i]['one'] = '';
  $data[$i]['two']="<center><font color=\"#ff0000\">$error</font></td>\n</tr>\n";
  $i++;
}


$data[$i]['one'] = "";
$data[$i]['two'] = "<h3>About You</h3>\n";

$i++;
$data[$i]['one'] = "<b>Last Name:</b> \n";
$data[$i]['two'] = $last_name . "\n";

$i++;

$data[$i]['one'] = "<b>First Name:</b> \n" ;
$data[$i]['two'] = $first_name . "\n";

$i++;

$data[$i]['one'] = "<b>Middle Initial:</b> \n" ;
$data[$i]['two'] = $middle_initial . "\n";

$i++;

$data[$i]['one'] = "<b>Preferred Name:</b> \n" ;
$data[$i]['two'] = $preferred_name . "\n"; 

$i++;

$data[$i]['one'] = "<b>E-mail:</b> \n";
$data[$i]['two'] = $email . "\n";


$i++;
$data[$i]['one'] = "Do you wish to register for a First-Year Seminar?";

$string = "";
if (strtolower($fys) == 'y') {
  $string = "Yes";
 }
 else if (strtolower($fys) == 'n') {
   $string = "No";
 }
$data[$i]['two'] = $string;

$i++;
$data[$i]['one'] = '';
$data[$i]['two'] = "Tell us your top five preferences among the Fall First Year Seminar course offerings,  by entering the number 1 to 5 in the box next to the course.  You will not be assigned to a course whose box you leave blank. 
";

$i++;
$data[$i]['one'] = "First-Year Seminars (choose up to 5 total)";
$data[$i]['two'] = "";

$string = "<div id='div-1a'>\n";
$string .= "<b>Fall Classes</b>\n<br>\n<br>\n";

foreach ($fys_courses as $k=>$v) {
  $parts = split("_",$k);
  $crn = $parts[1];
  $term = $parts[2];
  $k1 = preg_replace('/^s_/','',$k);

  if ($fys_courses_fall_info[$k1] != "") {
    $v1 = $fys_courses_fall_info[$k1];
    $v1 = "<a href=\"$url_to_use?crn=$crn&semester=$term" .
      "&skip_graphics=1&no_navs=1\" onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', width:800} )\">$v1</a>"; 
    for ($j = 1; $j < 6; $j++) {
      if ($v == $j) {
	$string .= $j . " " .$v1 . "<br>\n";
      }
    }
  }
}

$string .= "</div>\n";

$string .= "<div id='div-1b'>\n";
$string .= "<b>Spring  Classes</b>\n<br>\n<br>\n";

foreach ($fys_courses as $k=>$v) {
  $parts = split("_",$k);
  $crn = $parts[1];
  $term = $parts[2];
  $k1 = preg_replace('/^s_/','',$k);

  if ($fys_courses_spring_info[$k1] != "") {
    $v1 = $fys_courses_spring_info[$k1];
    $v1 = "<a href=\"$url_to_use?crn=$crn&semester=$term" .
      "&skip_graphics=1&no_navs=1\" onclick=\"return hs.htmlExpand(this, { objectType: 'iframe', width:800} )\">$v1</a>"; 
    for ($j = 1; $j < 6; $j++) {
      if ($v == $j) {
	$string .= $j . " " .$v1 . "<br>\n";
      }
    }
  }
}

$string .= "</div>\n";
$data[$i]['two'] = $string;

$i++;
$data[$i]['one'] = "If you chose BISC 112 or BISC 113 in the previous question, please confirm that you scored 4 or 5 in the Biology AP Exam by clicking on this checkbox.";
$data[$i]['two'] = ($fys_prereq == ''?'No':'Yes');

$m = 1;

$columns = array("one"=>'one',
		 "two"=>'two');

$t_columns = array();

foreach ($columns as $key=>$value) {
  $t_columns[$key]['key'] = $value;
  $t_columns[$key]['label'] = $value;
  $t_columns[$key]['sortable'] = "false";
  $t_columns[$key]['width'] = '100';
  $t_columns[$key]['className'] = '';
}

$t_columns['two']['width'] = '700';
$t_columns['one']['width'] = '300';


$yui = new yuitable("formtable1");
$yui->setColumns($t_columns);
print "<center>\n";

$yui->table($data, $urls, $sort,0);
$yui->printFooter();
print "</center>\n";

if ( ($my_role == ADMIN_ID) & ($id != "") ) {
  print  "<input type='hidden' name='id' value=\"$id\">\n";
 }


if ($no_navs != "") {
  print  "<input type='hidden' name='no_navs' value=\"$no_navs\">\n";
 }


if ($skip_graphics != "") {
  print  "<input type='hidden' name='skip_graphics' value=\"$skip_graphics\">\n";
 }

print  "</center>\n";


// Hide the header_container div if requested.

if ($skip_graphics == 1) {
  print  "<script>\n";
  print  "$('#header_container').hide()";
  print  "</script>\n";
}




include_once "includes/footer.php";

?>


