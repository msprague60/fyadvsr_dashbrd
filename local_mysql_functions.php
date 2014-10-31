<?php

  // This is a local mysql class for functions local to the app.

class local_mysql_functions {

  // Initialize

  function __construct ($dblink, $mysql) {
  
    if (!isset($_SESSION['loggedInSessionFlag'])) {
      include ('verify_login.php');
      exit;
    }
    
    $this->mysql = $mysql;
    $this->dblink = $dblink;
    $this->logged_in_user = $_SESSION['username'];
    
  }

  function getPreferredPronoun(&$result,$username)
  {
      $sql = "select pronoun from advising where username='$username'";
      $results = $this->dblink->query($sql) or die("Could not query getPreferredPronoun " . $this->dblink->error);
      while($row = $results->fetch_assoc())
      {
          $result = $row['pronoun'];
      }
  }
  
  //Get the ID for the supplied User.
  function getIdByUser($tableName, $user, &$result)
  {
  	$sql = "SELECT max(id) FROM $tableName WHERE username=?";
  	$stmt = $this->dblink->prepare($sql) or die("Could not prepare getIdByUser query.");
  	$stmt->bind_param('s', $user);
  	$stmt->execute() or die("Could not execute getIdByUser query");
  	$stmt->bind_result($result);
  	$stmt->fetch();
  	$stmt->close();
  }
  
  // Get all relevant data from all the tables for a given $id

  function getAllData ($tableName, &$values, $id) {

    $sql = "select * from $tableName where id=?";
    $stmt = $this->dblink->prepare($sql) or die ("Error in preparing DB statement in getting all data from $tableName table"); 
    $stmt->bind_param("i",$id);
    $stmt->execute() or die ("Error executing a sql statement in getting all data from the $tableName table");

    $fieldnames = array();
    $result = array();
    $this->mysql->fetchArraySetup($stmt,$result, $fieldnames);
    $stmt->fetch();
    $stmt->close();

    // Get the result array filled with the associated column values;

    for ($i = 0; $i < sizeof($fieldnames); $i++) {
      $values[$fieldnames[$i]] = $result[$i];
    }


  }

  function getMyEnrollments(&$data, $user, $term_code = '') {
    
    $additional = '';
    if (trim($term_code) != '') {
      $additional = " and term=? ";
    }
    $data = array();
    $sql = "select * from checklist.enrollments where username=? $additional order by course";
    
    $stmt = $this->dblink->prepare($sql) or die ("Error accessing info from enrollments database table");
    if (trim($term_code) != '') {
      $stmt->bind_param("ss",$user,$term_code);
    }
    else {
      $stmt->bind_param("s",$user);
    }
    $stmt->execute() or die ("Error executing a sql statement in deleting into interviewers table " );
    
    $fieldnames = array();
    $result = array();
    $this->mysql->fetchArraySetup($stmt,$result, $fieldnames);
    
    $j = 0;
    while ($stmt->fetch()) {
      for ($i = 0; $i < sizeof($fieldnames); $i++) {
	$data[$j][$fieldnames[$i]] = $result[$i];
      }
      $j++;  
    }
    
  }
  
}  
