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
 * This is main file of showing video list, called from list.php in ajax.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_once('locallib.php');
defined('MOODLE_INTERNAL') || die();

$settings = get_settings();
$dirs = get_directories();
if (!CLI_SCRIPT) {
    require_login();

    // Check if user have permissionss.
    $context = context_system::instance();
    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        // TODO : write nice errors.
        die("Access Denied. Please see your site admin.");
    }
}

$PAGE->set_context(context_system::instance());

$videolist = array();

if (isset($SESSION->video_tags) && is_array($SESSION->video_tags)) {
    $list = implode("', '", $SESSION->video_tags);
    $list = "'" . $list . "'";
    $videos = local_video_directory_get_videos_by_tags($list);
} else {
    $videos = local_video_directory_get_videos();
}


foreach ($videos as $video) {
    // Do not show filename.
    unset($video->filename);
    if (is_numeric($video->convert_status)) {
        $video->convert_status = get_string('state_' . $video->convert_status, 'local_video_directory');
    }
    /*$video->tags = str_replace('/tag/index.php', '/local/video_directory/list.php',
    $OUTPUT->tag_list(core_tag_tag::get_item_tags('local_video_directory', 'local_video_directory', $video->id), "", 'videos'));*/
    $video->tags = str_replace('/tag/index.php?tc=1', '/local/video_directory/tag.php?action=add&tag=',
    $OUTPUT->tag_list(core_tag_tag::get_item_tags('local_video_directory', 'local_video_directory', $video->id), "", 'videos'));
/*    $video->thumb = str_replace(".png", "-mini.png", $video->thumb);
    $thumbdata = explode('-', $video->thumb);
    $thumbid = $thumbdata[0];
    $thumbseconds = isset($thumbdata[1]) ? "&second=$thumbdata[1]" : '';
    $video->thumb = "<a href='$CFG->wwwroot/local/video_directory/thumbs.php?id=$video->id' title='" .
        get_string('clicktochangethumb', 'local_video_directory') .
        "'>" . ($video->thumb ? "<img src='$CFG->wwwroot/local/video_directory/thumb.php?id=$thumbid$thumbseconds&mini=1 '
        class='local_video_directory_thumb'>" : get_string('noimage', 'local_video_directory')) . "</a>";
*/
    $versions = $DB->get_records('local_video_directory_vers', array('file_id' => $video->id));
    $versionsbutton = '<a href="' . $CFG->wwwroot . '/local/video_directory/versions.php?id=' .
            $video->id . '" title="' . get_string('versions', 'local_video_directory') .
            '" alt="' . get_string('versions', 'local_video_directory') . '">
            <i class="fa fa-clock-o';
    if (!$versions) {
         $versionsbutton .= ' grey';
    }
    $versionsbutton .= '" aria-hidden="true" ></i></a>';
    // $versionsbutton .= '.png" class="local_video_directory_action_thumb"></a>';

    if (!file_exists( $dirs['converted'] . $video->id . ".mp4")) {
        $video->convert_status .= '<br>' . get_string('awaitingconversion', 'local_video_directory');
    }

    $video->thumb = local_video_get_thumbnail_url($video->thumb, $video->id);

    if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
        $video->actions = '';
    } else {
        $video->actions = '
        <a href="' . $CFG->wwwroot . '/local/video_directory/delete.php?video_id=' .
            $video->id . '" title="' . get_string('delete') .
            '" alt="' . get_string('delete') . '">
            <i class="fa fa-eraser" aria-hidden="true"></i>
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/edit.php?video_id=' . $video->id .
            '" title="' . get_string('edit') . '" alt="' . get_string('edit') . '">
            <i class="fa fa-wrench" aria-hidden="true"></i>
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/thumbs.php?id=' . $video->id .
            '" title="' . get_string('clicktochangethumb', 'local_video_directory')
            . '" alt="' . get_string('clicktochangethumb', 'local_video_directory') . '">
            <i class="fa fa-camera" aria-hidden="true"></i>
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/upload_subs.php?id=' . $video->id .
            '" title="' . get_string('upload_subs', 'local_video_directory') . '"
            alt="' . get_string('upload_subs', 'local_video_directory') . '">
            <i class="fa fa-align-center';

        if (!$video->subs) {
             $video->actions .= " grey";
        }

        $video->actions .= '" aria-hidden="true"></i>
        </a>
        ' . $versionsbutton;
    }

    if (get_streaming_server_url()) {
        $video->streaming_url = '<a target="_blank" href="' . get_streaming_server_url() . '/' . $video->id . '.mp4">'
                                . get_streaming_server_url() . '/' . $video->id . '.mp4</a><br>';
    }
    $video->streaming_url .= '<a target="_blank" href="play.php?video_id=' . $video->id . '" >'.
        $CFG->wwwroot . '/local/video_directory/play.php?video_id=' .
        $video->id . '</a>';

    if ($video->private) {
        $checked = "checked";
    } else {
        $checked = "";
    }

    // Do not allow non owner to edit privacy and title.
    if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
         $video->private = '';
    } else {
        $video->private = "<p style='display: none'>" . $video->private . '</p><input type="checkbox"
                             class="checkbox ajax_edit" id="private_' . $video->id . '" ' . $checked . '>';
        $video->orig_filename = "<p style='display: none'>" . htmlspecialchars($video->orig_filename, ENT_QUOTES)
                                 . "</p><input type='text' class='hidden_input ajax_edit' id='orig_filename_" .
        $video->id . "' value='" . htmlspecialchars($video->orig_filename, ENT_QUOTES) . "'>";
    }

    $videolist[] = $video;
}

echo json_encode($videolist, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
