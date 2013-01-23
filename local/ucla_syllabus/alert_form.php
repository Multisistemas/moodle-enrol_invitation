<?php
/**
 * Syllabus alert form definition.
 *
 * @package    local
 * @subpackage ucla_syllabus
 * @copyright  2012 UC Regents
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class alert_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('html', html_writer::tag('div',
                get_string('alert_msg', 'local_ucla_syllabus')));

        // need to use multiple submit buttons so that form is sent without the
        // need for js onclick handlers
        $alert_buttons = array();
        $alert_buttons[] = $mform->createElement('submit', 'yesbutton',
                get_string('alert_yes', 'local_ucla_syllabus'));
        $alert_buttons[] = $mform->createElement('submit', 'nobutton',
                get_string('alert_no', 'local_ucla_syllabus'));
        $alert_buttons[] = $mform->createElement('submit', 'laterbutton',
                get_string('alert_later', 'local_ucla_syllabus'));
        $mform->addGroup($alert_buttons, 'alert_buttons', '', array(' '), false);
        $mform->closeHeaderBefore('alert_buttons');
    }
}