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
 * Distribute subtitles to played video.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');

$subsize = 10;

$settings = get_settings();

$id = required_param('video_id', PARAM_INT);
$download = optional_param('download', 0, PARAM_INT);
$language = optional_param('language', 0, PARAM_RAW);
header("Content-type: text/vtt");
if ($download) {
    header('Content-Disposition: attachment; filename="' . $id . '.vtt"');
}
$dirs = get_directories();
if (file_exists($dirs['subsdir'] . $id . ($language ? "-" . $language : '') . ".vtt")) {
    readfile($dirs['subsdir'] . $id . ($language ? "-" . $language : '') . ".vtt");
} else {
    // Do we have words from google in this movie.
    $words = $DB->get_records('local_video_directory_words', ['video_id' => $id]);
    if ($words) {
        echo "WEBVTT\n\n";
        $counter = 0;
        $sentence = "";
        foreach ($words as $word) {
            $counter++;
            if ($counter == 1) {
                $start = gmdate("H:i:s", substr($word->start, 0 , -1)) . '.000';
            }
            $sentence .= $word->word . ' ';
            if ($counter == $subsize) {
                $counter = 0;
                echo $start . ' --> ' . gmdate("H:i:s", substr($word->end, 0, -1)) . '.000' . "\n";
                echo $sentence . "\n\n";
                $sentence = '';
            }
        }
    }
}
