<?php

function xmldb_block_ucla_video_furnace_upgrade($oldversion = 0) {
    global $DB;
            
    $dbman = $DB->get_manager();
    
    // make courseid nullable, so that if it doesn't make a course on the system
    // the record is still added
    if ($oldversion < 2012062200) {
        $table = new xmldb_table('ucla_video_furnace');        
        
        // first need to remove index
        $index = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, array('courseid'));
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }        
        
        // Changing nullability of field courseid on table ucla_video_furnace to null
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'id');
        $dbman->change_field_notnull($table, $field);

        // then add back index
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }        
        
        // video_furnace savepoint reached
        upgrade_block_savepoint(true, 2012062200, 'ucla_video_furnace');
    }
   
    // drop _del_flag column that was causing errors on fresh install because of
    // invalid characters
    if ($oldversion < 2012091900) {

        // Define field _del_flag to be dropped from ucla_video_furnace
        $table = new xmldb_table('ucla_video_furnace');
        $field = new xmldb_field('_del_flag');

        // Conditionally launch drop field _del_flag
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // video_furnace savepoint reached
        upgrade_block_savepoint(true, 2012091900, 'ucla_video_furnace');
    }

    
    
    return true;
}
