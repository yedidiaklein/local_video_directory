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
require_once('includes/phpqrcode.php');

$id = required_param('id', PARAM_INT);

$uniqid = $DB->get_field('local_video_directory', 'uniqid', ['id' => $id]);

$embedurl = $CFG->wwwroot . '/local/video_directory/embed.php?id=' . $uniqid;

header('Content-Type: image/png');
QRcode::png($embedurl); // Creates code image and outputs it directly into browser.


