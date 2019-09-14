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
 * Concatenate videos.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');
require_once("$CFG->libdir/formslib.php");

$id = optional_param('video_id', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('studio', 'local_video_directory'));
$PAGE->set_title(get_string('studio', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/studio_cat.php?video_id=' . $id);
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('studio', 'local_video_directory'),
        new moodle_url('/local/video_directory/studio.php?video_id=' . $id));
$PAGE->navbar->add(get_string('merge', 'local_video_directory'));

class simplehtml_form extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form; // Don't forget the underscore!

        $videos = $DB->get_records('local_video_directory', array());
        foreach ($videos as $video) {
            $names[$video->id] = $video->orig_filename." (".$video->id.")";
        }

        $id = optional_param('video_id', 0, PARAM_INT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('select', 'first', get_string('first', 'local_video_directory'), $names);
        $mform->setDefault('first', $id);

        $mform->addElement('select', 'second', get_string('second', 'local_video_directory'), $names);
        $mform->setDefault('second', $id);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    // Custom validation should be added here.
    public function validation($data, $files) {
        return array();
    }
}

// Instantiate simplehtml_form.
$mform = new simplehtml_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect($CFG->wwwroot . '/local/video_directory');
} else if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $now = time();
    $record = array(    "video_id" => $fromform->first,
                        "user_id" => $USER->id,
                        "state" => 0,
                        "video_id_cat" => $fromform->second,
                        "datecreated" => $now,
                        "datemodified" => $now,
                    );

    $id = $DB->insert_record("local_video_directory_cat", $record);

    redirect($CFG->wwwroot . '/local/video_directory/studio.php?video_id=' . $fromform->first,
                get_string('inqueue', 'local_video_directory'));
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    // Set default data (if any)
    // Displays the form.
    echo $OUTPUT->header();
    $mform->display();
}

echo $OUTPUT->footer();
