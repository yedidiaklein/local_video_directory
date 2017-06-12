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
 * You may localized strings in your plugin
 *
 * @package    local_video
 * @copyright  2016 OpenApp
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */

// This script is usefull for running manually the cron of video conversion w/o running moodle cron
// use in this way : 
// # su - www-data -s "/bin/bash" -c "php /var/www/html/moodle/local/video_directory/manualcron.php"
// (this example assume web is running by www-data [debian/ubuntu] change to apache in redhat/centos).
define('CLI_SCRIPT',1);
require_once( __DIR__ . "/../../config.php");
require('lib.php');

local_video_directory_cron();
