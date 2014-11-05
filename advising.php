
<?php   // Include header and config, and check to see if we want to skip navs and graphics.

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

?>

<style>
  #formtable1 thead { 
    display: none; 
  }
  tr, 
  tr b, 
  tr a {
    font-size: 14px;
  }
  input[type=text] {
    width: 694px;
    height: 20px;
  }
  td {
    font-size: 14px;
  }
  textarea {
    width: 681px;
    height: 162px;
    margin: 8px;
  }
</style>


<?php

// Arrays for form field validation (not needed, no requirements??)
  $required_fields = array (
        // 'pronoun'
        );

  $required_fields_detail = array (
        // 'pronoun' => 'Question regarding your preferred pronoun'
        );

// Check and make sure that the app is open for students.

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
  if ($no_navs != "1") {
    $menu_option = 1;
    printNavigation1($my_role, $roles, $role_titles, $master_navigations,$menu_option);
  }

  $explanation = "The questions below are designed to help you share information with your faculty advisor and other advisors on campus that can help them as they plan to support you in your first year at Wellesley College.  All questions are optional, but the more information you give us, the better we can anticipate your needs.  If you have questions about advising in general or about this form, please email Dean O'Keefe at <a href='mailto:'jokeefe@wellesley.edu'>jokeefe@wellesley.edu</a>.  Thanks!";

  printPageTitle("Faculty Advisor Information Form",$explanation);

?>


<form name="advising_form" enctype="multipart/form-data" method="post" action="">
<center>

<?php

// Get all the admin maintained information

  // Check to see if the Admin is coming here fresh to create a new entry.
  $passed_id = $_POST['id'];
  if (trim($passed_id) == "")
  {
    $passed_id = $_GET['id'];
  }

  if ($my_role == ADMIN_ID)
  {
    $id = safeString($passed_id,1);
    if (trim($id) == "")
    {
      $admin_new_entry= 1;
  //     print "No ID found";
  //     include_once "includes/footer.php";
  //     exit;
    }
  }


  // Check to see if an advisor or committee member is trying to create a form. If so, tell them they cannot do it.
  if (($student_info[0]['EMAIL'] == "") && ($my_role <= DEFAULT_ID))
  {
    $id = safeString($passed_id);
    if (trim($id) == "")
    {
      print "You are trying to create a new form. You cannot do it.<br>\n";
      include_once "includes/footer.php";
      exit;
    }
  }

  // If it is an admin type entry and a tanner id is provided, check the validity. This comes from list_submissions
  if ($my_role == ADMIN_ID)
  {
    $id = safeString($passed_id);
    if (trim($id) != "")
    {
      $return = $local_mysql->isValidId('advising',$id);
      if ($return != "")
      {
      print $return;
      include_once "includes/footer.php";
      exit;
       }
     }
  } else if ($my_role != ADMIN_ID) {

     // Get the  ID of the proposal for the logged in person if it exists.
     $result = array();
     $local_mysql->getId('advising',$result);

     $id = $result[0];
  }

$submit = $_POST['submit'];

