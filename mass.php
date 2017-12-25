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
 * Mass upload of videos from directory on server.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");
require_once('locallib.php');

$settings = get_settings();
$dirs = get_directories();


if (!CLI_SCRIPT) {
    require_login();

    // Check if user belong to the cohort or is admin.
    require_once($CFG->dirroot.'/cohort/lib.php');

    // Check if user have permissionss.
    $context = context_system::instance();
    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }

    // if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
    // die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    // }
}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('mass', 'local_video_directory'));
$PAGE->set_title(get_string('mass', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/mass.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('mass', 'local_video_directory'));


class mass_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $USER;

        $mform = $this->_form;

        $mform->addElement('html', '<table class="generaltable">');
        $mform->addElement('html', '<tr><th style="width:10%">' .get_string('choose', 'local_video_directory'). '</th><th>'
                                . get_string('filename', 'local_video_directory') . '</th><th>'
                                . get_string('size', 'local_video_directory') . '</th>'
                                . '<th>' . get_string('download_status', 'local_video_directory') . '</th></tr>');

        // Files in download queue.
        $wgets = $DB->get_records_sql('SELECT * FROM {local_video_directory_wget} WHERE success <> 4 AND owner_id= ?', array($USER->id));
        $dirs = get_directories();

        foreach ($wgets as $wget) {
            $mform->addElement('html', '<tr><td>');
            $mform->addElement('html', get_string('wget', 'local_video_directory'));
            $mform->addElement('html', '</td>');
            $filename = basename($wget->url);
            $mform->addElement('html', '<td>' . $wget->url.'</td><td>');

            if ($wget->success == 1) {
                $mform->addElement('html', local_video_directory_human_filesize(@filesize($dirs['wgetdir'].$filename)));
            }

            $mform->addElement('html', '</td><td>' . get_string('wget_' . $wget->success, 'local_video_directory') . '</td></tr>');
        }

        $files = listdir($dirs['massdir']);
        foreach ($files as $entry) {
            $entry = str_replace($dirs['massdir'], "", $entry);
            $mform->addElement('html', '<tr><td>');
            $mform->addElement('checkbox', base64_encode($entry), "");
            $mform->setDefault(base64_encode($entry), 'checked');
            $mform->addElement('html', '</td>');
            $mform->addElement('html', '<td>' . $entry.'</td><td>' .
                    local_video_directory_human_filesize(filesize($dirs['massdir']."/".$entry)).'</td></tr>');
        }

        $mform->addElement('html', '</table>');
        $mform->addElement('tags', 'tags', get_string('tags'),
            array('itemtype' => 'local_video_directory', 'component' => 'local_video_directory'));

        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new mass_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
    $context = context_system::instance();

    foreach ($fromform as $key => $value) {
        if (($key != "submitbutton") && ($key != "tags")) {
            $filename = base64_decode($key);
            $basename = basename($filename);
            $directory = str_replace($basename, "", $filename);

            $tags = $fromform->tags;

            if ($directory != "/") {
                // Remove / at start and end.
                $directory = preg_replace(array("/^\//", "/\/$/"), "", $directory);
                $directory = explode("/", $directory);
                if (is_array($fromform->tags)) {
                    $tags = array_merge($fromform->tags, $directory);
                } else {
                    $tags = $directory;
                }
            }
            $record = array('orig_filename' => $basename, 'owner_id' => $USER->id, 'private' => 1 );
            $lastinsertid = $DB->insert_record('local_video_directory', $record);
            $copied = copy($dirs['massdir'] . '/' . $filename , $dirs['uploaddir'] . $lastinsertid);
            if ($copied) {
                unlink($dirs['massdir'] . '/' . $filename);
            }
            core_tag_tag::set_item_tags('local_video_directory', 'local_video_directory', $lastinsertid, $context, $tags);
        }
    }
    removeemptysubfolders($dirs['massdir']);
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    $PAGE->requires->css('/local/video_directory/style.css');
    echo $OUTPUT->header();
    require('menu.php');
    $mform->display();
}
echo $OUTPUT->footer();

function listdir($startdir='.') {
    $files = array();
    if (is_dir($startdir)) {
        $fh = opendir($startdir);
        while (($file = readdir($fh)) !== false) {
            // Loop through the files, skipping . and .., and recursing if necessary.
            if (strcmp($file, '.') == 0 || strcmp($file, '..') == 0) {
                continue;
            }
            $filepath = $startdir . '/' . $file;
            if ( is_dir($filepath) ) {
                $files = array_merge($files, listdir($filepath));
            } else {
                array_push($files, $filepath);
            }
        }
        closedir($fh);
    } else {
        // False if the function was called with an invalid non-directory argument.
        $files = false;
    }

    return $files;
}

function removeemptysubfolders($path) {
    $dirs = get_directories();
    $empty = true;
    foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file) {
        $empty &= is_dir($file) && removeemptysubfolders($file);
    }
    if ($path != $dirs['massdir']) {
        return $empty && rmdir($path);
    } else {
        return $empty;
    }
}
