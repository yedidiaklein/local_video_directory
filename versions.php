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

require_once('init.php');
defined('MOODLE_INTERNAL') || die();

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('versions', 'local_video_directory'));
$PAGE->set_title(get_string('versions', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/versions.php');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('versions', 'local_video_directory'));
$PAGE->set_pagelayout('base');
$PAGE->requires->css('/local/video_directory/style.css');
$PAGE->requires->js('/local/video_directory/js/list.js');
$PAGE->set_context(context_system::instance());
$context = context_user::instance($USER->id);

$id = required_param('id', PARAM_INT);

echo $OUTPUT->header();

// Menu.
require('menu.php');

$versions = $DB->get_records('local_video_directory_vers', array('file_id' => $id));

echo "<table><tr><th>" . get_string('date') . "</th><th>" . get_string('view') .
        "</th><th>" . get_string('restore') . "</th></tr>";

foreach ($versions as $version) {
    echo '<tr><td>' . strftime("%A, %d %B %Y %H:%M", $version->datecreated) . '</td><td>
    <img class="play_video action_thumb" onclick="local_video_directory.play(\'play.php?video_id=' .
            $version->file_id . "_" . $version->datecreated . '\')" " src="' . $CFG->wwwroot . '/local/video_directory/pix/play.svg">
            </td><td><a href="restore.php?id=' . $id . '&restore=' . $version->datecreated . '"><img class="play_video action_thumb" 
            src="' . $CFG->wwwroot . '/local/video_directory/pix/synchronize.svg"></a></td></tr>'; 
}
echo "</table>";

?>
<div id='video_player' style='display:none'>
    <a href=# class='close' onclick='local_video_directory.close_player();'>
        &times; <?php echo get_string('close', 'local_video_directory'); ?>
    </a>
    <br>
    <video id="my-video" controls preload="auto"></video>
</div>

<?php

echo $OUTPUT->footer();
