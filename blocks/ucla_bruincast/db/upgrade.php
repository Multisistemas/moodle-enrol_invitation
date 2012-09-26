<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_block_ucla_bruincast_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    $result = true;

    // YYYYMMDDVV
    if ($result && $oldversion < 2012011015) {
        $table = new xmldb_table('ucla_bruincast');
        
        $index = new xmldb_index('term', XMLDB_INDEX_NOTUNIQUE, array('term'));

        // Conditionally launch add index term
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('srs', XMLDB_INDEX_NOTUNIQUE, array('srs'));

        // Conditionally launch add index srs
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        
        // bruincast savepoint reached
        upgrade_block_savepoint(true, 2012011015, 'ucla_bruincast');        
    }

    // YYYYMMDDVV
    if ($result && $oldversion < 2012011016) {
        $table = new xmldb_table('ucla_bruincast');
        
        $index = new xmldb_index('term', XMLDB_INDEX_NOTUNIQUE, array('term'));

        // Conditionally launch drop index term
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        
        $index = new xmldb_index('srs', XMLDB_INDEX_NOTUNIQUE, array('srs'));

        // Conditionally launch drop index srs
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        $index = new xmldb_index('term_srs', XMLDB_INDEX_NOTUNIQUE, array('term', 'srs'));

        // Conditionally launch add index term_srs
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        
        // bruincast savepoint reached
        upgrade_block_savepoint(true, 2012011016, 'ucla_bruincast');        
    }
    
    if ($oldversion < 2012091100) {

        // Define field courseid to be added to ucla_bruincast
        $table = new xmldb_table('ucla_bruincast');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'id');

        // Conditionally launch add field courseid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // bruincast savepoint reached
        upgrade_block_savepoint(true, 2012091100, 'ucla_bruincast');
    }
    
    return $result;
}