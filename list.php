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

    // Check if user belong to the cohort or is admin.
    require_once($CFG->dirroot.'/cohort/lib.php');

    // Check if user have permissionss.
    $context = context_system::instance();
    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }

    // if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
    // die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    // }
}

$tags = optional_param('tag', 0, PARAM_RAW);

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

$PAGE->requires->js('/local/video_directory/js/list.js');
$PAGE->requires->css('/local/video_directory/style.css');


// Table headers.
$PAGE->requires->strings_for_js(array('id', 'private', 'streaming_url', 'owner', 'orig_filename', 'convert_status'),
    'local_video_directory');

echo $OUTPUT->header();

// Menu.
require('menu.php');

echo '<div id="tools" class="tools"><button id="datatable_ajax_reload" class="btn btn-default">' .
        get_string('reload', 'local_video_directory') . '</button>';

echo ' <button id="datatable_ajax_clear_tags" class="btn btn-default">' .
        get_string('show_all', 'local_video_directory') . '</button>';

echo '<div class="existing_tags">' . get_string('existing_tags', 'local_video_directory').':';
// Find all movies tags.
$alltags = $DB->get_records_sql('SELECT DISTINCT name
                                FROM {tag_instance} ti
                                LEFT JOIN {tag} t
                                ON ti.tagid=t.id
                                WHERE itemtype = \'local_video_directory\'
                                ORDER BY name');

echo '<span class="tag_list hideoverlimit videos">
    <ul class="inline-list">';

foreach ($alltags as $key => $value) {
    echo '<li>
                <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?action=add&tag=' .
                urlencode($key) . '" class="label label-info ">+ ' . $key . '</a>
          </li>';
}
echo '</ul></span></div>';

if (is_array($SESSION->video_tags)) {
    echo '<div class="selected_tags">' . get_string('selected_tags', 'local_video_directory').':';
    echo '<span class="tag_list hideoverlimit videos">
    <ul class="inline-list">';

    foreach ($SESSION->video_tags as $key => $value) {
        echo '<li>
                <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?action=remove&tag=' .
                urlencode($value) . '" class="label label-info "> &times; ' . $value . '</a>
          </li>';
    }
    echo '</ul></span></div>';
}

?>

</div>
<table id="video_table" class="video-table display" cellspacing="0">
    <thead>
        <tr>
<?php
$liststrings = array('actions', 'thumb', 'id', 'owner', 'orig_filename', 'length',
                    'convert_status', 'private', 'streaming_url', 'tags');

foreach ($liststrings as $liststring) {
            echo '<th>' . get_string($liststring, 'local_video_directory') . '</th>';
}
?>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<div id='video_player' class="video-player" style='display:none'>
    <a href=# class='close' onclick='local_video_directory.close_player();'>
        &times; <?php echo get_string('close', 'local_video_directory'); ?>
    </a>
    <br>

    <video id="my-video" class="my-video" controls preload="auto"></video>

</div>

<?php
echo $OUTPUT->footer();
