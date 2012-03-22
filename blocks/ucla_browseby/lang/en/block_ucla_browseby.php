<?php

$string['pluginname'] = 'UCLA browse-by';
$string['displayname'] = 'Browse by:';

// Links displayed in the block
$string['link_subjarea'] = 'Subject area';
$string['link_division'] = 'Division';
$string['link_instructor'] = 'Instructor';
$string['link_collab'] = 'Collaboration sites';
$string['link_mycourses'] = 'My sites';

// This is for errors
$string['illegaltype'] = 'View for "{$a}" does not exist.';

// Subject area
$string['subjarea_title'] = 'Subject areas in {$a}';
$string['all_subjareas'] = 'All hosted subject areas'; 

// Divisions
$string['division_title'] = 'Divisions';
$string['division_none'] = 'No divisions were found on this server';
$string['division_noterm'] = 'No divisions were found for this term, please try another term.';

// Instructors
$string['instructorsall'] = 'All instructors';
$string['instructorswith'] = 'Instructors, last name starting with "{$a}"';
$string['instructosallterm'] = 'All instructors for {$a}';
$string['noinstructorsterm'] = 'There are no instructors teaching on this server for {$a}, please try another term.';
$string['noinstructors'] = 'There were no instructors found.';

// Instructors -> courses
$string['coursesbyinstr'] = 'Courses taught by {$a}';
$string['coursesinsubjarea'] = 'Courses in "{$a}"';

// Collaborations
$string['collab_notfound'] = 'No collaboration sites found.';
$string['collab_notcollab'] = 'This category is not considered a category for collaboration sites.';
$string['collab_coursesincat'] = 'Courses in this category';
$string['collab_viewall'] = 'Collaboration sites';
$string['collab_viewin'] = 'Collaboration sites in {$a}';
$string['collab_nocoursesincat'] = 'No courses were found in this cateogry';

$string['sitename'] = 'Site name';
$string['projectlead'] = 'Project lead';
$string['coursecreators'] = 'Course owner';

// Options
$string['title_division'] = 'Disable browse-by division';
$string['title_subjarea'] = 'Disable browse-by subject areas';
$string['title_instructor'] = 'Disable browse-by instructors';
$string['title_collab'] = 'Disable browse collaboration sites';
$string['title_ignore_coursenum'] = 'Minimum course number to hide';
$string['title_allow_acttypes'] = 'Activity types to allow';

$string['desc_division'] = 'Check box to disable the ability to use divisions to narrow down subject areas to look at.';
$string['desc_subjarea'] = 'Check box to disable the ability to use subject areas to narrow down courses to look at.';
$string['desc_instructor'] = 'Check box to disable the ability to see the courses an instructor is teaching.';
$string['desc_collab'] = 'Check box to disable the ability for guests to browse collaboration sites.';
$string['desc_ignore_coursenum'] = 'Courses with a course number larger than this number will be hidden from the browse-by results unless they have an associated website in the Registrar.';
$string['desc_allow_acttypes'] = 'Comma-delimited list. Courses with the activity type (i.e. LEC, SEM) specified in this list will be visible in the browse-by results. If you want all courses to be visible, leave this blank.';

$string['title_syncallterms'] = 'Sync for all terms';
$string['desc_syncallterms'] = 'Check box to enable synchronization of all terms that is available on this server. It will uncheck itself after it runs.';

$string['title_use_local_courses'] = 'Use local courses';
$string['desc_use_local_courses'] = 'Check box to allow for local courses to override the URL that has been provided by the Registrar. Otherwise, the data that the Registrar has provided will be considered infallible.';

// Courses view
$string['nousersinrole'] = 'N / A';
$string['session_break'] = 'Summer session {$a}';
$string['registrar_link'] = 'Registrar';

// Headers in courses view
$string['course'] = 'Course';
$string['instructors'] = 'Instructors';
$string['coursedesc'] = 'Course description';
