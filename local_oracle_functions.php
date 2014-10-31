<?php

  // This provides additional functions to use with Oracle
  // which are local to the App.

class local_oracle_functions {

  // Initialize

  function __construct ($dblink, $oracle) { 
  
    if (!isset($_SESSION['loggedInSessionFlag'])) {
      include ('verify_login.php');
      exit;
    }
    
    $this->dblink = $dblink;
    $this->oracle = $oracle;
    $this->logged_in_user = $_SESSION['username'];

  }
 
  //we are getting this from Banner to eliminate delays 
  //need to join to sgradvr to get advisor/advisee relationship and to goremal to get advisor_pidm from passed in username
  //sgradvr stores advisor pidm, advisee pidm, term code effective
  //need to make sure that sgbstdn_term_code_eff = sgradvr_term_code_eff
  function FirstYearStudentsLookup(&$results, $advisor_pidm, $name, $term) {
  
    $name = "%".$name."%";

  	$sql="select spriden_last_name last_name,
                     spriden_first_name first_name,
                     spriden_mi mi,
                     spriden_id id,
                     spriden_pidm,
                     goremal_email_address email
             from spriden,goremal,sgbstdn,sgradvr a
             where spriden_change_ind is null
             and goremal_pidm = spriden_pidm
             and goremal_emal_code = 'WEL'
             and sgbstdn_pidm = spriden_pidm
             and sgbstdn_styp_code in ('1','2','3')
             and sgbstdn_stst_code = 'AS'
             and sgbstdn_term_code_eff = :term
             and substr(goremal_email_address,1,instr(goremal_email_address, '@')-1) like :name
             and a.sgradvr_advr_pidm = :advisor_pidm
             and a.sgradvr_pidm = spriden_pidm
             and a.sgradvr_term_code_eff <= sgbstdn_term_code_eff
             and a.sgradvr_advr_code = 'FYAA'
             and a.sgradvr_term_code_eff = (select max(b.sgradvr_term_code_eff) from sgradvr b
                                            where a.sgradvr_pidm = b.sgradvr_pidm
                                            and a.sgradvr_advr_code = b.sgradvr_advr_code)";
             
   
	//             and substr(goremal_email_address,1,instr(goremal_email_address, '@')-1) in ('jsulliv8','izerkle','smichels','kchan5','tvarma','spera','skim61')";
  
  	$stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in fy student lookup");
  
  	oci_bind_by_name($stmt, ":name",$name, -1) or die ("Error in binding name in fy student lookup");
  	oci_bind_by_name($stmt, ":advisor_pidm",$advisor_pidm, -1) or die ("Error in binding advisor in fy student lookup");
  	oci_bind_by_name($stmt, ":term",$term, -1) or die ("Error in binding term in fy student lookup");
  
  	oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in fy student lookup" . var_dump(oci_error($stmt)));
  
  	$nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
  
  }
  
  function AdminFirstYearStudentsLookup(&$results, $name, $term) {
  
    $name = "%".$name."%";

  	$sql="select spriden_last_name last_name,
                     spriden_first_name first_name,
                     spriden_mi mi,
                     spriden_id id,
                     spriden_pidm,
                     goremal_email_address email
             from spriden,goremal,sgbstdn,sgradvr a
             where spriden_change_ind is null
             and goremal_pidm = spriden_pidm
             and goremal_emal_code = 'WEL'
             and sgbstdn_pidm = spriden_pidm
             and sgbstdn_styp_code in ('1','2','3')
             and sgbstdn_stst_code = 'AS'
             and sgbstdn_term_code_eff = :term
             and substr(goremal_email_address,1,instr(goremal_email_address, '@')-1) like :name
             and a.sgradvr_pidm = spriden_pidm
             and a.sgradvr_term_code_eff <= sgbstdn_term_code_eff
             and a.sgradvr_advr_code = 'FYAA'
             and a.sgradvr_term_code_eff = (select max(b.sgradvr_term_code_eff) from sgradvr b
                                            where a.sgradvr_pidm = b.sgradvr_pidm
                                            and a.sgradvr_advr_code = b.sgradvr_advr_code)";
             
   
	//             and substr(goremal_email_address,1,instr(goremal_email_address, '@')-1) in ('jsulliv8','izerkle','smichels','kchan5','tvarma','spera','skim61')";
  
  	$stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in admin fy student lookup");
  
  	oci_bind_by_name($stmt, ":name",$name, -1) or die ("Error in binding name in admin fy student lookup");
  	oci_bind_by_name($stmt, ":term",$term, -1) or die ("Error in binding term in admin fy student lookup");
  
  	oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in admin fy student lookup" . var_dump(oci_error($stmt)));
  
  	$nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
  
  }
  
