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

function local_video_directory_cron() {
    global $CFG, $DB;

    include_once( $CFG->dirroot . "/local/video_directory/init.php");

    $ffmpeg = $settings->ffmpeg;
    $streamingurl = $settings->streaming.'/';
    $ffprobe = $settings->ffprobe;
    $ffmpegsettings = $settings->ffmpegsettings;
    $thumbnailseconds = $settings->thumbnailseconds;
    $php = $settings->php;
    $multiresolution = $settings->multiresolution;
    $resolutions = $settings->resolutions;
    $origdir = $uploaddir;
    $streamingdir = $converted;

    // Check if we've to convert videos.
    $videos = $DB->get_records('local_video_directory', array("convert_status" => 1));
    // Move all video that have to be converted to Waiting.. state (4) just to make sure that there is not 
    // multiple cron that converts same files.
    $wait = $DB->execute('UPDATE {local_video_directory} SET convert_status = 4 WHERE convert_status = 1');

    foreach ($videos as $video) {
        // Update convert_status to 2 (Converting....).
        $record = array("id" => $video->id, "convert_status" => "2");
        $update = $DB->update_record("local_video_directory", $record);
        $convert = '"' . $ffmpeg . '" -i ' . $origdir . $video->id . ' ' . $ffmpegsettings . ' ' .
                $streamingdir . $video->id . ".mp4";
        exec($convert);

        // Check if was converted.
        if (file_exists($streamingdir . $video->id . ".mp4")) {
            // Get Video Thumbnail.
            if (is_numeric($thumbnailseconds)) {
                $timing = gmdate("H:i:s", $thumbnailseconds);
            } else {
                $timing = "00:00:05";
            }

            $thumb = '"' . $ffmpeg . '" -i ' . $origdir . $video->id . " -ss " . $timing . " -vframes 1 " . $streamingdir . $video->id . ".png";
            $thumbmini = '"' . $ffmpeg . '" -i ' . $origdir . $video->id . " -ss " . $timing . " -vframes 1 -vf scale=100:-1 " . $streamingdir . $video->id . "-mini.png";

            exec($thumb);
            exec($thumbmini);

            // Get video length.
            $lengthcmd = $ffprobe ." -v error -show_entries format=duration -sexagesimal -of default=noprint_wrappers=1:nokey=1 " . $streamingdir . $video->id . ".mp4";
            $lengthoutput = exec( $lengthcmd );
            // Remove data after .
            $arraylength = explode(".", $lengthoutput);
            $length = $arraylength[0];


            $metadata = array();
            $metafields = array("height" => "stream=height", "width" => "stream=width", "size" => "format=size");
            foreach ($metafields as $key => $value) {
                $metadata[$key] = exec($ffprobe . " -v error -show_entries " . $value .
                    " -of default=noprint_wrappers=1:nokey=1 " . $streamingdir . $video->id . ".mp4"); 
            }

            // Update that converted and streaming URL.
            $record = array("id" => $video->id,
                            "convert_status" => "3",
                            "streamingurl" => $streamingurl . $video->id . ".mp4",
                            "filename" => $video->id . ".mp4",
                            "thumb" => $video->id,
                            "length" => $length,
                            "height" => $metadata['height'],
                            "width" => $metadata['width'],
                            "size" => $metadata['size'],
                            "timecreated" => time(),
                            "timemodified" => time()
                            );

            $update = $DB->update_record("local_video_directory", $record);
        } else {
            // Update that converted and streaming URL.
            $record = array("id" => $video->id, "convert_status" => "5");
            $update = $DB->update_record("local_video_directory", $record);
        }
        // Delete original file.
        unlink($origdir . $video->id);
    }

    // Take care of wget table.
    $wgets = $DB->get_records('local_video_directory_wget', array("success" => 0));

    if ($wgets) {
        foreach ($wgets as $wget) {
            $record = array('id' => $wget->id, 'success' => 1);
            $update = $DB->update_record("local_video_directory_wget", $record);
//            exec($php . ' ' . $CFG->dirroot . '/local/video_directory/scripts/wget.php ' . base64_encode($wget->url) . ' &');
            $filename = basename($wget->url);

            echo "Downloading $wget->url to $wgetdir\n";
            echo "Filename is $filename";
            file_put_contents($wgetdir . $filename, fopen($wget->url, 'r'));

            // Move to mass directory once downloaded.
            if (copy($wgetdir . $filename, $massdir . $filename)) {
                unlink($wgetdir . $filename);
                $sql = "UPDATE {local_video_directory_wget} SET success = 2 WHERE url = ?";
                $DB->execute($sql, array($wget->url));
            }
            // Doing one download per cron.
            break;
        }
    }
    if ($multiresolution) {
        // Create multi resolutions streams.
        $videos = $DB->get_records("local_video_directory", array('convert_status' => 3));
        foreach ($videos as $video) {
            create_dash($video->id, $converted, $multidir, $ffmpeg, $resolutions);
        }
    }
}

function local_video_directory_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE, $USER;

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        require_once($CFG->dirroot.'/cohort/lib.php');
        $settings = get_config('local_video_directory');

        if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
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

function create_dash($id, $converted, $dashdir, $ffmpeg, $resolutions) {
    global $DB, $CFG;

    include_once( $CFG->dirroot . "/local/video_directory/init.php");
    
    // Update state to 6 - creating dash streams.
    $DB->update_record("local_video_directory", array('id' => $id, 'convert_status' => 6));

    $video = $DB->get_record("local_video_directory", array('id' => $id));

    // Multi resolutions for dash-ing.
    // first take care of current resolution.
    $cmd = $ffmpeg . " -i " . $converted . $id . ".mp4" . 
    " -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' " . 
    " " . $dashdir . $id . "_" . $video->height . ".mp4";
    exec($cmd);
    $record=array("video_id" => $id,
                  "height" => $video->height,
                  "filename" => $id . "_" . $video->height . ".mp4",
                  "datecreated" => time(),
                  "datemodified" => time());
    $DB->insert_record("local_video_directory_multi", $record);

    $resolutions = explode(",", $resolutions);

    foreach ($resolutions as $resolution) {
        if (($resolution < $video->height) && (is_numeric($resolution))) {
            $cmd = $ffmpeg . " -i " . $converted . $id . ".mp4" .
            " -strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -x264opts 'keyint=24:min-keyint=24:no-scenecut' -vf scale=-2:" . 
            $resolution . " " . $dashdir . $id . "_" . $resolution . ".mp4";
            exec($cmd);
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