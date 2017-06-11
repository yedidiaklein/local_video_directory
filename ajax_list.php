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
require_once('init.php');
defined('MOODLE_INTERNAL') || die();

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
                                                LEFT JOIN {user} u on v.owner_id = u.id
                                                LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                                                LEFT JOIN {tag} t on ti.tagid=t.id
                                                WHERE ti.itemtype = \'local_video_directory\' AND t.name IN (' . $list . ')
                                                AND (owner_id =' . $USER->id . ' OR (private IS NULL OR private = 0))
                                                GROUP by id');
    }
} else {
        if (is_siteadmin($USER)) {
        $videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
        ' AS name FROM {local_video_directory} v LEFT JOIN {user} u on v.owner_id = u.id');
        } else {
        $videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
        ' AS name FROM {local_video_directory} v LEFT JOIN {user} u on v.owner_id = u.id WHERE owner_id =' . $USER->id .
        ' OR (private IS NULL OR private = 0)');
    }
}

foreach ($videos as $video) {
    if (is_numeric($video->convert_status)) {
        $video->convert_status = get_string('state_' . $video->convert_status, 'local_video_directory');
    }

    $video->tags = str_replace('/tag/index.php', '/local/video_directory/list.php',
    $OUTPUT->tag_list(core_tag_tag::get_item_tags('local_video_directory', 'local_video_directory', $video->id), "", 'videos'));
    $video->thumb = str_replace(".png", "-mini.png", $video->thumb);
    $thumbdata = explode('-', $video->thumb);
    $thumbid = $thumbdata[0];
    $thumbseconds = isset($thumbdata[1]) ? "&second=$thumbdata[1]" : '';
    $video->thumb = "<a href='$CFG->wwwroot/local/video_directory/thumbs.php?id=$video->id' title='" .
        get_string('clicktochangethumb', 'local_video_directory') .
        "'>" . ($video->thumb ? "<img src='$CFG->wwwroot/local/video_directory/thumb.php?id=$thumbid$thumbseconds' 
        class='thumb'>" : get_string('noimage', 'local_video_directory')) . "</a>";

    if (file_exists("$CFG->dataroot/videos/converted/$video->id.mp4")) {
        $playbutton = '<img class="play_video action_thumb" onclick="local_video_directory.play(\'play.php?video_id=' .
        $video->id . '\')" " src="' . $CFG->wwwroot . '/local/video_directory/pix/play.svg">';
    } else {
        $playbutton = '';
        $video->convert_status .= '<br>' . get_string('awaitingconversion', 'local_video_directory');
    }

    if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
        $video->actions = $playbutton;
    } else {
        $video->actions = '
        <a href="' . $CFG->wwwroot . '/local/video_directory/delete.php?video_id=' . $video->id . '" title="delete" alt="delete">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/delete.svg" class="action_thumb">
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/edit.php?video_id=' . $video->id .'" title="edit" alt="edit">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/pencil.svg" class="action_thumb">
        </a>
        <a href="' . $CFG->wwwroot . '/local/video_directory/upload_subs.php?id=' .
        $video->id .'" title="subtitles upload" alt="subtitles upload">
            <img src="' . $CFG->wwwroot . '/local/video_directory/pix/subs';

        if (!isset($video->subs)) {
            $video->actions .= "_grey";
        }

        $video->actions .= '.svg" class="action_thumb">
        </a>
        ' . $playbutton;
    }

    $video->streaming_url = '<a target="_blank" href="' . $video->streaming_url .'" >' . $video->streaming_url . '</a><br>';
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
        $video->private = '<input type="checkbox" class="checkbox ajax_edit" id="private_' . $video->id . '" ' . $checked . '>';
        $video->orig_filename = "<input type='text' class='hidden_input ajax_edit' id='orig_filename_" .
            $video->id . "' value='" . htmlspecialchars($video->orig_filename, ENT_QUOTES). "'>";
    }

    $videolist[] = $video;
}

echo json_encode($videolist, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
