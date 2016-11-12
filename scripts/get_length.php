<?php
define('CLI_SCRIPT',1);

require_once( __DIR__ . '/../init.php');

$orig_dir = $uploaddir;

$streaming_dir = $converted;

$ffmpeg = 				$settings -> ffmpeg;
$streaming_url = 		$settings -> streaming.'/';
$ffprobe = 				$settings -> ffprobe;
$ffmpeg_settings = 		$settings -> ffmpeg_settings;
$thumbnail_seconds = 	$settings -> thumbnail_seconds;
$php = 					$settings -> php;

$videos = $DB->get_records('local_video_directory',array("length" => NULL));
foreach ($videos as $video) {

			//get video length
			$length_cmd = $ffprobe ." -v error -show_entries format=duration -sexagesimal -of default=noprint_wrappers=1:nokey=1 " . $streaming_dir . $video->id . ".mp4";
			$length_output = exec( $length_cmd );
			// remove data after .
			$array_length = explode(".", $length_output);
			$length = $array_length[0];


	$record = array("id" => $video->id, "length" => $length);
	$update = $DB->update_record("local_video_directory",$record);
	echo "Video ".$video->id." updated to length ".$length."\n";
}
