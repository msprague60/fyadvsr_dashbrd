<?php

  // An AJAX helper routine to lookup student or faculty/staff 

  // You don't want any headers...

$skip_header = 1;

include_once "config.php";

// Get the query variables. q is the search term
// fileName is the name of the field that needs to be filled upon selection.
// it is expected that you also have a hidden form field called fieldName_hidden.
// The routine sets the hidden field to email and the field to the full name.
// facstaff indicator - 1 means look up faculty or staff tabel.
//                    - 2 means first facstaff and then students
//                    - 3 means faculty only
//                    - 4 means staff only
//
// format - N Full Name only
//        - NE Name and Email address
//        - NED Name, Email and Department
//        - ND Name and Department
//        - NEC Name and Class
//        - NECM Name, Class and Majors
//        - NC Name and Class
//        - NCM Name, Class and Majors
//        - NM Name and Majors
//        - ALL (NED for faculty, staff and NCM for students)

$results = array();
$search = $_POST["q"];
$fieldName = $_POST["fieldName"];
$facstaff = $_POST['facstaff'];
$format = $_POST['format'];


// Check for the existence of fieldName and search term and that the search term
// is at least 3 characters long.

if (trim($search) == "") {
  print "Error in lookup";
  exit;
 }

if (trim($fieldName) == "") {
  print "Error in Lookup";
  exit;
 }

if (strlen($search) < 3) {
  print "Type at least 3 characters: " . $search;
  exit;
}

$search = safeString($search);

// Do the correct lookup.

$stresults = array();
$fresults = array();
$sresults = array();
$results = array();

if ($format == "") {
  $format = "ALL";
 }


// Staff

if ($facstaff == 4) {
  $oracle->facstaffLookupWithRank($search, $stresults,"");
 }

// Faculty

 else if ($facstaff == 3) {
   $oracle->facstaffLookupWithRank($search, $fresults,"");
 }

// Everyone

 else if ($facstaff == 2) {
   $oracle->facstaffLookup($search, $fresults,"");
   $oracle->studentLookup($search, $sresults);
 }

// Faculty or Staff
 else if ($facstaff == 1) {
   $oracle->facstaffLookupWithRank($search, $results,"");
 }

// Default - student only

 else {
   $oracle->studentLookup($search, $results);
 }

// For each match print one line with the full name.


// For facstaff == 2, first print the Faculty Staff and then the Students.

if ($facstaff == 4) {
  
  $vars = array();
  if ($format == 'ALL') {
    $format = "NED";
  }
  
  foreach ($stresults as $r) {
    if ($r['FAC_STAFF_OR_EMERITUS'] == 'Staff') {
      $vars['N'] = $r['LAST_NAME'] .  ", " . $r['FIRST_NAME'] . " " . $r['MIDDLE_INITIAL'] ;
      $vars['E'] = " (" . $r['EMAIL'] . ")  ";
      $vars['D'] = " " .$r['PRIMARY_DEPARTMENT'] . " ";
      if ($format == 'ALL') {
	$format = "NED";
      }
      
      $name = "";
      $parts = str_split($format,1);
      
      foreach ($parts as $p) {
	$name .= $vars[$p] ." - ";
      }

      $name = substr($name,0,-3);

      $name = preg_replace("/'/","\\'",$name);
      print "<a href=\"javascript:fieldFill('" . $name . "','" . $r['EMAIL'] . "', '$fieldName');\">" . 
	$name . 
	"</a><br>\n";
    }
  }
 }

// Faculty

 else if ($facstaff == 3) {
   
   $vars = array();
   if ($format == 'ALL') {
     $format = "NED";
   }
   
   foreach ($fresults as $r) {

     if ($r['FAC_STAFF_OR_EMERITUS'] == 'Faculty') {
       $vars['N'] = $r['LAST_NAME'] .  ", " . $r['FIRST_NAME'] . " " . $r['MIDDLE_INITIAL'] ;
       $vars['E'] = " (" . $r['EMAIL'] . ")  ";
       $vars['D'] = " " .$r['PRIMARY_DEPARTMENT'] . " ";
       
       $name = "";
       $parts = str_split($format,1);

       foreach ($parts as $p) {
	 $name .= $vars[$p] . " - ";
       }
       
       $name = substr($name,0,-3);
       
       $name = preg_replace("/'/","\\'",$name);
       print "<a href=\"javascript:fieldFill('" . $name . "','" . $r['EMAIL'] . "', '$fieldName');\">" . 
	 $name . 
	 "</a><br>\n";
     }
   }
 }
  
