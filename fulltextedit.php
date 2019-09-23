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

    if (!has_capability('local/video_directory:video', $context) && !is_video_admin($USER)) {
        die("Access Denied. Please see your site admin.");
    }

}

require_once("$CFG->libdir/formslib.php");
$videoid = optional_param('videoid', 0, PARAM_INT);
$sectionid  = optional_param('sectionid', 0, PARAM_INT);

// TODO:
// Check that user is owner or admin!!

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('fulltextedit', 'local_video_directory'));
$PAGE->set_title(get_string('fulltextedit', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/fulltextedit.php?videoid=' . $videoid . '&sectionid=' . $sectionid);
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('fulltextedit', 'local_video_directory'));

class textedit_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $videoid, $sectionid;

        if ($videoid != 0) {
            $words = $DB->get_records('local_video_directory_words', ['video_id' => $videoid, 'section_id' => $sectionid]);
        }

        $mform = $this->_form;

        $mform->addElement('hidden', 'sectionid', $sectionid);
        $mform->setType('sectionid', PARAM_INT);
        $mform->addElement('hidden', 'videoid', $videoid);
        $mform->setType('videoid', PARAM_INT);

        if ($words) {
            foreach ($words as $word) {
                $mform->addElement('text', 'word_' . $word->id, $word->start . ' - ' . $word->end);
                $mform->setType('word_' . $word->id, PARAM_RAW);
                $mform->setDefault('word_' . $word->id, $word->word ); // Default value.
            }
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

$mform = new textedit_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/fulltext.php?video_id=' . $videoid);
} else if ($fromform = $mform->get_data()) {

    // Check that user has rights to edit this video.
    local_video_edit_right($fromform->videoid);

    $section = '';
    // Update words table.
    foreach ($fromform as $key => $value) {
        if (substr($key, 0, 5) == 'word_') {
            $parts = explode("_", $key);
            $record = [ 'id' => $parts[1], 'word' => $value];
            $upd = $DB->update_record('local_video_directory_words', $record);
            $section .= $value . ' ';
        }
    }

    // Update section table.
    $section = substr($section, 0, -1); // Delete last space.
    $upd = $DB->update_record('local_video_directory_txtsec', ['id' => $fromform->sectionid, 'content' => $section]);

    redirect($CFG->wwwroot . '/local/video_directory/fulltext.php?video_id=' . $videoid);
} else {
    echo $OUTPUT->header();
    $mform->display();

}

echo $OUTPUT->footer();