<?php

function xmldb_local_video_directory_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017040403) {

        // Define table local_video_multi to be created.
        $table = new xmldb_table('local_video_directory_multi');

        // Adding fields to table local_video_directory_multi.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('video_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('width', XMLDB_TYPE_INTEGER, '15', null, null, null, null);
        $table->add_field('height', XMLDB_TYPE_INTEGER, '15', null, null, null, null);
        $table->add_field('size', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('filename', XMLDB_TYPE_CHAR, '200', null, XMLDB_NOTNULL, null, null);
        $table->add_field('datecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('datemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_video_directory_multi
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_video_directory_multi.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_video_directory');
        $field = new xmldb_field('height', XMLDB_TYPE_INTEGER, '13', null, null, null, null, 'length');
        // Conditionally launch add field height.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('width', XMLDB_TYPE_INTEGER, '13', null, null, null, null, 'length');
        // Conditionally launch add field height.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('size', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'length');
        // Conditionally launch add field height.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'length');
        // Conditionally launch add field height.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'length');
        // Conditionally launch add field height.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }



        // Video_directory savepoint reached.
        upgrade_plugin_savepoint(true, 2017040403, 'local', 'video_directory');
   	}


}

	

