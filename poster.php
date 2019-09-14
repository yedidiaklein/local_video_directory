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
 * Showing thumnail
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');

$settings = get_settings();

$id = required_param('id', PARAM_INT);

$dirs = get_directories();
$streamingdir = $dirs['converted'];

$video = $DB->get_record('local_video_directory', ['id' => $id]);
$splitted = explode("-", $video->thumb);
if (count($splitted) > 1) {
    if (is_numeric($splitted[1])) {
        $second = $splitted[1];
    }
} else {
    $second = '';
}

if ($video->filename != $id . '.mp4') {
    $filename = $video->filename;
} else {
    $filename = $id;
}

header("Content-type: image/png");
readfile($streamingdir . $filename . ($second ? "-" . $second : '') . ".png");
