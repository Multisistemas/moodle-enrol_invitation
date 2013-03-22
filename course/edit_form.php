<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/publicprivate/course.class.php');
require_once($CFG->libdir.'/publicprivate/site.class.php');
require_once($CFG->dirroot . '/admin/tool/uclasiteindicator/lib.php');

class course_edit_form extends moodleform {
    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB;

        $mform    = $this->_form;

        $course        = $this->_customdata['course']; // this contains the data of this form
        $category      = $this->_customdata['category'];
        // START UCLA MOD CCLE-2389 - override with site request category,
        // This forces the edit form to display the requested category. 
        // If the category is changed, that preference is also saved by siteindicator
        if(!empty($course->id) && $request = siteindicator_request::load($course->id)) {
            $course->category = $request->request->categoryid;
        }
        // END UCLA MOD CCLE-2389
        $editoroptions = $this->_customdata['editoroptions'];
        $returnto = $this->_customdata['returnto'];
        
        $systemcontext   = get_context_instance(CONTEXT_SYSTEM);
        $categorycontext = get_context_instance(CONTEXT_COURSECAT, $category->id);

        if (!empty($course->id)) {
            $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
            $context = $coursecontext;
        } else {
            $coursecontext = null;
            $context = $categorycontext;
        }

        $courseconfig = get_config('moodlecourse');

        $this->course  = $course;
        $this->context = $context;
        
        // START UCLAMOD CCLE-2389 - site indicator info display
        
        if(!empty($course->id) && ucla_map_courseid_to_termsrses($this->course->id)) {
            // is a registrar site
//            $mform->addElement('static', 'indicator', 
//                    get_string('type', 'tool_uclasiteindicator'), 
//                    get_string('site_registrar', 'tool_uclasiteindicator'));
        } else {
            // user can assign site type if they have the capability at site, 
            // category, or course level
            $can_edit_sitetype = false;
            if (has_capability('tool/uclasiteindicator:edit', $systemcontext) || 
                    has_capability('tool/uclasiteindicator:edit', $categorycontext) ||
                    (!empty($coursecontext) && has_capability('tool/uclasiteindicator:edit', $coursecontext))) {
                $can_edit_sitetype = true;
            }

            $indicator = null;
            if (!empty($course->id)) {
                $indicator = siteindicator_site::load($course->id);
            }            

            // do not allow TA site type to be changed via GUI
            if (!empty($indicator) &&
                    $indicator->property->type == siteindicator_manager::SITE_TYPE_TASITE) {
                $can_edit_sitetype = false;
            }


            // only display site type info if there is a type and user can edit
            if ($can_edit_sitetype || !empty($indicator)) {
                $mform->addElement('header','uclasiteindicator', get_string('pluginname', 'tool_uclasiteindicator'));
            }
            
            if(!empty($indicator)) {                
                $indicator_type = html_writer::tag('strong',
                        siteindicator_manager::get_types_list($indicator->property->type));
                $mform->addElement('static', 'indicator', get_string('type', 'tool_uclasiteindicator'), 
                        $indicator_type);
                
                $roles = $indicator->get_assignable_roles();
                $mform->addElement('static', 'indicator_roles', get_string('roles', 'tool_uclasiteindicator'), 
                        '<strong>' . implode('</strong>, <strong>', $roles) . '</strong>');
            }
                                
            // Change the site type
            if($can_edit_sitetype) {
                if (empty($indicator)) {
                    // no indicator found, display ability for user to choose type
                    // if they have the capability to edit
                    $indicator_type = get_string('no_indicator_type', 'tool_uclasiteindicator');
                    $mform->addElement('static', 'indicator', get_string('type', 'tool_uclasiteindicator'), 
                            $indicator_type);                    
                }

                $types = siteindicator_manager::get_types_list();
                $radioarray = array();
                foreach($types as $type) {
                    // don't allow tasite type to be selected
                    if (siteindicator_manager::SITE_TYPE_TASITE == $type['shortname']) {
                        continue;
                    }
                    $descstring = '<strong>' . $type['fullname'] . '</strong> - ' . $type['description'];
                    $attributes = array(
                        'class' => 'indicator-form',
                        'value' => $type['shortname']
                    );
                    $radioarray[] = $mform->createElement('radio', 'indicator_change', '', $descstring, $type['shortname'], $attributes);
                }
                $mform->addGroup($radioarray, 'indicator_type_radios', get_string('change', 'tool_uclasiteindicator'), array('<br/>'), false);
                $mform->addGroupRule('indicator_type_radios', get_string('required'), 'required');
                
                if (!empty($indicator)) {
                    $mform->setDefault('indicator_change', $indicator->property->type);
                }
            }            
        }
        // END UCLA MOD CCLE-2389
/// form definition with new course defaults
//--------------------------------------------------------------------------------
        $mform->addElement('header','general', get_string('general', 'form'));

        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);

        // verify permissions to change course category or keep current
        if (empty($course->id)) {
            if (has_capability('moodle/course:create', $categorycontext)) {
                $displaylist = array();
                $parentlist = array();
                make_categories_list($displaylist, $parentlist, 'moodle/course:create');
                $mform->addElement('select', 'category', get_string('category'), $displaylist);
                $mform->addHelpButton('category', 'category');
                $mform->setDefault('category', $category->id);
            } else {
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $category->id);
            }
        } else {
            if (has_capability('moodle/course:changecategory', $coursecontext)) {
                $displaylist = array();
                $parentlist = array();
                make_categories_list($displaylist, $parentlist, 'moodle/course:create');
                if (!isset($displaylist[$course->category])) {
                    //always keep current
                    $displaylist[$course->category] = format_string($DB->get_field('course_categories', 'name', array('id'=>$course->category)));
                }
                $mform->addElement('select', 'category', get_string('category'), $displaylist);
                $mform->addHelpButton('category', 'category');
            } else {
                //keep current
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $course->category);
            }
        }

        $mform->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_MULTILANG);
        if (!empty($course->id) and !has_capability('moodle/course:changefullname', $coursecontext)) {
            $mform->hardFreeze('fullname');
            $mform->setConstant('fullname', $course->fullname);
        }

        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_MULTILANG);
        if (!empty($course->id) and !has_capability('moodle/course:changeshortname', $coursecontext)) {
            $mform->hardFreeze('shortname');
            $mform->setConstant('shortname', $course->shortname);
        }

        // START UCLA MOD: CCLE-2940 - TERM-SRS Numbers needed in Course ID Number field
        // We aren't using idnumber to put in term-srs anymore, so just query 
        // for term-srs using the cross-listing api and put in the results as
        // a constant        
