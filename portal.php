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
require_once('locallib.php');

//$settings = get_settings();
//$dirs = get_directories();

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('portal', 'local_video_directory'));
$PAGE->set_title(get_string('portal', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/portal.php');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('portal', 'local_video_directory'));
$PAGE->set_pagelayout('base');
$PAGE->requires->css('/local/video_directory/style.css');
$PAGE->requires->js('/local/video_directory/js/play.js');


// include font awesome in case of moodle 32 and older
if ($CFG->branch < 33) {
    $PAGE->requires->css(new moodle_url('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'));
}

echo $OUTPUT->header();


$videos = local_video_directory_get_videos('views');

foreach ($videos as $video) {
    $video->thumbnail = local_video_get_thumbnail_url($video->thumb, $video->id, 1);
}

echo $OUTPUT->render_from_template("local_video_directory/portal", array('videos' =>array_values($videos),'streaming' => get_streaming_server_url()));

//print_r($videos);

echo $OUTPUT->footer();
