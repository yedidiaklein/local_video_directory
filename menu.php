<?php
require_once('locallib.php');

$selected = basename($_SERVER['SCRIPT_NAME']);

$settings=get_config('local_video_directory');
?>
<div class="alert alert-default alert-block" role="alert">
    <?= get_string('freedisk','local_video_directory') . ': ' . human_filesize(disk_free_space($CFG->dataroot), 2, $settings->df) ?>
</div>
<ul id='videomenu'>
<?php
$menu = array('list','upload','mass','wget');

foreach ($menu as $item) {
    if ($item . '.php' == $selected)
        echo '<li id="selected"><a href="' . $item . '.php">' . get_string($item,'local_video_directory') . '</a></li>';
    else 
        echo '<li><a href="' . $item . '.php">' . get_string($item,'local_video_directory') . '</a></li>';
            
}

?>
</ul>
<br>
