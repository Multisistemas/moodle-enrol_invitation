<?php

/**
 * Block class for UCLA Copyright Status Update
 *
 * @package    ucla
 * @subpackage ucla_copyright_status
 * @copyright  2012 UC Regents    
 * @author     Jun Wan <jwan@humnet.ucla.edu>                                         
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later 
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/licenselib.php');

/*
 * Initializes all $PAGE variables.
*/
function init_copyright_page($course, $courseid, $context){
	global $PAGE;
	$PAGE->set_url('/blocks/ucla_copyright_status/view.php', 
		array('courseid' => $courseid));

	$page_title = $course->shortname.': '.get_string('pluginname',
		'block_ucla_copyright_status');

	$PAGE->set_context($context);
	$PAGE->set_title($page_title);

	$PAGE->set_heading($course->fullname);

	$PAGE->set_pagelayout('course');
	$PAGE->set_pagetype('course-view-'.$course->format);

}

/*
 * Retrive copyright information for all the files uploaded through add a resource
 * @param $courseid
 * @return filename,author and copyright information
*/
function get_files_copyright_status_by_course($courseid,$filter=null){
	global $DB;
	global $CFG;
	$sql = "SELECT f.id, f.filename, f.author, f.license, f.timemodified, f.contenthash, cm.id as cmid
         FROM {files} f
		 INNER JOIN {context} c
		 ON c.id = f.contextid
		 INNER JOIN {course_modules} cm
		 ON cm.id = c.instanceid
		 INNER JOIN {resource} r
		 ON cm.instance = r.id
		 WHERE r.course = $courseid
		 AND f.filename <> '.'";
	// include files have null value in copyright status as default status
	if ($filter && $filter == $CFG->sitedefaultlicense){
		$sql .= " AND (f.license is null or f.license = '' or f.license = '$filter')";
	}
	else if ($filter && $filter != 'all'){
		$sql .= " AND f.license = '$filter'";
	}
	$sql .= " GROUP BY f.contenthash";
	return $DB->get_records_sql($sql);
}

/*
 * Calculate files copyright status statistics.  Files with same contenthash treated as one file
 * Files have license as null will be included as Copyright status not yet identified.
 * @param $filelist, $licensetypes
 * @return statistics array with file license type as key, and number of the files of that type as value. 
 * @including total file count. File with same contenthash treated as one file.
*/

function calculate_copyright_status_statistics($filelist){
	global $CFG;
	$sum_array = array(); // array stored license type and its quantity
	$total = 0;
	foreach($filelist as $record){
		// include null license as not yet identified statistics
		$license = !empty($record->license)?$record->license:$CFG->sitedefaultlicense;
		// initialize
		$sum_array[$license] = isset($sum_array[$license])?$sum_array[$license]:0;
		$sum_array[$license]++; // calculate each type total
		$total++;
	}
	$sum_array['total'] = $total;
	return $sum_array;
}

/*
 * Return a group of file ids that have the same content hash
 * @param $fileid
 * @return array of file ids 
*/

function get_file_ids($fileid){
	global $DB;
	$res_contenthash = $DB->get_records('files', array('id'=>$fileid), null, 'id, contenthash');
	$sql_get_fileids = "SELECT f.id FROM {files} f where f.contenthash = '".$res_contenthash[$fileid]->contenthash ."'";
	return $DB->get_records_sql($sql_get_fileids); // array of file ids with the same contenthash
}

/*
 * Update copyright status for files
 * @param form post data include string with file id and license the user choose
 * @param $user
*/

function update_copyright_status($data){
	// loop through submitted data
	global $DB, $USER;
	foreach ($data as $key => $value){
		$a = explode('_', $key);
		$id = trim($a[1]);
		$value = trim($value);
		if (isset($id)){
			$id_array_with_same_contenthash = get_file_ids($id);
			// loop through all files with same contenthash
			foreach ($id_array_with_same_contenthash as $fid => $other){
				$params = array('id'=>$fid, 'license'=>$value, 'timemodified'=>time(), 'author'=>trim($USER->lastname).", ".trim($USER->firstname));
				$DB->update_record('files', $params);
			} 
		}
	}
}

/*
 * Display file list with copyright status associated with the file for a course
 * @param $courseid, $filter
 * @return array of file ids 
*/