  function getGeneralData(&$results,$uname) {

    $sql = "select s.pidm,
                   s.id,
                   s.class_desc,
                   s.current_name,
                   s.sort_name,
                   s.pref_first_name,
                   s.stud_stat_code,
                   s.stud_stat_desc,
                   s.level_desc,
                   s.stud_type_desc,
                   s.admit_term,
                   s.citizen_code,
                   s.birth_nation_code,
                   s.citiz_nation_code,
                   s.date_of_birth,
                   p.home_phone_number,
                   p.cell_phone_number,
                   w_email.wel_email,
                   g_email.gen_email,
                   adr.home_street_line1,
                   adr.home_street_line2,
                   adr.home_street_line3,
                   adr.home_city,
                   adr.home_state,
                   adr.home_zip,
                   adr.home_nation_desc,
                   appl.high_school,
                   appl.hs_street_line_1,
                   appl.hs_street_line_2,
                   appl.hs_street_line_3,
                   appl.hs_city,
                   appl.hs_state_code,
                   appl.hs_zip,
                   appl.hs_nation_description,
                   appl.major_description_1 intended_major1,
                   appl.major_description_2 intended_major2
       from sdspers s,
            genphon p,
            genaddr adr,
            amsappl appl,
            (select pidm,
                    email wel_email
             from genemal_r
             where emal = 'WEL') w_email,
            (select pidm,
                    email gen_email
             from genemal_r
             where emal = 'GEN') g_email
       where stud_stat_code = 'AS'
       and stud_type_code in ('1','2','3')
       and s.pidm = p.pidm (+)
       and s.pidm = w_email.pidm (+)
       and s.pidm = g_email.pidm (+)
       and s.pidm = adr.pidm (+)
       and s.pidm = appl.pidm (+)    
       and substr(wel_email,1,instr(wel_email,'@',1)-1) = :uname";

    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in general data lookup");

    oci_bind_by_name($stmt, ":uname",$uname, -1) or die ("Error in binding uname in general data lookup");

    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in general data lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
  }

  function getPidmByUser(&$results,$uname) {
    
    $sql = "select pidm from genemal_r where substr(email,1,instr(email,'@',1)-1) = :uname and emal = 'WEL'";

    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in pidm by user lookup");

    oci_bind_by_name($stmt, ":uname",$uname, -1) or die ("Error in binding uname in pidm by user lookup");


    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in pidm by user lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    
  }

  function getStudentTestScores(&$results,$pidm) {
    
    $sql = "select test_description,test_score,test_date from amstest where pidm = :pidm";

    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in test score lookup");

    oci_bind_by_name($stmt, ":pidm",$pidm, -1) or die ("Error in binding pidm in test score lookup");


    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in test score lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    
  }

  function getFYCourses(&$results,$pidm) {
    
    $sql = "select c.crn,
                   t.term_desc,
                   c.subj_code||' '||crse_number||' '||section_number course,
                   c.title,
                   c.credit_hours,
                   c.days1||decode(days2,null,'','<br>'||days2)||decode(days3,null,'','<br>'||days3) days,
                   c.startend1||decode(startend2,null,'','<br>'||startend2)||decode(startend3,null,'','<br>'||startend3) startend,
                   c.loc1||decode(loc2,null,'','<br>'||loc2)||decode(loc3,null,'','<br>'||loc3) loc,
                   c.instructor1_printname||
                      decode(c.instructor2_printname,null,null,', '||c.instructor2_printname)||
                      decode(c.instructor3_printname,null,null,', '||c.instructor3_printname) instructors
            from sdssdtl c,
                 sdsregi_r r,
                 sdssatr a,
                 genterm t
            where c.term_code = r.term
            and c.term_code = a.term
            and c.term_code = t.term
            and c.crn = a.crn
            and a.attribute in ('FYS','WFY')
            and r.crn = c.crn
            and c.subj_code <> 'LEAV'
            and r.reg_status_code in ('RE','RW')
            and r.pidm = :pidm";


    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in fy courses lookup");

    oci_bind_by_name($stmt, ":pidm",$pidm, -1) or die ("Error in binding pidm in fy courses lookup");


    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in fy courses lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    
  }

