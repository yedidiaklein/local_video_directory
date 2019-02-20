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
 * Mass upload of videos from directory on server.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();

$id = required_param('video_id', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('studio', 'local_video_directory'));
$PAGE->set_title(get_string('studio', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/studio.php?video_id=' . $id);
$PAGE->navbar->add(get_string('pluginname', 'local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('studio', 'local_video_directory'));
$PAGE->set_pagelayout('base');
$PAGE->requires->css('/local/video_directory/style.css');

$context = context_system::instance();
if (!has_capability('local/video_directory:video', $context) && !is_video_admin($USER)) {
    die("Access Denied. You must get rights... Please see your site admin.");
}

$videoname = $DB->get_field('local_video_directory', 'orig_filename', ['id' => $id]);
echo $OUTPUT->header();

echo $OUTPUT->heading("<font color='red'>*** BETA ***</font> " . get_string('studio', 'local_video_directory') .
                        ' - <span class="videoname">' . $videoname . '</span>');

$tools = [ 
        ['name' => 'crop', 'description' => get_string('crop', 'local_video_directory')] ,
        ['name' => 'merge', 'description' => get_string('merge', 'local_video_directory')] ,
        ['name' => 'cut', 'description' => get_string('cut', 'local_video_directory')] ,
        ['name' => 'cat', 'description' => get_string('cat', 'local_video_directory')] ,
    ];

$tasks_crop = local_video_directory_studio_tasks($id, 'crop', ['startx', 'starty', 'endx', 'endy']);
$tasks_merge = local_video_directory_studio_tasks($id, 'merge', ['video_id_small', 'height', 'border']);
$tasks_cut = local_video_directory_studio_tasks($id, 'cut', ['secbefore', 'secafter']);
$tasks_cat = local_video_directory_studio_tasks($id, 'cat', ['video_id_cat']);


$tasks = array_merge($tasks_crop, $tasks_merge, $tasks_cut, $tasks_cat);

echo $OUTPUT->render_from_template("local_video_directory/studio",
    array('tools' => array_values($tools), 'tasks' => array_values($tasks) , 'id' => $id, 'taskscount' => count($tasks)));

echo $OUTPUT->footer();