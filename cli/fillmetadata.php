<?php
define('CLI_SCRIPT',1);
require_once( __DIR__ . '/../../../config.php');
require_once( __DIR__ . '/../locallib.php');

$settings = get_settings();
$dirs = get_directories();
$converted = $dirs['converted'];

$ffprobe = $settings -> ffprobe;


$videos = $DB->get_records('local_video_directory',array("height" => NULL));
foreach ($videos as $video) {

			if (file_exists($converted . $video->id . ".mp4")) {
				echo "processing :".$video->id."\n";
				$metadata=array();
				$metafields = array("height" => "stream=height", "width" => "stream=width", "size" => "format=size");
				foreach ($metafields as $key => $value) {
					$metadata[$key] = exec($ffprobe . " -v error -show_entries " . $value . " -of default=noprint_wrappers=1:nokey=1 " . $converted . $video->id . ".mp4"); 
				}
	
				// update that converted and streaming URL
				$record = array("id" => $video->id, 
							"height" => $metadata['height'],
							"width" => $metadata['width'],
							"size" => $metadata['size'],
							"timecreated" => time(),
							"timemodified" => time(),
							'uniqid' => uniqid('', true)
							);

				$update = $DB->update_record("local_video_directory",$record);
				print_r($record);
			}
}
