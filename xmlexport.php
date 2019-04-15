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
 * List all videos.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_once( __DIR__ . '/locallib.php');

$settings = get_settings();
if ($settings->allowxmlexport == 0) {
    die("Access Denied");
}

header("Content-Type: application/xml; charset=utf-8");

$videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
                                ' AS owner FROM {local_video_directory} v LEFT JOIN {user} u on v.owner_id = u.id WHERE private=0');

$streaming = get_streaming_server_url();

echo '<catalog>';                                
foreach ($videos as $video) {
    echo '<video>';
    foreach ($video as $key => $value) {
        echo '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
    }
    echo '<streaming>' . $streaming . "/" . $video->id . '.mp4</streaming>' . "\n";
    echo '<iframe>' . $CFG->wwwroot . "/local/video_directory/embed.php?id=" . $video->uniqid . '</iframe>' . "\n";
    echo '<thumbnail>' . $CFG->wwwroot . "/local/video_directory/thumb.php?id=$video->id&amp;mini=1" . '</thumbnail>' . "\n";
    
    echo '</video>';
}
echo '</catalog>';