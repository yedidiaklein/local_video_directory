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
 * Video player.
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

$id = required_param('video_id', PARAM_RAW);

$dirs = get_directories();
$file = $dirs['converted'] . $id .".mp4";


$fp = @fopen($file, 'rb');
$size = filesize($file); // File size.
$length = $size; // Content length.
$start = 0; // Start byte.
$end = $size - 1; // End byte.
header('Content-type: video/mp4');
header("Accept-Ranges: 0-$length");
header("Accept-Ranges: bytes");
if (isset($_SERVER['HTTP_RANGE'])) {
    $cstart = $start;
    $cend   = $end;
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        header("X-Data: filename $file");
        exit;
    }
    if ($range == '-') {
        $cstart = $size - substr($range, 1);
    } else {
        $range = explode('-', $range);
        $cstart = $range[0];
        $cend = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
    }
    $cend = ($cend > $end) ? $end : $cend;
    if ($cstart > $cend || $cstart > $size - 1 || $cend >= $size) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        header("Content-Range: bytes $start-$end/$size");
        header("X-Data: filename $file");
        exit;
    }
    $start = $cstart;
    $end = $cend;
    $length = $end - $start + 1;
    fseek($fp, $start);
    header('HTTP/1.1 206 Partial Content');
}
header("Content-Range: bytes $start-$end/$size");
header("Content-Length: ".$length);
$buffer = 1024 * 8;
while (!feof($fp) && ($p = ftell($fp)) <= $end) {
    if ($p + $buffer > $end) {
        $buffer = $end - $p + 1;
    }
    set_time_limit(0);
    echo fread($fp, $buffer);
    ob_flush();
}
fclose($fp);
exit();
