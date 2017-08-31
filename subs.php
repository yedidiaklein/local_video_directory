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
 * Distribute subtitles to played video.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

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

$id = required_param('video_id', PARAM_INT);
$download = optional_param('download', 0, PARAM_INT);
$language = optional_param('language', 0, PARAM_RAW);
header("Content-type: text/vtt");
if ($download) {
    header('Content-Disposition: attachment; filename="' . $id . '.vtt"');
}
$dirs = get_directories();
readfile($dirs['subsdir'] . $id . ($language ? "-" . $language : '') . ".vtt");