//        $mform->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="100"  size="10"');
//        $mform->addHelpButton('idnumber', 'idnumbercourse');
//        $mform->setType('idnumber', PARAM_RAW);
//        if (!empty($course->id) and !has_capability('moodle/course:changeidnumber', $coursecontext)) {
//            $mform->hardFreeze('idnumber');
//            $mform->setConstants('idnumber', $course->idnumber);
//        }
        $mform->addElement('static','idnumber', get_string('idnumbercourse'));
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        if (!empty($course->id)) {
            // only query for term-srs if course exists
            require_once($CFG->dirroot . '/local/ucla/lib.php');
            $course_info = ucla_get_course_info($course->id);    
            $idnumber = '';
            if (!empty($course_info)) {
                // create string
                $first_entry = true;
                foreach ($course_info as $course_record) {
                    $first_entry ? $first_entry = false : $idnumber .= ', ';
                    $idnumber .= sprintf('%s (%s)', 
                            ucla_make_course_title($course_record), 
                            make_idnumber($course_record));
                }                    
            }
            $course->idnumber = $idnumber;     
        }
        // END UCLA MOD: CCLE-2940
        

        $mform->addElement('editor','summary_editor', get_string('coursesummary'), null, $editoroptions);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);

        if (!empty($course->id) and !has_capability('moodle/course:changesummary', $coursecontext)) {
            $mform->hardFreeze('summary_editor');
        }

        // BEGIN UCLA MOD: CCLE-3278-Change-options-on-course-edit-settings-page
        $has_editadvancedcoursesettings = false;
        if (empty($coursecontext) || 
                has_capability('local/ucla:editadvancedcoursesettings', $coursecontext)) {
            // handle case in which a new course is being created
            $has_editadvancedcoursesettings = true;
        }
        /*
        $courseformats = get_plugin_list('format');
        $formcourseformats = array();
        foreach ($courseformats as $courseformat => $formatdir) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
        $mform->addHelpButton('format', 'format');
        $mform->setDefault('format', $courseconfig->format);
        
        $mform->addElement('select', 'coursedisplay', get_string('coursedisplay'),
            array(COURSE_DISPLAY_SINGLEPAGE => get_string('coursedisplay_single'),
                COURSE_DISPLAY_MULTIPAGE => get_string('coursedisplay_multi')));
        $mform->addHelpButton('coursedisplay', 'coursedisplay');
        $mform->setDefault('coursedisplay', $courseconfig->coursedisplay);
        */
        if ($has_editadvancedcoursesettings) {
            $courseformats = get_plugin_list('format');
            $formcourseformats = array();
            foreach ($courseformats as $courseformat => $formatdir) {
                $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
            }
            $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
            $mform->addHelpButton('format', 'format');
            $mform->setDefault('format', $courseconfig->format);
            
            $mform->addElement('select', 'coursedisplay', get_string('coursedisplay'),
                array(COURSE_DISPLAY_SINGLEPAGE => get_string('coursedisplay_single'),
                    COURSE_DISPLAY_MULTIPAGE => get_string('coursedisplay_multi')));
            $mform->addHelpButton('coursedisplay', 'coursedisplay');
            $mform->setDefault('coursedisplay', COURSE_DISPLAY_SINGLEPAGE);                
        } else {
            $mform->addElement('static', 'format_readonly', get_string('format'),
                    get_string('pluginname', "format_$courseconfig->format"));
            
            $coursedisplay_strings = array(COURSE_DISPLAY_SINGLEPAGE => get_string('coursedisplay_single'),
                    COURSE_DISPLAY_MULTIPAGE => get_string('coursedisplay_multi'));
            $coursedisplay_default = isset($courseconfig->coursedisplay) ? $courseconfig->coursedisplay : COURSE_DISPLAY_SINGLEPAGE;
            $mform->addElement('static', 'coursedisplay_readonly', get_string('coursedisplay'),
                    $coursedisplay_strings[$coursedisplay_default]);            
        }
        // END UCLA MOD: CCLE-3278

        $max = $courseconfig->maxsections;
        if (!isset($max) || !is_numeric($max)) {
            $max = 52;
        }
        for ($i = 0; $i <= $max; $i++) {
            $sectionmenu[$i] = "$i";
        }
        $mform->addElement('select', 'numsections', get_string('numberweeks'), $sectionmenu);
        $mform->setDefault('numsections', $courseconfig->numsections);

        $mform->addElement('date_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', time() + 3600 * 24);

        $choices = array();
        $choices['0'] = get_string('hiddensectionscollapsed');
        $choices['1'] = get_string('hiddensectionsinvisible');
        $mform->addElement('select', 'hiddensections', get_string('hiddensections'), $choices);
        $mform->addHelpButton('hiddensections', 'hiddensections');
        $mform->setDefault('hiddensections', $courseconfig->hiddensections);

        $options = range(0, 10);
        $mform->addElement('select', 'newsitems', get_string('newsitemsnumber'), $options);
        $mform->addHelpButton('newsitems', 'newsitemsnumber');
        $mform->setDefault('newsitems', $courseconfig->newsitems);

        $mform->addElement('selectyesno', 'showgrades', get_string('showgrades'));
        $mform->addHelpButton('showgrades', 'showgrades');
        $mform->setDefault('showgrades', $courseconfig->showgrades);

        $mform->addElement('selectyesno', 'showreports', get_string('showreports'));
        $mform->addHelpButton('showreports', 'showreports');
        $mform->setDefault('showreports', $courseconfig->showreports);

        // Handle non-existing $course->maxbytes on course creation.
        $coursemaxbytes = !isset($course->maxbytes) ? null : $course->maxbytes;

        // Let's prepare the maxbytes popup.
        $choices = get_max_upload_sizes($CFG->maxbytes, 0, 0, $coursemaxbytes);
        // BEGIN UCLA MOD: CCLE-3278-Change-options-on-course-edit-settings-page
        /*
        $mform->addElement('select', 'maxbytes', get_string('maximumupload'), $choices);
        $mform->addHelpButton('maxbytes', 'maximumupload');
        $mform->setDefault('maxbytes', $courseconfig->maxbytes); 
        */
        if ($has_editadvancedcoursesettings) {
            $mform->addElement('select', 'maxbytes', get_string('maximumupload'), $choices);
            $mform->addHelpButton('maxbytes', 'maximumupload');
            $mform->setDefault('maxbytes', $courseconfig->maxbytes);
        } else {
            $mform->addElement('static', 'maxbytes_readonly', 
                    get_string('maximumupload'), $choices[$courseconfig->maxbytes]);
            //$mform->addHelpButton('maxbytes_readonly', 'maximumupload');
        }
        // END UCLA MOD: CCLE-3278

        if (!empty($course->legacyfiles) or !empty($CFG->legacyfilesinnewcourses)) {
            if (empty($course->legacyfiles)) {
                //0 or missing means no legacy files ever used in this course - new course or nobody turned on legacy files yet
                $choices = array('0'=>get_string('no'), '2'=>get_string('yes'));
            } else {
                $choices = array('1'=>get_string('no'), '2'=>get_string('yes'));
            }
            $mform->addElement('select', 'legacyfiles', get_string('courselegacyfiles'), $choices);
            $mform->addHelpButton('legacyfiles', 'courselegacyfiles');
            if (!isset($courseconfig->legacyfiles)) {
                // in case this was not initialised properly due to switching of $CFG->legacyfilesinnewcourses
                $courseconfig->legacyfiles = 0;
            }
            $mform->setDefault('legacyfiles', $courseconfig->legacyfiles);
        }

        // START UCLA MOD: CCLE-2315 - CUSTOM DEPARTMENT THEMES

        // make sure that user can edit course theme (either at course or category context)
        $editcoursetheme = false;
        if ((!empty($coursecontext) && has_capability('local/ucla:editcoursetheme', $coursecontext)) ||
                !empty($categorycontext) && has_capability('local/ucla:editcoursetheme', $categorycontext)) {
            $editcoursetheme = true;
        }

        //if (!empty($CFG->allowcoursethemes) {
        if (!empty($CFG->allowcoursethemes) && $editcoursetheme) {
        // END UCLA MOD: CCLE-2315
            $themeobjects = get_list_of_themes();
            $themes=array();
            $themes[''] = get_string('forceno');
            foreach ($themeobjects as $key=>$theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);
            
            // START UCLA MOD CCLE-2315 - enable editing of banner logo
            global $OUTPUT;
            
            // If we're using the uclasharedcourse theme, we want to allow a course
            // to upload extra logos
            if(!empty($OUTPUT->coursetheme)) {

                // Add a file manager
                $mform->addElement('filemanager', 'logo_attachments', get_string('additional_logos', 'theme_uclasharedcourse'), 
                        null, $OUTPUT->course_logo_config());

                // Show logo guide
                $pix_url = $OUTPUT->pix_url('guide', 'theme');
                $img = html_writer::empty_tag('img', array('src' => $pix_url));
                $mform->addElement('static', 'description', '', $img);

                // Check if we already have images
                $draftitemid = file_get_submitted_draft_itemid('logo_attachments');

                file_prepare_draft_area($draftitemid, 
                        $coursecontext->id,
                        'theme_uclasharedcourse', 'course_logos', $course->id, 
                        $OUTPUT->course_logo_config());   
                
                $data['logo_attachments'] = $draftitemid;                     

                $this->set_data($data);
            }
            // END UCLA MOD CCLE-2315
        }
        
