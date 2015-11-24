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
require_once($CFG->dirroot.'/report/jemena/lib.php');
require_once($CFG->dirroot.'/report/jemena/index_form.php');
require_once($CFG->dirroot.'/lib/adminlib.php');

admin_externalpage_setup('reportjemena2');

// Set the context
require_login();
$systemcontext = context_system::instance();
require_capability('report/jemena:view', $systemcontext);

// Get the parameters
$category = optional_param('category', 0, PARAM_INT);
$role = optional_param('role', 0, PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$userstatus = optional_param('userstatus', 0, PARAM_INT);

if (!empty($_POST['buttons']['reset'])) {
    redirect('');
}

// Set the page details

$returnurl = '/report/jemena/index.php';

$PAGE->set_url($returnurl);
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('reporttitle', 'report_jemena'));
$PAGE->set_pagelayout('report');
$PAGE->set_heading(get_string('reporttitle', 'report_jemena'));

$module = array('name' => 'ajax',
                'fullpath' => '/report/jemena/ajax.js',
                'requires' => array('event'));
$PAGE->requires->js_init_call('', array(), true, $module);

$PAGE->set_url($CFG->wwwroot.'/report/jemena/index.php');
$PAGE->set_context($systemcontext);

$active=0;

$form = new report_jemena_filter_form('', array('category' => $category, 'role' => $role, 'course' => $course, 'active' => $active));

if ($data = $form->get_data()) {
    $datefrom = $data->completiondatefrom;
    $dateto = $data->completiondateto;
    // Begin creating the table
    $table = new html_table();
    // Set the row heading object
    $row = new html_table_row();
    // Create the cell
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = "<strong>" . get_string('processarea', 'report_jemena') . "</strong>";
    $row->cells[] = $cell;
    // Create the cell
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = "<strong>" . get_string('course', 'report_jemena') . "</strong>";
    $row->cells[] = $cell;
    // Create the cell
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = "<strong>" . get_string('traineesenrolled', 'report_jemena') . "</strong>";
    $row->cells[] = $cell;
    // Create the cell
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = "<strong>" . get_string('traineescompleted', 'report_jemena') . "</strong>";
    $row->cells[] = $cell;
    // Create the cell
    $cell = new html_table_cell();
    $cell->header = true;
    $cell->text = "<strong>" . get_string('percentcompleted', 'report_jemena') . "</strong>";
    $row->cells[] = $cell;
    // Finally - add the headers cells to the row
    $table->data[] = $row;
    // Loop through the courses
    if (!$data->course) {
        if ($courses = jemena_get_meta_linked_courses($data->category, $data->role)) {
            foreach ($courses as $c) {
                // Get statistics
                $stats = jemena_get_course_statistics($c->id, $role, $datefrom, $dateto, false, $userstatus);
                if ($stats->traineesenrolled == 0) {
                    $percentage = 0;
                } else {
                    $percentage = ($stats->traineescompleted / $stats->traineesenrolled) * 100;
                }
                // Fill the table
                $row = new html_table_row();
                // Add new html cell
                $cell = new html_table_cell();
                $cell->style = 'text-align:center';
                $cell->text = $c->categoryname;
                $row->cells[] = $cell;
                // Add new html cell
                $cell = new html_table_cell();
                $cell->style = 'text-align:center';
                $cell->text = $c->shortname;
                $row->cells[] = $cell;
                // Add new html cell
                $cell = new html_table_cell();
                $cell->style = 'text-align:center';
                $cell->text = $stats->traineesenrolled;
                $row->cells[] = $cell;
                // Add new html cell
                $cell = new html_table_cell();
                $cell->style = 'text-align:center';
                $cell->text = $stats->traineescompleted;
                $row->cells[] = $cell;
                // Add new html cell
                $cell = new html_table_cell();
                $cell->style = 'text-align:center';
                $cell->text = round($percentage) . "%";
                $row->cells[] = $cell;
                // Add to the row
                $table->data[] = $row;
            }
        } else {
            // Fill the table
            $row = new html_table_row();
            // Add new html cell
            $cell = new html_table_cell();
            $cell->style = 'text-align:center';
            $cell->text = get_string('nocourses', 'report_jemena');;
            $cell->colspan = 5;
            $row->cells[] = $cell;
            // Add to the row
            $table->data[] = $row;
        }
    } else {
        // Get statistics
        $stats = jemena_get_course_statistics($course, $role, $data->completiondatefrom, $data->completiondateto, true, $userstatus);
        if ($stats->traineesenrolled == 0) {
            $percentage = 0;
        } else {
            $percentage = ($stats->traineescompleted / $stats->traineesenrolled) * 100;
        }
        // Fill the table
        $row = new html_table_row();
        // Add new html cell
        $cell = new html_table_cell();
        $cell->style = 'text-align:center';
        $cell->text = $stats->category;
        $row->cells[] = $cell;
        // Add new html cell
        $cell = new html_table_cell();
        $cell->style = 'text-align:center';
        $cell->text = $stats->courseshortname;
        $row->cells[] = $cell;
        // Add new html cell
        $cell = new html_table_cell();
        $cell->style = 'text-align:center';
        $cell->text = $stats->traineesenrolled;
        $row->cells[] = $cell;
        // Add new html cell
        $cell = new html_table_cell();
        $cell->style = 'text-align:center';
        $cell->text = $stats->traineescompleted;
        $row->cells[] = $cell;
        // Add new html cell
        $cell = new html_table_cell();
        $cell->style = 'text-align:center';
        $cell->text = round($percentage) . "%";
        $row->cells[] = $cell;
        // Add to the row
        $table->data[] = $row;
    }
}

echo $OUTPUT->header();
echo "<link type='text/css' href='styles.css' rel='stylesheet'>";
echo $OUTPUT->heading(get_string('pluginname', 'report_jemena'));
echo $form->display();
if (!empty($table)) {
    echo $OUTPUT->box(html_writer::table($table), 'center');
    echo $OUTPUT->single_button($CFG->wwwroot.'/report/jemena/report.php?category='.$data->category.'&role='.$data->role.'&course='.$data->course.'&datefrom='.$datefrom.'&dateto='.$dateto.'&userstatus='.$userstatus, get_string('downloadreport', 'report_jemena'), 'POST');
    echo $OUTPUT->single_button($CFG->wwwroot.'/report/jemena/business_unit_report.php?category='.$data->category.'&role='.$data->role.'&course='.$data->course.'&datefrom='.$datefrom.'&dateto='.$dateto.'&userstatus='.$userstatus, get_string('businessunitreport', 'report_jemena'), 'POST');

    //echo $CFG->wwwroot.'/report/jemena/business_unit_report.php?category='.$data->category.'&role='.$data->role.'&course='.$data->course.'&datefrom='.$datefrom.'&dateto='.$dateto;
}
echo $OUTPUT->footer();
