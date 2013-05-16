<?php
/**
 * Strings
 *
 * @package    report
 * @subpackage uclastats
 * @copyright  UC Regents
 */

$string['pluginname'] = 'UCLA stats console';
$string['uclastats:view'] = 'View UCLA stats console cached queries';
$string['uclastats:query'] = 'Run UCLA stats console queries';
$string['uclastats:manage'] = 'Manage UCLA stats console cached queries (delete or lock results)';

$string['parameters'] = 'Parameters: {$a}';
$string['lastran'] = 'Last ran by {$a->who} on {$a->when}';

// report strings
$string['index_welcome'] = 'Please select a report.';
$string['report_list'] = 'Report list';
$string['run_report'] = 'Run report';
$string['warning_high_load'] = 'WARNING: Report may take a long time to run. ' .
        'Please run new reports during off peak hours. Viewing cached results is fine.';
$string['export_options'] = 'Export: ';

// parameter strings
$string['noparams'] = 'No additional parameters needed to run report';
$string['term'] = 'Term';
$string['subjarea'] = 'Subject area';

// cached results strings
$string['cached_results_table'] = 'Cached results';
$string['header_param'] = 'Parameters';
$string['header_results'] = 'Results';
$string['header_lastran'] = 'Last ran';
$string['header_actions'] = 'Actions';
$string['view_results'] = 'View results';
$string['lock_results'] = 'Lock';
$string['locked_results'] = 'Locked';
$string['unlock_results'] = 'Unlock';
$string['delete_results'] = 'Delete';
$string['successful_delete'] = 'Successfully deleted result';
$string['successful_unlock'] = 'Successfully unlocked result';
$string['successful_lock'] = 'Successfully locked result';
$string['error_delete_locked'] = 'Cannot delete locked results';
$string['undefined_action'] = 'The requested action , {$a} , is undefined';
$string['confirm_delete'] = 'Are you sure you want to delete the result?';

// strings for sites_per_term
$string['sites_per_term'] = 'Sites per term (course)';
$string['sites_per_term_help'] = 'Returns number of Registrar course sites built for a given term.';
$string['site_count'] = 'Site count';

// strings for course_modules_used
$string['course_modules_used'] = 'Activity/Resource modules (course)';
$string['course_modules_used_help'] = 'Returns name and number of course modules used by courses sites for a given term.';
$string['module'] = 'Activity/Resource module';
$string['count'] = 'Count';

// strings for collab_modules_used
$string['collab_modules_used'] = 'Activity/Resource modules (collab)';
$string['collab_modules_used_help'] = 'Returns name and number of collab modules used by collab sites. Excludes "test" sites.';

// strings for unique_logins_per_term
$string['unique_logins_per_term'] = 'Unique logins per term (system)';
$string['unique_logins_per_term_help'] = 'Counts the average number of unique ' .
        'logins per day and week for a given term. Then gives the total unique ' .
        'logins for the term. Uses the term start and end date to calculate results';
$string['per_day'] = 'Per day';
$string['per_week'] = 'Per week';
$string['per_term'] = 'Per term';
$string['start_end_times'] = 'Start/End';
$string['unique_logins_per_term_cached_results'] = 'Per day: {$a->day} | Per week: {$a->week} | Per term: {$a->term}';

// strings for subject_area_report
$string['subject_area_report'] = 'Subject area report (course)';
$string['subject_area_report_help'] = 'Report that generates a collection of useful statistics that 
    departments can use. Some statistical statistics include, number of enrolled students, 
    class site hits, and forum activity. Was originally requested by Psychology in CCLE-2673.';
$string['course_id'] = 'Course ID';
$string['course_title'] = 'Course';
$string['course_students'] = 'Enrolled students';
$string['course_instructors'] = 'Instructors (role)';
$string['course_forums'] = 'Forum topics'; 
$string['course_posts'] = 'Forum posts';
$string['course_hits'] = 'Total student views';
$string['course_student_percent'] = 'Students visiting site';
$string['course_files'] = 'Resource files';
$string['course_size'] = 'Resource file size (MB)';
$string['course_syllabus'] = 'Syllabus';

//strings for file_size_report
$string['file_size'] = 'File size (system)';
$string['file_size_help'] = 'Returns the number of files over 1 MB.';
$string['file_count'] = 'File count';

//strings for inactive_collab_sites
$string['inactive_collab_sites'] = 'Inactive sites (collab)';
$string['inactive_collab_sites_help'] = 'Returns number of inactive collab sites. Inactivity is based on if a site has not had a single page view for 6 months. Does not count guest user access. Includes test sites.';

//strings for inactive_course_sites
$string['inactive_course_sites'] = 'Inactive sites (course)';
$string['inactive_course_sites_help'] = 'Reports number of inactive course sites. ' .
        'Inactivity is based on if a course has not had any log hits 1 week after ' .
        'the start of the term. Handles the different starting times for summer ' .
        'sessions. Does not count guest user access.';
$string['division'] = 'Division';

//strings for role_count
$string['role_count'] = 'Role count (course)';
$string['role_count_help'] = 'Returns the total for each role for all courses for a given term';
$string['role'] = 'Role';

//string for course_block_sites
$string['course_block_sites'] = 'Blocks (course)';
$string['course_block_sites_help'] = 'Returns name and number of blocks used by course sites for a given term.';
$string['blockname'] = 'Block name';

//string for collab_block_sites
$string['collab_block_sites'] = 'Blocks (collab)';
$string['collab_block_sites_help'] = 'Returns the name and number of blocks used by collab sites.';

//strings for custom_theme_report
$string['custom_theme'] = 'Custom theme report (system)';
$string['custom_theme_help'] = 'Displays sites that are using a custom theme.';
$string['theme_count'] = 'Number of sites using custom theme: ';
$string['course_shortname'] = 'Course';
$string['course_title'] = 'Course title';
$string['theme'] = 'Theme';

//strings for repository usage report
$string['repository_usage'] = 'Repository usage (system)';
$string['repository_usage_help'] = 'Find repository usage for: Dropbox, Google, Box, Server files, and My CCLE files.';
$string['repo_name'] = 'Repository';
$string['repo_count'] = 'File count';

//strings for large courses report
$string['large_courses'] = 'Large sites (course)';
$string['large_courses_help'] = 'For a given term, list all the courses over {$a}.';
$string['other'] = 'Other';
$string['video'] = 'Video';
$string['audio'] = 'Audio';
$string['image'] = 'Image';
$string['web_file'] = 'Web file';
$string['spreadsheet'] = 'Spreadsheet';
$string['document'] = 'Document';
$string['archive'] = 'Archive';
$string['presentation'] = 'Presentation';

//strings for large collab sites report
$string['large_collab_sites'] = 'Large sites (collab)';
$string['large_collab_sites_help'] = 'List all the collaboration sites over {$a}.';

//strings for final quiz report
$string['final_quiz_report'] = 'Final quiz report (course)';
$string['final_quiz_report_help'] = 'Displays the number of quizzes taken during finals week by division.';

// error strings
$string['nocachedresults'] = 'No cached results found';
$string['invalidterm'] = 'Invalid term';
$string['invalidreport'] = 'Invalid report';
$string['resultnotbelongtoreport'] = 'Requested result does not belong to current report';

// strings for unit testing
$string['param1'] = 'Parameter 1';
$string['param2'] = 'Parameter 2';
$string['result1'] = 'Result 1';
$string['result2'] = 'Result 2';
$string['uclastats_base_mock'] = 'UCLA stats base class';
$string['uclastats_base_mock_help'] = 'Text explaining what report does.';