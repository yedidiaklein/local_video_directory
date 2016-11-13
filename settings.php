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

//require_once($CFG->dirroot.'/cohort/lib.php');

//$cohorts = cohort_get_all_cohorts();
//print_r($cohorts['cohorts']);

// Ensure the configurations for this site are set
if ( $hassiteconfig ){
 
	// Create the new settings page
	// - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
	// $settings will be NULL
	$settings = new admin_settingpage( 'local_video_directory', 'Video System Settings' );
 
 
	// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/ffmpeg',
 
		// This is the friendly title for the config, which will be displayed
		'Ffmpeg Path',
 
		// This is helper text for this config field
		'Please insert here local ffmpeg path',
 
		// This is the default value
		'/usr/bin/ffmpeg',
 
		// This is the type of Parameter this config is
		PARAM_SAFEPATH
 
	) );
 

	// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/ffprobe',
 
		// This is the friendly title for the config, which will be displayed
		'Ffprobe Path',
 
		// This is helper text for this config field
		'Please insert here local ffprobe path',
 
		// This is the default value
		'/usr/bin/ffprobe',
 
		// This is the type of Parameter this config is
		PARAM_SAFEPATH
 
	) );


	// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/php',
 
		// This is the friendly title for the config, which will be displayed
		'php path',
 
		// This is helper text for this config field
		'Please insert here local php path',
 
		// This is the default value
		'/usr/bin/php',
 
		// This is the type of Parameter this config is
		PARAM_SAFEPATH
 
	) );


	// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/streaming',
 
		// This is the friendly title for the config, which will be displayed
		'Streaming Server URL',
 
		// This is helper text for this config field
		'Please Insert Here your Streaming Server URL Including Path',
 
		// This is the default value
		'http://' . $CFG->wwwroot . '/streaming',
 
		// This is the type of Parameter this config is
		PARAM_URL
 
	) ); 
 
	// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/ffmpeg_settings',
 
		// This is the friendly title for the config, which will be displayed
		'Ffmpeg Parameters',
 
		// This is helper text for this config field
		'For Advanced Users - Ffmpeg conversion parameters',
 
		// This is the default value
		'-strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart',
 
		// This is the type of Parameter this config is
		PARAM_TEXT
 
	) );
	
		// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/thumbnail_seconds',
 
		// This is the friendly title for the config, which will be displayed
		'Thumbnail Seconds',
 
		// This is helper text for this config field
		'Seconds From Video Starting for Default Thumbnail',
 
		// This is the default value
		'5',
 
		// This is the type of Parameter this config is
		PARAM_INT
 
	) ); 

		// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/df',
 
		// This is the friendly title for the config, which will be displayed
		'Alert on Low Free Disk Space (Bytes)',
 
		// This is helper text for this config field
		'Show the Free Disk Space in Red (Bytes)',
 
		// This is the default value
		'1000000000',
 
		// This is the type of Parameter this config is
		PARAM_INT
 
	) ); 

		// Add a setting field to the settings for this page
	$settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_video_directory/cohort',
 
		// This is the friendly title for the config, which will be displayed
		'Cohort ID of allowed users',
 
		// This is helper text for this config field
		'You should create a cohort, and set its ID here',
 
		// This is the default value
		'1',
 
		// This is the type of Parameter this config is
		PARAM_INT
 
	) ); 


	// Create 
	$ADMIN->add( 'localplugins', $settings );

 
}

