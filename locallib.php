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
 * Locallib for local functions.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_video_directory_human_filesize($bytes, $decimals = 2, $red = 0) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);

    if (($red != 0) && ($bytes < $red)) {
        return '<df style="color:red">' . sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . '</df>';
    } else {
        return '<df>' . sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . '</df>';
    }
}

function local_video_directory_get_tagged_pages($tag, $exclusivemode = false, $fromctx = 0, $ctx = 0, $rec = 1, $page = 0) {
    global $CFG, $OUTPUT, $PAGE;
    $PAGE->requires->css(new moodle_url($CFG->wwwroot . '/local/video_directory/style.css'));

    // Include font awesome in case of moodle 32 and older.
    if ($CFG->branch < 33) {
        $PAGE->requires->css('/local/video_directory/font_awesome/css/all.min.css');
    }
    $perpage = 10;
    $query = '';
    $builder = new core_tag_index_builder('local_video_directory', 'local_video_directory',
                                          $query, "", $page * $perpage, $perpage + 1);
    $tagfeed = new core_tag\output\tagfeed();

    $videos = local_video_directory_get_videos_by_tags("", $tag->id);

    foreach ($videos as $video) {
        $thumb = local_video_get_thumbnail_url($video->thumb, $video->id);
        $tagfeed->add('<i class="fa fa-file-video-o" aria-hidden="true" style="font-size: xx-large;"></i>',
                        '<a href="' . $CFG->wwwroot .'/local/video_directory/list.php?tc=1&tag=' . rawurlencode($tag->name) . '">' .
                        $thumb . '<span class="local_video_directory-intag-title">' . $video->orig_filename . '</span></a>',
                        '<b>' . get_string('owner', 'local_video_directory') . ': </b>' . $video->name . '<br><br>');
    }
    $content = $OUTPUT->render_from_template('core_tag/tagfeed', $tagfeed->export_for_template($OUTPUT));

    $totalpages = ceil(count($videos) / $perpage);
    return new core_tag\output\tagindex($tag, 'local_video_directory', 'local_video_directory', $content,
                                        $exclusivemode, $fromctx, $ctx, $rec, $page, $totalpages);
}

function local_video_edit_right($videoid) {
    global $DB, $CFG, $USER;
    $video = $DB->get_record("local_video_directory", array('id' => $videoid));
    if ((is_video_admin() || $video->owner_id == $USER->id)) {
        return 1;
    } else {
        redirect($CFG->wwwroot . '/local/video_directory/list.php', get_string('accessdenied', 'admin'));
    }
}

// Return streaming server url.
function get_streaming_server_url() {
    global $DB;
    $settings = get_settings();

    return $settings->streaming;
}

// Check if streaming server and symlink or settings exists and work.
function check_streaming_server_url() {
    global $DB;
    $settings = get_settings();

    $firstvideo = $DB->get_records('local_video_directory', [], '', '*', 0, 1);

    if ($firstvideo) {
        $url = $settings->streaming . '/' . local_video_directory_get_filename(current($firstvideo)->id) . '.mp4';
        $headers = get_headers($url);
        if (strstr($headers[0] , "200")) {
            $streamingurl = $settings->streaming;
        } else {
            $streamingurl = false;
        }
    }
    return $streamingurl;
}

// Returns settings.
function get_settings() {

    $settings = get_config('local_video_directory');
    $shellcomponents = array('ffmpeg', 'ffprobe', 'php');
    $iswin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

    foreach ($shellcomponents as $sc) {
        if (isset($settings->$sc)) {
            $settings->$sc = ($iswin && isset($settings->{$sc . 'drive'}) && preg_match('~^[a-z]$~',
            $settings->{$sc . 'drive'}) ? $settings->{$sc . 'drive'} .
            ":" . (strpos($settings->$sc, '/') === 0 ? '' : '/') : '') .
            ($iswin ? str_replace('/', DIRECTORY_SEPARATOR, $settings->$sc) : $settings->$sc);
        }
    }
    return $settings;
}