//--------------------------------------------------------------------------------
        enrol_course_edit_form($mform, $course, $context);

//--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('groups', 'group'));

        /**
         * Flag to enable or disable public/private if it is enabled for the
         * site or if it is activated for the course.
         *
         * @author ebollens
         * @version 20110719
         */
        if(PublicPrivate_Site::is_enabled() || (PublicPrivate_Course::is_publicprivate_capable($course) 
                && PublicPrivate_Course::build($course)->is_activated())) {
            $choices = array();
            $choices[0] = get_string('disable');
            $choices[1] = get_string('enable');
            $mform->addElement('select', 'enablepublicprivate', get_string('publicprivate','local_publicprivate'), $choices);
            $mform->addHelpButton('enablepublicprivate', 'publicprivateenable');
            $mform->setDefault('enablepublicprivate', empty($course->enablepublicprivate) ? 1 : $course->enablepublicprivate);
        }

        $choices = array();
        $choices[NOGROUPS] = get_string('groupsnone', 'group');
        $choices[SEPARATEGROUPS] = get_string('groupsseparate', 'group');
        $choices[VISIBLEGROUPS] = get_string('groupsvisible', 'group');
        $mform->addElement('select', 'groupmode', get_string('groupmode', 'group'), $choices);
        $mform->addHelpButton('groupmode', 'groupmode', 'group');
        $mform->setDefault('groupmode', $courseconfig->groupmode);

        $choices = array();
        $choices['0'] = get_string('no');
        $choices['1'] = get_string('yes');
        $mform->addElement('select', 'groupmodeforce', get_string('groupmodeforce', 'group'), $choices);
        $mform->addHelpButton('groupmodeforce', 'groupmodeforce', 'group');
        $mform->setDefault('groupmodeforce', $courseconfig->groupmodeforce);

        //default groupings selector
        $options = array();
        $options[0] = get_string('none');
        $mform->addElement('select', 'defaultgroupingid', get_string('defaultgrouping', 'group'), $options);

