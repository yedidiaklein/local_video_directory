<?php
// This file is icluded on all module files and has global variables that are needed all over

require_once( __DIR__ . '/../../config.php');

$settings = get_config('local_video_directory');

if (!CLI_SCRIPT) {
	require_login();

	//check if user belong to the cohort or is admin
	require_once($CFG->dirroot.'/cohort/lib.php');
	if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
		die("Access Denied (This Incident will be Reported....)");
	}
}

// Variables for this plugin

$uploaddir = $CFG->dataroot.'/videos/';
if (!file_exists($uploaddir)) {
   	mkdir($uploaddir, 0777, true);
}

$converted = $CFG->dataroot.'/videos/converted/';
if (!file_exists($converted)) {
   	mkdir($converted, 0777, true);
}

$massdir = $CFG->dataroot . '/videos/mass/';
if (!file_exists($massdir)) {
   	mkdir($massdir, 0777, true);
}

$wgetdir = $CFG->dataroot.'/videos/wget/';
if (!file_exists($wgetdir)) {
   	mkdir($wgetdir, 0777, true);
}

