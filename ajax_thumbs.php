<?php

require_once('init.php');

$ffmpeg = 			$settings -> ffmpeg;
$streaming_url = 		$settings -> streaming.'/';

$id = 		required_param('id',PARAM_INT);
$second = 	required_param('second',PARAM_INT);

$PAGE->set_context(context_system::instance());

$streaming_dir = $converted;

if (is_numeric($second)) {
	$timing = gmdate("H:i:s", $second);
} else {
	$timing = "00:00:05";
}

$thumb = $ffmpeg . " -i ". $streaming_dir . $id . ".mp4 -ss " . $timing . " -vframes 1  -vf scale=100:-1 " . $streaming_dir . $id . "-" . $second . ".png";
exec( $thumb );
if (file_exists($streaming_dir . $id . "-" . $second . ".png")) {
	 echo $streaming_url . $id . "-" . $second . ".png";
} else {
	echo $CFG->wwwroot . '/local/video_directory/pix/delete.png';
}
