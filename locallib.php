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
 * Locallib for local functions.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_video_directory_human_filesize($bytes, $decimals = 2, $red = 0) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);

    if (($red != 0) && ($bytes < $red)) {
        return '<df style="color:red">' . sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . '</df>';
    } else {
        return '<df>' . sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . '</df>';
    }
}

function local_video_directory_get_tagged_pages($tag, $exclusivemode = false, $fromctx = 0, $ctx = 0, $rec = 1, $page = 0) {
    global $CFG;
    $builder = new core_tag_index_builder('local_video_directory', 'local_video_directory',
                $query, $params, $page * $perpage, $perpage + 1);
    return 1;
}

function local_video_edit_right($videoid) {
    global $DB, $CFG, $USER;
    $video = $DB->get_record("local_video_directory", array('id' => $videoid));
    if ((is_siteadmin($USER) || $video->owner_id == $USER->id)) {
        return 1;
    } else {
        redirect($CFG->wwwroot . '/local/video_directory/list.php', get_string('accessdenied', 'admin'));
    }
}

// Check if streaming server and symlink or settings exists and work.
function get_streaming_server_url() {
    global $DB;
    $settings = get_settings();
    $firstvideo = $DB->get_records('local_video_directory', array());

    if ($firstvideo) {
        $url = $settings->streaming.'/'.current($firstvideo)->id.'.mp4';
        $headers = get_headers($url);
        if (strstr($headers[0] , "200")) {
            $streamingurl = $settings->streaming;
        } else {
            $streamingurl = false;
        }
    }
    return $streamingurl;
}

// Get settings and make directories if they are not exist.
function get_settings() {

    $settings = get_config('local_video_directory');
    $shellcomponents = array('ffmpeg', 'ffprobe', 'php');
    $iswin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    foreach ($shellcomponents as $sc) {
        if (isset($settings->$sc)) {
            $settings->$sc = ($iswin && isset($settings->{$sc . 'drive'}) && preg_match('~^[a-z]$~',
            $settings->{$sc . 'drive'}) ? $settings->{$sc . 'drive'} .
            ":" . (strpos($settings->$sc, '/') === 0 ? '' : '/') : '') .
            ($iswin ? str_replace('/', DIRECTORY_SEPARATOR, $settings->$sc) : $settings->$sc);
        }
    }
    return $settings;
}

function get_directories() {
    global $CFG;
    // Directories for this plugin.
    $dirs = array('uploaddir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos' . DIRECTORY_SEPARATOR,
                'converted' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'converted' . DIRECTORY_SEPARATOR,
                'massdir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'mass' . DIRECTORY_SEPARATOR,
                'wgetdir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'wget' . DIRECTORY_SEPARATOR,
                'multidir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'multi' . DIRECTORY_SEPARATOR,
                'subsdir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'subs' . DIRECTORY_SEPARATOR);

    foreach ($dirs as $key => $value) {
        // Add dataroot.
        $dirs[$key] = $CFG->dataroot.$value;
        // Create if doesn't exist.
        if (!file_exists($dirs[$key])) {
            mkdir($dirs[$key], 0777, true);
        }
    }
    return $dirs;
}

