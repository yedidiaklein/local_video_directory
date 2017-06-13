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
 * Easy way to download videos from url.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('init.php');
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('wget', 'local_video_directory'));
$PAGE->set_title(get_string('wget', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/wget.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('wget', 'local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');


class simplehtml_form extends moodleform {
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        $mform->addElement('text', 'url', get_string('wget', 'local_video_directory'));
        $mform->setType('url', PARAM_URL);
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    function validation($data, $files) {
        return array();
    }
}

$mform = new simplehtml_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
    $record = array("url" => $fromform->url, "owner_id" => $USER->id );
    $update = $DB->insert_record("local_video_directory_wget", $record);
    redirect($CFG->wwwroot . '/local/video_directory/mass.php');
} else {
    echo $OUTPUT->header();
    include_once('menu.php');
    echo get_string('url_download', 'local_video_directory').'<br>';
    $mform->display();
}

echo $OUTPUT->footer();