  function getAdvisor(&$results,$uname,$term) {
    
    $sql = "select spriden_first_name||' '||spriden_last_name advisor_name
             from spriden,goremal,sgradvr a
             where spriden_change_ind is null
             and a.sgradvr_advr_pidm = spriden_pidm
             and goremal_emal_code = 'WEL'
             and substr(goremal_email_address,1,instr(goremal_email_address, '@')-1) = :uname
             and a.sgradvr_pidm = goremal_pidm
             and a.sgradvr_term_code_eff <= :term
             and a.sgradvr_advr_code = 'FYAA'
             and a.sgradvr_term_code_eff = (select max(b.sgradvr_term_code_eff) from sgradvr b
                                            where a.sgradvr_pidm = b.sgradvr_pidm
                                            and a.sgradvr_advr_code = b.sgradvr_advr_code)";
             

    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in advisor lookup");

    oci_bind_by_name($stmt, ":uname",$uname, -1) or die ("Error in binding pidm in advisor lookup");
    oci_bind_by_name($stmt, ":term",$term, -1) or die ("Error in binding term in advisor lookup");

    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in advisor lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    
  }


  function getStudentsByAdvisor(&$results,$pidm,$term) {
    
    $sql = "select spriden_last_name last_name,
                    spriden_first_name first_name,
                    spriden_mi mi,
                    spriden_id id,
                    spriden_pidm,
                    goremal_email_address email,
                    substr(goremal_email_address,1,instr(goremal_email_address, '@')-1) name
             from spriden,goremal,sgbstdn,sgradvr a
             where spriden_change_ind is null
             and goremal_pidm = spriden_pidm
             and goremal_emal_code = 'WEL'
             and sgbstdn_pidm = spriden_pidm
             and sgbstdn_styp_code in ('1','2','3')
             and sgbstdn_stst_code = 'AS'
             and sgbstdn_term_code_eff = :term
             and a.sgradvr_advr_pidm = :advisor_pidm
             and a.sgradvr_pidm = spriden_pidm
             and a.sgradvr_term_code_eff <= sgbstdn_term_code_eff
             and a.sgradvr_advr_code = 'FYAA'
             and a.sgradvr_term_code_eff = (select max(b.sgradvr_term_code_eff) from sgradvr b
                                            where a.sgradvr_pidm = b.sgradvr_pidm
                                            and a.sgradvr_advr_code = b.sgradvr_advr_code)";
             


    //$sql = "select sgradvr_pidm from sgradvr,sgbstdn
    //            where sgradvr_advr_pidm = :pidm
    //            and sgradvr_advr_code = 'FYAA'
    //            and sgbstdn_pidm = sgradvr_pidm
    //            and sgbstdn_term_code_eff = :term
    //            and sgbstdn_stst_code = 'AS'
    //            and sgbstdn_styp_code in ('1','2','3')";

    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in students by advisor lookup");

    oci_bind_by_name($stmt, ":advisor_pidm",$pidm, -1) or die ("Error in binding pidm in students by advisor lookup");
    oci_bind_by_name($stmt, ":term",$term, -1) or die ("Error in binding term in students by advisor lookup");

    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in students by advisor lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    
  }


  function getAllStudents(&$results,$term) {
    
    $sql = "select spriden_last_name last_name,
                    spriden_first_name first_name,
                    spriden_mi mi,
                    spriden_id id,
                    spriden_pidm,
                    goremal_email_address email,
                    substr(goremal_email_address,1,instr(goremal_email_address, '@')-1) name
             from spriden,goremal,sgbstdn,sgradvr a
             where spriden_change_ind is null
             and goremal_pidm = spriden_pidm
             and goremal_emal_code = 'WEL'
             and sgbstdn_pidm = spriden_pidm
             and sgbstdn_styp_code in ('1','2','3')
             and sgbstdn_stst_code = 'AS'
             and sgbstdn_term_code_eff = :term
             and a.sgradvr_pidm = spriden_pidm
             and a.sgradvr_term_code_eff <= sgbstdn_term_code_eff
             and a.sgradvr_advr_code = 'FYAA'
             and a.sgradvr_term_code_eff = (select max(b.sgradvr_term_code_eff) from sgradvr b
                                            where a.sgradvr_pidm = b.sgradvr_pidm
                                            and a.sgradvr_advr_code = b.sgradvr_advr_code)";
             
    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in all students lookup");

    oci_bind_by_name($stmt, ":term",$term, -1) or die ("Error in binding term in all students lookup");

    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in all students lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    
  }


