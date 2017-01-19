<?php

require_once('init.php');
 
$tags = optional_param('tag',0, PARAM_RAW);
if ($tags != '') {
	$SESSION->video_tags = explode(',',$tags);
} else {
	$SESSION->video_tags = 'Gurnisht';
}

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('player','local_video_directory'));
$PAGE->set_title(get_string('player','local_video_directory'));
$PAGE->set_url('/local/video_directory/player.php');
$PAGE->navbar->add(get_string('pluginname','local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('player','local_video_directory'));

$PAGE->requires->css('/local/video_directory/style.css');

$streaming_url = 		$settings -> streaming.'/';

echo $OUTPUT->header();

//Menu
include('menu.php');

if ((isset ($SESSION->video_tags)) && (is_array($SESSION->video_tags))) {
	$list=implode('","',$SESSION->video_tags);
	$list='"'.$list.'"';
	
	$videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' AS name 
												FROM {local_video_directory} v 
												LEFT JOIN {user} u on v.owner_id = u.id 
												LEFT JOIN {tag_instance} ti on v.id=ti.itemid 
												LEFT JOIN {tag} t on ti.tagid=t.id 
												WHERE ti.itemtype="local_video_directory" and t.name in (' . $list . ') 
												GROUP by id ORDER BY id');
} else {
	$videos = $DB->get_records_sql('SELECT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) . ' AS name FROM {local_video_directory} v LEFT JOIN {user} u on v.owner_id = u.id ORDER BY v.id');
}


echo '<existing_tags>' . get_string('existing_tags','local_video_directory').':';
//find all movies tags
$alltags=$DB->get_records_sql('select distinct name from {tag_instance} ti LEFT JOIN {tag} t on ti.tagid=t.id where itemtype="local_video_directory" order by name');

echo '<span class="tag_list hideoverlimit videos">
    <ul class="inline-list">';

foreach ($alltags as $key => $value) {
	echo '<li>
                <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?from=player&action=add&tag=' . $key . '" class="label label-info ">+ ' . $key . '</a>
          </li>	'; 
}
echo '</ul></span></existing_tags>';

if (is_array($SESSION->video_tags)) {
	echo '<selected_tags>' . get_string('selected_tags','local_video_directory').':';
	echo '<span class="tag_list hideoverlimit videos">
    <ul class="inline-list">';

	foreach ($SESSION->video_tags as $key => $value) {
		echo '<li>
                <a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?from=player&action=remove&tag=' . $value . '" class="label label-info ">X ' . $value . '</a>
          </li>	'; 
	}
	echo '</ul></span></selected_tags>';
}

?>
<div id="playing"></div>
<div id="playlist_container">
<div id="player">
<video id="video" controls preload="auto" width="100%" height="500px">
</video>
</div>
<div id="player_list">
	<div id="plays">
<?php

	foreach ($videos as $video) {
		if (!isset($first)) {
			$first = $video->id;
			$first_title=$video->orig_filename;
		}
		$videotags=$DB->get_records_sql('select distinct name from {tag_instance} ti LEFT JOIN {tag} t on ti.tagid=t.id where itemtype="local_video_directory" and itemid=' . $video->id . ' order by name');
		$tagshtml = '<ul class="inline-list">';
		foreach ($videotags as $key => $value) {
			$tagshtml .= '<li><a href="' . $CFG->wwwroot . '/local/video_directory/tag.php?from=player&action=add&tag=' . $key . '" class="label label-info ">' . $key . '</a></li>	'; 
		}
		$tagshtml .= '</ul>';
		$video->thumb = str_replace(".png","-mini.png",$video->thumb);
		echo '<div onclick="playVideoByID(' . $video->id . ',\'' . $video->orig_filename . '\')" class="single_in_player">' .
			'<span class="image_container"><img src="' . $video -> thumb . '" class="thumb"><div class="length">'.
			$video->length . '</div></span>' .
			'<video_title>' . $video -> orig_filename . '</video_title>' . 
			'<br>' . $video -> name .   '<br>' .
			$tagshtml . 
			'</div>';
	}
?>	

</div></div></div>
<script type="text/javascript">

function playvideo() {
<?php
	echo "source.setAttribute('src', '" . $streaming_url . $first . ".mp4');";
?>
	video.appendChild(source);
	//video.play();
	var playing = document.getElementById('playing');
<?php
	echo "playing.innerHTML = '<h2>" . $first_title . "</h2>';";
?>
}

function playVideoByID(id,title) {  

    video.pause();
<?php
   	echo "source.setAttribute('src', '" . $streaming_url . "' + id + '.mp4');"; 
?>
    video.load();
    video.play();
   	var playing = document.getElementById('playing');
	playing.innerHTML = '<h2>' + title + '</h2>';

}

var video = document.getElementById('video');
var source = document.createElement('source');
playvideo();
</script>
<?php

echo $OUTPUT->footer();
