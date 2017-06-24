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
 * This file save changes on video name (using ajax).
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('init.php');
defined('MOODLE_INTERNAL') || die();

$PAGE->set_context(context_system::instance());

$id = required_param('id', PARAM_INT);
$status = optional_param('status', 0, PARAM_BOOL);
$value  = optional_param('value', "", PARAM_RAW);
$field  = required_param('field', PARAM_RAW);

if ($value != "") {
    $record = array("id" => $id, $field => urldecode($value));
} else {
    $record = array("id" => $id, "private" => (int)$status);
}

if ($update = $DB->update_record("local_video_directory", $record)) {
    echo '1';
}