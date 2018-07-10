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
 * Delete video.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_once('lib.php');

$config = get_config('local_video_directory');

if (!$config->allowanonymousembed) {
    require_login();
}

$PAGE->set_context(context_system::instance());
$PAGE->requires->css(new moodle_url('https://vjs.zencdn.net/7.0.2/video-js.css'));
$PAGE->set_pagelayout('embedded');
$PAGE->set_url('/local/video_directory/embed.php');

$uniqid = required_param('id', PARAM_RAW);
$video = $DB->get_record('local_video_directory', array('uniqid' => $uniqid));
if (is_numeric($uniqid) && (!$video)) {
    $videobyid = $DB->get_record('local_video_directory', array('id' => $uniqid));
    if ($videobyid) {
        $videoid = $uniqid;
    } else {
        die("Error...");
    }
} else if ($video) {
    $videoid = $video->id;
} else {
    die("Error...");
}

if ($config->embedtype == "dash") {
    $streamingurl = local_video_directory_get_dash_url($videoid);
    $dash = 1;
    $hls = 0;
} else if ($config->embedtype == "hls") {
    $streamingurl = local_video_directory_get_hls_url($videoid);
    $dash = 0;
    $hls = 1;
} else {
    // Should never get here.
    echo "Streaming type not supported...";
}

if (isset($videobyid)) {
    $video = $videobyid;
}

// Increase video view counter.
$views = $video->views + 1;
$DB->update_record('local_video_directory', array('id' => $video->id, 'views' => $views));

echo $OUTPUT->header();
echo $OUTPUT->render_from_template("local_video_directory/embed",
                                   array(   'videoid' => $videoid,
                                            'streamingurl' => $streamingurl,
                                            'wwwroot' => $CFG->wwwroot,
                                            'dash' => $dash,
                                            'hls' => $hls));
echo $OUTPUT->footer();
