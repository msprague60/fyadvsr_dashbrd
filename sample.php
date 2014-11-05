<?php

  // This is a sample php file with form submission.


  // Check to see if  we want to skip navs and graphics.

if (!isset($no_navs)) {
  $no_navs = ($_POST['no_navs']?$_POST['no_navs']:$_GET['no_navs']);
 }

if (!isset($skip_graphics)) {
  $skip_graphics = ($_POST['skip_graphics']?$_POST['skip_graphics']:$_GET['skip_graphics']);
 }

include_once "config.php";
include_once "includes/header.php";

// Add this app specific javascript to toggle states if USA is selected. 
// Also turn on tinyMCE for the textarea.
// Showing and hiding various DIVs can be done using jquery by providing the div name
// In the showPresenters and hidePresenters, there are DIVs called presenterblock_1
// presenterblock_2 etc. which are being either shown or hidden.
?>

<script>

tinyMCE.init({
  theme : "advanced",
      mode : "specific_textareas",
      editor_selector: "mceEditor",
      height:"400"
      });



function checkUSA(id,value) {

  if (value == united_states) 
    { 
      toggleTbody(id,'on') 
	}
  else {
    toggleTbody(id,'off')
      }
}

function showPresenters(value) {

  hideAllPresenters();
  n = parseInt(value) + 1;

  for (i = 2; i < n; i++) {
    $('#presenterblock_' + i).show();
  }

}

function hideAllPresenters() {

  for (i = 2; i < 7; i ++) {
    $('#presenterblock_' + i).hide();
  }

}
</script>

<?php

  // Defines required fields and a text to show when these fields are not
  // available
$required_fields = array (
			  'advisor',
			  'total_authors'
			  );

$required_fields_detail = array (
				
				 'advisor' => 'Advisor',
				 'total_authors' => 'Number of authors'
				 );

// Print the title and navigations
// Navigation here is role based. Only show navs for the role by indicating 1 at the end.

printPageTitle("Tanner Form","Please fill in all required fields and hit submit.");
if ($no_navs != "1") {
  printNavigation($my_role, $roles, $role_titles, $master_navigations,1);
 }

// Define the form. The multipart form is required if you want file uploads.
?>

<form name="tanner_form" enctype="multipart/form-data" method="post"
  action="/tanner/create_form.php">
<center>

<?php


// Get all the variables needed for dropdown etc.

$countries = array();

$local_mysql->getCountries($countries); 

// Do anything special for different roles.

if ( ($my_role == ADMIN_ID) ||
     ($my_role == $roles['advisor']) ||
     )
  {
    
    // Role Based stuff
  }

// Is this a submit?

$submit = $_POST['submit'];

// Is this a submission? If so, process the variables.


if (trim($submit) != "") {

  // Move the Post variables to recognizable variable names.

  foreach ($_POST as $key => $value) {
    ${$key} = $value;
  }


  // Check and make sure that the required fields are not null.
  // Also other requirements are checked here. Look at Tanner app for what a 
  // function like this does.

  $error = check_required_fields($required_fields_detail, $countries, $states, $tid, $local_mysql, $stored_number_of_authors);
  
  // If there are no errors, get ready to process the data
  
  if ($error == "") {

    // Do any preliminary work that needs to be done

    // Now, insert the info. Look in Tanner's local_mysql_functions for the actual function

    $types = "sssssssssss";
    $array_to_insert = array();
    array_push($array_to_insert,$advisor_id);
    array_push($array_to_insert,$thematic_category);
    array_push($array_to_insert,$tag[0]);
    array_push($array_to_insert,$tag[1]);
    array_push($array_to_insert,$tag[2]);
    array_push($array_to_insert,$total_authors);
    array_push($array_to_insert,$presentation_type);
    array_push($array_to_insert,$presentation_title);
    array_push($array_to_insert,$abstract);
    array_push($array_to_insert,$_SESSION['username']);
    array_push($array_to_insert,$tid);


    $local_mysql->updatePresentation($types,$array_to_insert);

    // If it is success, then simply show the results, a mirror of the form handler
    // But shows all variables instead of an editable form.

    $error = "Successfully updated the information. Your Tanner id is: $tid";
    $no_navs = 1;
    $skip_graphics = 1;

    include_once("show_form.php");
    exit;
  }
 }

// if this is not a submission, then get existing data from the database to load.
// if it exists. In this case, a valid $tid coming in means repeat visit, so load data. 

 else {

   if ($tid != "") {
     $values = array();
     $local_mysql->getAllData($values, $tid);

     foreach ($values as $key=>$value) {
       ${$key} = $value;
     }
     ${'presenter_1'} = ${'email_1'};
   }

 }

// Print the form