// Is this a submission? If so, process the variables.
if (trim($submit) != "")
{
  // Move the Post variables to recognizable variable names.
  foreach ($_POST as $key => $value)
  {
    ${$key} = $value;
  }

  // Check for Admin role
  if ($my_role == ADMIN_ID)
  {
    $student_info = array();
    $oracle->enteringStudentsLookup($applicant, $student_info,'sdsespu.user_name','Y');
  }

  $vars = array_keys($student_info[0]);

  foreach ($vars as $v)
  {
    if ($v == "ID")
    {
      $v = 'BANNER_ID';
    }
    ${strtolower($v)} = $student_info[0]{$v};
  }

  $applicant = $email;

  // Is user submitting?
  if ($submit == "Submit")
  {
    // Check and make sure that the required fields are not null.
    // Also other requirements are checked here.
    // if one is coming here after save, you need to retrieve the file names from db.
    $error = check_required_fields($required_fields_detail,$id, $local_mysql,1);
  }

  if ($error == "")
  {
    // Is this a brand new entry? If so, get a new id.
    if ($id == "")
    {
      $id = $local_mysql->createNewId('advising');
    }

    // If everything checks out, insert data to the database.
    if ($error == "")
    {

      /* Set types for bind parameters. Types: s = string, i = integer, d = double,  b = blob */
      $types = "sssssssssssssssi";

      // Array to collect all submission data
      $array_to_insert = array();

      // Get username
       $uname = "";

        if ($my_role == ADMIN_ID)
        {
          $parts = split('@',$applicant);
          $uname = $parts[0];
        }
        else
        {
          $uname = $_SESSION['username'];
        }

      array_push($array_to_insert,$uname);
      array_push($array_to_insert,$areas_of_study);
      array_push($array_to_insert,$two_courses);
      array_push($array_to_insert,$skills);
      array_push($array_to_insert,$language);
      array_push($array_to_insert,$transition);
      array_push($array_to_insert,$other_info);
      array_push($array_to_insert,$preferred_name);
      array_push($array_to_insert,$pronoun);
      array_push($array_to_insert,$high_school_subjs);   
      array_push($array_to_insert,$_POST['programs']['latina']);
      array_push($array_to_insert,$_POST['programs']['lbtgq']);
      array_push($array_to_insert,$_POST['programs']['african']);
      array_push($array_to_insert,$_POST['programs']['asian']);
    
      $submitted = "";
      if ($submit == 'Submit')
      {
        $submitted = 'Y';
      }
      array_push($array_to_insert,$submitted);
      array_push($array_to_insert,$id);

      $table_columns = array('username','areas_of_study','two_courses','skills','language','transition','other_info','preferred_name','pronoun','high_school_subjs','latina','lbtgq','african','asian','submitted');

      $error .= $local_mysql->updateSubmission('advising',$types,$array_to_insert,$table_columns);

      if ($error == "")
      {
        if ($my_role != DEFAULT_ID)
        {
           $no_navs = 1;
           $skip_graphics = 1;
        }
        if ($submit == "Submit")
        {
           $error = "Thanks for successfully submitting your information.  Please note that you cannot return to this form to change it.<br>\n";
           include_once("show_advising.php");
           exit;
        }
        else
        {
           $error = "Successfully saved your information. Please note that we have not checked to see if you have all the required information. This will be done when you Submit the request.<br>\n";
        }
        $no_navs = 1;
        $skip_graphics = 1;
      }
    }
  }
 }

// if this is not a submission, then get the data from the databases, unless this is a new entry.
 else
 {

   if ($id != "")
   {

     $values = array();
     $local_mysql->getAllData('advising', $values,$id);

     foreach ($values as $key=>$value)
       {
         ${$key} = $value;
       }

     if ($submitted == 'Y')
     {
      $error = "You have already submitted this form, with the information displayed below.";
      $no_navs = 1;
      $skip_graphics = 1;
      include_once('show_advising.php');
      exit;
    }

     $info = array();
     $oracle->enteringStudentsLookup($username,$info,'sdsespu.user_name','Y');

     $vars = array_keys($info[0]);
     foreach ($vars as $v)
     {
       ${strtolower($v) } = $info[0]{$v};
     }

   } else {
     $vars = array_keys($student_info[0]);
     foreach ($vars as $v)
     {
       if ($v == "ID") {
          $v = 'BANNER_ID';
       }
       ${strtolower($v)} = $student_info[0]{$v};
     }
   }
 }


$i = 0;

if ($error != "")
{
  $data[$i]['one'] = '';
  $data[$i]['two']="<center><font color=\"#ff0000\">$error</font></td>\n</tr>\n";
  $i++;
}


//High School Subjects
$data[$i]['one'] = "<b>What high school subjects interested you the most? What did you like about them?</b>";
$data[$i]['two'] .= "<textarea name=\"high_school_subjs\">$high_school_subjs</textarea>";
$i++;

//Study Areas
$data[$i]['one'] = "<b>What areas of study do you hope to explore at some point during your time at Wellesley?</b> ";
$data[$i]['two'] .= "<textarea cols=80 rows=5 name=\"areas_of_study\">$areas_of_study</textarea>";
$i++;

//Courses
$data[$i]['one'] = "<b>Can you name two courses (aside from First-Year Writing Courses and First Year Seminars, which you will tell us about on the Pre-Registration form) that you hope to take during your first year? </b> ";
$data[$i]['one'] .= "<br /><br /><a href=\"https://courses.wellesley.edu/\" target=\"blank\">Click here to view Wellesley Course Catalog</a>";
$data[$i]['two'] .= "<textarea cols=80 rows=5 name=\"two_courses\">$two_courses</textarea>";

$i++;

//Helpful Skills
$data[$i]['one'] = "<b>What two skills do you have that will help you succeed at Wellesley? And what two skills you would like to improve?</b> ";
$data[$i]['two'] .= "<textarea cols=80 rows=5 name=\"skills\">$skills</textarea>";
$i++;