//--------------------------------------------------------------------------------
        $mform->addElement('header','', get_string('availability'));

        $choices = array();
        $choices['0'] = get_string('courseavailablenot');
        $choices['1'] = get_string('courseavailable');
        $mform->addElement('select', 'visible', get_string('availability'), $choices);
        $mform->addHelpButton('visible', 'availability');
        $mform->setDefault('visible', $courseconfig->visible);
        if (!has_capability('moodle/course:visibility', $context)) {
            $mform->hardFreeze('visible');
            if (!empty($course->id)) {
                $mform->setConstant('visible', $course->visible);
            } else {
                $mform->setConstant('visible', $courseconfig->visible);
            }
        }

//--------------------------------------------------------------------------------
        // BEGIN UCLA MOD: CCLE-3278-Change-options-on-course-edit-settings-page
        /*
        $mform->addElement('header','', get_string('language'));

        $languages=array();
        $languages[''] = get_string('forceno');
        $languages += get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'lang', get_string('forcelanguage'), $languages);
        $mform->setDefault('lang', $courseconfig->lang);
        */
        if ($has_editadvancedcoursesettings) {
            $mform->addElement('header','', get_string('language'));

            $languages=array();
            $languages[''] = get_string('forceno');
            $languages += get_string_manager()->get_list_of_translations();
            $mform->addElement('select', 'lang', get_string('forcelanguage'), $languages);
            $mform->setDefault('lang', $courseconfig->lang);
        }
        // END UCLA MOD: CCLE-3278
