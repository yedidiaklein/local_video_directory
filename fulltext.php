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
require_login();
require_once('locallib.php');
defined('MOODLE_INTERNAL') || die();

$settings = get_settings();

if (!CLI_SCRIPT) {
    require_login();

    // Check if user have permissionss.
    $context = context_system::instance();

    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        die("Access Denied. Please see your site admin.");
    }

}

require_once("$CFG->libdir/formslib.php");
$streamingurl = get_settings()->streaming;
$id = optional_param('video_id', 0, PARAM_INT);

// TODO:
// Check that user is owner or admin!!

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('fulltext', 'local_video_directory'));
$PAGE->set_title(get_string('fulltext', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/fulltext.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('fulltext', 'local_video_directory'));

class text_form extends moodleform {
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

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('checkbox', 'textit', get_string('fulltext', 'local_video_directory'));

        $mform->addElement('select', 'lang', get_string('lang', 'editor'), 
            [ 'he_IL' => get_string('heb','iso6392'), 'en_US' => get_string('eng','iso6392'), 'fr_FR' => get_string('fra', 'iso6392') ]);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new text_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {

    // Check that user has rights to edit this video.
    local_video_edit_right($fromform->id);

    $record = array("video_id" => $fromform->id, "user_id" => $USER->id, "state" => 0, "lang" => $fromform->lang, "datecreated" => time() );
    $insert = $DB->insert_record("local_video_directory_txtq", $record);
    redirect($CFG->wwwroot . '/local/video_directory/list.php'); // TODO : add here a string that say something about speech2text
} else {
    echo $OUTPUT->header();

    $video = $DB->get_record('local_video_directory', array("id" => $id));
    echo "<h2>" . $video->orig_filename . "</h2>";
    echo local_video_get_thumbnail_url($video->thumb, $video->id);

    $state = $DB->get_records('local_video_directory_txtq', ['video_id' => $id]);
    if ($state) {
        echo "<br><table border=1><tr>
                <th>" . get_string('lang', 'editor') . "</th>
                <th>" . get_string('date') . "</th>
                <th>" . get_string('convert_status', 'local_video_directory'). "</th></tr>";
        foreach($state as $st) {
            echo "<tr>";
            echo "<td>" . $st->lang .
                 "</td><td>" . strftime("%A, %d %B %Y %H:%M", $st->datecreated) . 
                 "</td><td>" . get_string('textstate_' . $st->state, 'local_video_directory') .
                 "</td></tr>"; 
        }
        echo "</table>";
    }

    $mform->display();

    $sections = $DB->get_records("local_video_directory_txtsec", array("video_id" => $id));

    echo $OUTPUT->render_from_template('local_video_directory/fulltext',
    ['wwwroot' => $CFG->wwwroot, 'sections' => array_values($sections), 'id' => $id]);

}

echo $OUTPUT->footer();