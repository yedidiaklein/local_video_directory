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
 * List version of file.
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

if (!is_video_admin($USER)) {
    die("Access Denied");
}

$add = optional_param('add', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
if ($delete) {
    $haschilds = $DB->get_records('local_video_directory_catvid', ['cat_id' => $delete]);
    if (!$haschilds) {
        $deleted = $DB->delete_records('local_video_directory_cats', ['id' => $delete]);
        if ($deleted) {
            $message = $delete . " " . get_string('deleted', 'moodle', $delete);
            $type = 'notifysuccess';
        } else {
            $message = get_string('deletednot', 'moodle', $delete);
            $type = 'notifyerror';
        }
    } else {
        $message = get_string('deletednot', 'moodle', $delete);
        $type = 'notifyerror';
    }
}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('categories', 'local_video_directory'));
$PAGE->set_title(get_string('categories', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/categories.php');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('categories', 'local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
// Include font awesome in case of moodle 32 and older.
if ($CFG->branch < 33) {
    $PAGE->requires->css('/local/video_directory/font_awesome/css/all.min.css');
}


echo $OUTPUT->header();
if (isset($message)) {
    echo $OUTPUT->notification($message, $type);
}
// Menu.
require('menu.php');

class category_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('text', 'cat_name', get_string('category'));
        $mform->setType('cat_name', PARAM_RAW);
        $mform->setDefault('cat_name', ''); // Default value.

        $all = $DB->get_records('local_video_directory_cats', []);
        $g[0] = ' - ';
        foreach ($all as $cat) {
            $g[$cat->id] = $cat->cat_name;
        }
        $mform->addElement('select', 'father', get_string('father', 'local_video_directory'), $g);

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new category_form();

// TODO : Find a way to insert before populating the form for getting new entries in form.
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/categories.php');
} else if ($fromform = $mform->get_data()) {
    $record = ['cat_name' => $fromform->cat_name,
                  'father_id' => $fromform->father];
    if ($id = $DB->insert_record('local_video_directory_cats', $record)) {
        echo "Inserted, ID is: " . $id . "<br>";
    }
}

if ($add) {
    echo '<h2>' . get_string('new_category', 'local_video_directory') . '</h2>';
    $mform->display();
} else {
    echo '<a href="?add=1"><h3>' . get_string('new_category', 'local_video_directory') . '</h3></a>';
}

$categories = $DB->get_records_sql('select c1.id,c1.cat_name,c2.cat_name as father_name,
                                    (SELECT count(*) FROM mdl_local_video_directory_catvid WHERE cat_id = c1.id) as times
                                    from {local_video_directory_cats} c1
                                    left join {local_video_directory_cats} c2 on c1.father_id = c2.id
                                    ');

echo $OUTPUT->render_from_template("local_video_directory/categories",
                                   array('categories' => array_values($categories)));
echo $OUTPUT->footer();