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
 * Upload subtitles
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

    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }

}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('upload_subs', 'local_video_directory'));
$PAGE->set_title(get_string('upload_subs', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/upload_subs.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('upload_subs', 'local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');

class upload_subs_form extends moodleform {
    public function definition() {
        global $CFG, $DB;
        $dirs = get_directories();
        $id = required_param('id', PARAM_INT);
        $mform = $this->_form;
        $dirs = get_directories();
        if (file_exists($dirs['subsdir'].$id.".vtt")) {
            $subsize = local_video_directory_human_filesize(filesize($dirs['subsdir'].$id.".vtt"));
            $mform->addElement('html', '<div class="alert alert-info alert-block fade in">'.
                                get_string('subs_exist_in_size', 'local_video_directory').
                " ".$subsize. ' (<a href=subs.php?video_id=' . $id
                . '&download=1>Download</a> / <a href=delete_subs.php?video_id=' . $id
                . '>Delete</a>)</div>');
        } else {
            $mform->addElement('html', '<div class="alert alert-warning alert-block fade in">'.
                                get_string('no_file', 'local_video_directory').'</div>');
        }

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('filepicker', 'userfile', get_string('file'), null, array('accepted_types' => '*'));
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new upload_subs_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
    $name = $mform->get_new_filename('userfile');
    if (substr($name, -3) != "vtt") {
        if (substr($name, -3) == "srt") {
            include("srt2vtt.php");
            $dirs = get_directories();
            // Save uploaded file.
            $success = $mform->save_file('userfile', $dirs['subsdir'].$fromform->id . ".srt");
            $srt = file_get_contents($dirs['subsdir'] . $fromform->id . ".srt");
            // Convert to vtt.
            $vtt = srt2vtt($srt);
            file_put_contents($dirs['subsdir'].$fromform->id.".vtt", $vtt);
            // Delete uploaded file.
            unlink($dirs['subsdir'].$fromform->id.".srt");
        } else {
            // Should be better, strings and error on list page.
            echo "This file type is not supported";
            redirect($CFG->wwwroot . '/local/video_directory/list.php');
        }
    } else {
        $success = $mform->save_file('userfile', $dirs['subsdir'].$fromform->id.".vtt");
    }
    $record = array("id" => $fromform->id, "subs" => 1);
    $update = $DB->update_record("local_video_directory", $record);
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    echo $OUTPUT->header();
    require('menu.php');
    $mform->display();
}

echo $OUTPUT->footer();
