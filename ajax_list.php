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

    // Check if user belong to the cohort or is admin.
    // require_once($CFG->dirroot.'/cohort/lib.php');

    // Check if user have permissionss.
    $context = context_system::instance();
    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }
    // if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
    // die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    // }
}

$PAGE->set_context(context_system::instance());

$videolist = array();

if (isset($SESSION->video_tags) && is_array($SESSION->video_tags)) {
    $list = implode("', '", $SESSION->video_tags);
    $list = "'" . $list . "'";
    if (is_siteadmin($USER)) {
        $videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' AS name
                                                FROM {local_video_directory} v
                                                LEFT JOIN {user} u on v.owner_id = u.id
                                                LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                                                LEFT JOIN {tag} t on ti.tagid=t.id
                                                WHERE ti.itemtype = \'local_video_directory\' AND t.name IN (' . $list . ')
                                                GROUP by id');
    } else {
        $videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' AS name
                                                FROM {local_video_directory} v
                                                LEFT JOIN {user} u on v.owner_id = u.idsudo apt install vlc
                                                LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                                                LEFT JOIN {tag} t on ti.tagid=t.id
                                                WHERE ti.itemtype = \'local_video_directory\' AND t.name IN (' . $list . ')
                                                AND (owner_id =' . $USER->id . ' OR (private IS NULL OR private = 0))
                                                GROUP by id');
    }
} else {
    if (is_siteadmin($USER)) {
        $videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
                                        ' AS name FROM {local_video_directory} v
                                        LEFT JOIN {user} u on v.owner_id = u.id');
    } else {
        $videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
                                                ' AS name FROM {local_video_directory} v
                                        LEFT JOIN {user} u on v.owner_id = u.id WHERE owner_id =' . $USER->id .
                                        ' OR (private IS NULL OR private = 0)');
    }
}

foreach ($videos as $video) {
    // Do not show filename.
    unset($video->filename);
    if (is_numeric($video->convert_status)) {
        $video->convert_status = get_string('state_' . $video->convert_status, 'local_video_directory');
    }

    $video->tags = str_replace('/tag/index.php', '/local/video_directory/list.php',
    $OUTPUT->tag_list(core_tag_tag::get_item_tags('local_video_directory', 'local_video_directory', $video->id), "", 'videos'));
    $video->thumb = str_replace(".png", "-mini.png", $video->thumb);
    $thumbdata = explode('-', $video->thumb);
    $thumbid = $thumbdata[0];
    $thumbseconds = isset($thumbdata[1]) ? "&second=$thumbdata[1]" : '';

    $versions = $DB->get_records('local_video_directory_vers', array('file_id' => $video->id));
    $versionsbutton = '<a href="' . $CFG->wwwroot . '/local/video_directory/versions.php?id=' .
            $video->id . '" title="' . get_string('versions', 'local_video_directory') .
            '" alt="' . get_string('versions', 'local_video_directory') . '">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/version';
    if (!$versions) {
         $versionsbutton .= '_grey';
    }
    $versionsbutton .= '.png" class="action_thumb"></a>';

    if (file_exists( $dirs['converted'] . $video->id . ".mp4")) {
        $alt = 'title="' . get_string('play', 'local_video_directory') . '"
            alt="' . get_string('play', 'local_video_directory') . '"';
        if (get_streaming_server_url()) {
            $playbutton = ' onclick="local_video_directory.play(\'' . get_streaming_server_url() . "/" .
                        $video->id . '.mp4\')" "';
        } else {
            $playbutton = ' onclick="local_video_directory.play(\'play.php?video_id=' .
            $video->id . '\')" " ';
        }
    } else {
        $playbutton = '';
        $video->convert_status .= '<br>' . get_string('awaitingconversion', 'local_video_directory');
    }

    $video->thumb = "<div class='video-thumbnail' " . $playbutton . ">" . ($video->thumb ? "<img src='$CFG->wwwroot/local/video_directory/thumb.php?id=$thumbid$thumbseconds&mini=1 '
        class='thumb' " . $playbutton ." >" : get_string('noimage', 'local_video_directory')) . "</div>";

    if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
        $video->actions = '';
    } else {
        $video->actions = '
        <a href="' . $CFG->wwwroot . '/local/video_directory/delete.php?video_id=' .
            $video->id . '" title="' . get_string('delete') .
            '" alt="' . get_string('delete') . '">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/delete.png" class="action_thumb">
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/edit.php?video_id=' . $video->id .
            '" title="' . get_string('edit') . '" alt="' . get_string('edit') . '">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/settings.png" class="action_thumb">
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/thumbs.php?id=' . $video->id .
            '" title="' . get_string('clicktochangethumb','local_video_directory') . '" alt="' . get_string('clicktochangethumb','local_video_directory') . '">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/camera.png" class="action_thumb">
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/upload_subs.php?id=' .
            $video->id .'" title="' . get_string('upload_subs', 'local_video_directory') . '"
            alt="' . get_string('upload_subs', 'local_video_directory') . '">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/subs';

        if (!$video->subs) {
             $video->actions .= "_grey";
        }

        $video->actions .= '.png" class="action_thumb">
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
