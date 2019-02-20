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
 * Restore video from previous version.
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
$settings = get_settings();

if (!CLI_SCRIPT) {
    require_login();


    // Check if user have permissionss.
    $context = context_system::instance();

    if (!has_capability('local/video_directory:video', $context) && !is_video_admin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }

}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('restore'));
$PAGE->set_title(get_string('restore'));
$PAGE->set_url('/local/video_directory/restore.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('restore'));

class restore_form extends moodleform {
    public function definition() {
        global $CFG, $DB;

        $id = optional_param('id', 0, PARAM_INT);
        $restore = optional_param('restore', 0, PARAM_INT);

        if ($id != 0) {
            $video = $DB->get_record('local_video_directory', array("id" => $id));

            if ($video->convert_status < 3) {
                redirect($CFG->wwwroot . '/local/video_directory/list.php',
                        get_string('cant_upload_or_restore_while_converting', 'local_video_directory'));
            }

            $origfilename = $video->orig_filename;
        } else {
            $origfilename = "";
        }
        $mform = $this->_form;
        $mform->addElement('html', "<h2>" . get_string('restore', 'local_video_directory')
                                 . " " . $origfilename . " (ID: " . $id . ")</h2>");
        $mform->addElement('html', get_string('sure_restore', 'local_video_directory')
                                 . ": " . strftime("%A, %d %B %Y %H:%M", $restore) . "?");

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'restore', $restore);
        $mform->setType('restore', PARAM_RAW);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new restore_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
    // If user want to restore, act like new upload to same id.
    $dirs = get_directories();
    copy($dirs['converted'] . $fromform->id . "_" . $fromform->restore . ".mp4", $dirs['uploaddir'] . $fromform->id);
    $record['id'] = $fromform->id;
    $record['convert_status'] = 1;
    $DB->update_record('local_video_directory', $record);

    redirect($CFG->wwwroot . '/local/video_directory/list.php', get_string('restore_in_queue' , 'local_video_directory'));
} else {
    echo $OUTPUT->header();
    $mform->display();

}

echo $OUTPUT->footer();
