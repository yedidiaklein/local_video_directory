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
 * Tag selection redirect.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();
require_once( __DIR__ . '/locallib.php');
$settings = get_settings();

if (!CLI_SCRIPT) {
    require_login();

    // Check if user have permissionss.
    $context = context_system::instance();

    if (!has_capability('local/video_directory:video', $context) && !is_siteadmin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }
}

$tag = required_param('tag', PARAM_RAW);
$action = required_param('action', PARAM_RAW);
$from = optional_param('from', 'list', PARAM_RAW);


if ($action == 'add') {
    if (is_array($SESSION->video_tags)) {
        if (array_search($tag, $SESSION->video_tags) === false) {
            $SESSION->video_tags[] = $tag;
        }
    } else {
        $SESSION->video_tags = array($tag);
    }
} else if ($action == 'remove') {
    if (($key = array_search($tag, $SESSION->video_tags)) !== false) {
        unset($SESSION->video_tags[$key]);
    }
}

$list = implode(",", $SESSION->video_tags);

redirect($CFG->wwwroot . '/local/video_directory/' . $from . '.php?tag='.$list);
