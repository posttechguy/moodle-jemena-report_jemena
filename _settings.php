<?php
/**
 * Custom authentication for Jemena Report project
 *
 * Administration settings
 *
 * @package     report_jemena
 * @author      Bevan Holman <bevan@pukunui.com>, Pukunui
 * @copyright   2015 onwards, Pukunui
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Settings menu.

$ADMIN->add('root', new admin_category('report_jemena', get_string('pluginname', 'report_jemena')));

$ADMIN->add('report_jemena',
    new admin_externalpage('reportjemena', get_string('jemenasearch', 'report_jemena'),
    new moodle_url('/report/jemena/index.php'), 'report/jemena:view')
);