function get_directories() {
    global $CFG;
    // Directories for this plugin.
    $dirs = array('uploaddir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos' . DIRECTORY_SEPARATOR,
                'converted' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'converted' . DIRECTORY_SEPARATOR,
                'massdir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'mass' . DIRECTORY_SEPARATOR,
                'wgetdir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'wget' . DIRECTORY_SEPARATOR,
                'multidir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'multi' . DIRECTORY_SEPARATOR,
                'subsdir' => DIRECTORY_SEPARATOR . 'local_video_directory_videos'
                    . DIRECTORY_SEPARATOR . 'subs' . DIRECTORY_SEPARATOR);

    foreach ($dirs as $key => $value) {
        // Add dataroot.
        $dirs[$key] = $CFG->dataroot.$value;
        // Create if doesn't exist.
        if (!file_exists($dirs[$key])) {
            mkdir($dirs[$key], 0777, true);
        }
    }
    return $dirs;
}

function local_video_directory_get_videos_by_tags($list, $tagid=0, $start = null, $length = null, $search = 0, $order=0) {
    global $USER, $DB, $SESSION, $CFG;
    $settings = get_settings();
    $params = [];
    $whereor = '';
    $and = '';

    if (count($list) > 0) {
        $intags = " ti.itemtype = 'local_video_directory' ";
    } else {
        $intags = ' 1 = 1 ';
    }

    if (count($SESSION->categories)) {
        foreach ($SESSION->categories as $key => $value) {
            $c[] = $value['id'];
        }
        list($insql, $catsparams) = $DB->get_in_or_equal($c);
        $cats = ' cat_id ' . $insql;
    } else {
        $cats = '';
        $catsparams = [];
    }

    if (@count($SESSION->groups)) {
        foreach ($SESSION->groups as $key => $value) {
            $g[] = $value['name'];
        }
        list($insql, $groupsparams) = $DB->get_in_or_equal($g);
        $groups = ' usergroup ' . $insql;
    } else {
        $groups = '';
        $groupsparams = [];
    }

    if ($order) {
        $orderby = " ORDER BY $order ";
    } else {
        $orderby = "";
    }

    if ($list != "" && $list != []) {
        list($insql, $params) = $DB->get_in_or_equal($list);
        $and = ' AND t.name ' . $insql;
    } else if (is_numeric($tagid) && ($tagid != 0)) {
        $and = ' AND t.id =' . $tagid . ' ';
    }

    if (!is_numeric($start) || !is_numeric($length)) {
        $start = $length = null;
    }

    if ($search) {
        $match = " (usergroup LIKE ? OR orig_filename LIKE ? OR firstname LIKE ? OR  lastname LIKE ? OR uniqid LIKE ?
                    OR t.name LIKE ?) ";
        if ($groups != '') {
            $match .= " AND (" . $groups . ") ";
        }
        $where = " WHERE " . $match;
        $whereor = " AND " . $match;
        $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"],
                                $groupsparams, $catsparams);
    } else {
        if ($groups != '') {
            $where = " WHERE (" . $groups . ") ";
            $whereor = " AND (" . $groups . ") ";
        }
        if (($cats != '') && ($groups == '')) {
            $where = " WHERE (" . $cats . ") ";
            $whereor = " AND (" . $cats . ") ";
        }
        if (($cats != '') && ($groups != '')) {
            $where = $whereor .= " AND (" . $cats . ") ";

        }
        $params = array_merge($params, $groupsparams, $catsparams);
    }

    if (($CFG->dbtype == 'mariadb' || $cfg->dbtype == 'mysql')) {
        $aggregate = ' GROUP_CONCAT(cat_name, " ") ';
    } else if (($CFG->dbtype == 'mssql' || $cfg->dbtype == 'sqlsrv')) {
        $aggregate = " STRING_AGG(cat_name, ', ') ";
    }
    if (is_video_admin()) {
        $sql = 'SELECT DISTINCT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname"))
                                                . ' AS name
                                                FROM {local_video_directory} v
                                                LEFT JOIN {user} u on v.owner_id = u.id
                                                LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                                                LEFT JOIN {tag} t on ti.tagid=t.id
                                                LEFT JOIN {local_video_directory_catvid} c ON c.video_id = v.id
                                                WHERE ' . $intags .
                                                $and . $whereor . $orderby;
        $videos = $DB->get_records_sql($sql, $params, $start, $length);
    } else {
        if (($settings->group == "institution") || ($settings->group == "department")) {
            $videos = $DB->get_records_sql('SELECT DISTINCT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname"))
                . ' AS name
                FROM {local_video_directory} v
                LEFT JOIN {user} u on v.owner_id = u.id
                LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                LEFT JOIN {tag} t on ti.tagid=t.id
                LEFT JOIN {local_video_directory_catvid} c ON c.video_id = v.id
                WHERE ' . $intags . $and . $whereor .
                'AND (owner_id =' . $USER->id . ' OR (private IS NULL OR private = 0)
                OR (usergroup = \'' . $USER->{$settings->group} . '\'))
                ' . $orderby, $params, $start, $length);
        } else {
            $videos = $DB->get_records_sql('SELECT DISTINCT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname"))
                                                . ' AS name
                                                FROM {local_video_directory} v
                                                LEFT JOIN {user} u on v.owner_id = u.id
                                                LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                                                LEFT JOIN {tag} t on ti.tagid=t.id
                                                LEFT JOIN {local_video_directory_catvid} c ON c.video_id = v.id
                                                WHERE ' . $intags . $and . $whereor .
                                                'AND (owner_id =' . $USER->id . ' OR (private IS NULL OR private = 0))
                                                ' . $orderby, $params, $start, $length);

        }
    }
    return $videos;
}

function local_video_directory_get_videos($order = 0, $start = null, $length = null, $search=0) {
    global $USER, $DB, $SESSION, $CFG;
    $settings = get_settings();
    if ($order) {
        $orderby = " ORDER BY $order ";
    } else {
        $orderby = "";
    }

    if (!is_numeric($start) || !is_numeric($length)) {
        $start = $length = null;
    }

    $params = null;

    if (isset($SESSION->categories) && count($SESSION->categories)) {
        foreach ($SESSION->categories as $key => $value) {
            $c[] = $value['id'];
        }
        list($insql, $catsparams) = $DB->get_in_or_equal($c);
        $cats = ' cat_id ' . $insql;
    } else {
        $cats = '';
        $catsparams = [];
    }

    if (isset($SESSION->groups) && count($SESSION->groups)) {
        foreach ($SESSION->groups as $key => $value) {
            $g[] = $value['name'];
        }
        list($insql, $groupsparams) = $DB->get_in_or_equal($g);
        $groups = ' usergroup ' . $insql;
    } else {
        $groups = '';
        $groupsparams = [];
    }

    if ($search) {
        $match = " (usergroup LIKE ? OR orig_filename LIKE ? OR firstname LIKE ?
                    OR  lastname LIKE ? OR uniqid LIKE ? OR t.name LIKE ? ) ";
        if ($groups != '') {
            $match .= " AND (" . $groups . ") ";
        }
        if ($cats != '') {
            $match .= " AND (" . $cats . ") ";
        }

        $where = " WHERE " . $match;
        $whereor = " AND " . $match;
        $params = array_merge(["%$search%", "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"],
                                $groupsparams, $catsparams);
    } else {
        $where = "";
        $whereor = "";
        if ($groups != '') {
            $where = " WHERE (" . $groups . ") ";
            $whereor = " AND (" . $groups . ") ";
        }
        if (($cats != '') && ($groups == '')) {
            $where = " WHERE (" . $cats . ") ";
            $whereor = " AND (" . $cats . ") ";
        }
        if (($cats != '') && ($groups != '')) {
            $where = $whereor .= " AND (" . $cats . ") ";

        }
        $params = array_merge($groupsparams, $catsparams);
    }

    if (is_video_admin()) {
        $videos = $DB->get_records_sql('SELECT DISTINCT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
                                    ' AS name
                                    FROM {local_video_directory} v
                                    LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                                    LEFT JOIN {tag} t on ti.tagid=t.id
                                    LEFT JOIN {user} u on v.owner_id = u.id
                                    LEFT JOIN {local_video_directory_catvid} c ON c.video_id = v.id' .
                                    $where . $orderby, $params, $start, $length);
    } else {
        if (($settings->group == "institution") || ($settings->group == "department")) {
            $videos = $DB->get_records_sql('SELECT DISTINCT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
                ' AS name
                FROM {local_video_directory} v
                LEFT JOIN {user} u on v.owner_id = u.id
                LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                LEFT JOIN {tag} t on ti.tagid=t.id
                WHERE (owner_id =' . $USER->id .
                ' OR (private IS NULL OR private = 0) OR (usergroup = \'' . $USER->{$settings->group} . '\'))' .
                $whereor . $orderby, $params, $start, $length);

        } else {
            $videos = $DB->get_records_sql('SELECT DISTINCT v.*, ' . $DB->sql_concat_join("' '", array("firstname", "lastname")) .
                                            ' AS name
                                            FROM {local_video_directory} v
                                            LEFT JOIN {tag_instance} ti on v.id=ti.itemid
                                                LEFT JOIN {tag} t on ti.tagid=t.id
                                    LEFT JOIN {user} u on v.owner_id = u.id WHERE (owner_id =' . $USER->id .
                                    ' OR (private IS NULL OR private = 0))' . $whereor . $orderby, $params, $start, $length);
        }
    }
    return $videos;
}

function local_video_get_thumbnail_url($thumb, $videoid, $clean=0) {
    global $CFG, $DB;
    $dirs = get_directories();
    $thumb = str_replace(".png", "-mini.png", $thumb);
    $thumbdata = explode('-', $thumb);
    $thumbid = $thumbdata[0];
    $thumbseconds = isset($thumbdata[1]) ? "&second=$thumbdata[1]" : '';

    $video = $DB->get_record('local_video_directory', ['id' => $videoid]);

    if ((file_exists( $dirs['converted'] . $videoid . ".mp4"))
        || (file_exists( $dirs['converted'] . $video->filename . ".mp4"))) {
        $alt = 'title="' . get_string('play', 'local_video_directory') . '"
            alt="' . get_string('play', 'local_video_directory') . '"';
        if (get_streaming_server_url()) {
            if ($video->filename != $videoid . '.mp4') {
                $playbutton = ' data-video-url="' . htmlspecialchars(get_streaming_server_url()) . "/" .
                $video->filename . '.mp4" data-id="' . $videoid . '"';
            } else {
                $playbutton = ' data-video-url="' . htmlspecialchars(get_streaming_server_url()) . "/" .
                        $videoid . '.mp4" data-id="' . $videoid . '"';
            }
        } else {
            $playbutton = ' data-video-url="play.php?video_id=' .
            $videoid . '" data-id="' . $videoid . '"';
        }
    } else {
        $playbutton = '';
    }

    $thumb = "<div class='video-thumbnail' " . $playbutton . ">" .
              ($thumb ? "<img src='$CFG->wwwroot/local/video_directory/thumb.php?id=$thumbid$thumbseconds&mini=1 '
        class='thumb' " . $playbutton ." >" : get_string('noimage', 'local_video_directory')) . "</div>";

    if ($clean) {
        $thumb = "$CFG->wwwroot/local/video_directory/thumb.php?id=$thumbid$thumbseconds";
    }

    return $thumb;
}

function local_video_directory_check_android_version($version = '4.5.0') {

    if (strstr($_SERVER['HTTP_USER_AGENT'], 'Android')) {
        preg_match('/Android (\d+(?:\.\d+)+)[;)]/', $_SERVER['HTTP_USER_AGENT'], $matches);
        return version_compare($matches[1], $version, '<=');
    }
}

function local_video_directory_studio_tasks($id, $table, $metadata) {
    global $DB, $USER;
    $datas = $DB->get_records('local_video_directory_' . $table, ['user_id' => $USER->id, 'video_id' => $id]);
    $tasks = [];
    foreach ($datas as $data) { // Startx starty endx endy.
        $metacat = "";
        foreach ($metadata as $metdat) {
            $metacat .= $data->$metdat . " ";
        }
        $tasks[] = [ 'task' => get_string($table, 'local_video_directory'),
                'state' => get_string('textstate_' . $data->state, 'local_video_directory'),
                'metadata' => $metacat,
                'date' => strftime("%A, %d %B %Y %H:%M", $data->datecreated)
             ];
    }
    return $tasks;
}

function local_video_directory_studio_action($data, $type) {
    global $DB, $CFG;
    $dirs = get_directories();
    $settings = get_settings();
    $ffmpeg = $settings->ffmpeg;
    $origdir = $dirs['uploaddir'];
    $streamingdir = $dirs['converted'];

    foreach ($data as $dat) {
        // First of all set state to 1.
        $DB->update_record('local_video_directory_' . $type, ['id' => $dat->id, 'state' => 1]);
        $filename = local_video_directory_get_filename($dat->video_id);
        if ($type == "crop") {
            if ($dat->startx < $dat->endx) {
                $startx = $dat->startx;
            } else {
                $startx = $dat->endx;
            }

            if ($dat->starty < $dat->endy) {
                $starty = $dat->starty;
            } else {
                $starty = $dat->endy;
            }

            $width = abs($dat->endx - $dat->startx);
            $height = abs($dat->endy - $dat->starty);
        }

        if ($dat->save == "new") {
            $name = $DB->get_field('local_video_directory', 'orig_filename', ['id' => $dat->video_id]);
            $record = array('orig_filename' => $type . ' ' . $name,
                    'owner_id' => $dat->user_id,
                    'private' => 1,
                    'uniqid' => uniqid('', true));
            $newid = $DB->insert_record('local_video_directory' , $record);
        } else { // Version.
            $record = array('id' => $dat->video_id,
                        'convert_status' => 1);
            $DB->update_record('local_video_directory' , $record);
            $newid = $dat->video_id;
        }
        if ($type == "crop") {
            $cmd[] = $ffmpeg . " -i " . $streamingdir . "/" . $filename .
                ".mp4 -filter:v \"crop=$width:$height:$startx:$starty\" " . $origdir . "/" . $newid . ".mp4";
        } else if ($type == "cat") {
            $cmd[] = $ffmpeg . " -i " . $streamingdir . "/" . $filename .
                ".mp4 -c copy -bsf:v h264_mp4toannexb -f mpegts $CFG->dataroot/temp/intermediate1.ts";
            $cmd[] = $ffmpeg . " -i " . $streamingdir . "/" . local_video_directory_get_filename($dat->video_id_cat) .
                ".mp4 -c copy -bsf:v h264_mp4toannexb -f mpegts $CFG->dataroot/temp/intermediate2.ts";
            $cmd[] = $ffmpeg . ' -i "concat:' . $CFG->dataroot . '/temp/intermediate1.ts|' .
                        $CFG->dataroot . '/tmp/intermediate2.ts" -c copy -bsf:a aac_adtstoasc ' .
                        $origdir . "/" . $newid . ".mp4";
        } else if ($type == "cut") {
            $start = gmdate("H:i:s", $dat->secbefore);
            $length = $DB->get_field('local_video_directory', 'length', ['id' => $dat->video_id]);
            $time = strtotime($length);
            $newlength = date("H:i:s", $time - $dat->secafter);
            $cmd[] = $ffmpeg . " -ss " . $start .  " -i " . $streamingdir . "/" . $filename .
                ".mp4 -to $newlength -c copy -copyts " . $origdir . "/" . $newid . ".mp4";
        } else if ($type == "merge") {
            $cmd[] = $ffmpeg . " -i " . $streamingdir . "/" . $filename . ".mp4 -i " . $dirs['multidir'] .
                $dat->video_id_small . "_" . $dat->height . ".mp4 -map 0:0 -map " . $dat->audio . ":1" .
                ' -strict -2 -vf "movie='.
                $dirs['multidir'] . local_video_directory_get_filename($dat->video_id_small) . "_" . $dat->height . ".mp4" .
                '[inner]; [in][inner] overlay=' . $dat->border . ':' . $dat->border . '[out]" ' .
                $origdir . "/" . $newid . ".mp4";
        } else if ($type == "speed") {
            $double = ($dat->speed / 100);
            $half = 1 / $double;
            $cmd[] = $ffmpeg . " -i " . $streamingdir . "/" . $filename .
            '.mp4 -filter_complex "[0:v]setpts=' . $half . '*PTS[v];[0:a]atempo=' . $double . '[a]" -map "[v]" -map "[a]" ' .
            $origdir . "/" . $newid . ".mp4";
        }

        foreach ($cmd as $cm) {
            echo "[local_video_directory] CMD: $cm";
            exec($cm);
        }
        copy($origdir . "/" . $newid . ".mp4", $origdir . "/" . $newid);
        unlink($origdir . "/" . $newid . ".mp4");
        if ($type == "cat") {
            unlink($CFG->dataroot . "/temp/intermediate1.ts");
            unlink($CFG->dataroot . "/temp/intermediate2.ts");
        }
        $DB->update_record('local_video_directory_' . $type, ['id' => $dat->id, 'state' => 2]);
    }
}

function is_video_admin($user = '') {
    global $USER;
    if (is_siteadmin($USER)) {
        return true;
    }

    // Check if user is video admin.
    $context = context_system::instance();
    $roles = get_user_roles($context, $USER->id, true);
    $keys = array_keys($roles);
    foreach ($keys as $key) {
        if ($roles[$key]->shortname == 'local_video_directory_admin') {
            return true;
        }
    }
    return false;
}

function local_video_get_groups($settings) {
    global $DB;
    if ($settings->group == "none") {
        return;
    }
    if (substr($settings->group, 0, 6) == 'local_') {
        $local = substr($settings->group, 6);
        $group = $DB->get_records_sql("SELECT DISTINCT data FROM {user_info_data} uid
                                        LEFT JOIN {user_info_field} uif ON uid.fieldid = uif.id
                                        WHERE shortname = '$local'");
        foreach ($group as $k => $v) {
            $g[$v->data] = $v->data;
        }
    } else if ($settings->group != "custom") {
        $group = $DB->get_records_sql("SELECT DISTINCT $settings->group from {user} ORDER BY $settings->group");
        foreach ($group as $k => $v) {
            $g[$v->{$settings->group}] = $v->{$settings->group};
        }
    } else {
        $group = explode(",", $settings->customgroup);
        foreach ($group as $k => $v) {
            $g[trim($v)] = trim($v);
        }
    }
    return $g;
}

// This function is for moving from id filename to hash filename.
function local_video_directory_get_filename ($id) {
    global $DB;
    $video = $DB->get_record('local_video_directory', ['id' => $id]);
    if ($video->filename != $id . '.mp4') {
        $filename = $video->filename;
    } else {
        $filename = $id;
    }
    return $filename;
}

/**
 * Check if a given ip is in a network
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 */
function local_video_directory_ip_in_range( $ip, $range ) {
    if ( strpos( $range, '/' ) == false ) {
        $range .= '/32';
    }
    // If $range is in IP/CIDR format eg 127.0.0.1/24 .
    list( $range, $netmask ) = explode( '/', $range, 2 );
    $rangedecimal = ip2long( $range );
    $ipdecimal = ip2long( $ip );
    $wildcarddecimal = pow( 2, ( 32 - $netmask ) ) - 1;
    $netmaskdecimal = ~ $wildcarddecimal;
    return ( ( $ipdecimal & $netmaskdecimal ) == ( $rangedecimal & $netmaskdecimal ) );
}