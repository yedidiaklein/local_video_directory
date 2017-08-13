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
 * Edit video details.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_once('locallib.php');
defined('MOODLE_INTERNAL') || die();

$settings = get_settings();

if (!CLI_SCRIPT) {
    require_login();

    // Check if user belong to the cohort or is admin.
    require_once($CFG->dirroot.'/cohort/lib.php');

    if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }
}

require_once("$CFG->libdir/formslib.php");
$streamingurl = get_settings()->streaming;
$id = optional_param('video_id', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('edit', 'local_video_directory'));
$PAGE->set_title(get_string('edit', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/edit.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('edit', 'local_video_directory'));

class edit_form extends moodleform {
    public function definition() {
        global $CFG, $DB;

        $id = optional_param('video_id', 0, PARAM_INT);

        if ($id != 0) {
            $video = $DB->get_record('local_video_directory', array("id" => $id));
            $origfilename = $video->orig_filename;
        } else {
            $origfilename = "";
        }
        $mform = $this->_form;

        $mform->addElement('text', 'origfilename', get_string('filename', 'local_video_directory'));
        $mform->setType('origfilename', PARAM_RAW);
        $mform->setDefault('origfilename', $origfilename ); // Default value.

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('tags', 'tags', get_string('tags'),
                    array('itemtype' => 'local_video_directory', 'component' => 'local_video_directory'));
        if ($id != 0) {
            $data = $DB->get_record('local_video_directory', array('id' => $id));
            $data->tags = core_tag_tag::get_item_tags_array('local_video_directory', 'local_video_directory', $id);
            $mform->setDefault('tags', $data->tags);
        }

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new edit_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {

    // Check that user has rights to edit this video.
    require('locallib.php');
    local_video_edit_right($fromform->id);

    $record = array("id" => $fromform->id, "orig_filename" => $fromform->origfilename );
    $update = $DB->update_record("local_video_directory", $record);
    $context = context_system::instance();
    core_tag_tag::set_item_tags('local_video_directory', 'local_video_directory', $fromform->id, $context, $fromform->tags);
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    echo $OUTPUT->header();

    $video = $DB->get_record('local_video_directory', array("id" => $id));
    echo '<video  width="655" controls preload="auto"
            poster="' . $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . str_replace("-", "&second=", $video->thumb) . '">
            <source src="play.php?video_id='. $id . '" type="video/mp4"">
          </video>';
    $mform->display();
    echo "<a href=upload.php?video_id=" . $id . ">" . get_string('upload_new_version', 'local_video_directory') . "</a><br>";
    $versions = $DB->get_records("local_video_directory_vers", array("file_id" => $id));
    if ($versions) {
        echo "<a href=versions.php?id=" . $id . ">" . get_string('versions', 'local_video_directory') . "</a>";
    }
}

echo $OUTPUT->footer();