print "<table cellspacing=10 width=750 bgcolor=\"#ffcc99\" border=1>\n";

// Print any errors on submission

if ($error != "") {
  print "<tr><td colspan=2><font color=\"#ff0000\">$error</font></td>\n</tr>\n";
}

// The form itself
// Here is an example of using javascript to turn on and off a Div

print "<tr>\n";
print "<th colspan=2 align=\"center\">Your Information (" .
${'last_name_1'} . ", " .
${'first_name_1'} . " " .
${'middle_nitial_1'} . " " .
")  <a href=\"javascript:toggleTbody('student_info','on')\">Show Details</a>\n" .
"<a href=\"javascript:toggleTbody('student_info','off')\">Hide Details</a>\n";
print "<input type=\"hidden\" id=\"presenter_1\"  value=\"" . ${"presenter_1"} . "\"" .   " name=\"presenter_1\" >\n";
if ($tid != "") {
  print "<br>Your Tanner ID: $tid";
 }

print "</th>\n";

// Using tbody for a section of the table for use in hiding or showing

print "<tbody id=\"student_info\">\n";
print "<tr> \n";
print "<td colspan=2 align=center>\n";
print "<table border=1>\n";
print "<tr> \n";
print "<td>\n";
print "Your First Name: <b>" . ${'first_name_1'} . "</b><br>\n ";
print "Your Middle Name: <b>" . ${'middle_nitial_1'} . "</b><br>\n "; 
print "Your Last Name: <b>" . ${'last_name_1'} . "</b><br>\n ";
print "Your Class Year: <b>" . ${'class_1'} . "</b><br>\n "; 
if (trim(${'major1_1'}) != "") {
  print "Your First Major: <b>" . ${'major1_1'} . "</b><br>\n "; 
 }
if (trim(${'major2_1'}) != "") {
  print "Your Second Major: <b>" . ${'major2_1'} . "</b><br>\n "; 
 }
print "</td>\n";
print "</tr>\n";
print "</table>\n";
print "</td>\n";
print "</tr>\n";
print "</tbody>\n";

// All fields

// Here is the use of generateSelect function to create a dropdown. 
// This also uses checkUSA to show the DIV of states below ONLY if 
// USA is chosen as the country

print "<td  bgcolor=\"#cccc99\">\n";
$additional = "ONCHANGE=checkUSA('states',this.selectedIndex)";
print generateSelect('country_1', $countries, $additional, ${"country_1"});
print "</td>\n";
print "</tr>\n";

print "<tbody id=\"states\">\n";
print "<tr>\n";
print "<td align=left width=20%>\n";
print "Select the State (*):\n";
print "</td>\n";

print "<td  bgcolor=\"#cccc99\">\n";
print generateSelect('state_1', $states,"", ${"state_1"});
print "</td>\n";
print "</tr>\n";
print "</tbody>\n";

// Here is an example of faculty staff name lookup
// ajaxLookup needs 4 parameters. First two are the name and value entered. 
// The third one is 0 for student lookup, 1 for faculty staff only or 2 for everyone.
// the fourth one is the URL of the lookup.php

print "<td  bgcolor=\"#cccc99\">\n";
print "<div>
  <input autocomplete=\"off\" type=\"text\" id=\"advisor_show\" value=\"$advisor_show\"  name=\"advisor_show\" size=\"100\" onkeyup=\"ajaxLookup(this.value, this.name, 2, 'http://sparrow.wellesley.edu/tanner/lookup.php')\" />\n";

print "<input type=\"hidden\" id=\"advisor\" value=\"$advisor\"  name=\"advisor\"> \n";

// In order for ajaxLookup to work properly, you need these DIVs and they must be named
// by taking the name of the input above and adding it to 
// suggestions_ and autoSuggestionList_

print "</div>\n";
print "<div class=\"suggestionsBox\" id=\"suggestions_advisor_show\" style=\"display: none;\">\n";
print "<div class=\"suggestionList\" id=\"autoSuggestionsList_advisor_show\">\n";
print "</div>\n";
print "</div>\n";
print "</td>\n";
print "</tr>\n";




 }
print "</table>\n";

// Carry forward the no_navs and skip_header variables

if ($no_navs != "") {
  print "<input type='hidden' name='no_navs' value=\"$no_navs\">\n";
 }


if ($skip_graphics != "") {
  print "<input type='hidden' name='skip_graphics' value=\"$skip_graphics\">\n";
 }

print "<input type=submit name=\"submit\" value=\"submit\">";

print "</center>\n";
print "</form>\n";


// Hide the header_container div if requested.

if ($skip_graphics == 1) {
  print "<script>\n";
  print "$('#header_container').hide()";
  print "</script>\n";
}

include_once "includes/footer.php";

?>


