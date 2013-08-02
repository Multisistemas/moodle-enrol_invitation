<?php

require_once($CFG->dirroot . '/enrol/renderer.php');

class theme_uclashared_core_renderer extends core_renderer {
    private $sep = NULL;
    private $theme = 'theme_uclashared';

    function separator() {
        if ($this->sep == NULL) {
            $this->sep = get_string('separator__', $this->theme);
        }

        return $this->sep;
    }
    
    /**
     * Attaches the meta tag needed for mobile display support
     * 
     * @return string 
     */
    function standard_head_html() {
        global $CFG;
        
        $out = parent::standard_head_html();
        
        // Add mobile support with option to switch
        if(get_user_device_type() != 'default') {
            $out .= '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=2.0; user-scalable=1;" />' . "\n";
        }
        
        // Attach print CSS
        $out .= '<link rel="stylesheet" type="text/css" media="print" href="' . $CFG->wwwroot .'/theme/uclashared/style/print.css" />' . "\n";
        
        return $out;
    }

    /** 
     *  Displays what user you are logged in as, and if needed, along with the 
     *  user you are logged-in-as.
     **/
    function login_info($withlinks = NULL) {
        global $CFG, $DB, $USER;

        $course = $this->page->course;

        // This will have login informations
        // [0] == Login information 
        // - Format  [REALLOGIN] (as \(ROLE\))|(DISPLAYLOGIN) (from MNET)
        // [1] == H&Fb link
        // [2] == Logout/Login button
        $login_info = array();

        $loginurl = get_login_url();
        $add_loginurl = ($this->page->url != $loginurl);

        $add_logouturl = false;

        $loginstr = '';

        if (isloggedin()) {
            $add_logouturl = true;
            $add_loginurl = false;

            $usermurl = new moodle_url('/user/profile.php', array(
                'id' => $USER->id
            ));


            // In case of mnet login
            $mnetfrom = '';
            if (is_mnet_remote_user($USER)) {
                $idprovider = $DB->get_record('mnet_host', array(
                    'id' => $USER->mnethostid
                ));

                if ($idprovider) {
                    $mnetfrom = html_writer::link($idprovider->wwwroot,
                        $idprovider->name);
                }
            }

            $realuserinfo = '';
            if (session_is_loggedinas()) {
                $realuser = session_get_realuser();
                $realfullname = fullname($realuser, true);
                $dest = new moodle_url('/course/loginas.php', array(
                    'id' => $course->id,
                    'sesskey' => sesskey()
                ));

                $realuserinfo = '[' . html_writer::link($dest, $realfullname) . ']'
                    . get_string('loginas_as', 'theme_uclashared');
            } 

            $fullname = fullname($USER, true);
            $userlink = html_writer::link($usermurl, $fullname);

            $rolename = '';
            // I guess only guests cannot switch roles
            if (isguestuser()) {
                $userlink = get_string('loggedinasguest');
                $add_loginurl = true;
            } else if (is_role_switched($course->id)) {
                $context = get_context_instance(CONTEXT_COURSE, $course->id);

                $role = $DB->get_record('role', array(
                    'id' => $USER->access['rsw'][$context->path]
                ));

                if ($role) {
                    $rolename = ' (' . format_string($role->name) . ') ';
                }
            } 

            $loginstr = $realuserinfo . $rolename . $userlink;

        } else {
            $loginstr = get_string('loggedinnot', 'moodle');
        }

        if (isset($SESSION->justloggedin)) {
            unset($SESSION->justloggedin);
            if (!empty($CFG->displayloginfailures) && !isguestuser()) {
                if ($count = count_login_failures($CFG->displayloginfailures, 
                        $USER->username, $USER->lastlogin)) {

                    $loginstr .= '&nbsp;<div class="loginfailures">';

                    if (empty($count->accounts)) {
                        $loginstr .= get_string('failedloginattempts', '', 
                                $count);
                    } else {
                        $loginstr .= get_string('failedloginattemptsall', '', 
                                $count);
                    }

                    if (has_capability('coursereport/log:view', 
                            get_context_instance(CONTEXT_SYSTEM))) {
                        $loginstr .= ' (' . html_writer::link(new moodle_url(
                            '/course/report/log/index.php', array(
                                'chooselog' => 1,
                                'id' => 1,
                                'modid' => 'site_errors'
                            )), get_string('logs')) . ')';
                    }

                    $loginstr .= '</div>';
                }
            }
        }

        $login_info[] = $loginstr;

        // The help and feedback link
        $fbl = $this->help_feedback_link();
        if ($fbl) {
            $login_info[] = $fbl;
        }
       
        // The actual login link
        if ($add_loginurl) {
            $login_info[] = html_writer::link($loginurl, 
                get_string('login'));
        } else if ($add_logouturl) {
            $login_info[] = html_writer::link(
                new moodle_url('/login/logout.php',
                    array('sesskey' => sesskey())), 
                get_string('logout')
            );
        }

        $separator = $this->separator(); 
        $login_string = implode($separator, $login_info);

        return $login_string;
    }

    /**
     *  Returns the HTML link for the help and feedback.
     **/
    function help_feedback_link() {
        $help_locale = $this->call_separate_block_function(
                'ucla_help', 'get_action_link'
            );

        if (!$help_locale) {
            return false;
        }
        
        $hf_link = get_string('help_n_feedback', $this->theme);

        return html_writer::link($help_locale, $hf_link);
    }

    /**
     *  Calls the hook function that will return the current week we are on.
     **/
    function weeks_display() {

        $weeks_text = $this->call_separate_block_function(
                'ucla_weeksdisplay', 'get_week_display'
            );

        if (!$weeks_text) {
            return false;
        }

        return $weeks_text;
    }

