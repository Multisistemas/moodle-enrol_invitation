<?php
/**
 * Version details
 *
 * @package    block
 * @subpackage ucla_my_sites
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2013091300;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2011112900;        // Requires this Moodle version
$plugin->component = 'block_ucla_my_sites'; // Full name of the plugin (used for diagnostics)

$plugin->dependencies = array('block_ucla_browseby' => 2012041700);
