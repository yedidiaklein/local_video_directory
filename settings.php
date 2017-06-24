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
 * You may have settings in your plugin
 *
 * @package    local_video_directory
 * @copyright  2016 OpenApp http://openapp.co.il
 * @license    http://www.gnu.org/copyleft/gpl.html gnu gpl v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage( 'local_video_directory', 'Video System Settings' );
    $iswin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    if ($iswin) {
        $settings->add( new admin_setting_configtext(
            'local_video_directory/ffmpegdrive',
            get_string('ffmpegdrive', 'local_video_directory'),
            get_string('ffmpegdrivedesc', 'local_video_directory'),
            '',
            PARAM_ALPHA
        ));
    }

    $settings->add( new admin_setting_configtext(
        'local_video_directory/ffmpeg',
        get_string('ffmpegpath', 'local_video_directory'),
        get_string('ffmpegpathdesc', 'local_video_directory'),
        $iswin ? '/ffmpeg/bin/ffmpeg.exe' : '/usr/bin/ffmpeg',
        PARAM_PATH
    ));

    if ($iswin) {
        $settings->add( new admin_setting_configtext(
            'local_video_directory/ffprobedrive',
            get_string('ffprobedrive', 'local_video_directory'),
            get_string('ffprobedrivedesc', 'local_video_directory'),
            '',
            PARAM_ALPHA
        ));
    }

    $settings->add( new admin_setting_configtext(
        'local_video_directory/ffprobe',
        get_string('ffprobepath', 'local_video_directory'),
        get_string('ffprobepathdesc', 'local_video_directory'),
        $iswin ? '/ffmpeg/bin/ffprobe.exe' : '/usr/bin/ffprobe',
        PARAM_PATH
    ));

    if ($iswin) {
        $settings->add( new admin_setting_configtext(
            'local_video_directory/phpdrive',
            get_string('phpdrive', 'local_video_directory'),
            get_string('phpdrivedesc', 'local_video_directory'),
            '',
            PARAM_ALPHA
        ));
    }

    $settings->add( new admin_setting_configtext(
        'local_video_directory/php',
        get_string('phppath', 'local_video_directory'),
        get_string('phppathdesc', 'local_video_directory') . ($iswin ? get_string('xampplink', 'local_video_directory') : ''),
        $iswin ? '/php/php' : '/usr/bin/php',
        PARAM_PATH
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/streaming',
        get_string('streamingurl', 'local_video_directory'),
        get_string('streamingurldesc', 'local_video_directory'),
        $CFG->wwwroot . '/streaming',
        PARAM_URL

    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/ffmpeg_settings',
        get_string('ffmpegparameters', 'local_video_directory'),
        get_string('ffmpegparametersdesc', 'local_video_directory'),
        '-strict -2 -c:v libx264 -crf 22 -c:a aac -movflags faststart -vf "scale=trunc(iw/2)*2:trunc(ih/2)*2"',
        PARAM_TEXT
     ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/thumbnail_seconds',
        get_string('thumbnailseconds', 'local_video_directory'),
        get_string('thumbnailsecondsdesc', 'local_video_directory'),
        '5',
        PARAM_INT
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/df',
        get_string('alertdiskspace', 'local_video_directory'),
        get_string('alertdiskspacedesc', 'local_video_directory'),
        '1000',
        PARAM_INT
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/cohort',
        get_string('cohortallowed', 'local_video_directory'),
        get_string('cohortalloweddesc', 'local_video_directory'),
        '1',
        PARAM_INT
    ));

    $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/multiresolution',
        get_string('multiresolution', 'local_video_directory'),
        get_string('multiresolutiondesc', 'local_video_directory'),
        '0'
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/resolutions',
        get_string('resolutions', 'local_video_directory'),
        get_string('resolutionsdesc', 'local_video_directory'),
        '1080,720,648,360,288,144',
        PARAM_TEXT
     ));


    // Create.
    $ADMIN->add( 'localplugins', $settings );
}