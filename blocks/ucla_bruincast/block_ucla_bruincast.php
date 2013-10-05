<?php

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(__FILE__))) . '/local/ucla/lib.php');

class block_ucla_bruincast extends block_base {

    /**
     * Called by moodle
     */
    public function init() {

        // initialize title and name
        $this->title = get_string('title', 'block_ucla_bruincast');
        $this->name = get_string('pluginname', 'block_ucla_bruincast');
    }

    /**
     * Called by moodle
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        return $this->content;
    }

    /**
     * Use UCLA Course menu block hook
     */
    public static function get_navigation_nodes($course) {
        // get global variable
        global $DB;
        
        $courseid = $course['course']->id; // course id from the hook function

        $nodes = array(); // initialize $nodes with an empty array for a good fallback; the empty array has no effect in coursemenu block hook.
        $previouslinks = array();
        $atleastone = false;
        
        if ($matchingcourses = $DB->get_records('ucla_bruincast', array('courseid' => $courseid))) {
            foreach ($matchingcourses as $bcnodes) {
                if (!empty($bcnodes->bruincast_url)) {
                        
                    $node_type = 'node_' . strtolower($bcnodes->restricted);
                    $node_type = str_replace(' ', '_', $node_type);

                    $reginfo = ucla_get_reg_classinfo($bcnodes->term, $bcnodes->srs);

                    if (strcmp($bcnodes->restricted, "Restricted") == 0) {
                        // get contexts for permission checking
                        $context = get_context_instance(CONTEXT_COURSE, $courseid);

                        $usedlink = false;

                        // check if already a link
                        foreach ($previouslinks as $link) {
                            if ($link == $bcnodes->bruincast_url) {
                                $usedlink = true;
                                break;
                            }
                        }

                        // check if has permission, then generate menu nodes if does
                        if (is_enrolled($context) || has_capability('moodle/site:config', $context)) {
                            if ($usedlink || sizeof($previouslinks) == 0) {

                                if (!$atleastone) {
                                    $node = navigation_node::create(
                                            'Bruincast ' .get_string($node_type, 'block_ucla_bruincast'), 
                                            new moodle_url($bcnodes->bruincast_url));
                                    $node->add_class('bruincast-link');
                                    $nodes[] = $node;
                                }

                                $atleastone = true;
                            } else {
                                $node = navigation_node::create('Bruincast ' . $reginfo->subj_area . $reginfo->coursenum, new moodle_url($bcnodes->bruincast_url));
                                $node->add_class('bruincast-link');
                                $nodes[] = $node;
                            }

                            $previouslinks[] = $bcnodes->bruincast_url;
                        }
                    } else { // if not restricted, no need for restriction checking, just generate nodes
                        $usedlink = false;

                        // check if already a link
                        foreach ($previouslinks as $link) {
                            if ($link == $bcnodes->bruincast_url) {
                                $usedlink = true;
                                break;
                            }
                        }


                        if ($usedlink || sizeof($previouslinks) == 0) {

                            if (!$atleastone) {
                                $node = navigation_node::create(
                                        'Bruincast ' .get_string($node_type, 'block_ucla_bruincast'), 
                                        new moodle_url($bcnodes->bruincast_url));
                                $node->add_class('bruincast-link');
                                $nodes[] = $node;
                            }

                            $atleastone = true;
                        } else {
                            $node = navigation_node::create('Bruincast ' . $reginfo->subj_area . $reginfo->coursenum, new moodle_url($bcnodes->bruincast_url));
                            $node->add_class('bruincast-link');
                            $nodes[] = $node;
                        }

                        $previouslinks[] = $bcnodes->bruincast_url;
                    }
                }
            }
        }
        return $nodes;
    }

    /**
     *  Called by moodle
     */
    public function applicable_formats() {

        return array(
            'site-index' => false,
            'course-view' => false,
            'my' => false,
            'block-ucla_bruincast' => false,
            'not-really-applicable' => true
        );
        // hack to make sure the block can never be instantiated
    }

    /**
     *  Called by moodle
     */
    public function instance_allow_multiple() {
        return false; // disables multiple block instances per page
    }

    /**
     *  Called by moodle
     */
    public function instance_allow_config() {
        return false; // disables instance configuration
    }

}
