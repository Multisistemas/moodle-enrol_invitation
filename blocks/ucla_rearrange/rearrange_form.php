<?php 

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class ucla_rearrange_form extends moodleform {

    function definition() {
        $mform =& $this->_form;

        $course_id = $this->_customdata['course_id'];
        $sections = $this->_customdata['sections'];

        $mform->addElement('hidden', 'course_id', $course_id);
        $mform->addElement('hidden', 'serialized', '',
            array('id' => 'serialized'));
        $mform->setType('serialized', PARAM_RAW);

        foreach ($sections as $section) {
            $fieldname = 'serialized-section-' . $section;
            $mform->addElement('hidden', $fieldname,
                '', array('id' => 'serialized-' . $section));
            $mform->setType($fieldname, PARAM_RAW);
        }

        $this->add_action_buttons();

        $mform->addElement('header');

        $mform->addElement('html', html_writer::tag('div',
            get_string('javascriptrequired', 'group'), array('id' => 
                block_ucla_rearrange::primary_domnode)));

        $this->add_action_buttons();
    }
}
