<?php

/**
 * Handles AJAX requests
 *
 * @copyright Jemena
 * @author    Mark Nelson <mark@moodle.com.au>, Pukunui Technology
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/report/jemena/lib.php');

$category = required_param('category', PARAM_INT);
$role = required_param('role', PARAM_INT);

echo json_encode(jemena_get_meta_linked_courses($category, $role, false, true));
?>
