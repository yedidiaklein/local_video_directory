<?php
define('CLI_SCRIPT', 1);

require_once( __DIR__ . '/../init.php');

$url = $argv[1];

if (strlen($url) < 7) {
	die('Invalid url');
}

$url = base64_decode($url);
$filename = basename($url);

file_put_contents($wgetdir . $filename, fopen($url, 'r'));

// move to mass directory once downloaded
if (copy($wgetdir . $filename, $massdir . $filename)) {
	unlink($wgetdir.$filename);
	$sql = "UPDATE {local_video_directory_wget} SET success = 2 WHERE url = ?";
	$DB->execute($sql, array($url));
}
