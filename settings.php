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
    $settings = new admin_settingpage( 'local_video_directory', get_string('settings', 'local_video_directory') );
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
        $iswin ? '/ffmpeg/bin/ffmpeg.exe' : $CFG->dirroot . '/local/video_directory/ffmpeg_static_linux/ffmpeg',
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
        $iswin ? '/ffmpeg/bin/ffprobe.exe' : $CFG->dirroot . '/local/video_directory/ffmpeg_static_linux/ffprobe',
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
        'local_video_directory/youtubedl',
        get_string('youtube-dlpath', 'local_video_directory'),
        get_string('youtube-dldesc', 'local_video_directory'),
        $iswin ? '/bin/youtube-dl.exe' : '/usr/bin/youtube-dl',
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

     $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/showwhere',
        get_string('showwhere', 'local_video_directory'),
        get_string('showwheredesc', 'local_video_directory'),
        '0'
    ));

     $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/showqr',
        get_string('showqr', 'local_video_directory'),
        get_string('showqrdesc', 'local_video_directory'),
        '0'
    ));

    $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/embedcolumn',
        get_string('embedcolumn', 'local_video_directory'),
        get_string('embedcolumndesc', 'local_video_directory'),
        '0'
    ));

     $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/showembed',
        get_string('showembed', 'local_video_directory'),
        get_string('showembeddesc', 'local_video_directory'),
        '0'
    ));

    // Embed type.
    $settings->add(
        new admin_setting_configselect('local_video_directory/embedtype',
        get_string('embed_type', 'local_video_directory'), '', '', array("none" => "none", "dash" => "dash", "hls" => "hls")));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/embedoptions',
        get_string('embed', 'local_video_directory'),
        '',
        'style="width: 99vw; height: 56vw; max-width: 1280px; max-height: 720px;" frameBorder="0" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"',
         PARAM_TEXT
    ));
    

    $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/allowanonymousembed',
        get_string('allowanonymousembed', 'local_video_directory'),
        get_string('allowanonymousembeddesc', 'local_video_directory'),
        '0'
    ));

    $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/allowxmlexport',
        get_string('allowxmlexport', 'local_video_directory'),
        get_string('allowxmlexportdesc', 'local_video_directory') . ' ' . $CFG->wwwroot . '/local/video_directory/xmlexport.php',
        '0'
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/nginxmultiuri',
        get_string('nginxmultiuri', 'local_video_directory'),
        get_string('nginxmultiuridesc', 'local_video_directory'),
        'multiuri',
        PARAM_TEXT
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/dashbaseurl',
        get_string('dashbaseurl', 'local_video_directory'),
        get_string('dashbaseurldesc', 'local_video_directory'),
        $CFG->wwwroot . '/dash',
        PARAM_URL
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/hlsbaseurl',
        get_string('hlsbaseurl', 'local_video_directory'),
        get_string('hlsbaseurldesc', 'local_video_directory'),
        $CFG->wwwroot . '/hls',
        PARAM_URL
    ));

    $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/googlespeech',
        get_string('googlespeech', 'local_video_directory'),
        get_string('googlespeechdesc', 'local_video_directory'),
        '0'
    ));

    $settings->add( new admin_setting_configtextarea(
        'local_video_directory/googlejson',
        get_string('googlejson', 'local_video_directory'),
        get_string('googlejsondesc', 'local_video_directory'),
        '',
        PARAM_RAW
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/googlestoragebucket',
        get_string('googlestoragebucket', 'local_video_directory'),
        get_string('googlestoragebucketdesc', 'local_video_directory'),
        'video',
        PARAM_TEXT
    ));

    $ar_group = array("none" => get_string('none', 'moodle'),
            "department" => get_string('department', 'moodle'), 
            "institution" => get_string('institution', 'moodle'), 
            "custom" => get_string('customgroup', 'local_video_directory'));
    $locals = $DB->get_records('user_info_field', ['datatype' => 'text'], 'name');
    foreach ($locals as $local) {
        $ar_group['local_' . $local->shortname] = $local->name;
    }
    $settings->add(
        new admin_setting_configselect('local_video_directory/group',
        get_string('group', 'moodle'), '', '', $ar_group
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/customgroup',
        get_string('customgroup', 'local_video_directory'),
        get_string('customgroupdesc', 'local_video_directory'),
        'group1, group2, group3',
        PARAM_TEXT
    ));

    $settings->add( new admin_setting_configcheckbox(
        'local_video_directory/groupcloud',
        get_string('groupcloud', 'local_video_directory'),
        get_string('groupclouddesc', 'local_video_directory'),
        '0'
    ));

    $settings->add( new admin_setting_configtext(
        'local_video_directory/fieldorder',
        get_string('fieldorder', 'local_video_directory'),
        get_string('fieldorder', 'local_video_directory'),
        'actions, thumb, id, name, usergroup, orig_filename, length, convert_status, private, streaming_url, tags',
        PARAM_TEXT
    ));
 
    // Create.
    $ADMIN->add( 'localplugins', $settings );

     $ADMIN->add('server', new admin_externalpage('local_video_directory_list',
        get_string('pluginname', 'local_video_directory'),
        new moodle_url('/local/video_directory/')));

}