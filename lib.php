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
 * Functions.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once( __DIR__ . '/locallib.php');

function local_video_directory_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE, $USER;

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $settings = get_config('local_video_directory');

        $context = context_system::instance();
        if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
            return;
        }

        $strfather = get_string('pluginname', 'local_video_directory');
        $fathernode = navigation_node::create(
            $strfather,
            null,
            navigation_node::NODETYPE_BRANCH,
            'local_video_directory_father',
            'local_video_directory_father'
        );

        $settingnode->add_node($fathernode);
        $strlist = get_string('list', 'local_video_directory');
        $url = new moodle_url('/local/video_directory/list.php', array('id' => $PAGE->course->id));
        $listnode = navigation_node::create(
            $strlist,
            $url,
            navigation_node::NODETYPE_LEAF,
            'local_video_directory_list',
            'local_video_directory_list',
            new pix_icon('f/avi-24', $strlist)
        );

        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $listnode->make_active();
        }

        $strupload = get_string('upload', 'local_video_directory');
        $urlupload = new moodle_url('/local/video_directory/upload.php', array('id' => $PAGE->course->id));
        $uploadnode = navigation_node::create(
            $strupload,
            $urlupload,
            navigation_node::NODETYPE_LEAF,
            'local_video_directory_upload',
            'local_video_directory_upload',
            new pix_icon('t/addcontact', $strupload)
        );

        if ($PAGE->url->compare($urlupload, URL_MATCH_BASE)) {
            $uploadnode->make_active();
        }

        $fathernode->add_node($listnode);
        $fathernode->add_node($uploadnode);
    }
}

function local_video_directory_create_dash($id, $converted, $dashdir, $ffmpeg, $resolutions) {
    global $DB, $CFG;

    // Update state to 6 - creating dash streams.
    $DB->update_record("local_video_directory", array('id' => $id, 'convert_status' => 6));

    $video = $DB->get_record("local_video_directory", array('id' => $id));

    // Multi resolutions for dash-ing.
    // first take care of current resolution.
    $cmd = " -y -i " . escapeshellarg($converted . $id . ".mp4") .
        " -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts "
        . escapeshellarg("keyint=24:min-keyint=24:no-scenecut") .
        " " . escapeshellarg($dashdir . $id . "_" . $video->height . ".mp4");

    exec($ffmpeg . $cmd);
    $record = array("video_id" => $id,
                  "height" => $video->height,
                  "filename" => $id . "_" . $video->height . ".mp4",
                  "datecreated" => time(),
                  "datemodified" => time());
    $DB->insert_record("local_video_directory_multi", $record);

    $resolutions = explode(",", $resolutions);

    foreach ($resolutions as $resolution) {
        if (($resolution < $video->height) && (is_numeric($resolution))) {
            $cmd = " -y -i " . escapeshellarg($converted . $id . ".mp4") .
            " -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts "
            . escapeshellarg("keyint=24:min-keyint=24:no-scenecut") .
            " -vf " . escapeshellarg( "scale=-2:" . $resolution) ." " . escapeshellarg($dashdir . $id . "_" . $resolution . ".mp4");
            exec($ffmpeg . $cmd);
            $record = array("video_id" => $id,
                          "height" => $resolution,
                          "filename" => $id . "_" . $resolution . ".mp4",
                          "datecreated" => time(),
                          "datemodified" => time());
            $DB->insert_record("local_video_directory_multi", $record);
        }
    }
    // Update state to 7 - ready + multi.
    $DB->update_record("local_video_directory", array('id' => $id, 'convert_status' => 7));

}

function local_video_directory_get_dash_url($videoid) {
    global $DB;
    
    $config = get_config('local_video_directory');

    $dash_streaming = $config->dashbaseurl;
    $nginx_multi = 'multiuri';

    $id = $videoid;
    $streams = $DB->get_records("local_video_directory_multi",array("video_id" => $id));
    foreach ($streams as $stream) {
        $files[]=$stream->filename;
    }

    $parts=array();
    foreach ($files as $file) {
        $parts[] = preg_split("/[_.]/", $file);
    }

    $dash_url = $dash_streaming . "/" . $parts[0][0] . "_";
    foreach ($parts as $key => $value) {
        $dash_url .= "," . $value[1];
    }
    $dash_url .= "," . ".mp4".$nginx_multi."/manifest.mpd";

    return $dash_url;			
}