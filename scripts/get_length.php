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
 * This script was created for old movies that didn't get legth.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', 1);

require_once( __DIR__ . '/../init.php');

$origdir = $uploaddir;
$streamingdir = $converted;
$ffprobe = $settings->ffprobe;
$videos = $DB->get_records('local_video_directory', array('length' => null));

foreach ($videos as $video) {
    // Get video length.
    $length_cmd = $ffprobe ." -v error -show_entries format=duration -sexagesimal -of default=noprint_wrappers=1:nokey=1 "
        . $streamingdir . $video->id . ".mp4";
    $length_output = exec( $length_cmd );
    // Remove data after.
    $array_length = explode(".", $length_output);
    $length = $array_length[0];
    $record = array("id" => $video->id, "length" => $length);
    $update = $DB->update_record("local_video_directory", $record);
    echo "Video ".$video->id." updated to length ".$length."\n";
}
