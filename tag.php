<?php

require_once('init.php');

$tag = required_param('tag', PARAM_RAW);
$action = required_param('action', PARAM_RAW);
$from = optional_param('from','list',PARAM_RAW);


if ($action == 'add') {
    if (is_array($SESSION->video_tags)) {
        if(array_search($tag, $SESSION->video_tags) === FALSE) {
            $SESSION->video_tags[]=$tag;
        }
    } else {
        $SESSION->video_tags = array($tag);
    }    
} elseif ($action == 'remove') {
    if(($key = array_search($tag, $SESSION->video_tags)) !== false) {
        unset($SESSION->video_tags[$key]);
    }
}

$list = implode(",", $SESSION->video_tags);

redirect($CFG->wwwroot . '/local/video_directory/' . $from . '.php?tag='.$list);


