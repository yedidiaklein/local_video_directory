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
 * Crop video by choosing new rectangle.
 *
 * @package    local_video_directory
 * @copyright  2019 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_login();
require_once('locallib.php');
defined('MOODLE_INTERNAL') || die();

$settings = get_settings();

if (!CLI_SCRIPT) {
    require_login();

    // Check if user have permissionss.
    $context = context_system::instance();

    if (!has_capability('local/video_directory:video', $context) && !is_video_admin($USER)) {
        die("Access Denied. Please see your site admin.");
    }

}

require_once("$CFG->libdir/formslib.php");
$streamingurl = get_settings()->streaming;
$id = optional_param('video_id', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('crop', 'local_video_directory'));
$PAGE->set_title(get_string('crop', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/studio_crop.php?video_id=' . $id);
$PAGE->set_pagelayout('standard');

$PAGE->requires->css('/local/video_directory/style.css');

$PAGE->requires->js('/local/video_directory/js/crop.js');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('studio', 'local_video_directory'), new moodle_url('/local/video_directory/studio.php?video_id=' . $id));
$PAGE->navbar->add(get_string('crop', 'local_video_directory'));

class crop_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $USER;

        $id = optional_param('video_id', 0, PARAM_INT);

/*        if ($id != 0) {
            $video = $DB->get_record('local_video_directory', array("id" => $id));
        } else {
            $origfilename = "";
            $owner = 0;
        }
        */
        $mform = $this->_form;

        
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('html', '<div id="rectangleData"></div><br><br>');


        $mform->addElement('select', 'save', get_string('save', 'moodle'),
            [ 'version' => get_string('newversion', 'local_video_directory'), 
              'new' => get_string('newvideo', 'local_video_directory')
            ]);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new crop_form();

$width = 640; // default width, very important for crop calculation

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {

    // Check that user has rights to edit this video.
    local_video_edit_right($fromform->id);
    $video = $DB->get_record('local_video_directory', array("id" => $fromform->id));
    $ratio = $video->width / $width;
    $now = time();
    $record = array("video_id" => $fromform->id,
                    "user_id" => $USER->id,
                    "timecreated" => $now,
                    "timemodified" => $now,
                    "state" => 0,
                    "save" => $fromform->save,
                    "startx" => ($_POST['StartX'] * $ratio),
                    "starty" => ($_POST['StartY'] * $ratio),
                    "endx" => ($_POST['EndX'] * $ratio),
                    "endy" => ($_POST['EndY'] * $ratio));
    $insert = $DB->insert_record("local_video_directory_crop", $record);
    redirect($CFG->wwwroot . '/local/video_directory/studio.php?video_id=' . $fromform->id,
                get_string('inqueue', 'local_video_directory'));
} else {
    echo $OUTPUT->header();

    $video = $DB->get_record('local_video_directory', array("id" => $id));

    $height = $width / ($video->width / $video->height);

    if ($streaming = get_streaming_server_url()) {
        $url = $streaming . "/" . $id . ".mp4";
    } else {
        $url = $CFG->wwwroot . "/local/video_directory/play.php?video_id=" . $id;
    }

    echo $OUTPUT->render_from_template('local_video_directory/studio_crop',
    ['wwwroot' => $CFG->wwwroot, 
     'url' => $url,
     'id' => $id, 
     'thumb' => str_replace("-", "&second=", $video->thumb), 
     'height' => $height, 
     'width' => $width]);


    $mform->display();
}

echo $OUTPUT->footer();