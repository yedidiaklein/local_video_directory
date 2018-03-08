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
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');

$settings = get_settings();
if (!CLI_SCRIPT) {
    require_login();

    // Check if user have permissionss.
    $context = context_system::instance();
    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        die("Access Denied. You must get rights... Please see your site admin.");
    }

}

$tags = optional_param('tag', 0, PARAM_RAW);
$tc = optional_param('tc', 0, PARAM_INT);

if ($tc == 1) {
    redirect($CFG->wwwroot . "/local/video_directory/tag.php?action=add&tag=".$tags);
}

if ($tags == '') {
    $SESSION->video_tags = ' - ';
}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('list', 'local_video_directory'));
$PAGE->set_title(get_string('list', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/list.php');
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('list', 'local_video_directory'));
$PAGE->set_pagelayout('base');

$PAGE->requires->js(new moodle_url('https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js'));
$PAGE->requires->css(new moodle_url('https://cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css'));

// Include font awesome in case of moodle 32 and older.
if ($CFG->branch < 33) {
    $PAGE->requires->css(new moodle_url('https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'));
}

$PAGE->requires->js('/local/video_directory/js/list.js');
$PAGE->requires->css('/local/video_directory/style.css');


// Table headers.
$PAGE->requires->strings_for_js(array('id', 'private', 'streaming_url', 'owner', 'orig_filename', 'convert_status'),
    'local_video_directory');

echo $OUTPUT->header();

// Menu.
require('menu.php');

// Find all movies tags.
$alltags = $DB->get_records_sql('SELECT DISTINCT name
FROM {tag_instance} ti
LEFT JOIN {tag} t
ON ti.tagid=t.id
WHERE itemtype = \'local_video_directory\'
ORDER BY name');

/*echo '<div id="tools" class="local_video_directory_tools"><button id="datatable_ajax_reload" class="btn btn-default">' .
        get_string('reload', 'local_video_directory') . '</button>';

echo ' <button id="datatable_ajax_clear_tags" class="btn btn-default">' .
        get_string('show_all', 'local_video_directory') . '</button>';

echo '<div class="local_video_directory_existing_tags">' . get_string('existing_tags', 'local_video_directory').':';

echo '<span class="tag_list hideoverlimit local_video_directory_videos">
    <ul class="inline-list">';*/
$alltagsurl = array();

foreach ($alltags as $key => $value) {
    // echo '<li>
    // <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?action=add&tag=' .
    // urlencode($key) . '" class="label label-info ">+ ' . $key . '</a>
    // </li>';

    array_push($alltagsurl, array('name' => $key, 'url' => urlencode($key)));
    // $value = array('value'=>$value, 'url'=>urlencode($key));

}
// print_r($alltagsurl);die;
// echo '</ul></span></div>';
$selectedtags = array();
if (is_array($SESSION->video_tags)) {
    // echo '<div class="local_video_directory_selected_tags">' . get_string('selected_tags', 'local_video_directory').':';
    // echo '<span class="tag_list hideoverlimit local_video_directory_videos">
    // <ul class="inline-list">';


    foreach ($SESSION->video_tags as $key => $value) {
        // echo '<li>
        // <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?action=remove&tag=' .
        // urlencode($value) . '" class="label label-info "> &times; ' . $value . '</a>
        // </li>';
        array_push($selectedtags, array('name' => $value, 'url' => urlencode($value)));
        // $value = array('value'=>$value, 'url'=>urlencode($value));
    }
    // echo '</ul></span></div>';
}
/* after php
</div>
<table id="video_table" class="local_video_directory_video-table display" cellspacing="0">
    <thead>
        <tr>*/
?>


<?php
$listheaders = array('actions', 'thumb', 'id', 'owner', 'orig_filename', 'length',
                    'convert_status', 'private', 'streaming_url', 'tags');
$liststrings = array();
foreach ($listheaders as $liststring) {
            array_push($liststrings, get_string($liststring, 'local_video_directory'));
}

/*
        </tr>
    </thead>
    <tbody></tbody>
</table>

<div id='video_player' class="local_video_directory_video-player" style='display:none'>
    <a href=# class='close' onclick='local_video_directory.close_player();'>
        &times; <?php echo get_string('close', 'local_video_directory'); ?>
    </a>
    <br>

    <video id="my-video" class="local_video_directory_my-video" controls preload="auto"></video>

</div>


*/
echo $OUTPUT->render_from_template('local_video_directory/list',
 ['wwwroot' => $CFG->wwwroot, 'alltags' => $alltagsurl, 'existvideotags' => is_array($SESSION->video_tags),
 'videotags' => $selectedtags, 'liststrings' => $liststrings]);

echo $OUTPUT->footer();

