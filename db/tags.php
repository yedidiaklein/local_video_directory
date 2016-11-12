<?php
$tagareas = array(
    array(
        'itemtype' => 'local_video_directory',  // This must be a name of the database table (without prefix).
        'component' => 'local_video_directory', // This can be omitted for plugins since it can only be full frankenstyle name of the plugin.
        'callback' => 'local_video_directory_get_tagged_pages',
        'callbackfile' => '/local/video_directory/locallib.php',
    ),
);
