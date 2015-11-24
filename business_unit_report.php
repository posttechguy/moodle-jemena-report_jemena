<?php

/**
 * Shows the business unit report
 *
 * @author Justin Keane, Cubic Consulting
 * @copyright Jemena
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package admin
 * @subpackage report
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/jemena/lib.php');
require_once($CFG->dirroot.'/report/jemena/index_form.php');

// Set the context
//$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$systemcontext = context_system::instance();
require_login();
require_capability('report/jemena:view', $systemcontext);

// Get the parameters
$category = optional_param('category', 0, PARAM_INT);
$role = optional_param('role', 0, PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$userstatus = optional_param('userstatus', 0, PARAM_INT);
$datefrom = optional_param('datefrom', 0, PARAM_INT);
$dateto = optional_param('dateto', 0, PARAM_INT);

// Set variable to store csv data
$csvdata = array();
// Create heading for listing results
$heading = get_string('businessunit', 'report_jemena') . "," . get_string('mandatoryoptional', 'report_jemena') . "," . get_string('iltelearning', 'report_jemena') . "," .
		   get_string('percentcompleted', 'report_jemena');

$csvdata[] = $heading . "\n";


$businessunitstats = jemena_get_business_unit_course_statistics($category, $course, $role, $datefrom, $dateto, $userstatus);



if ($businessunitstats){
	// loop through the stats
	foreach($businessunitstats as $stat){

		if ($stat->ismandatory){
			$mand_opt = "Mandatory";
		}
		else{
			$mand_opt = "Optional";
		}
		$ilt_elearning = $stat->idnumber;
		$percentage = 0;
		if ($stat->enrolledcount > 0){
			$percentage = ($stat->completedcount / $stat->enrolledcount * 100) . "%";
		}

		$csvdata[] = $stat->businessunit . "," . $mand_opt . "," . $ilt_elearning . "," . $percentage . "\n";
	}
}


$filename = 'business_unit_report_'.date("Ymd-His").'.csv';

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
