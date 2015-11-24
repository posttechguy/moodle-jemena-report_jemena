<?php

/**
 * Library functions for Jemena administrator report
 *
 * @author Mark Nelson, Pukunui Technology
 * @copyright Jemena
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package admin
 * @subpackage report
 */

/**
 * Return all the categories on the site
 *
 * @return array
 */

function jemena_get_categories()
{
    global $DB;
    
    $arrcategories = array();
    $arrcategories[0] = get_string('allprocessareas', 'report_jemena');
    $sql = "SELECT DISTINCT id, name " .
           "FROM {course_categories} " .
           "WHERE name != 'Roles' " .
           "AND visible = 1 " .
           "ORDER BY name ASC";
    $arrcategories += $DB->get_records_sql_menu($sql);
    
    return $arrcategories;
}

/**
 * Return all the meta courses
 *
 * @return array
 */

function jemena_get_meta_courses()
{
    global $DB;
    
    $arrcategories = array();
    $arrcategories[0] = get_string('allroles', 'report_jemena');
    $sql = "SELECT DISTINCT c.id, c.shortname " .
           "FROM {course} c ".
           "INNER JOIN {enrol} e " .
           "ON c.id = e.customint1 " .
           "ORDER BY shortname ASC";
    $arrcategories += $DB->get_records_sql_menu($sql);
    
    return $arrcategories;
}

/**
 * Return all the courses linked via 
 * a metacourse enrolment
 *
 * @param int $category
 * @param int $course
 * @param boolean $dropdown is this for the select box?
 * @param boolean $ajax ajax request ?
 * @return array
 */

function jemena_get_meta_linked_courses($category, $course, $dropdown = false, $ajax = false)
{
    global $DB;
    
    if ($category && $course) {
        $sql = "SELECT DISTINCT c.id, c.shortname, c.idnumber, c.ismandatory, cc.name as categoryname " .
               "FROM {course} c " .
               "INNER JOIN {course_categories} cc " .
               "ON c.category = cc.id " .
               "LEFT JOIN ( " .
               "     SELECT DISTINCT e.customint1, e.enrol " .
               "     FROM {enrol} e " .
               "     WHERE e.enrol = 'meta') As a " .
               "ON a.customint1 = c.id " .
               "INNER JOIN {enrol} as b " .
               "ON b.courseid = c.id " .
               "WHERE a.enrol IS NULL " .
               "AND category = '$category' " .
               "AND b.customint1 = '$course' " .
               "AND b.enrol = 'meta' " .
               "AND c.format != 'site' " .
               "AND c.visible = 1 " .
               "ORDER BY c.shortname";
    } else if ($category) {
        $sql = "SELECT DISTINCT c.id, c.shortname, c.idnumber, c.ismandatory, cc.name as categoryname " .
               "FROM {course} c " .
               "INNER JOIN {course_categories} cc " .
               "ON c.category = cc.id " .
               "LEFT JOIN ( " .
               "   SELECT DISTINCT e.customint1, e.enrol " .
               "   FROM {enrol} e " .
               "   WHERE e.enrol = 'meta') AS a " .
               "ON a.customint1 = c.id " .
               "WHERE a.enrol is NULL " .
               "AND c.category = '$category' " .
               "AND c.format != 'site' " .
               "AND c.visible = 1 " .
               "ORDER BY c.shortname";
    } else if ($course) {
        $sql = "SELECT DISTINCT c.id, c.shortname, c.idnumber, c.ismandatory, cc.name as categoryname " .
               "FROM {course} c " .
               "INNER JOIN {course_categories} cc " .
               "ON c.category = cc.id " .
               "INNER JOIN {enrol} e " .
               "ON c.id = e.courseid " .
               "WHERE e.customint1 = '$course' " .
               "AND e.enrol = 'meta' " .
               "AND c.format != 'site' " .
               "AND c.visible = 1 " .
               "ORDER BY c.shortname";
    } else {
        $sql = "SELECT DISTINCT c.id, c.shortname, c.idnumber, c.ismandatory, cc.name as categoryname " .
               "FROM {course} c " .
               "INNER JOIN {course_categories} cc " .
               "ON c.category = cc.id " .
               "LEFT JOIN ( " .
               "   SELECT DISTINCT e.customint1, e.enrol " .
               "   FROM {enrol} e " .
               "   WHERE e.enrol = 'meta') AS a " .
               "ON a.customint1 = c.id " .
               "WHERE a.enrol is NULL " .
               "AND c.format != 'site' " .
               "AND c.visible = 1 " .
               "ORDER BY c.shortname";
    }
    
    if ($dropdown) {
        $arrcourses = array();
        $arrcourses[0] = get_string('allcourses', 'report_jemena');
        $arrcourses += $DB->get_records_sql_menu($sql);
        return $arrcourses;
    } else if ($ajax) {
        $options = "<option value='0'>" . get_string('allcourses', 'report_jemena') . "</option>";
        if ($courses = $DB->get_records_sql($sql)) {
            foreach ($courses as $c) {
                $options .= "<option value='$c->id'>" . $c->shortname . "</option>";
            }
        }
        return $options;
    } else {
        return $DB->get_records_sql($sql);
    }
}

