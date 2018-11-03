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
require_login();
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');
require_once("$CFG->libdir/formslib.php");

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('portal', 'local_video_directory'));
$PAGE->set_title(get_string('portal', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/portal.php');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('portal', 'local_video_directory'));
$PAGE->set_pagelayout('base');
$PAGE->requires->css('/local/video_directory/style.css');

// Include font awesome in case of moodle 32 and older.
if ($CFG->branch < 33) {
    $PAGE->requires->css(new moodle_url('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'));
}

class portal_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $USER;

        $mform = $this->_form;

        $mform->addElement('text', 'search', get_string('search'));
        $mform->setType('search', PARAM_TEXT);
        $search = optional_param('search', 0, PARAM_TEXT);
        if ($search) {
            $mform->setDefault('search', $search);
        }


        $buttonarray = array();
        $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('search'));
        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        return array();
    }
}

$mform = new portal_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/portal.php');
} else if ($fromform = $mform->get_data()) {

    redirect($CFG->wwwroot . '/local/video_directory/portal.php?search=' . $fromform->search);

} else {


    echo $OUTPUT->header();

    echo '<p class="local_video_directory_backtolist">
          <a href="list.php" alt ="' . get_string('list', 'local_video_directory') . '">' . get_string('list', 'local_video_directory') . '</a>
          </p>';

    $mform->display();

    $search = optional_param('search', 0, PARAM_TEXT);
    $streaming = get_streaming_server_url();

    if ($search) {
        $admin = is_siteadmin($USER);
        $videos = $DB->get_records_sql('SELECT DISTINCT v.* FROM {local_video_directory} v
                                        LEFT JOIN {local_video_directory_txtsec} t
                                        ON v.id = t.video_id
                                        WHERE ' . $DB->sql_like('t.content', ':content',0)
                                        . ' OR ' . $DB->sql_like('v.orig_filename', ':name',0)
                                        . ' AND (v.owner_id = :id OR v.private = 0 OR 1 = :admin)'
                                        , [ 'content' => '%' . $search . '%', 'id' => $USER->id, 'admin' => $admin, 'name' => '%' . $search . '%' ]);
        $fulltexts = $DB->get_records_sql('SELECT t.id, t.video_id, t.content, t.start, t.end FROM {local_video_directory} v
                                        LEFT JOIN {local_video_directory_txtsec} t
                                        ON v.id = t.video_id
                                        WHERE ' . $DB->sql_like('t.content', ':content',0)
                                        . ' AND (v.owner_id = :id OR v.private = 0 OR 1 = :admin)'
                                        , [ 'content' => '%' . $search . '%', 'id' => $USER->id, 'admin' => $admin ]);
        foreach ($fulltexts as $fulltext) {
            $fulltext->content = preg_replace('!(' . $search . ')!i', '<font style="color:red; font-weight:bold;">$1</font>', $fulltext->content);
            if (!isset($videos[$fulltext->video_id]->content)) {
                $videos[$fulltext->video_id]->content = '';
            }
            $startsec = explode(".", $fulltext->start);
            $videos[$fulltext->video_id]->content .= "<a href=#><p data-video-url='$streaming/$fulltext->video_id.mp4#t=$startsec[0]'>" . $fulltext->start . " - " . $fulltext->end
                                                    . "</p></a>" . $fulltext->content . "<hr>"; 
        }
    } else {
        $videos = local_video_directory_get_videos('views');
    }
    
    foreach ($videos as $video) {
        $video->thumbnail = local_video_get_thumbnail_url($video->thumb, $video->id, 1);
        if ($search) {
            $video->orig_filename = preg_replace('!(' . $search . ')!i', '<font style="color:red; font-weight:bold;">$1</font>', $video->orig_filename);
        }
    }
    if ($search) {
        echo $OUTPUT->render_from_template("local_video_directory/portal_search",
                array('videos' => array_values($videos), 'streaming' => $streaming));
    } else {
    echo $OUTPUT->render_from_template("local_video_directory/portal",
                array('videos' => array_values($videos), 'streaming' => $streaming));
    }
    echo $OUTPUT->render_from_template('local_video_directory/player', []);
}

echo $OUTPUT->footer();
