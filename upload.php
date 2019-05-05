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

require_once( __DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot."/repository/lib.php");

$settings = get_settings();
if (!CLI_SCRIPT) {
    require_login();

    // Check if user have permissions.
    $context = context_system::instance();

    if (!has_capability('local/video_directory:video', $context) && !is_video_admin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }

}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('upload', 'local_video_directory'));
$PAGE->set_title(get_string('upload', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/upload.php');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('upload', 'local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');
$PAGE->requires->css('/local/video_directory/styles/select2.min.css');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js_amd_inline('require([\'jquery\',\'local_video_directory/edit\'])');

// Include font awesome in case of moodle 32 and older.
if ($CFG->branch < 33) {
    $PAGE->requires->css('/local/video_directory/font_awesome/css/all.min.css');
}

$context = context_user::instance($USER->id);

class upload_form extends moodleform {
    // Add elements to form.
    public function definition() {
        global $CFG, $DB, $context, $USER, $settings;

        $id = optional_param('video_id', 0, PARAM_INT);

        $mform = $this->_form; // Don't forget the underscore!

        if ($id) {
            $video = $DB->get_record('local_video_directory', array("id" => $id));
            if ($video->convert_status < 3) {
                redirect($CFG->wwwroot . '/local/video_directory/list.php',
                        get_string('cant_upload_or_restore_while_converting', 'local_video_directory'));
            }
            $mform->addElement('html', '<h3>' . get_string('upload_new_version', 'local_video_directory')
                                . " - " . get_string('id', 'local_video_directory') . " " . $id . "</h3>");
        }

        $mform->addElement('checkbox', 'private', get_string('private', 'local_video_directory'));
        $mform->setDefault('private', 'checked');

        $mform->addElement('text', 'origfilename', get_string('orig_filename', 'local_video_directory'));
        $mform->setType('origfilename', PARAM_RAW);

        $mform->addElement('tags', 'tags', get_string('tags'),
                    array('itemtype' => 'local_video_directory', 'component' => 'local_video_directory'));
        if ($id) {
            $data->tags = core_tag_tag::get_item_tags_array('local_video_directory', 'local_video_directory', $id);
            $mform->setDefault('tags', $data->tags);
        }

        if (is_video_admin($USER)) {
            $owner[0] = $USER->firstname . " " . $USER->lastname; 
            $mform->addElement('select', 'owner', get_string('owner', 'local_video_directory'), $owner);
        }

        if ($settings->group != "none") {
            $g = local_video_get_groups($settings);

            $option = array();
            if (!is_video_admin($USER)) {
                $option = ['disabled' => true];
            }

            $select = $mform->addElement('select', 'usergroup', get_string('group', 'moodle'), $g, $option);
            if ($settings->group != "custom") {
                if (substr($settings->group,0,6) == 'local_') {
                    $local = substr($settings->group,6);
                    $select->setSelected($USER->profile[$local]);
                } else {
                    $select->setSelected($USER->{$settings->group});
                }
            }
        }


        $mform->addElement('filemanager', 'attachments', get_string('file', 'moodle'), null,
                    array('subdirs' => 3, 'maxfiles' => 50,
                          'accepted_types' => array('audio' , 'video'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL));

        if (empty($entry->id)) {
               $entry = new stdClass;
               $entry->id = null;
        }

        $draftitemid = file_get_submitted_draft_itemid('attachments');
        file_prepare_draft_area($draftitemid, $context->id, 'mod_glossary', 'attachment', $entry->id,
                        array('subdirs' => 3, 'maxfiles' => 50));

        $entry->attachments = $draftitemid;

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

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

// Instantiate upload_form.
$mform = new upload_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $dirs = get_directories();
    $files = $DB->get_records_select('files', "itemid = $fromform->attachments and filename <> '.'",
                null , 'contenthash , filename');
    $counter = 0;
    foreach ($files as $file) {
        if (isset($_POST['owner']) && ($_POST['owner'] != 0)) {
            $owner = $_POST['owner'];
        } else {
            $owner = $USER->id;
        }
        
        if ($fromform->origfilename != '') {
            $name = $fromform->origfilename;
            if ($counter != 0) {
                $name  .= "_" . $counter;
            }
        } else {
            $name = $file->filename;
        }
        $counter++;
        $record = array('orig_filename' => $name, 'owner_id' => $owner, 'uniqid' => uniqid('', true), 'usergroup' =>  $fromform->usergroup);
        if ((isset($fromform->private)) && ($fromform->private)) {
            $record['private'] = 1;
        }
        // New video.
        if ($fromform->id == 0) {
            $lastinsertid = $DB->insert_record('local_video_directory', $record);
            core_tag_tag::set_item_tags('local_video_directory', 'local_video_directory', $lastinsertid, $context, $fromform->tags);

            // Uploading new video on existing ID.
        } else {
            // Check that user has rights to edit this video.
            local_video_edit_right($fromform->id);

            if (isset($_POST['owner']) && ($_POST['owner'] != 0)) {
                $record['owner_id'] = $_POST['owner'];
            }
            $lastinsertid = $fromform->id;
            $record['id'] = $fromform->id;
            $record['convert_status'] = 1;
            $DB->update_record('local_video_directory', $record);
            core_tag_tag::set_item_tags('local_video_directory', 'local_video_directory', $lastinsertid, $context, $fromform->tags);
        }
        $path = substr($file->contenthash, 0, 2) . "/" . substr($file->contenthash, 2, 2) . "/";
        copy($CFG->dataroot . "/filedir/" . $path . $file->contenthash, $dirs['uploaddir'] . $lastinsertid);
    }
    redirect($CFG->wwwroot . '/local/video_directory/list.php', get_string('file_uploaded', 'local_video_directory'));
} else {
    // Displays the form.
    echo $OUTPUT->header();
    // Menu.
    include('menu.php');
    $mform->display();
}

echo $OUTPUT->footer();
