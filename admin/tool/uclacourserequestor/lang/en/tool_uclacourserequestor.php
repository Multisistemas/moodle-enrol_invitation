<?php
$string['pluginname'] = "UCLA course requestor";
$string['uclacourserequestor'] = $string['pluginname'];
$string['courserequestor:view'] = "View " . $string['pluginname'];

$string['srslookup'] = "SRS number lookup (Registrar)";

// Fetch from Registrar
$string['fetch'] = 'Get courses from Registrar';
$string['buildcourse'] = "Get course";
$string['builddept'] = "Get department courses";

$string['views'] = 'View existing requests';
$string['viewcourses'] = "View/Edit existing requests";

// Status readable 
$string['build'] = "To be built";
$string['failed'] = "Failed creator";
$string['live'] = "Live";

$string['delete'] = 'Delete';

// This string should be rarely used
$string['noviewcourses'] = "There are no existing requests.";

$string['crosslistnotice'] = "You can add crosslists while these couses are waiting in queue to be built.";

$string['error'] = 'Some of the courses that you have requested have problems with them. Please look over them and if needed, submit your requests again.';

$string['all_department'] = 'All departments';
$string['all_term'] = 'All terms';
$string['all_action'] = 'All statuses';

$string['noinst'] = 'Not Assigned';

$string['newrequestid'] = 'New entry';
$string['newrequestcourseid'] = 'Not built yet';

$string['checkchanges'] = 'Validate requests without saving changes';
$string['submitfetch'] = 'Submit requests';
$string['submitviews'] = 'Save changes';

$string['norequestsfound'] = 'No courses found for given request.';

// Table headers for the requests
$string['id'] = 'Request ID';
$string['courseid'] = 'Associated Course ID';
$string['term'] = 'Term';
$string['srs'] = 'SRS';
$string['course'] = 'Course';
$string['department'] = 'Department';
$string['instructor'] = 'Instructors';
$string['requestoremail'] = 'Requestor email';
$string['crosslist'] = 'Crosslist?';
$string['timerequested'] = 'Time requested';
$string['action'] = 'Status';
$string['status'] = 'Condition';
$string['mailinst'] = 'E-Mail instructor';
$string['hidden'] = 'Course built hidden from students';
$string['nourlupdate'] = 'Do NOT send URL to MyUCLA';
$string['crosslists'] = 'Crosslisted SRSes';

$string['deletefetch'] = 'Ignore';
$string['deleteviews'] = 'Remove request';

$string['addmorecrosslist'] = 'Add another entry';

// Crosslisting errors
$string['illegalcrosslist'] = 'Another course has this SRS as a crosslist';
$string['hostandchild'] = 'This course is a crosslist of another course. Please explicitly disable the building of one of the courses.';
$string['srserror'] = 'The SRS number must be exactly 9 digits long';

$string['queuetobebuilt'] = "Courses in queue to be built";
$string['queueempty'] = "The queue is empty. All courses have been built as of now.";

$string['alreadysubmitted'] = "This SRS number has already been submitted as a request. ";
$string['checktermsrs'] = "Cannot find course. Please check the term and SRS again.";
$string['childcourse'] =  " has either been submitted for course creation or is a child course";
$string['duplicatekeys'] = "Duplicate entry. The alias is already inserted.";
$string['checksrsdigits'] = "Please check your SRS input. It has to be a 9 digit numeric value.";
$string['submittedforcrosslist'] = "Submitted for crosslisting";
$string['newaliascrosslist'] = "New aliases submitted for crosslisting with host: ";
$string['crosslistingwith'] = " - submitted for crosslisting with ";
$string['individualorchildcourse'] = " is already submitted individually or as a child course. ";
$string['submittedtobebuilt'] = " submitted to be built ";

$string['delete_successful'] = "Deleted course entry: ";
$string['delete_error'] = "Unable to find course entry to delete: ";

