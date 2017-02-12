<?php
// This script is usefull for running manually the cron of video conversion w/o running moodle cron
// use in this way : 
// # su - www-data -s "/bin/bash" -c "php /var/www/html/moodle3.1/local/video_directory/manualcron.php"
// (this example assume web is running by www-data [debian/ubuntu] change to apache in redhat/centos)

define('CLI_SCRIPT',1);

include_once( __DIR__ . "/../../config.php");

include('lib.php');

local_video_directory_cron();