// Faculty, Staff and Students

 else if ($facstaff == 2) {
   
   $vars = array();

   if ($format == 'ALL') {
     $format = "NED";
   }

   foreach ($fresults as $r) {
     $vars['N'] = $r['LAST_NAME'] .  ", " . $r['FIRST_NAME'] . " " . $r['MIDDLE_INITIAL'] ;
     $vars['E'] = " (" . $r['EMAIL'] . ")  ";
     $vars['D'] = " " .$r['PRIMARY_DEPARTMENT'] . " ";

     $name = "";
     $parts = str_split($format,1);
     
     foreach ($parts as $p) {
       $name .= $vars[$p] . " - ";
     }

     $name = substr($name,0,-3);

     $name = preg_replace("/'/","\\'",$name);
     print "<a href=\"javascript:fieldFill('" . $name . "','" . $r['EMAIL'] . "', '$fieldName');\">" . 
       $name . 
       "</a><br>\n";
   }
   
   $vars = array();

   if ($format == 'ALL') {
     $format = "NECM";
   }

   foreach ($sresults as $r) {
     $vars['M'] = " " .$r['FIRST_MAJOR'] . " ";
     if (trim($r['SECOND_MAJOR']) != "") {
       $vars['M'] .= " & " . $r['SECOND_MAJOR'];
     }
     
     $vars['N'] = $r['LAST_NAME'] .  ", " . $r['FIRST_NAME'] . " " . $r['MIDDLE_INITIAL'] ;
     
     $vars['E'] = " (" . $r['EMAIL'] . ")  ";
     $vars['C'] = " Class of " .  $r['CLASS_YEAR'] .  " ";
     
     $name = "";
     $parts = str_split($format,1);
     
     foreach ($parts as $p) {
       $name .= $vars[$p] . " - ";
     }

     $name = substr($name,0,-3);     
     
     $name = preg_replace("/'/","\\'",$name);
     
     print "<a href=\"javascript:fieldFill('" . $name . "','" . $r['EMAIL'] . "', '$fieldName');\">" . 
       $name . 
       "</a><br>\n";
   }
 }

// Faculty or Staff

 else if ($facstaff == 1) {

   $vars = array();
   if ($format == 'ALL') {
     $format = "NED";
   }

   foreach ($results as $r) {
     $vars['N'] = $r['LAST_NAME'] .  ", " . $r['FIRST_NAME'] . " " . $r['MIDDLE_INITIAL'] ;
     $vars['E'] = " (" . $r['EMAIL'] . ")  ";
     $vars['D'] = " " .$r['PRIMARY_DEPARTMENT'] . " ";
     
     $name = "";
     $parts = str_split($format,1);
     
     foreach ($parts as $p) {
       $name .= $vars[$p] . " - ";
     }

     $name = substr($name,0,-3);     
     
     $name = preg_replace("/'/","\\'",$name);
     print "<a href=\"javascript:fieldFill('" . $name . "','" . $r['EMAIL'] . "', '$fieldName');\">" . 
       $name . 
       "</a><br>\n";
   }
 }

// Students only

 else {
   
   $vars = array();

   if ($format == 'ALL') {
     $format = "NECM";
   }

   foreach ($results as $r) {
     $vars['M'] = " " .$r['FIRST_MAJOR'] . " ";
     if (trim($r['SECOND_MAJOR']) != "") {
       $vars['M'] .= " & " . $r['SECOND_MAJOR'];
     }
     
     $vars['N'] = $r['LAST_NAME'] .  ", " . $r['FIRST_NAME'] . " " . $r['MIDDLE_INITIAL'] ;
     
     $vars['E'] = " (" . $r['EMAIL'] . ")  ";
     $vars['C'] = " Class of " .  $r['CLASS_YEAR'] .  " ";
     
     $name = "";
     $parts = str_split($format,1);
     
     foreach ($parts as $p) {
       $name .= $vars[$p] . " - ";
     }

     $name = substr($name,0,-3);     

     $name = preg_replace("/'/","\\'",$name);
     
     print "<a href=\"javascript:fieldFill('" . $name . "','" . $r['EMAIL'] . "', '$fieldName');\">" . 
       $name . 
       "</a><br>\n";
   }
 
 }
   


?>
