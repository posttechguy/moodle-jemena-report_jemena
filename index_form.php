<?php

/**
 * The report form
 *
 * @author Mark Nelson, Pukunui Technology
 * @copyright Jemena
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package admin
 * @subpackage report
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

/**
 * Form for filtering the jemena search
 */
class report_jemena_filter_form extends moodleform {

    /**
     * Definition for the form
     */
    function definition() {
        // Create form
        $mform = $this->_form;
        
        $category = $this->_customdata['category'];
        $role = $this->_customdata['role'];
        $course = $this->_customdata['course'];
        $active = $this->_customdata['active'];

        $mform->addElement('header', 'selectprocess', get_string('formdescription', 'report_jemena'));

        // The category
        $mform->addElement('select', 'category', get_string('processarea', 'report_jemena'), jemena_get_categories());
        $mform->setType('category', PARAM_INT);
        $mform->setDefault('category', $category);
        // The role
        $mform->addElement('select', 'role', get_string('role', 'report_jemena'), jemena_get_meta_courses());
        $mform->setType('role', PARAM_INT);
        $mform->setDefault('role', $role);
        // Add line break
        $mform->addElement('html', '<hr />');
        // The course
        $mform->addElement('select', 'course', get_string('course', 'report_jemena'), jemena_get_meta_linked_courses($category, $role, true));
        $mform->setType('course', PARAM_INT);
        $mform->setDefault('course', $course);
        // Active/inactive
        $mform->addElement('select', 'userstatus', get_string('userstatus', 'report_jemena'), jemena_get_user_status());
        $mform->setType('userstatus', PARAM_INT);
        $mform->setDefault('active', $active);
        // The completion date from
        $mform->addElement('date_selector', 'completiondatefrom', get_string('completiondatefrom', 'report_jemena'), array('optional' => true));
        $mform->setType('completiondatefrom', PARAM_INT);
        // The completion date to
        $mform->addElement('date_selector', 'completiondateto', get_string('completiondateto', 'report_jemena'), array('optional' => true));
        $mform->setType('completiondateto', PARAM_INT);
        // Add the submit and reset buttons
        $buttons = array();
        $buttons[] =& $mform->createElement('submit', 'submit', get_string('search', 'report_jemena'), 'asd');
        $buttons[] =& $mform->createElement('submit', 'reset', get_string('reset', 'report_jemena'));
        $mform->addGroup($buttons, 'buttons', '', '', true);
    }
    /**
     * Validate the form submission
     *
     * @param array $data  submitted form data
     * @param array $files submitted form files
     * @return array
     */
    public function validation($data, $files) {
    //    global $DB;

    }
}

