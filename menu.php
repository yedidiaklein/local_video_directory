<?php
require_once('locallib.php');

$selected = basename($_SERVER['SCRIPT_NAME']);

$settings=get_config('local_video_directory');
echo get_string('freedisk','local_video_directory') . ' : ' . human_filesize(disk_free_space($CFG->dataroot),2,$settings->df);

echo "<videomenu><ul>";

$menu = array('list','player','upload','mass','wget');

foreach ($menu as $item) {
	if ($item . '.php' == $selected)
		echo '<li id="selected"><a href="' . $item . '.php">' . get_string($item,'local_video_directory') . '</a></li>';
	else 
		echo '<li><a href="' . $item . '.php">' . get_string($item,'local_video_directory') . '</a></li>';
			
}


echo "</ul></videomenu><br>";

?>