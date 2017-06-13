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
 * Script for wgeting videos.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', 1);

require_once( __DIR__ . '/../init.php');

$url = $argv[1];

if (strlen($url) < 7) {
    die('Invalid url');
}

$url = base64_decode($url);
$filename = basename($url);

file_put_contents($wgetdir . $filename, fopen($url, 'r'));

// move to mass directory once downloaded
if (copy($wgetdir . $filename, $massdir . $filename)) {
    unlink($wgetdir.$filename);
    $sql = "UPDATE {local_video_directory_wget} SET success = 2 WHERE url = ?";
    $DB->execute($sql, array($url));
}
