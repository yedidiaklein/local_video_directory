<?php
// This file is icluded on all module files and has global variables that are needed all over

require_once( __DIR__ . '/../../config.php');

$settings = get_config('local_video_directory');
$shellcomponents = array('ffmpeg', 'ffprobe', 'php');
$iswin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

foreach ($shellcomponents as $sc) {
	if (isset($settings->$sc)) {
		$settings->$sc = ($iswin && isset($settings->{$sc . 'drive'}) && preg_match('~^[a-z]$~', $settings->{$sc . 'drive'}) ? $settings->{$sc . 'drive'} . ":" . (strpos($settings->$sc, '/') === 0 ? '' : '/') : '') . $settings->$sc;
	}
}

if (!CLI_SCRIPT) {
	require_login();

	//check if user belong to the cohort or is admin
	require_once($CFG->dirroot.'/cohort/lib.php');
	
	if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
		die("Access Denied. You must be a member of the designated cohort. Please see your site admin.");
	}
}

// Variables for this plugin

$uploaddir = $CFG->dataroot.'/videos/';
if (!file_exists($uploaddir)) {
   	mkdir($uploaddir, 0777, true);
}

$converted = $CFG->dataroot . '/videos/converted/';
if (!file_exists($converted)) {
   	mkdir($converted, 0777, true);
}

$massdir = $CFG->dataroot . '/videos/mass/';
if (!file_exists($massdir)) {
   	mkdir($massdir, 0777, true);
}

$wgetdir = $CFG->dataroot . '/videos/wget/';
if (!file_exists($wgetdir)) {
   	mkdir($wgetdir, 0777, true);
}