/**
 * Return active/inactive status for users
 *
 * @return array
 */

function jemena_get_user_status()
{
	$arrstatus = array();
	$arrstatus[0] = get_string('allusers', 'report_jemena');
	$arrstatus[1] = "Active";
	$arrstatus[2] = "Inactive";
	
    return $arrstatus;
}

/**
 * Return all the completion statistics by business unit
 * for the business unit report
 *
 * @param int $category
 * @param int $course
 * @param int $role
 * @param int $datefrom
 * @param int $dateto
 * @return object
 */

function jemena_get_business_unit_course_statistics($category, $course, $role, $datefrom, $dateto, $userstatus)
{
	global $DB;
	
	// Include a concatenated column to generate a unique value so Moodle doesn't group  my results
	$sql = "SELECT CONCAT(muid.id, '-', mc.id, '-', mc.ismandatory) AS 'uniqueid', muid.data AS 'businessunit', mc.id, mc.ismandatory, mc.idnumber, COUNT(mcc.timecompleted) AS 'completedcount' " . 
				", COUNT(mu.id) AS enrolledcount " . 
		   "FROM {user} mu " . 
		   "INNER JOIN {user_info_data} muid " . 
		   "ON mu.id = muid.userid " . 
		   "INNER JOIN {user_info_field} muif " . 
		   "ON muid.fieldid = muif.id " . 
		   "INNER JOIN {user_enrolments} mue " . 
		   "ON mu.id = mue.userid " . 
		   "INNER JOIN {enrol} me " . 
		   "ON mue.enrolid = me.id " . 
		   "INNER JOIN {course_completions} mcc " . 
		   "ON mu.id = mcc.userid " . 
		   "INNER JOIN {course} mc " . 
		   "ON mcc.course = mc.id " . 
		   "AND me.courseid = mc.id " . 
		   "WHERE muif.shortname like 'businessunit' ";
		   
		   if ($userstatus == 1){ // active users
			   $sql .= "AND mu.auth like 'manual' ";
		   }
		   else if ($userstatus == 2){ // inactive users
			   $sql .= "AND mu.auth like 'nologin' ";
		   }
		   
		   $sql .= "AND mc.visible = 1 ";
		   
		   if ($category){
			$sql .= "AND mc.category = '$category' ";
		   }
		   
		   if ($course){
			   $sql .= "AND mc.id = '$course' ";
		   }
		   if ($role) {
			   $sql .= "AND me.customint1 = '$role' ";
		   }
		   if ($datefrom) {
			   $sql .= "AND (mue.timestart = 0 OR mue.timestart >= '$datefrom') ";
		   }
		   if ($dateto) {
			   $sql .= "AND mue.timestart <= '$dateto' AND (mue.timeend = 0 OR mue.timeend <= '$dateto') ";
		   }
		   
	$sql .="GROUP BY muid.data, mc.ismandatory, mc.idnumber " . 
		   "ORDER BY muid.data ASC";  

		   
		   return $DB->get_records_sql($sql);
}

/**
 * Return course statistics for the
 * jemena report
 *
 * @param int $course
 * @param int $role
 * @param int $datefrom
 * @param int $dateto
 * @param boolean $needcoursedetails
 *      are course details needed, or obtained earlier?
 * @return object
 */