    /**
     *  This a wrapper around pix().
     *  
     *  It will make a picture of the logo, and turn it into a link.
     *
     *  @param string $pix Passed to pix().
     *  @param string $pix_loc Passed to pix().
     *  @param moodle_url $address Destination of anchor.
     *  @return string The logo HTML element.
     **/
    function logo($pix, $pix_loc, $address=null) {
        global $CFG, $OUTPUT;

        if ($address == null) {
            $address = new moodle_url($CFG->wwwroot);
        } 

        $pix_url = $this->pix_url($pix, $pix_loc);
        //UCLA MOD BEGIN: CCLE-2862-Main_site_logo_image_needs_alt_attribute
        $logo_alt = get_string('UCLA_CCLE_text', 'theme_uclashared');
        $logo_img = html_writer::empty_tag('img', array('src' => $pix_url, 'alt' => $logo_alt));
        //UCLA MOD END: CCLE-2862
        $link = html_writer::link($address, $logo_img);

        return $link;
    }

    /**
     *      Displays the text underneath the UCLA | CCLE logo.
     *
     *      Will reach into the settings to see if the hover over should be 
     *      displayed.
     **/
    function sublogo() {
        $display_text   = $this->get_config($this->theme, 'logo_sub_text');

        return $display_text;
    }
    
    // This function will be called only in class sites 
    function control_panel_button() {
        global $CFG, $OUTPUT;

        // Hack since contexts and pagelayouts are different things
        // Hack to fix: display control panel link when updating a plugin
        if ($this->page->context == get_context_instance(CONTEXT_SYSTEM)) {
            return '';
        }

        // Use html_writer to render the control panel button
        $cp_text = html_writer::empty_tag('img', 
            array('src' => $OUTPUT->pix_url('cp_button', 'block_ucla_control_panel'),
                  'alt' => get_string('control_panel', $this->theme)));
        
        $cp_link = $this->call_separate_block_function(
                'ucla_control_panel', 'get_action_link'
            );

        if (!$cp_link) {
            return false;
        }
       
        $cp_button = html_writer::link($cp_link, $cp_text, 
            array('class' => 'control-panel-button'));

        return $cp_button;
    }

    function footer_links() {
        global $CFG;

        $links = array(
            'contact_ccle', 
            'about_ccle',
            'privacy',
            'copyright',
            'uclalinks',
            'separator',            
            'school',
            'registrar',
            'myucla',
            'disability'
        );

        $footer_string = '';
        
        $custom_text = trim(get_config($this->theme, 'footer_links'));
        if (!empty($custom_text)) {
            $footer_string = $custom_text; 
            array_unshift($links, 'separator');
        }

        // keep all links before seperator from opening into new window
        $open_new_window = false;
        foreach ($links as $link) {
            if ($link == 'separator') {
                $footer_string .= '&nbsp;';
                $footer_string .= $this->separator();
                $open_new_window = true;
            } else {
                $link_display = get_string('foodis_' . $link, $this->theme);
                $link_href = get_string('foolin_' . $link, $this->theme);
                if (empty($open_new_window)) {
                    $params = array('href' => $link_href);
                } else {
                    $params = array('href' => $link_href, 'target' => '_blank');                    
                }
                
                $link_a = html_writer::tag('a', $link_display, $params);

                $footer_string .= '&nbsp;' . $link_a;
            }
        }

        return $footer_string;
    }

    function copyright_info() {
        $curr_year = date('Y');
        return get_string('copyright_information', $this->theme, $curr_year);
    }

    /**
     *  Attempts to get a feature of another block to generate special text or
     *  link to put into the theme.
     *
     *  TODO This function should not belong to this specific block
     **/
    function call_separate_block_function($blockname, $functionname) {
        if (during_initial_install()) {
            return '';
        }

        return block_method_result($blockname, $functionname, 
            $this->page->course);
    }

    function get_environment() {
        $c = $this->get_config($this->theme, 'running_environment');

        if (!$c) {
            return 'prod';
        } 

        return $c;
    }

    /**
     *  Overwriting pix icon renderers to not use icons for action buttons.
     *
     * @param action_link $link
     * @return string HTML fragment
     */
    function render_action_link(action_link $action) {
        $noeditingicons = get_user_preferences('noeditingicons', 1);
        if (!empty($noeditingicons)) {
            if ($action->text instanceof pix_icon) {
                $icon = $action->text;

                /// We want to preserve the icon (but hide it), 
                /// so the YUI js references remain intact
                $icon->attributes['style'] = 'display:none';
                $attr = $icon->attributes;
                $displaytext = $attr['alt'];

                $out = $this->render($icon);

                // Output hidden icon and text
                $action->text = $out . $displaytext;
            }
        }

        return parent::render_action_link($action);
    }

    /**
     *  Wrapper function to prevent initial install.
     **/
    function get_config($plugin, $var) {
        if (!during_initial_install()) {
            return get_config($plugin, $var);
        } 

        return false;
    }
    
    public function edit_button(moodle_url $url) {
        // CCLE-3740 - In order to handle correct redirects for landing
        // page, we use an alias for section 0 that UCLA format expects.
        $section = optional_param('section', null, PARAM_INT);
        if(!is_null($section) && $section === 0) {
            $url->param('section', -1);
        }
        
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }

        return $this->single_button($url, $editstring);
    }
}

// TODO move this to a more relevant location?
class theme_uclashared_core_enrol_renderer extends core_enrol_renderer {
    // Overriding functionality for enrol-users page
    function user_groups_and_actions($userid, $groups, $allgroups, 
                                     $canmanagegroups, $pageurl) {

        // Easiest solution: prevent editing of groups from this UI
        return parent::user_groups_and_actions($userid, $groups, $allgroups,
            false, $pageurl);
            
    }

}
