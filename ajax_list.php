<?php

require_once('init.php');

$PAGE->set_context(context_system::instance());

$videolist = array();

if (isset($SESSION->video_tags) && is_array($SESSION->video_tags)) {
	$list=implode('","',$SESSION->video_tags);
	$list='"'.$list.'"';
	if (is_siteadmin($USER)) {
		$videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' name 
												FROM {local_video_directory} v 
												LEFT JOIN {user} u on v.owner_id = u.id 
												LEFT JOIN {tag_instance} ti on v.id=ti.itemid 
												LEFT JOIN {tag} t on ti.tagid=t.id 
												WHERE ti.itemtype="local_video_directory" and t.name in (' . $list . ') 
												GROUP by id');
	} else {
		$videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' name 
												FROM {local_video_directory} v 
												LEFT JOIN {user} u on v.owner_id = u.id 
												LEFT JOIN {tag_instance} ti on v.id=ti.itemid 
												LEFT JOIN {tag} t on ti.tagid=t.id 
												WHERE ti.itemtype="local_video_directory" and t.name in (' . $list . ') 
												AND (owner_id ='.$USER->id.' OR (private IS NULL OR private = 0))
												GROUP by id');
		}
} else {
	if (is_siteadmin($USER)) {
		$videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' name FROM {local_video_directory} v LEFT JOIN {user} u on v.owner_id = u.id');
	} else {
		$videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' name FROM {local_video_directory} v LEFT JOIN {user} u on v.owner_id = u.id WHERE owner_id ='.$USER->id.' OR (private IS NULL OR private = 0)');
	}
}

foreach ($videos as $video) {
	if (is_numeric($video->convert_status)) {
		$video->convert_status = get_string('state_'.$video->convert_status,'local_video_directory');
	}
	
	$video->tags = str_replace('/tag/index.php','/local/video_directory/list.php',
		$OUTPUT->tag_list(core_tag_tag::get_item_tags('local_video_directory', 'local_video_directory', $video->id), "", 'videos'));
	
	$video->thumb = str_replace(".png","-mini.png",$video->thumb);
	$video->thumb = '<a href="' .$CFG->wwwroot . '/local/video_directory/thumbs.php?id=' . $video->id . '&length=' . $video->length . '"><img src="' . $video->thumb . '" class="thumb"></a>';
	
	
	if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
		$video -> actions = '<img id="play_video" onclick="play(\''.$video->streaming_url.'\')" " src="'. $CFG->wwwroot . '/local/video_directory/pix/play.svg" class="action_thumb">'; 
	} else { 
		$video->actions = '<a href="' . $CFG->wwwroot . '/local/video_directory/delete.php?video_id=' . $video->id .'" title="delete" alt="delete">' .
		'<img src="' . $CFG->wwwroot . '/local/video_directory/pix/delete.svg" class="action_thumb"></a> ' .
		'<a href="' . $CFG->wwwroot . '/local/video_directory/edit.php?video_id=' . $video->id .'" title="edit" alt="edit">' .
		'<img src="' . $CFG->wwwroot . '/local/video_directory/pix/pencil.svg" class="action_thumb"></a>
		<img id="play_video" onclick="play(\''.$video->streaming_url.'\')" " src="'. $CFG->wwwroot . '/local/video_directory/pix/play.svg" class="action_thumb">';
	}
	
	$video->streaming_url = '<a target="_blank" href="' . $video->streaming_url .'" >' . $video->streaming_url . '</a>';
	
	if ($video->private) $checked = "checked";
	else $checked = ""; 
		
	// do not allow non owner to edit privacy and title
	if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
	 	$video->private = '';
	} else { 
		$video -> private = '<input type="checkbox" class="checkbox ajax_edit" id="private_' . $video->id .'" '. $checked  .'>';
		$video -> orig_filename = "<input type='text' class='hidden_input ajax_edit' id='orig_filename_".$video->id."' value='". htmlspecialchars($video -> orig_filename, ENT_QUOTES). "'>";		
	}
	
	$videolist[] = $video;
}

echo json_encode($videolist, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