function jemena_get_course_statistics($courseid, $role, $datefrom, $dateto, $needcoursedetails = true, $userstatus)
{
    global $DB;
    
    // Get context
//    $context = get_context_instance(CONTEXT_COURSE, $courseid);


	$context = context_course::instance($courseid);


    
    $stats = new stdClass();
    
    if ($needcoursedetails) {
        // Get the course and category details
        $sql = "SELECT c.shortname as courseshortname, ca.name as category " .
               "FROM {course} c " .
               "INNER JOIN {course_categories} ca " .
               "ON c.category = ca.id " .
               "WHERE c.id = '$courseid'";
        $result = $DB->get_record_sql($sql);
        $stats->courseshortname = $result->courseshortname;
        $stats->category = $result->category;
    }
    
    // Get the total number of users enrolled, not
    // including those that are not a student
    $sql = "SELECT COUNT(DISTINCT u.id) as count " .
           "FROM {user} u " .
           "INNER JOIN {user_enrolments} ue " .
           "ON u.id = ue.userid " .
           "INNER JOIN {enrol} e " .
           "ON ue.enrolid = e.id " .
           "INNER JOIN {role_assignments} ra " .
           "ON u.id = ra.userid " .
           "WHERE u.deleted = 0 ";
    if ($userstatus == 1){ // active users (manual)
	   $sql .= "AND u.auth like 'manual' ";
    }
    elseif ($userstatus == 2){ // inactive users (nologin)
	   $sql .= "AND u.auth like 'nologin' ";
    }
    if ($role) {
        $sql .= "AND e.customint1 = '$role' ";
    }
    if ($datefrom) {
        $sql .= "AND (ue.timestart = 0 OR ue.timestart >= '$datefrom') ";
    }
    if ($dateto) {
        $sql .= "AND ue.timestart <= '$dateto' AND (ue.timeend = 0 OR ue.timeend <= '$dateto') ";
    }
    $sql .= "AND ra.contextid = '$context->id' " .
            "AND ra.roleid = '5' ";
    $result = $DB->get_record_sql($sql);
    $stats->traineesenrolled = $result->count;

    // Get the total number of users who have completed
    $sql = "SELECT COUNT(DISTINCT u.id) as count " .
           "FROM {user} u " .
           "INNER JOIN {user_enrolments} ue " .
           "ON u.id = ue.userid " .
           "INNER JOIN {enrol} e " .
           "ON ue.enrolid = e.id " .
           "INNER JOIN {course_completions} cc " .
           "ON u.id = cc.userid " .
           "WHERE u.deleted = 0 " .
           "AND e.courseid = '$courseid' ";
    if ($userstatus == 1){ // active users (manual)
	   $sql .= "AND u.auth like 'manual' ";
    }
    elseif ($userstatus == 2){ // inactive users (nologin)
	   $sql .= "AND u.auth like 'nologin' ";
    }
    if ($role) {
        $sql .= "AND e.customint1 = '$role' ";
    }
    if ($datefrom) {
        $sql .= "AND cc.timecompleted >= '$datefrom' ";
    }
    if ($dateto) {
        $sql .= "AND cc.timecompleted <= '$dateto' ";
    }
    $sql .= "AND cc.course = '$courseid' " .
            "AND cc.timecompleted IS NOT NULL ";
    $result = $DB->get_record_sql($sql);
    $stats->traineescompleted = $result->count;

    return $stats;
}

/**
 * Return the users for a course, and if
 * a date is specified, only completed
 * users that fit the time frame.
 *
 * @param int $course
 * @param int $role
 * @param int $datefrom
 * @param int $dateto
 * @return object
 */

