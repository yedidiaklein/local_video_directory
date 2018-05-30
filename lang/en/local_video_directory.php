<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * You may localized strings in your plugin
 *
 * @package    local_video
 * @copyright  2016 OpenApp
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */
$string['pluginname'] = 'Video Directory';

$string['video_directory:video'] = 'use local video directory';

$string['actions'] = 'Actions';
$string['agree'] = 'Agree to proceed';
$string['are_you_sure'] = 'Are you sure you want to delete this video ?';
$string['are_you_sure_subs'] = 'Are you sure you want to delete this subtitle file of ';
$string['awaitingconversion'] = 'Awaiting conversion';
$string['choose'] = 'Choose';
$string['choose_thumb'] = 'Please select thumbnail after loading is complete';
$string['clicktochangethumb'] = 'Click here to change thumbnail';
$string['close'] = 'Close';
$string['convert_status'] = 'State';
$string['download_status'] = 'Download status';
$string['edit'] = 'Edit video details';
$string['errorcreatingthumbat'] = 'Error creating thumb at';
$string['existing_tags'] = 'Existing tags';
$string['filename'] = 'Filename';
$string['files'] = 'File list';
$string['file_uploaded'] = 'File uploaded succesfully';
$string['freedisk'] = 'Free disk space';
$string['id'] = 'ID';
$string['length'] = 'Video legnth';
$string['list'] = 'List of videos';
$string['live'] = 'Live video';
$string['mass'] = 'Uploaded files';
$string['noimage'] = 'No image';
$string['orig_filename'] = 'Description';
$string['owner'] = 'Owner';
$string['play'] = 'Play';
$string['player'] = 'Player view';
$string['private'] = 'Private';
$string['reload'] = 'Reload';
$string['selected_tags'] = 'Selected tags';
$string['show_all'] = 'Show all videos';
$string['size'] = 'File size';
$string['state_1'] = 'File uploaded';
$string['state_2'] = 'File in conversion';
$string['state_3'] = 'File is ready';
$string['state_4'] = 'Waiting for conversion';
$string['state_5'] = 'Conversion failed';
$string['state_6'] = 'Creating multi resolution';
$string['state_7'] = 'Ready + Multi resolution';
$string['streaming_url'] = 'Streaming URL';
$string['tagarea_local_video_directory'] = 'Videos';
$string['tags'] = 'Tags';
$string['thumb'] = 'Thumbnail';
$string['upload'] = 'Upload';
$string['url_download'] = 'Insert here URL for downloading video';
$string['wget'] = 'Upload from link';
$string['wget_0'] = 'In queue';
$string['wget_1'] = 'Downloading...';
$string['wget_2'] = 'Moved to uploaded files area';
$string['clicktochangethumb'] = 'Click to Change thumb';
$string['ffmpegdrive'] = 'Ffmpeg drive';
$string['ffmpegpath'] = 'Ffmpeg path';
$string['ffprobedrive'] = 'Ffprobe drive';
$string['ffprobepath'] = 'Ffprobe path';
$string['phpdrive'] = 'PHP drive';
$string['phppath'] = 'PHP path';
$string['streamingurl'] = 'Streaming server URL';
$string['ffmpegparameters'] = 'Ffmpeg parameters';
$string['thumbnailseconds'] = 'Thumbnail seconds';
$string['alertdiskspace'] = 'Alert on low free disk space (MBytes)';
$string['cohortallowed'] = 'Cohort ID of allowed users (not relevant anymore)';
$string['clicktochangethumb'] = 'Click to change thumb';
$string['clicktochangethumbdesc'] = 'Click to change thumb';
$string['ffmpegdrivedesc'] = 'If your ffmpeg is not in the same drive as your moodle and not in the system path, please enter the drive letter here.';
$string['ffmpegpathdesc'] = 'Please enter the path to your local ffmpeg executable files including the executable filename itself. Windows users note: please use forward slashes instead of backslashes.';
$string['ffprobedrivedesc'] = 'If your ffprobe is not in the same drive as your moodle and not in the system path, please enter the drive letter here.';
$string['ffprobepathdesc'] = 'Please enter the path to your local ffprobe executable file including the executable filename itself. Windows users note: backslashes are converted to forward slashes.';
$string['phpdrivedesc'] = 'If your php installation is not in the same drive as your moodle and not in the system path, please enter the drive letter here.';
$string['phppathdesc'] = 'Please enter the path to your local php executable file including the executable filename itself. Windows users note: please use forward slashes instead of backslashes.';
$string['xampplink'] = ' If you are using XAMPP click here: <a onclick="document.getElementById(\'id_s_local_video_directory_php\').value = \'/xampp/php/php\'" style="cursor: pointer">Set Value</a>.';
$string['streamingurldesc'] = 'Please enter your streaming server URL including path here';
$string['ffmpegparametersdesc'] = 'For advanced users - Ffmpeg conversion parameters';
$string['thumbnailsecondsdesc'] = 'How many seconds from video start to extract default thumbnail';
$string['alertdiskspacedesc'] = 'Show the free disk space in red (MBytes)';
$string['cohortalloweddesc'] = 'Cohort ID of allowed users';
$string['cohortallowed'] = 'You can create a cohort for users allowed to manage and upload videos, and set its ID here';
$string['multiresolution'] = 'Encode in multiple resolutions';
$string['multiresolutiondesc'] = 'This is important for multi bit rate streaming using Nginx kaltura streaming module';
$string['resolutions'] = 'Resolution to encode';
$string['resolutionsdesc'] = 'Please insert list of resolutions (height) comma separated';
$string['upload_subs'] = 'Subtitles upload';
$string['subs_exist_in_size'] = 'Subtitles file exist and is in size :';
$string['no_file'] = 'File was not uploaded yet';
$string['upload_new_version'] = 'Upload new version';
$string['list_versions'] = 'List versions';
$string['versions'] = 'Versions';
$string['subs_deleted'] = 'Subtitle file deleted successfully.';
$string['restore_in_queue'] = "Restore request in queue...";
$string['cant_upload_or_restore_while_converting'] = "Can't upload or restore while this video is in converting action";
$string['portal'] = "Video Portal";
$string['sure_restore'] = "Are you sure that you want to restore the video from";
$string['restore'] = "Restore";
$string['dashbaseurl'] = "Dash server base url";
$string['dashbaseurldesc'] = "Insert here the base url of your dash server";
$string['allowanonymousembed'] = "Allow anonymous embed";
$string['allowanonymousembeddesc'] = "Allow embedding video without need of Moodle authentication";
$string['nginxmultiuridesc'] = "String for vod_multi_uri_suffix setting in Nginx";
$string['nginxmultiuri'] = "Multiuri nginx setting";
$string['embed'] = "Code for embeding video";
$string['embed_type'] = "Embed Type";