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

require_once('init.php');
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('upload','local_video_directory'));
$PAGE->set_title(get_string('upload','local_video_directory'));
$PAGE->set_url('/local/video_directory/upload.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname','local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('upload','local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');

class simplehtml_form extends moodleform {
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $mform->addElement('checkbox', 'private', get_string('private', 'local_video_directory'));
        $mform->setDefault('private','checked');

        $mform->addElement('filepicker', 'userfile', get_string('file'), null, array('maxbytes' => 1000000000, 'accepted_types' => '*'));
        
        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('cancel'));
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
      $name = $mform->get_new_filename('userfile');
    
    $record = array('orig_filename' => $name , 'owner_id' => $USER->id);
    if ((isset($fromform->private)) && ($fromform->private)) {
        $record['private'] = 1;
    }

    $lastinsertid = $DB->insert_record('local_video_directory', $record);
    $success = $mform->save_file('userfile', $uploaddir.$lastinsertid);
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    echo $OUTPUT->header();
          
    require 'menu.php';
    $mform->display();
}

echo $OUTPUT->footer();