function jemena_get_users($courseid, $role, $datefrom, $dateto, $userstatus)
{
    global $DB;
    
    // Get all the users in that course
    $sql = "SELECT DISTINCT(u.id), u.firstname, u.lastname, u.email, u.department, u.auth, cc.timecompleted ";
    if (!$role) {
       $sql .= ", c.shortname as courseshortname ";
    }
    $sql .= "FROM {user} u " .
            "INNER JOIN {user_enrolments} ue " .
            "ON u.id = ue.userid " .
            "INNER JOIN {enrol} e " .
            "ON ue.enrolid = e.id " .
            "INNER JOIN {role_assignments} ra " .
            "ON u.id = ra.userid ";
    
    if (!$role) {
       $sql .= "LEFT JOIN {course} c " .
               "ON e.customint1 = c.id ";
    }
    $sql .= "INNER JOIN {course_completions} cc " .
            "ON u.id = cc.userid " .
            "WHERE u.deleted = 0 ";
	if ($userstatus == 1){ // active users (manual)
	   $sql .= "AND u.auth like 'manual' ";
	}
	elseif ($userstatus == 2){ // inactive users (nologin)
	   $sql .= "AND u.auth like 'nologin' ";
	}
    $sql .= "AND e.courseid = '$courseid' ";
    if ($datefrom) {
        $sql .= "AND cc.timecompleted >= '$datefrom' ";
    }
    if ($dateto) {
        $sql .= "AND cc.timecompleted <= '$dateto' ";
    }
    if ($role) {
        $sql .= "AND e.customint1 = '$role' ";
    }
    $sql .= "AND ra.roleid = 5 " .
            "AND cc.course = '$courseid' " .
            "GROUP BY u.id";

    return $DB->get_records_sql($sql);
}

/**
 * Return the time completed information
 * taking into account what dates were
 * specified in the form
 *
 * @param int $timecompleted
 * @param int $datefrom
 * @param int $dateto
 * @param str $strcompleted the string used in the loop
 * @param str $format the time format
 * @return object
 */

function jemena_get_completed_date($timecompleted, $datefrom, $dateto, $strcompleted, $format) 
{
    $timemet = true;

    if ($datefrom) {
        if ((empty($timecompleted)) || ($timecompleted <= $datefrom)) {
            $timemet = false;
        }
    }
    if ($dateto) {
        if ((empty($timecompleted)) || ($timecompleted >= $dateto)) {
            $timemet = false;
        }
    }
    if ($timemet) {
         $completed = (!empty($timecompleted)) ? $strcompleted : "";
         $timecompleted = (!empty($timecompleted)) ? userdate($timecompleted, $format) : "";
    } else {
        $completed = "";
        $timecompleted = "";
    }
    
    $results = new stdclass;
    $results->completed = $completed;
    $results->timecompleted = $timecompleted;
    
    return $results;
}

/**
 * Return the users custom profile field
 *
 * @param int $userid
 * @param text $field
 * @return object
 */

function jemena_get_custom_field($userid, $field)
{
    global $DB;
    
    $sql = "SELECT ud.data " .
           "FROM {user_info_field} uf " .
           "INNER JOIN {user_info_data} ud " .
           "ON uf.id = ud.fieldid " .
           "WHERE uf.name = '$field' " .
           "AND ud.userid = '$userid'";
    if ($result = $DB->get_record_sql($sql)) {
        return $result->data;
    } else {
        return "";
    }
}

/**
 * Return the users course grade
 *
 * @param int $userid
 * @param int $courseid
 * @return object
 */

function jemena_get_course_grade($userid, $courseid)
{
    global $CFG;
    
    require_once($CFG->dirroot.'/lib/gradelib.php');
    require_once($CFG->dirroot.'/grade/querylib.php');
   
    // Get the score for the course
    if ($objgrade = grade_get_course_grade($userid, $courseid)) {
        return $objgrade->grade;
    } else {
        return "";
    }
}

/**
 * Return the users custom profile field
 *
 * @param int $userid
 * @param int $courseid
 * @param str $format the format of the date
 * @return object
 */

function jemena_get_course_completion_stats($userid, $courseid, $format = '')
{
    global $CFG, $DB;
    require_once($CFG->dirroot.'/lib/gradelib.php');
    require_once($CFG->dirroot.'/grade/querylib.php');

    $completionstats = new stdClass();

    // Get the score for the course
    $objgrade = grade_get_course_grade($userid, $courseid);
    $completionstats->score = round($objgrade->grade, 2);

    $sql = "SELECT * " .
           "FROM {course_completions} cc " .
           "WHERE userid = '$userid' " .
           "AND course = '$courseid' " .
           "AND timecompleted IS NOT NULL";
    if ($stats = $DB->get_record_sql($sql)) {
        $completionstats->completed = get_string('completed', 'report_jemena');
        $completionstats->datecompleted = userdate($stats->timecompleted, $format);
    } else {
        $completionstats->completed = get_string('notcompleted', 'report_jemena');
        $completionstats->datecompleted = "";
    }
    
    return $completionstats;
}
