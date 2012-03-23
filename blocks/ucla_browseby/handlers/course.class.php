<?php

class course_handler extends browseby_handler {
    const browseall_sql_helper =  "
        SELECT 
        CONCAT(ubc.term, '-', ubc.srs, '-', ubci.uid) AS 'recordsetid',
        ubc.section AS 'sectnum',
        ubc.course AS 'coursenum',
        ubc.activitytype,
        ubc.subjarea as 'subj_area',
        ubc.url,
        ubc.term,
        ubc.srs,
        ubc.ses_grp_cd AS session_group,
        ubc.session AS session_code,
        ubc.coursetitlelong AS course_title,
        ubc.sectiontitle AS section_title,
        ubc.sect_enrl_stat_cd AS enrolstat,
        ubc.catlg_no AS course_code,
        ubc.activitytype, 
        ubci.uid,
        COALESCE(user.firstname, ubci.firstname) AS firstname,
        COALESCE(user.lastname, ubci.lastname) AS lastname,
        ubci.profcode,
        user.url AS userlink,
        urc.courseid
    ";

    function get_params() {
        // This uses division in breadcrumbs
        return array('subjarea', 'uid', 'division', 'alpha');
    }

    function handle($args) {
        global $OUTPUT, $PAGE;

        $subjarea = null;
        $instructor = null;

        $t = false;
        $s = '';

        $terms_select_where = '';
        $terms_select_param = null;

        // This is the parameters for one of the two possible query
        // types in this function...
        $params = array();

        $fullcourselist = array();

        if (isset($args['term'])) {
            $term = $args['term'];
            $termwhere = ' AND ubc.term = :term ';
            $param['term'] = $args['term'];
        } else {
            $termwhere = '';
        }

        $issummer = false;
        if (is_summer_term($term)) {
            $issummer = true;
        }

        if (isset($args['subjarea'])) {
            $subjarea = $args['subjarea'];

            // These are saved for wayyy later
            $terms_select_where = 'subj_area = ?';
            $terms_select_param = array($subjarea);

            $subjareapretty = to_display_case(
                $this->get_pretty_subjarea($subjarea));

            $t = get_string('coursesinsubjarea', 'block_ucla_browseby',
                $subjareapretty);

            // Get all courses in this subject area but from 
            // our browseall tables
            $sql = self::browseall_sql_helper . "
                FROM {ucla_browseall_classinfo} ubc
                INNER JOIN {ucla_browseall_instrinfo} ubci
                    USING(term, srs)
                LEFT JOIN {ucla_request_classes} urc
                    USING(term, srs)
                LEFT JOIN {user} user
                    ON ubci.uid = user.idnumber
                WHERE ubc.subjarea = :subjarea
                $termwhere
            ";

            $param['subjarea'] = $subjarea;

            $courseslist = $this->get_records_sql($sql, $param);

            // We came here from subjarea, so add some stuff
            if (!empty($args['division'])) {
                // Add the generic division thing
                subjarea_handler::alter_navbar();

                // This is from subjarea_handler, but I cannot
                // figure out how to generalize  and reuse
                // Display the specific division's subjareas link
                $navbarstr = get_string('subjarea_title', 
                    'block_ucla_browseby', $args['division']);
            } else {
                // Came from all subjareas
                $navbarstr = get_string('all_subjareas',
                    'block_ucla_browseby');
            }

            $urlobj = clone($PAGE->url);
            $urlobj->remove_params('subjarea');
            $urlobj->params(array('type' => 'subjarea'));
            $PAGE->navbar->add($navbarstr, $urlobj);
        } else if (isset($args['uid'])) {
            ucla_require_db_helper();

            // This is the local-system specific instructor's courses view
            $instructor = $args['uid'];

            $sqlhelp = instructor_handler::combined_select_sql_helper();
            
            $sql = self::browseall_sql_helper . "
                FROM $sqlhelp ubi
                LEFT JOIN {ucla_browseall_classinfo} ubc
                    USING(term, srs)
                LEFT JOIN $sqlhelp ubci
                    USING(term, srs)
                LEFT JOIN {ucla_request_classes} urc
                    USING(term, srs)
                LEFT JOIN {user} user
                    ON ubci.uid = user.idnumber
                WHERE 
                    ubi.uid = :uid
            ";

            $param['uid'] = $instructor;

            $courseslist = $this->get_records_sql($sql, $param);

            // hack to hide some terms
            // Also, we're going to get the actual user information
            $instruser = false;
            $terms_avail = array();
            foreach ($courseslist as $course) {
                $tt = $course->term;
                $terms_avail[$tt] = $tt;

                if ($instruser == false && $course->uid == $instructor) {
                    $instruser = $course;
                }
            }

            if (!$instruser) {
                print_error('noinstructorfound');
            } else {
                // Get stuff...
                $instruser->firstname = 
                    ucla_format_name($instruser->firstname);
                $instruser->lastname = ucla_format_name($instruser->lastname);

                $t = get_string('coursesbyinstr', 'block_ucla_browseby',
                    fullname($instruser));
            }

            list($terms_select_where, $terms_select_param) =
                $this->render_terms_restricted_helper($terms_avail);

            if (!empty($args['alpha'])) {
                instructor_handler::alter_navbar();

                // This is from subjarea_handler, but I cannot
                // figure out how to generalize  and reuse
                // Display the specific division's subjareas link
                $navbarstr = get_string('instructorswith', 
                    'block_ucla_browseby', strtoupper($args['alpha']));
            } else {
                // Came from all subjareas
                $navbarstr = get_string('instructorsall',
                    'block_ucla_browseby');
            }

            $urlobj = clone($PAGE->url);
            $urlobj->remove_params('uid');
            $urlobj->params(array('type' => 'instructor'));
            $PAGE->navbar->add($navbarstr, $urlobj);
        } else {
            // There is no way to know what we are looking at
            return array(false, false);
        }

        $use_local_courses = $this->get_config('use_local_courses');

        // Takes a denormalized Array of course-instructors and
        // returns a set of courses into $fullcourseslist
        $fullcourseslist = array();
        foreach ($courseslist as $course) {
            $k = make_idnumber($course);

            // Apend instructors, since they could have duplicate rows
            if (isset($fullcourseslist[$k])) {
                $courseobj = $fullcourseslist[$k];
                $courseobj->instructors[$course->uid] = 
                    $this->fullname($course);
            } else {
                $courseobj = new stdclass(); 
                $courseobj->dispname 
                    = ucla_make_course_title(get_object_vars($course));

                if ($use_local_courses && !empty($course->courseid)) {
                    $course->id = $course->courseid;
                    $courseobj->url = 
                        uclacoursecreator::build_course_url($course);
                } else if (!empty($course->url)) {
                    $courseobj->url = $course->url;
                } else if (!self::ignore_course($course)) {
                    $courseobj->url = $this->registrar_url(
                        $course
                    );

                    $courseobj->nonlinkdispname = $courseobj->dispname;
                    $courseobj->dispname =  '(' . html_writer::tag(
                        'span', get_string('registrar_link', 
                            'block_ucla_browseby'),
                        array('class' => 'registrar-link')) . ')';
                } else {
                    continue;
                }

                $cancelledmess = '';
                if (enrolstat_is_cancelled($course->enrolstat)) {
                    $cancelledmess = html_writer::tag('span', 
                        get_string('cancelled'), 
                        array('class' => 'ucla-cancelled-course')) . ' ';
                }

                // TODO make this function name less confusing
                $courseobj->fullname = $cancelledmess . 
                    uclacoursecreator::make_course_title(
                        $course->course_title, $course->section_title
                    );

                $courseobj->instructors = 
                    array($course->uid => $this->fullname($course));

                $courseobj->session_group = $course->session_group;
            }

            $fullcourseslist[$k] = $courseobj;
        }

        // Flatten out instructors for display
        foreach ($fullcourseslist as $k => $course) {
            $instrstr = '';
            if (!empty($course->instructors)) {
                $instrstr = implode(' / ', $course->instructors);
            }

            $course->instructors = $instrstr;
            $fullcourseslist[$k] = $course;
        }
        
        $s .= block_ucla_browseby_renderer::render_terms_selector(
            $args['term'], $terms_select_where, $terms_select_param);

        $headelements = array('course', 'instructors', 'coursedesc');
        $headelementsdisp = array();

        foreach ($headelements as $headelement) {
            $headelementsdisp[] = get_string($headelement, 
                'block_ucla_browseby');
        }

        if ($issummer) { 
            $sessionsplits = array();
            foreach ($fullcourseslist as $k => $fullcourse) {
                $session = $fullcourse->session_group;

                if (!isset($sessionsplits[$session])) {
                    $sessionsplits[$session] = array();
                }

                unset($fullcourse->session_group);

                $sessionsplits[$session][$k] = $fullcourse;
            }

            $table = new html_table();
            
            foreach ($sessionsplits as $session => $courses) {
                $sessioncell = new html_table_cell();
                $sessioncell->text = $OUTPUT->heading(get_string(
                    'session_break', 'block_ucla_browseby', $session), 3);

                $sessioncell->colspan = '3';
                $sessionrow = new html_table_row();
                $sessionrow->cells[] = $sessioncell;
                
                $subtable = block_ucla_browseby_renderer::
                    ucla_browseby_courses_list($courses);

                $table->data[] = $sessionrow;
                $table->data = array_merge($table->data, $subtable->data);
            }

            $s .= html_writer::table($table);
        } else {
            foreach ($fullcourseslist as $k => $course) {
                unset($fullcourseslist[$k]->session_group);
            }

            $table = block_ucla_browseby_renderer::ucla_browseby_courses_list(
                $fullcourseslist);

            $s .= html_writer::table($table);
        }

        return array($t, $s);
    }
    