  function getStudentSchedule(&$results,$pidm,$term) {
    
    $sql = "select c.crn,
                   c.subj_code||' '||crse_number||' '||section_number course,
                   c.title,
                   c.credit_hours,
                   c.days1||decode(days2,null,'','<br>'||days2)||decode(days3,null,'','<br>'||days3) days,
                   c.startend1||decode(startend2,null,'','<br>'||startend2)||decode(startend3,null,'','<br>'||startend3) startend,
                   c.loc1||decode(loc2,null,'','<br>'||loc2)||decode(loc3,null,'','<br>'||loc3) loc,
                   c.instructor1_printname||
                      decode(c.instructor2_printname,null,null,', '||c.instructor2_printname)||
                      decode(c.instructor3_printname,null,null,', '||c.instructor3_printname) instructors
            from sdssdtl c,sdsregi_r r where c.term_code = :term
            and c.term_code = r.term
            and r.crn = c.crn
            and c.subj_code <> 'LEAV'
            and r.reg_status_code in ('RE','RW')
            and r.pidm = :pidm";

    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in student schedule lookup");

    oci_bind_by_name($stmt, ":pidm",$pidm, -1) or die ("Error in binding pidm in student schedule lookup");
    oci_bind_by_name($stmt, ":term",$term, -1) or die ("Error in binding term in student schedule lookup");

    oci_execute($stmt, OCI_DEFAULT) or die ("Error in exectuing lookup sql in student schedule lookup"); 

    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
    
  }


  function getStudentHousing($id, &$results)
  {
  	$sql = "select sdspers.last_name,
			       sdspers.first_name,
			       sdspers.primary_email,
			       sdsrasg_r.bldg_code,
			       sdsrasg_r.room_number
			FROM sdspers,
			     sdsrasg_r
			WHERE sdspers.primary_email = :bindvar
			  and sdspers.term = to_char(sysdate, 'YYYY') || '09'
			  and sdspers.pidm = sdsrasg_r.pidm(+)
			  and sdspers.term = sdsrasg_r.term(+)
			  and sdsrasg_r.assignment_status_code(+) = 'AR'";
  	
  	$stmt = oci_parse($this->dblink, $sql) or die("Error in parsing SQL in student housing");
  	 
  	oci_bind_by_name($stmt, ":bindvar",$id, -1) or die ("Error in binding id in student housing");
  	
  	oci_execute($stmt, OCI_DEFAULT) or die ("Error in executing lookup in sql in student housing. ");
  	
  	$nrows = oci_fetch_all($stmt, $results, null, null, OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
  }
  
  function getAdvComments(&$results,$stu_pidm,$adv_pidm,$current_term) {
    
    $sql = "select adv_comments,activity_date
      from wellesley.fyadv_comments 
            where term_code = :current_term
            and stu_pidm = :stu_pidm
            and adv_pidm = :adv_pidm
            order by activity_date desc";

    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in adv comments lookup");
    
    oci_bind_by_name($stmt, ":stu_pidm",$stu_pidm, -1) or die ("Error in binding stu_pidm in adv comments lookup");
    oci_bind_by_name($stmt, ":adv_pidm",$adv_pidm, -1) or die ("Error in binding adv_pidm in adv comments lookup");
    oci_bind_by_name($stmt, ":current_term",$current_term, -1) or die ("Error in binding current_term in adv_comments lookup");
    
    oci_execute($stmt, OCI_DEFAULT) or die ("Error in executing sql in adv_comments lookup"); 
   
    $nrows = oci_fetch_all($stmt, $results, null, null,OCI_FETCHSTATEMENT_BY_ROW + OCI_ASSOC);
  }
  
  function SaveAdvComments($stu_pidm,$adv_pidm,$current_term,$comments) {
    
    $sql = "insert into wellesley.fyadv_comments ac
                                        (ac.stu_pidm,
                                         ac.adv_pidm,
                                         ac.term_code,
                                         ac.adv_comments,
                                         ac.activity_date)
                                  values(:stu_pidm,
                                         :adv_pidm,
                                         :current_term,
                                         :comments,
                                         sysdate)";


    $stmt = oci_parse($this->dblink,$sql) or die ("Error in parsing SQL in reg comments save");
    
    oci_bind_by_name($stmt, ":stu_pidm",$stu_pidm, -1) or die ("Error in binding stu_pidm in adv comments save");
    oci_bind_by_name($stmt, ":adv_pidm",$adv_pidm, -1) or die ("Error in binding adv_pidm in adv comments save");
    oci_bind_by_name($stmt, ":current_term",$current_term, -1) or die ("Error in binding current_term in adv_comments save");
    oci_bind_by_name($stmt, ":comments",$comments, -1) or die ("Error in binding comments in adv_comments save");
    
    oci_execute($stmt, OCI_DEFAULT) or die ("Error in executing sql in adv_comments save"); 
    oci_commit($this->dblink);
    
  }
  
}