//--------------------------------------------------------------------------------
        if (completion_info::is_enabled_for_site()) {
            $mform->addElement('header','', get_string('progress','completion'));
            $mform->addElement('select', 'enablecompletion', get_string('completion','completion'),
                array(0=>get_string('completiondisabled','completion'), 1=>get_string('completionenabled','completion')));
            $mform->setDefault('enablecompletion', $courseconfig->enablecompletion);

            $mform->addElement('checkbox', 'completionstartonenrol', get_string('completionstartonenrol', 'completion'));
            $mform->setDefault('completionstartonenrol', $courseconfig->completionstartonenrol);
            $mform->disabledIf('completionstartonenrol', 'enablecompletion', 'eq', 0);
        } else {
            $mform->addElement('hidden', 'enablecompletion');
            $mform->setType('enablecompletion', PARAM_INT);
            $mform->setDefault('enablecompletion',0);

            $mform->addElement('hidden', 'completionstartonenrol');
            $mform->setType('completionstartonenrol', PARAM_INT);
            $mform->setDefault('completionstartonenrol',0);
        }

/// customizable role names in this course
//--------------------------------------------------------------------------------
        $mform->addElement('header','rolerenaming', get_string('rolerenaming'));
        $mform->addHelpButton('rolerenaming', 'rolerenaming');

        if ($roles = get_all_roles()) {
            if ($coursecontext) {
                $roles = role_fix_names($roles, $coursecontext, ROLENAME_ALIAS_RAW);
            }
            $assignableroles = get_roles_for_contextlevels(CONTEXT_COURSE);
            foreach ($roles as $role) {
                $mform->addElement('text', 'role_'.$role->id, get_string('yourwordforx', '', $role->name));
                if (isset($role->localname)) {
                    $mform->setDefault('role_'.$role->id, $role->localname);
                }
                $mform->setType('role_'.$role->id, PARAM_TEXT);
               //BEGIN UCLA MOD: CCLE-2939- Suppress or Restrict Role Renaming
               //Place all role renaming under "Advanced" button so only people who know how to rename roles will use it
                $mform->setAdvanced('role_'.$role->id);
            }
        }
               //END UCLA MOD: CCLE-2939
//--------------------------------------------------------------------------------
        $this->add_action_buttons();
//--------------------------------------------------------------------------------
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);

/// finally set the current form data
//--------------------------------------------------------------------------------
        $this->set_data($course);
    }

    function definition_after_data() {
        global $DB;

        $mform = $this->_form;

        // add available groupings
        if ($courseid = $mform->getElementValue('id') and $mform->elementExists('defaultgroupingid')) {
            $options = array();
            if ($groupings = $DB->get_records('groupings', array('courseid'=>$courseid))) {
                foreach ($groupings as $grouping) {
                    $options[$grouping->id] = format_string($grouping->name);
                }
            }
            $gr_el =& $mform->getElement('defaultgroupingid');
            $gr_el->load($options);
        }
    }


/// perform some extra moodle validation
    function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        if ($foundcourses = $DB->get_records('course', array('shortname'=>$data['shortname']))) {
            if (!empty($data['id'])) {
                unset($foundcourses[$data['id']]);
            }
            if (!empty($foundcourses)) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }
                $foundcoursenamestring = implode(',', $foundcoursenames);
                $errors['shortname']= get_string('shortnametaken', '', $foundcoursenamestring);
            }
        }

        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));

        return $errors;
    }
}

