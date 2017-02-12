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
 * You may have settings in your plugin
 *
 * @package    local_video_directory
 * @copyright  2016 OpenApp http://openapp.co.il
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ( $hassiteconfig ){
	$settings = new admin_settingpage( 'local_video_directory', 'Video System Settings' );

	$settings->add( new admin_setting_configtext(
		'local_video_directory/ffmpeg',
		'Ffmpeg Path',
		'Please enter the path to your local ffmpeg executable files including the executable filename itself. Windows users note: forward slashes are converted to backslashes.',
		'/usr/bin/ffmpeg',
		PARAM_PATH
	));

	$settings->add( new admin_setting_configtext(
		'local_video_directory/ffprobe',
		'Ffprobe Path',
		'Please enter the path to your local ffprobe executable file including the executable filename itself. Windows users note: backslashes are converted to forward slashes.',
		'/usr/bin/ffprobe',
		PARAM_PATH
	));

	$settings->add( new admin_setting_configtext(
		'local_video_directory/php',
		'php path',
		'Please enter the path to your local php executable file including the executable filename itself. Windows users note: backslashes are converted to forward slashes.',
		'/usr/bin/php',
		PARAM_PATH
	));

	$settings->add( new admin_setting_configtext(
		'local_video_directory/streaming',
		'Streaming Server URL',
		'Please enter Here your Streaming Server URL Including Path',
		$CFG->wwwroot . '/streaming',
		PARAM_URL
 
	)); 
 
	// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
		'local_video_directory/ffmpeg_settings',
		'Ffmpeg Parameters',
		'For Advanced Users - Ffmpeg conversion parameters',
		'-strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart',
		PARAM_TEXT
 
	) );
	$settings->add( new admin_setting_configtext(
		'local_video_directory/thumbnail_seconds',
		'Thumbnail Seconds',
		'Seconds From Video Starting for Default Thumbnail',
		'5',
		PARAM_INT
	)); 

	$settings->add( new admin_setting_configtext(
		'local_video_directory/df',
		'Alert on Low Free Disk Space (Bytes)',
		'Show the Free Disk Space in Red (Bytes)',
		'1000000000',
		PARAM_INT
	)); 

	$settings->add( new admin_setting_configtext(
		'local_video_directory/cohort',
		'Cohort ID of allowed users',
		'You should create a cohort, and set its ID here',
		'1',
		PARAM_INT
	)); 

	// Create 
	$ADMIN->add( 'localplugins', $settings );
}
