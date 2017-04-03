<?php
defined('MOODLE_INTERNAL') || die();

require_once('init.php');

$PAGE->set_context(context_system::instance());

$id = required_param('id', PARAM_INT);
$status = optional_param('status', 0 ,PARAM_BOOL);
$value  = optional_param('value', "" ,PARAM_RAW);
$field  = required_param('field', PARAM_RAW);

if ($value != "") {
	$record = array("id" => $id, $field => urldecode($value));	
} else {
	$record = array("id" => $id, "private" => (int)$status);
}

if ($update = $DB->update_record("local_video_directory",$record)) {
	echo '1';
}