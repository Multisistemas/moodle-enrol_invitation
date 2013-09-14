<?php

/**
 *  This could be abstracted out another level.
 **/
class easyupload_activity_form extends easy_upload_form {
    var $allow_publicprivate = true;
    var $enable_availability = false;

    function specification() {
        $mform =& $this->_form;

        $course = $mform->getElement('course')->getValue();

        $mform->addElement('hidden', 'redirectme', '/course/modedit.php');
        $mform->setType('redirectme', PARAM_URL);

        // Add the select form
        $actsel = $mform->addElement('select', 'add', 
            get_string('dialog_add_activity_box', self::associated_block));

        // we need to specially handle activities
        // Due to nested types... this MAY be needed for resources too
        // Iteration of recursion...
        foreach ($this->activities as $cursor => $actname) {
            $prefix = '-';
            if (is_array($actname)) {
                $temp = array_keys($actname);
                $cursor = reset($temp);
                $actname = reset($actname);
            }

            $tempstack = array(
                array($cursor => $actname)
            );

            while (!empty($tempstack)) {
                $temppop = array_pop($tempstack);

                $objname = reset($temppop);
                $temppop = array_keys($temppop);
                $objref = reset($temppop);

                if (is_array($objname)) {
                    $tempstack[] = array($prefix);

                    foreach ($objname as $subcur => $subact) {
                        $tempstack[] = array($subcur => $subact);
                    }

                    $tempstack[] = array($prefix . ' ' . $objref);
                    $prefix .= '-';
                } else {

                    if ($objname[0] == '-') {
                        $actsel->addOption($objname, '',
                            array('disabled' => 'disabled'));
                    } else {
                        $actsel->addOption($objname, $objref);
                    }
                }
            }
        }
    }

    function get_coursemodule() {
        return false;
    }

    /**
     *  Required for redirection forms.
     **/
    function get_send_params() {
        return array('course', 'add', 'section', 'private');
    }
}

// End of File
