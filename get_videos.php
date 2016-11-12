<?php

require_once( __DIR__ . '/../../config.php');

$keyword = optional_param('keyword',0, PARAM_RAW);
$search = optional_param('search',0, PARAM_RAW);

$query = ' SELECT v.id, streaming_url, orig_filename,thumb as thumbnail, CONCAT(firstname," ",lastname) as name
												FROM {local_video_directory} v 
												LEFT JOIN {user} u on v.owner_id = u.id ';

if ($keyword && ($search == "description")) {
	$where = " WHERE orig_filename LIKE '%".$keyword."%' ";
} elseif ($keyword && ($search == "tags")) {
	$where = ' LEFT JOIN {tag_instance} ti on v.id=ti.itemid 
												LEFT JOIN {tag} t on ti.tagid=t.id 
												WHERE ti.itemtype="local_video_directory" and t.name LIKE "%' . $keyword . '%" '; 
//												GROUP by id');	
} else  {
	$where = "";
}

//file_put_contents("/var/www/le-mood.openapp.co.il/moodledata/log", $query . $where . ' AND convert_status = :convert_status' ,FILE_APPEND);


	$videos = $DB->get_records_sql($query . $where . 
							' AND convert_status = :convert_status' ,
							array("convert_status" => "3"));



echo json_encode($videos);