function display_copyright_status_contents($courseid, $filter){
	global $CFG;
	global $OUTPUT;
	global $PAGE;
	
	$url = '/blocks/ucla_copyright_status/view.php';
	$PAGE->set_url($url, array('courseid' => $courseid));	// get copyright data
	$PAGE->requires->js('/theme/uclashared/javascript/jquery-1.5.2.min.js');
	$PAGE->requires->js('/blocks/ucla_copyright_status/view.js');
	$PAGE->requires->string_for_js('changes_saved', 'block_ucla_copyright_status');

	// get license types
	$licensemanager = new license_manager();
    $licenses = $licensemanager->get_licenses(array('enabled'=>1));
	$license_options = array();
	$license_options['all']='All';
	foreach($licenses as $license){
		$license_options[$license->shortname]=$license->fullname;
	}

	// start output screen
	echo html_writer::tag('h1','Copyright Status',array('class' => 'classHeader'));
	// if javascript disabled
	echo html_writer::tag('noscript',get_string('javascriptdisabled', 'block_ucla_copyright_status'),array('id'=>'block-ucla-copyright-status-noscript'));

	// display statistics 
	$all_copyrights = get_files_copyright_status_by_course($courseid);
	$stat_array = calculate_copyright_status_statistics($all_copyrights, $licenses);
	//if no files, do not calculate
	if ($stat_array['total']>0){
		echo html_writer::start_tag('div', array('id' => 'block_ucla_copyright_status_stat'));
		echo html_writer::start_tag('ul');
		foreach($license_options as $k=>$v){
			if ($k != 'all'){
				// if tbd, shown in red
				$text_style_class = 'block-ucla-copyright-status-stat-num';
				if ($k == $CFG->sitedefaultlicense){
					$text_style_class = 'block-ucla-copyright-status-stat-num-red';
				}
				$stat_count = isset($stat_array[$k])?$stat_array[$k]:0;
				echo html_writer::tag('li', $v.':'.html_writer::start_tag('span', array('class'=>$text_style_class)).'('.$stat_count.'/'.$stat_array['total'].', '.number_format($stat_count*100/$stat_array['total'],0,'','').'%)'.html_writer::end_tag('span'));
			}
		}
		echo html_writer::end_tag('ul');
		echo html_writer::end_tag('div');
	}
	// end display statistics


	echo html_writer::start_tag('div', array('id' => 'block_ucla_copyright_status_cp'));

	// display copyright filter
	echo html_writer::start_tag('form', array('id'=>'block_ucla_copyright_status_form_copyright_status_list', 'action'=>$PAGE->url->out(), 'method'=>'post'));
	echo html_writer::start_tag('div', array('id' => 'block_ucla_copyright_status_filter'));
	echo html_writer::tag('span', get_string('copyright_status', 'block_ucla_copyright_status'), array('id'=>'block_ucla_copyright_status_t1'));
	echo html_writer::select($license_options, 'filter_copyright', $filter, false, array('id'=>'block_ucla_copyright_status_id_filter_copyright'));
	$PAGE->requires->js_init_call('M.util.init_select_autosubmit', array('form_copyright_status_list', 'block_ucla_copyright_status_id_filter_copyright', ''));
	echo html_writer::end_tag('div');
	echo html_writer::end_tag('form');
	// end display copyright filter

	// display copyright status list
	unset($license_options['all']);
    $t = new html_table();
    $t->head = array('', get_string('choosecopyright', 'local_ucla'), 
		get_string('updated_dt', 'block_ucla_copyright_status'),
		get_string('author', 'block_ucla_copyright_status'));
	$course_copyright_status_list = get_files_copyright_status_by_course($courseid,$filter);
	$count = 1;
    foreach ($course_copyright_status_list as $record) {
		$select_copyright = html_writer::select($license_options, 'filecopyright_'.$record->id, $record->license);
		$t->data[] = array($count, html_writer::tag('a', $record->filename, array('href'=>$CFG->wwwroot.'/mod/resource/view.php?id='.$record->cmid)).html_writer::tag('div',$select_copyright, array('class'=>'block-ucla-copyright-status-list')), strftime("%B %d %Y %r",$record->timemodified), $record->author); 
		$count++;
	}
	echo html_writer::start_tag('div', array('id'=>'block_ucla_copyright_status_id_cp_list'));
	if (count($course_copyright_status_list)>0){
		echo html_writer::tag('div', get_string('instruction_text1', 'block_ucla_copyright_status'), array('class'=>'block-ucla-copyright-status-red-text-item'));
		echo html_writer::table($t);  
	}
	else{
		echo get_string('no_files', 'block_ucla_copyright_status');
	}
	echo html_writer::end_tag('div');
	// end display copyright status list

	echo html_writer::end_tag('div'); 
	
	// display save changes button
	if (count($course_copyright_status_list)>0){
		echo html_writer::tag('div', html_writer::empty_tag('input', array('id'=>'block_ucla_copyright_status_btn1', 'value'=>get_string('save_button','block_ucla_copyright_status'), 'type'=>'button')), array('class'=>'block-ucla-copyright-status-save-button'));
		echo html_writer::start_tag('div', array('id' => 'block_ucla_copyright_status_d1'));
		echo html_writer::end_tag('div');
	}
	// end display save changes button
	// end output screen
}