//Language
$data[$i]['one'] = "<b>Are you planning on taking a language at Wellesley?  Which?</b> ";
$data[$i]['one'] .= "<br><br>Courses offered: Arabic, Chinese, French, German, Greek, Hebrew, Hindi-Urdu, Italian, Japanese, Korean, Latin, Portuguese, Russian, Spanish, Swahili";
$data[$i]['two'] .= "<textarea cols=80 rows=5 name=\"language\">$language</textarea>";
$i++;

//Transition
// $data[$i]['one'] = "<b>What are your thoughts about the transition that you are about to make from high school to college?  In what ways do you think life at Wellesley will be different for you than your secondary school experience?  What are your important intellectual and social goals for the time ahead? What do you hope to explore this coming year?</b> ";
$data[$i]['one'] = "<b>What are your thoughts about the transition that you are about to make from high school to college?";
$data[$i]['one'] .= "<br><br>In what ways do you think life at Wellesley will be different for you than your secondary school experience?";
$data[$i]['one'] .= "<br><br>What are your important intellectual and social goals for the time ahead?";
$data[$i]['two'] .= "<textarea cols=80 rows=5 name=\"transition\">$transition</textarea>";
$i++;

//Other
$data[$i]['one'] = "<b>Is there anything else you would like your faculty advisor to know about you in advance?</b> ";
$data[$i]['two'] .= "<textarea cols=80 rows=5 name=\"other_info\">$other_info</textarea>";
$i++;

//Name
$data[$i]['one'] = "<b>What is your preferred name?</b>\n";
// if ($pref_first_name == '') {
if ($preferred_name == '') {
  $data[$i]['two'] = "You do not have a preferred first name on file. If you want to provide one, please enter it in the box below.\n";
}
else 
{
  // $data[$i]['two'] = "Our records show that your preferred first name is " . $pref_first_name . ". If you would like to change this, please enter your preferred name in the box below.\n";
  $data[$i]['two'] = "Our records show that your preferred first name is <b>" . $preferred_name . "</b>. If you would like to change this, please enter your preferred name in the box below.\n";
}
$data[$i]['two'] .= "<input type='text' value='$preferred_name' name='preferred_name' /><br>\n";
$i++;

//Pronoun
$data[$i]['one'] = "<b>What is your preferred pronoun?</b>";
$data[$i]['two'] = "Many students use the traditional female pronouns (she/her) but others prefer different pronouns (he/him, ze/zir, they/them, and so on).  If you do have a preference, we want to try to respect that, so please feel free to share that information below.";
$data[$i]['two'] .= "<input type='text' value='$pronoun' name='pronoun' /><br>\n";
$i++;

//Programs

$programs_selections = array (
                'latina' => 'Advisor to Latina Students',
                'lbtgq' => 'Advisor to LBTGQ Students',
                'african' => 'Advisor to Students of African Descent',
                'asian' => 'Advisor to Students of Asian Descent'
                );


$data[$i]['one'] = "<b>Would you like to hear about programs offered by any of the following Wellesley offices?</b> \n";
$data[$i]['one'] .= "<br>Check all that appeal to you.\n";

foreach ($programs_selections as $p => $description) {
$checked = "";
  if (in_array($p,$programs)) {
    $checked = " checked ";
  }
  $data[$i]['two'] .= "<input type=checkbox name=\"programs[$p]\" value=\"$p\" $checked>\n$description</input><br>\n";
}

$i++;

$columns = array("one"=>'one',"two"=>'two');
$t_columns = array();

foreach ($columns as $key=>$value)
{
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

if ( ($my_role == ADMIN_ID) & ($id != "") )
{
  print  "<input type='hidden' name='id' value=\"$id\">\n";
 }


if ($no_navs != "")
{
  print  "<input type='hidden' name='no_navs' value=\"$no_navs\">\n";
 }


if ($skip_graphics != "")
{
  print  "<input type='hidden' name='skip_graphics' value=\"$skip_graphics\">\n";
 }

print "<input type=submit name=submit value=Submit>\n";
print "<input type=submit name=submit value=Save>\n";

print  "</center>\n";
print  "</form>\n";


// Hide the header_container div if requested.

if ($skip_graphics == 1)
{
  print  "<script>\n";
  print  "$('#header_container').hide()";
  print  "</script>\n";
}


include_once "includes/footer.php";

?>


