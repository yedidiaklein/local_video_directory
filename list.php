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
require_login();
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');

$settings = get_settings();
if (!CLI_SCRIPT) {
    require_login();

    // Check if user have permissionss.
    $context = context_system::instance();
    if (!has_capability('local/video_directory:video', $context) && !is_video_admin($USER)) {
        die("Access Denied. You must get rights... Please see your site admin.");
    }

}

$tags = optional_param('tag', 0, PARAM_RAW);
$tc = optional_param('tc', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_RAW);
$group = optional_param('group', 0, PARAM_RAW);
$category = optional_param('category', 0, PARAM_RAW);

if (!property_exists($SESSION,'groups')) {
    $SESSION->groups = [];
}

if (!property_exists($SESSION,'categories')) {
    $SESSION->categories = [];
}

if ($action) {
    if ($action == "addgroup") {
        $SESSION->groups[$group] = ['name' => $group];
    } else if ($action == "removegroup") {
        unset($SESSION->groups[$group]);
    } else if ($action == "addcategory") {
        $SESSION->categories[$category] = ['id' => $category];
    } else if ($action == "removecategory") {
        unset($SESSION->categories[$category]);
    } 
}

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

$PAGE->requires->js('/local/video_directory/datatables/jquery.dataTables.js');
$PAGE->requires->css('/local/video_directory/datatables/jquery.dataTables.min.css');

require($CFG->libdir . '/jquery/plugins.php'); // Just populates the variable "$plugins" in the next line.
$PAGE->requires->css('/lib/jquery/' . $plugins['ui-css']['files'][0]);

// Include font awesome in case of moodle 32 and older.
if ($CFG->branch < 33) {
    $PAGE->requires->css('/local/video_directory/font_awesome/css/all.min.css');
}

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

$alltagsurl = array();

foreach ($alltags as $key => $value) {
    array_push($alltagsurl, array('name' => $key, 'url' => urlencode($key)));
}
$selectedtags = array();
if (!isset($SESSION->video_tags)) {
    $SESSION->video_tags = array();
}

if (is_array($SESSION->video_tags)) {
    if ((count($SESSION->video_tags) > 0)) {
        foreach ($SESSION->video_tags as $key => $value) {
            array_push($selectedtags, array('name' => $value, 'url' => urlencode($value)));
        }
    }
}

$fields = $settings->fieldorder;
$fields = explode(",", $fields);
$fieldsv = [];
$liststrings = [];
foreach ($fields as $key => $value) {
    if (!(
        ($settings->group == 'none' && trim($value) == 'usergroup') || 
        ($settings->embedcolumn == 0 && trim($value) == 'streaming_url')
        )) {
            $fieldsv[$key]['name'] = trim($value);
            array_push($liststrings, get_string(trim($value), 'local_video_directory'));            
        }
} 

$groups = local_video_get_groups($settings);
$g = [];
if ($groups) {
    foreach ($groups as $key => $value) {
        $g[$key]['name'] = $value;
    }
}

$cats = $DB->get_records('local_video_directory_cats', [], 'id', 'id, cat_name');
foreach ($cats as $cat) {
    $allcats[$cat->id] = $cat->cat_name;
}
$selectedcats = array();
foreach ($SESSION->categories as $cat) {
    $selectedcats[$cat['id']] = ['id' => $cat['id'] ,'name' => $allcats[$cat['id']]];
} 

echo $OUTPUT->render_from_template('local_video_directory/list',
 ['wwwroot' => $CFG->wwwroot, 'alltags' => $alltagsurl, 'existvideotags' => is_array($SESSION->video_tags),
 'videotags' => $selectedtags, 'liststrings' => $liststrings, 
 'lang' => current_language(), 'fields' => array_values($fieldsv),
 'showgroupcloud' => $settings->groupcloud, 'groups' => array_values($g),
 'existselectedgroups' => count($SESSION->groups), 'selectedgroups' => array_values($SESSION->groups),
 'showcatscloud' => $settings->catscloud, 'categories' => array_values($cats),
 'existselectedcats' => count($SESSION->categories), 'selectedcats' => array_values($selectedcats),
 'tags' => $tags
 ]);
echo $OUTPUT->render_from_template('local_video_directory/player', []);
echo $OUTPUT->footer();