<?php

define('CLI_SCRIPT',1);

include_once( __DIR__ . "/../../config.php");

include('lib.php');

local_video_cron();

