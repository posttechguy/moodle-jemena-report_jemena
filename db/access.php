<?php
/**
 * Capabilities
 *
 * @package     report_jemena
 * @author      Bevan Holman <bevan@pukunui.com>, Pukunui
 * @copyright   2015 onwards, Pukunui
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = array(

    'report/jemena:view' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    )
);
