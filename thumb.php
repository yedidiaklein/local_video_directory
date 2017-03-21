<?php
require_once 'init.php';
$id = required_param('id',PARAM_INT);
$second = optional_param('second', 0, PARAM_INT);
$streaming_dir = $converted;
header("Content-type: image/png");
readfile($streaming_dir . $id . ($second ? "-" . $second : '') . ".png");
