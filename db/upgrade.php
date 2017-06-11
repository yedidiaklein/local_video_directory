<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 defined('MOODLE_INTERNAL') || die();

function xmldb_local_video_directory_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017050400) {
        $table = new xmldb_table('local_video_directory');
        $field = new xmldb_field('subs', XMLDB_TYPE_INTEGER, '1', 0, XMLDB_NOTNULL, null, 0, 'length');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Video_directory savepoint reached.
        upgrade_plugin_savepoint(true, 2017050400, 'local', 'video_directory');
    }

    if ($oldversion < 2017043005) {
        $table = new xmldb_table('local_video_directory');
        $field = new xmldb_field('views', XMLDB_TYPE_INTEGER, '13', 0, XMLDB_NOTNULL, null, 0, 'length');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Video_directory savepoint reached.
        upgrade_plugin_savepoint(true, 2017043005, 'local', 'video_directory');
    }

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

        // Adding keys to table local_video_directory_multi.
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
    return 1;
}