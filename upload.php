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
 * Upload video(s).
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('init.php');
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot."/repository/lib.php");

$streamingurl = $settings->streaming;

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('upload', 'local_video_directory'));
$PAGE->set_title(get_string('upload', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/upload.php');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('upload', 'local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');
$PAGE->set_context(context_system::instance());
$context = context_user::instance($USER->id);


class simplehtml_form extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG, $DB, $context; 
        $mform = $this->_form; // Don't forget the underscore! 
        $mform->addElement('checkbox', 'private', get_string('private', 'local_video_directory'));
        $mform->setDefault('private','checked');
        $mform->addElement('filemanager', 'attachments', get_string('file', 'moodle'), null,
                    array('subdirs' => 3, 'maxfiles' => 50,
                          'accepted_types' => array('audio','video'), 'return_types'=> FILE_INTERNAL | FILE_EXTERNAL));

        if (empty($entry->id)) {
               $entry = new stdClass;
               $entry->id = null;
        }

        $draftitemid = file_get_submitted_draft_itemid('attachments');
        file_prepare_draft_area($draftitemid, $context->id, 'mod_glossary', 'attachment', $entry->id,
                        array('subdirs' => 3, 'maxfiles' => 50));

        $entry->attachments = $draftitemid;
        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
    // Custom validation should be added here.
    function validation($data, $files) {
        return array();
    }
}

// Instantiate simplehtml_form. 
$mform = new simplehtml_form();
 
// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
// In this case you process validated data. $mform->get_data() returns data posted in form.
  
    $files = $DB->get_records_select('files', "itemid = $fromform->attachments and filename <> '.'",
			null, 'contenthash, filename');
    foreach ($files as $file) {
        $record = array('orig_filename' => $file->filename, 'owner_id' => $USER->id);
        if ((isset($fromform->private)) && ($fromform->private)) {
            $record['private'] = 1;
        }
        $lastinsertid = $DB->insert_record('local_video_directory', $record);
        $path = substr($file->contenthash, 0,2) . "/" . substr($file->contenthash, 2, 2)."/";
        copy($CFG->dataroot."/filedir/".$path.$file->contenthash, $uploaddir.$lastinsertid);
    }
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    // Displays the form.
    echo $OUTPUT->header();
    // Menu.
    include('menu.php');
    $mform->display();
}

echo $OUTPUT->footer();