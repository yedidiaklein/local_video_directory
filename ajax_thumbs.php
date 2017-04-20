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
require_once 'init.php';
defined('MOODLE_INTERNAL') || die();

$ffmpeg = $settings->ffmpeg;
$id = required_param('id', PARAM_INT);
$second = required_param('second', PARAM_INT);
$streaming_dir = $converted;

$PAGE->set_context(context_system::instance());

if (is_numeric($second)) {
    $timing = gmdate("H:i:s", $second);
} else {
    $timing = "00:00:05";
}

$thumb = '"' . $ffmpeg . "\" -i ". $streaming_dir . $id . ".mp4 -ss " . $timing . " -vframes 1  -vf scale=100:-1 " . $streaming_dir . $id . "-" . $second . ".png";
$output = exec($thumb);

if (file_exists($streaming_dir . $id . "-" . $second . ".png")) {
    echo $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $id . "&second=" . $second;
} else {
    echo 'noimage';
}
