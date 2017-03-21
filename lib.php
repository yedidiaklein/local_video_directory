<?php

function local_video_directory_cron() {
 
	global $CFG , $DB;

	include_once( $CFG->dirroot . "/local/video_directory/init.php");

	$ffmpeg = $settings->ffmpeg;
	$streaming_url = $settings->streaming.'/';
	$ffprobe = $settings->ffprobe;
	$ffmpeg_settings = $settings->ffmpeg_settings;
	$thumbnail_seconds = $settings->thumbnail_seconds;
	$php = $settings->php;

	$orig_dir = $uploaddir;
	$streaming_dir = $converted;

	// check if we've to convert videos
	$videos = $DB->get_records('local_video_directory', array("convert_status" => 1));
	// Move all video that have to be converted to Waiting.. state (4) just to make sure that there is not multiple cron that converts same files
	$wait = $DB->execute('UPDATE {local_video_directory} SET convert_status = 4 WHERE convert_status = 1');
	foreach ($videos as $video) {
		// update convert_status to 2 (Converting....)
		$record = array("id" => $video->id, "convert_status" => "2");
		$update = $DB->update_record("local_video_directory",$record);
		$convert = '"' . $ffmpeg . '"' . " -i ". $orig_dir . $video->id . ' ' . $ffmpeg_settings . ' ' . $streaming_dir . $video->id . ".mp4";
		exec( $convert );

		// Check if was converted
		if (file_exists($streaming_dir . $video->id . ".mp4")) {
			// Get Video Thumbnail
			if (is_numeric($thumbnail_seconds)) {
				$timing = gmdate("H:i:s", $thumbnail_seconds);
			} else {
				$timing = "00:00:05";
			}
			$thumb = '"' . $ffmpeg . '"' . " -i ". $orig_dir . $video->id . " -ss " . $timing . " -vframes 1 " . $streaming_dir . $video->id . ".png";
			$thumb_mini = '"' . $ffmpeg . '"' . " -i ". $orig_dir . $video->id . " -ss " . $timing . " -vframes 1 -vf scale=100:-1 " . $streaming_dir . $video->id . "-mini.png";

			exec( $thumb );
			exec( $thumb_mini );

			//get video length
			$length_cmd = $ffprobe ." -v error -show_entries format=duration -sexagesimal -of default=noprint_wrappers=1:nokey=1 " . $streaming_dir . $video->id . ".mp4";
			$length_output = exec( $length_cmd );
			// remove data after .
			$array_length = explode(".", $length_output);
			$length = $array_length[0];

			// update that converted and streaming URL
			$record = array("id" => $video->id, 
							"convert_status" => "3", 
							"streaming_url" => $streaming_url . $video->id . ".mp4", 
							"filename" => $video->id . ".mp4",
							"thumb" => $video->id,
							"length" => $length
							);


			$update = $DB->update_record("local_video_directory",$record);
		} else {
			// update that converted and streaming URL
			$record = array("id" => $video->id, "convert_status" => "5");
			$update = $DB->update_record("local_video_directory",$record);			
		}
		//delete original file
		unlink($orig_dir . $video->id);
	}

	// take care of wget table
	$wgets = $DB->get_records('local_video_directory_wget', array("success" => 0));
	
	if ($wgets) {
		foreach ($wgets as $wget) {
			$record = array('id' => $wget->id,'success' => 1);
			$update = $DB->update_record("local_video_directory_wget",$record);
			exec($php . ' ' . $CFG->dirroot . '/local/video_directory/scripts/wget.php ' . base64_encode($wget->url) . ' &');
		}
	}
}

function local_video_directory_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE, $USER;

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        require_once($CFG->dirroot.'/cohort/lib.php');
        $settings=get_config('local_video_directory');

		if (!cohort_is_member($settings->cohort, $USER->id) && !is_siteadmin($USER)) {
			return ;
		}
        
        $strfather = get_string('pluginname', 'local_video_directory');
        $fathernode = navigation_node::create(
            $strfather,
            null,
            navigation_node::NODETYPE_BRANCH,
            'local_video_directory_father',
            'local_video_directory_father'
        );

        $settingnode->add_node($fathernode);
        $strlist = get_string('list', 'local_video_directory');
        $url = new moodle_url('/local/video_directory/list.php', array('id' => $PAGE->course->id));
        $listnode = navigation_node::create(
            $strlist,
            $url,
            navigation_node::NODETYPE_LEAF,
            'local_video_directory_list',
            'local_video_directory_list',
            new pix_icon('f/avi-24', $strlist)
        );
		
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $listnode->make_active();
        }
        
        $strupload = get_string('upload', 'local_video_directory');
        $urlupload = new moodle_url('/local/video_directory/upload.php', array('id' => $PAGE->course->id));
        $uploadnode = navigation_node::create(
            $strupload,
            $urlupload,
            navigation_node::NODETYPE_LEAF,
            'local_video_directory_upload',
            'local_video_directory_upload',
            new pix_icon('t/addcontact', $strupload)
        );
		
        if ($PAGE->url->compare($urlupload, URL_MATCH_BASE)) {
            $uploadnode->make_active();
        }

        $fathernode->add_node($listnode);
        $fathernode->add_node($uploadnode);
    }
}
