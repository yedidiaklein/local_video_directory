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
$streamingurl = get_settings()->streaming;
$id = optional_param('video_id', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('edit', 'local_video_directory'));
$PAGE->set_title(get_string('edit', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/edit.php?video_id=' . $id);
$PAGE->set_pagelayout('standard');

$PAGE->requires->css('/local/video_directory/style.css');

$PAGE->requires->css('/local/video_directory/styles/select2.min.css');


$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('edit', 'local_video_directory'));

class edit_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $USER;

        $id = optional_param('video_id', 0, PARAM_INT);

        if ($id != 0) {
            $video = $DB->get_record('local_video_directory', array("id" => $id));
            $origfilename = $video->orig_filename;
            $usergroup = $video->usergroup;
            $owner = array();
            $length = $video->length;
            $timecreated = strftime("%A, %d %B %Y %H:%M", $video->timecreated);
        } else {
            $origfilename = "";
            $owner = 0;
            $usergroup = "";
            $length = 0;
            $timecreated = 0;
        }
        $mform = $this->_form;

        $mform->addElement('text', 'origfilename', get_string('orig_filename', 'local_video_directory'));
        $mform->setType('origfilename', PARAM_RAW);
        $mform->setDefault('origfilename', $origfilename ); // Default value.

        $settings = get_settings();

        if ($settings->group != "none") {
            $g = local_video_get_groups($settings);

            $option = array();
            if (!is_video_admin($USER)) {
                $option = ['disabled' => true];
            }

            $select = $mform->addElement('select', 'usergroup', get_string('group', 'moodle'), $g, $option);
            $select->setSelected($usergroup);
        }

        if ($settings->categories) {
            $allcats = $DB->get_records('local_video_directory_cats', []);
            foreach ($allcats as $cat) {
                $c[$cat->id] = $cat->cat_name;
            }
            $mform->addElement('autocomplete', 'category', '<a href="categories.php">' .
                                get_string('categories', 'local_video_directory') . '</a>',
                                $c, ['class' => 'local_video_directory_categories']);
            $mform->getElement('category')->setMultiple(true);
            $multicats = $DB->get_records('local_video_directory_catvid', ['video_id' => $id]);
            $catselected = array();
            foreach ($multicats as $cat) {
                $catselected[] = $cat->cat_id;
            }
            $mform->getElement('category')->setSelected($catselected);
        }

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('tags', 'tags', get_string('tags'),
                    array('itemtype' => 'local_video_directory', 'component' => 'local_video_directory'));
        if ($id != 0) {
            $data = $DB->get_record('local_video_directory', array('id' => $id));
            $data->tags = core_tag_tag::get_item_tags_array('local_video_directory', 'local_video_directory', $id);
            $mform->setDefault('tags', $data->tags);
        }

        if (is_video_admin($USER) && (is_array($owner))) {
            $owneruser = $DB->get_record('user', ['id' => $video->owner_id]);
            $owner[$video->owner_id] = $owneruser->firstname . " " . $owneruser->lastname;
            $mform->addElement('select', 'owner', get_string('owner', 'local_video_directory'), $owner);
        }

        $mform->addElement('text', 'length', get_string('length', 'local_video_directory'), ['disabled' => true]);
        $mform->setType('length', PARAM_RAW);
        $mform->setDefault('length', $length ); // Default value.

        $mform->addElement('text', 'timecreated', get_string('timecreated', 'local_video_directory'), ['disabled' => true]);
        $mform->setType('timecreated', PARAM_RAW);
        $mform->setDefault('timecreated', $timecreated ); // Default value.

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
    local_video_edit_right($fromform->id);
    if (isset($fromform->category)) {
        // Delete all multi groups records.
        $DB->delete_records('local_video_directory_catvid', ['video_id' => $fromform->id]);
        foreach ($fromform->category as $cat) {
            $DB->insert_record('local_video_directory_catvid', ['video_id' => $fromform->id, 'cat_id' => $cat]);
        }
    }
    $record = array("id" => $fromform->id,
                    "orig_filename" => $fromform->origfilename,
                    "usergroup" => $fromform->usergroup);

    if ((isset($_POST['owner'])) && (is_video_admin($USER))) { // Only admins updates owners.
        $record['owner_id'] = $_POST['owner'];
    }
    $update = $DB->update_record("local_video_directory", $record);
    $context = context_system::instance();
    core_tag_tag::set_item_tags('local_video_directory', 'local_video_directory', $fromform->id, $context, $fromform->tags);
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    echo $OUTPUT->header();

    $video = $DB->get_record('local_video_directory', array("id" => $id));
    $versions = $DB->get_records("local_video_directory_vers", array("file_id" => $id));

    if ($video->filename != $id . '.mp4') {
        $filename = $video->filename;
    } else {
        $filename = $id;
    }

    $streaming = get_streaming_server_url() . "/" . $filename . ".mp4";

    echo $OUTPUT->render_from_template('local_video_directory/edit',
    [   'wwwroot' => $CFG->wwwroot,
        'versions' => $versions,
        'id' => $id,
        'thumb' => str_replace("-", "&second=", $video->thumb),
        'streaming' => $streaming
        ]);

    $mform->display();

    echo "<img src='qr.php?id=" . $id . "'>";
    $embedurl = $CFG->wwwroot . '/local/video_directory/embed.php?id=' . $video->uniqid;
    echo '<div class="video_embed"><h2>' . get_string('embeding', 'local_video_directory') . '</h2>&lt;iframe src="' . $embedurl
            . '" ' . $settings->embedoptions . ' >&lt;/iframe><br>'
            . "<a href='$streaming'> $streaming </a> </div>";
}

echo $OUTPUT->footer();