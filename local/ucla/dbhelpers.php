<?php

/**
 *  Class of static functions that help with commonly used mass-database
 *  functionality.
 *  Please don't explicitly include this file, include the local/ucla/lib.php
 *  instead, and then call the function ucla_require_db_helper()
 **/
class db_helper {
    /**
     * Use this to get a term-srs from a role assignment of a user.
     *  Table alias needed: user -> us
     *  Table aliases used: ra, ro, ct, co, rc
     **/
    const join_role_assignments_request_classes_sql = "
        INNER JOIN {role_assignments} ra
            ON ra.userid = us.id
        INNER JOIN {role} ro
            ON ro.id = ra.roleid
        INNER JOIN {context} ct
            ON ct.id = ra.contextid
        INNER JOIN {course} co
            ON co.id = ct.instanceid
        INNER JOIN {ucla_request_classes} rc
            ON co.id = rc.courseid
    ";
        

    /** 
     *  Will check a table for entries, insert and update entries provided
     *  in the arguments.
     *  @param  $table      The table to work with
     *  @param  $tabledata  
     *      Array( Array(), ... ) to sync the table with. Should have
     *      indices specified in $syncfields.
     *  @param  $syncfields
     *      Array() of fields from table and tabledata to compare old 
     *      and new entries with.
     *  @param  $partialwhere
     *      The where statement in a get_records_select() to synchronize
     *      a smaller part of a table.
     *  @param  $partialparmas
     *      The parameters for a get_records_select() to synchronize
     *      a smaller part of a table.
     *  @return
     *      Array( 
     *          0 => Array(inserted entries), 
     *          1 => Array(updated entries), 
     *          2 => Array(deleted entries)
     *      )
     **/
    static function partial_sync_table($table, $tabledata, $syncfields,
            $partialwhere=null, $partialparams=null) {
        global $DB;

        $partial = ($partialwhere === null || $partialparams === null);

        // Optimization for delete all
        if (empty($tabledata)) {
            if ($partial) {
                $r = $DB->delete_records_select($table, 
                    $partialwhere, $partialparams);
            } else {
                // This means a full delete...
                $r = $DB->delete_records($table);
            }

            return $r;
        }

        // Get existing records to determine if we're going to insert or
        // going to update
        if ($partial) {
            $existingrecords = $DB->get_records($table);
        } else {
            $existingrecords = $DB->get_records_select($table, 
                $partialwhere, $partialparams);
        }


        // Since if it exists already we update, we're going to be
        // constantly searching through this array, so we're going to
        // speed it up by doing something they call "indexing"
        $existing_indexed = array();
        foreach ($existingrecords as $record) {
            $existing_indexed[self::dynamic_hash($record, $syncfields)]
                = $record;
        }

        $inserted = array();
        $updated = array();

        foreach ($tabledata as $data) {
            $hash = self::dynamic_hash($data, $syncfields);

            if (isset($existing_indexed[$hash])) {
                $data['id'] = $existing_indexed[$hash]->id;

                $DB->update_record($table, $data);

                $updated[$hash] = $data;
            } else {
                $id = $DB->insert_record($table, $data);
                $data['id'] = $id;

                $inserted[$hash] = $data;

                $existing_indexed[$hash] = (object) $data;
            }
        }

        // We're going to generate a set of ids of records we're going
        // to obliterate
        if (empty($existing_indexed)) {
            return;
        }

        $delete_ids = array();
        $deleted = array();
        foreach ($existing_indexed as $hash => $existing) {
            if (isset($inserted[$hash]) || isset($updated[$hash])) {
                continue;
            }

            $delete_ids[] = $existing->id;
            $deleted[] = get_object_vars($existing);
        }

        if (!empty($delete_ids)) {
            list($sqlin, $params) = $DB->get_in_or_equal($delete_ids);
            $where = 'id ' . $sqlin;

            $DB->delete_records_select($table, $where, $params);
        }

        return array($inserted, $updated, $deleted);
    }

    /**
     *  Automatically generates a non-optimized hash.
     *  @param  $data   The object/Array to hash. Needs to have
     *      fields provided in $hashfields.
     *  @param  $hashfields The fields to use as a hash.
     *  @return string That should uniquely identify the data.
     **/
    static function dynamic_hash($data, $hashfields) {
        $prehash = array();
        if (is_object($data)) {
            $datarr = get_object_vars($data);
        } else {
            $datarr = $data;
        }

        foreach ($hashfields as $field) {
            if (isset($datarr[$field])) {
                $prehash[$field] = $datarr[$field];
            } else {
                $prehash[$field] = null;
            }
        }

        return serialize($prehash);
    }



    /**
     *  Convenience function that automatically gets a bunch of stuff
     *  regarding users in courses.
     *  @param $where SQL string - does not need WHERE. 
     *      For table names: 
     *          use c for {course}
     *          use u for {user}
     *          use rcq for {ucla_request_classes}
     *  NOTE: This kind of function might already exist in moodle somewhere...
     **/
    static function get_users_select($where='', $params=null, $groupby='') {
        global $DB;

        // Massive SQL statement because "we know how things work down there"
        // This basically gets the users in a course
        $sql = "
        SELECT
            CONCAT(c.id, '-', u.id, '-', r.id) AS rsid,
            c.id AS course_id,
            c.shortname,
            c.fullname,
            u.id AS user_id,
            u.firstname,
            u.lastname,
            u.email,
            u.address,
            u.maildisplay,
            u.url,
            r.id AS role_id,
            r.shortname AS role_shortname,
            rcq.srs,
            rcq.term
        FROM {user} u
        INNER JOIN {role_assignments} ra
            ON ra.userid = u.id
        INNER JOIN {role} r
            ON r.id = ra.roleid
        INNER JOIN {context} x 
            ON x.id = ra.contextid
        INNER JOIN {course} c
            ON c.id = x.instanceid
        INNER JOIN {ucla_request_classes} rcq
            ON c.id = rcq.courseid
        ";

        if (!empty($where)) {
            $sql .= "
                WHERE $where
            ";
        }

        if (!empty($groupby)) {
            $sql .= "
                GROUP BY $groupby
            ";
        }

        $results = $DB->get_records_sql($sql, $params);

        return $results;
    }
}
