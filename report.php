<?php

/**
 * Shows the report
 *
 * @author Mark Nelson, Pukunui Technology
 * @copyright Jemena
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package admin
 * @subpackage report
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/jemena/lib.php');
require_once($CFG->dirroot.'/report/jemena/index_form.php');





// Set some strings
$format = get_string('strftimedatefullshort', 'langconfig');
$strcompleted = get_string('completed', 'report_jemena');
$strdisabled = get_string('disabled', 'report_jemena');

// Set the context
// $systemcontext = get_context_instance(CONTEXT_SYSTEM);
$systemcontext = context_system::instance();
require_login();
require_capability('report/jemena:view', $systemcontext);


$returnurl = '/report/jemena/index.php';


$PAGE->set_url($returnurl);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('report');

// Get the parameters

// Get user status


$userstatusarray = array(get_string('allstatuses', 'report_jemena'), 'Active', 'Inactive');
$userstatus = optional_param('userstatus', 0, PARAM_INT);
$struserstatus = $userstatusarray[$userstatus];

if ($category = optional_param('category', 0, PARAM_INT)) {
    // Category specified, so show the category in search results
    $objcategory = $DB->get_record('course_categories', array('id' => $category));
    $strcategory = $objcategory->name;
} else {
    // No category specified, show 'the 'all categories' in search results
    $strcategory = get_string('allprocessareas', 'report_jemena');
}
if ($role = optional_param('role', 0, PARAM_INT)) {
    // Role specified, so show the role in search results, as well as add a role column ($rolename)
    $rolename = $DB->get_record('course', array('id' => $role), 'id, shortname');
    $rolename = $rolename->shortname;
    $strrole = $rolename;
} else {
    // No Role specified, do not add a role column, and display 'all roles' as search result
    $strrole = get_string('allroles', 'report_jemena');
}
if ($course = optional_param('course', 0, PARAM_INT)) {
    // Course specified, so show the course in search results
    $sql = "SELECT c.id, c.shortname, cc.name as categoryname " .
           "FROM {course} c " .
           "INNER JOIN {course_categories} cc " .
           "ON c.category = cc.id " .
           "WHERE c.id = '$course'";
    $course = $DB->get_record_sql($sql);
    $strcourse = $course->shortname;
} else {
    // No course specified, so display 'all courses' in search results
    $strcourse = get_string('allcourses', 'report_jemena');
}
if ($datefrom = optional_param('datefrom', 0, PARAM_INT)) {
    $strcompletiondatefrom = userdate($datefrom, $format);
} else {
    $strcompletiondatefrom = $strdisabled;
}
if ($dateto = optional_param('dateto', 0, PARAM_INT)) {
    $strcompletiondateto = userdate($dateto, $format);
} else {
    $strcompletiondateto = $strdisabled;
}
// Set variable to store csv data
$csvdata = array();
// Display the search criteria
$searchcriteria = get_string('processarea', 'report_jemena') . ',' . $strcategory . "\n";
$csvdata[] = $searchcriteria;
$searchcriteria = get_string('role', 'report_jemena') . ',' . $strrole . "\n";
$csvdata[] = $searchcriteria;
$searchcriteria = get_string('course', 'report_jemena') . ',' . $strcourse . "\n";
$csvdata[] = $searchcriteria;
$searchcriteria = get_string('completiondatefrom', 'report_jemena') . ',' . $strcompletiondatefrom . "\n";
$csvdata[] = $searchcriteria;
$searchcriteria = get_string('completiondateto', 'report_jemena') . ',' . $strcompletiondateto . "\n";
$csvdata[] = $searchcriteria;
$csvdata[] = "\n";
$csvdata[] = "\n";
// Create heading for listing users
$heading = get_string('firstname', 'report_jemena') . "," . get_string('lastname', 'report_jemena') . "," . get_string('department', 'report_jemena') . "," .
           get_string('businessunit', 'report_jemena') . "," . get_string('sapjob', 'report_jemena') . "," . get_string('processarea', 'report_jemena') . ",";
// if ($role) {
    // $heading .= get_string('role') . ",";
// }
$heading .= get_string('course', 'report_jemena') . "," . get_string('score', 'report_jemena') . "," . get_string('completed', 'report_jemena') . "," .
            get_string('datecompleted', 'report_jemena');
$csvdata[] = $heading . "\n";
if ($course) {
    if ($users = jemena_get_users($course->id, $role, $datefrom, $dateto, $userstatus)) {
        // Loop through users
        foreach ($users as $u) {
            // Get the statistics
            $businessunit = jemena_get_custom_field($u->id, 'SAP Job');
            $sapjob = jemena_get_custom_field($u->id, 'Business Unit');
            //$completionstats = jemena_get_course_completion_stats($u->id, $course->id, $format);
            // if (!$role) {
                // $rolename = $u->courseshortname;
            // }
            $grade = jemena_get_course_grade($u->id, $course->id);
            $completedinfo = jemena_get_completed_date($u->timecompleted, $datefrom, $dateto, $strcompleted, $format);
            $completed = $completedinfo->completed;
            $timecompleted = $completedinfo->timecompleted;
            $csvdata[] = $u->firstname . "," . $u->lastname . "," . $u->department . "," . $businessunit . "," . $sapjob . "," .
                         $course->categoryname . "," . $course->shortname . "," . $grade . "," . $completed . "," .
                         $timecompleted . "\n";
        }
    }
} else {
    if ($courses = jemena_get_meta_linked_courses($category, $role)) {
        // Loop through courses
        foreach ($courses as $c) {
            if ($users = jemena_get_users($c->id, $role, $datefrom, $dateto, $userstatus)) {
                // Loop through users
                foreach ($users as $u) {
                    // Get the statistics
                    $businessunit = jemena_get_custom_field($u->id, 'SAP Job');
                    $sapjob = jemena_get_custom_field($u->id, 'Business Unit');
                    //$completionstats = jemena_get_course_completion_stats($u->id, $c->id, $format);
                    // if (!$role) {
                        // $rolename = $u->courseshortname;
                    // }
                    $grade = jemena_get_course_grade($u->id, $c->id);
                    $completedinfo = jemena_get_completed_date($u->timecompleted, $datefrom, $dateto, $strcompleted, $format);
                    $completed = $completedinfo->completed;
                    $timecompleted = $completedinfo->timecompleted;
                    $csvdata[] = $u->firstname . "," . $u->lastname . "," . $u->department . "," . $businessunit . "," . $sapjob . "," .
                                 $c->categoryname . "," . $c->shortname . "," . $grade . "," . $completed . "," .
                                 $timecompleted . "\n";
                }
            }
        }
    }
}

$filename = 'report_'.date("Ymd-His").'.csv';

@header('Content-Disposition: download; filename='.$filename);
@header('Content-Type: text/csv');

// Check that the csvdata is not empty
if (!empty($csvdata)) {
    // Loop through all data
    foreach ($csvdata as $data) {
        echo strip_tags($data);
    }
}

exit;
?>
