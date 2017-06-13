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
 * This file has global settings and is included in all module pages.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This file is icluded on all module files and has global variables that are needed all over.

require_once( __DIR__ . '/../../config.php');

$settings = get_config('local_video_directory');
$shellcomponents = array('ffmpeg', 'ffprobe', 'php');
$iswin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

foreach ($shellcomponents as $sc) {
    if (isset($settings->$sc)) {
        $settings->$sc = ($iswin && isset($settings->{$sc . 'drive'}) && preg_match('~^[a-z]$~', 
        $settings->{$sc . 'drive'}) ? $settings->{$sc . 'drive'} .
        ":" . (strpos($settings->$sc, '/') === 0 ? '' : '/') : '') .
        ($iswin ? str_replace('/', DIRECTORY_SEPARATOR, $settings->$sc) : $settings->$sc);
    }
}

if (!CLI_SCRIPT) {
    require_login();

    // Check if user belong to the cohort or is admin.
    require_once($CFG->dirroot.'/cohort/lib.php');
    
    if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
        die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
    }
}

// Directories for this plugin.
$dirs = array('uploaddir' => DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR,
              'converted' => DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'converted' . DIRECTORY_SEPARATOR,
                'massdir' => DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'mass' . DIRECTORY_SEPARATOR,
                'wgetdir' => DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'wget' . DIRECTORY_SEPARATOR,
               'multidir' => DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'multi' . DIRECTORY_SEPARATOR,
                'subsdir' => DIRECTORY_SEPARATOR . 'videos' . DIRECTORY_SEPARATOR . 'subs' . DIRECTORY_SEPARATOR);

foreach ($dirs as $key => $value) {
    // Add dataroot.
    $dirs[$key] = $CFG->dataroot.$value;
    // Create if doesn't exist.
    if (!file_exists($dirs[$key])) {
           mkdir($dirs[$key], 0777, true);
    }
    $$key = $dirs[$key];
}