    /** 
     *  Poorly named convenience function. Displays user information, 
     *      with a link if there is a provided 
     *
     *  URL in the user table.
     *  @param $userinfo stdClass {
     *      firstname, lastname, userlink
     *  }
     **/
    function fullname($userinfo) {
        $name = ucla_format_name(fullname($userinfo));
        if (!empty($userinfo->userlink)) {
            $userurl = $userinfo->userlink;

            if (strpos($userurl, 'http://') === false 
                    && strpos($userurl, 'https://') === false) {
                $userurl = 'http://' . $userurl;
            }

            $name = html_writer::link(new moodle_url($userurl),
                $name);
        } 

        return $name;
    }

    function registrar_url($course) {
        $page = 'http://www.registrar.ucla.edu/schedule/detselect';

        $term = $course->term;

        $issummerterm = is_summer_term($term);
        $query = '.aspx?termsel=' . $term . '&subareasel=' 
            . urlencode($course->subj_area) . '&idxcrs=' 
            . urlencode($course->course_code);

        if ($issummerterm) {
            $page .= '_summer';
            $query .= $course->session_group;
        }

        return $page . $query;
    }

    function ignore_course($course) {
        if (!empty($course->course_code)) {
            $coursecode = intval(substr($course->course_code, 0, 4));
            $ignorecoursenum = $this->get_config('ignore_coursenum');
            if ($ignorecoursenum) {
                $ignorecoursenum = trim($ignorecoursenum);

                if ($coursecode > $ignorecoursenum) {
                    return true;
                }
            }
        }

        if (!empty($course->activitytype)) {
            $allowacttypes = $this->get_config('allow_acttypes');
            if (empty($allowacttypes)) {
                return false;
            } else {
                if (is_string($allowacttypes)) {
                    $acttypes = explode(',', $allowacttypes);
                } else {
                    $acttypes = $allowacttypes;
                }

                foreach ($acttypes as $acttype) {
                    if ($course->activitytype == trim($acttype)) {
                        return false;
                    } 
                }
            }
        }

        return true;
    }

    /**
     *
     **/
    protected function get_config($name) {
        if (!isset($this->configs)) {
            $this->configs = get_config('block_ucla_browseby');
        }


        if (empty($this->configs->{$name})) {
            return false;
        }

        return $this->configs->{$name};
    }
    
    protected function get_user($userid) {
        global $DB;

        return $DB->get_record('ucla_browseall_instrinfo', 
            array('uid' => $userid));
    }
}
