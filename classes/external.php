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
 * External Web Service
 *
 * @package    local_video_directory
 * @copyright  2018 Yedidia Klein OpenApp Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
/**
 * Main class for external API.
 * @copyright  2018 Yedidia Klein OpenApp Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_video_directory_external extends external_api {
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function edit_parameters() {
        return new external_function_parameters(
                array('videoid' => new external_value(PARAM_INT,  'Video ID', VALUE_DEFAULT, 0),
                      'value'   => new external_value(PARAM_RAW, 'Video new name', VALUE_DEFAULT, ''),
                      'field'   => new external_value(PARAM_RAW, 'Field name to update', VALUE_DEFAULT, ''),
                      'status'  => new external_value(PARAM_BOOL, 'Video new name', VALUE_DEFAULT, '0')
                )
        );
    }
    /**
     * Returns success if name change
     * @param int $videoid The id of video
     * @param string $value Value to change
     * @param string $field Name of field to change
     * @param int $status For private status
     * @return int success (1)
     */
    public static function edit($videoid, $value, $field, $status) {
        global $USER, $DB;
        // Parameter validation.
        // REQUIRED.
        $params = self::validate_parameters(self::edit_parameters(),
                array('videoid' => $videoid, 'value' => $value, 'field' => $field, 'status' => $status));
        // Context validation.
        $context = context_system::instance();
        self::validate_context($context);
        // Capability checking.
        if (!has_capability('local/video_directory:video', $context)) {
            throw new moodle_exception('accessdenied');
        }

        if ($value != "") {
            $record = array("id" => $videoid, $field => urldecode($value));
        } else {
            $record = array("id" => $videoid, "private" => (int)$status);
        }

        if ($update = $DB->update_record("local_video_directory", $record)) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function edit_returns() {
        return new external_value(PARAM_INT, 'Success (1) while name was updated');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function thumb_parameters() {
        return new external_function_parameters(
                array('videoid' => new external_value(PARAM_INT, 'Video ID', VALUE_DEFAULT, 0),
                      'seconds' => new external_value(PARAM_INT, 'Seconds from video start', VALUE_DEFAULT, 0)
                )
        );
    }
    /**
     * Returns url of new thumbnail
     * @param int $videoid ID of video
     * @param int $seconds Seconds from start to take the thumbnail
     * @return string
     */
    public static function thumb($videoid, $seconds) {
        global $CFG;
        // Parameter validation.
        // REQUIRED.
        $params = self::validate_parameters(self::thumb_parameters(),
                    array('videoid' => $videoid, 'seconds' => $seconds));
        // Context validation.
        $context = context_system::instance();
        self::validate_context($context);
        // Capability checking.
        if (!has_capability('local/video_directory:video', $context)) {
            throw new moodle_exception('accessdenied');
        }

        require_once(__DIR__ . '/../locallib.php');
        $settings = get_settings();

        $ffmpeg = $settings->ffmpeg;
        $id = $videoid;
        $dirs = get_directories();
        $streamingdir = $dirs['converted'];

        if (is_numeric($seconds)) {
            $timing = gmdate("H:i:s", $seconds);
        } else {
            $timing = "00:00:05";
        }
        // Check that $ffmpeg is a file.
        if (file_exists($ffmpeg)) {
            // Added -y for windows during execution it will ask wheather to Overwite or not [y/n] -y make overwrite always.
            $thumb = '"' . $ffmpeg . '" -y -i ' . escapeshellarg($streamingdir . $id . ".mp4")
                . " -ss " . escapeshellarg($timing) . " -vframes 1  -vf scale=100:-1 "
                . escapeshellarg($streamingdir . $id . "-" . $seconds . ".png");
            $output = exec( $thumb );
        }
        if (file_exists($streamingdir . $id . "-" . $seconds . ".png")) {
            return $CFG->wwwroot . '/local/video_directory/thumb.php?id=' . $id . "&second=" . $seconds;
        } else {
            return 'noimage';
        }
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function thumb_returns() {
        return new external_value(PARAM_TEXT, 'Return the URL of the new generated thumbnail');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function videolist_parameters() {
        return new external_function_parameters(
            array('data' => new external_value(PARAM_RAW,  'DATA', VALUE_DEFAULT, "")
        ));
    }
    /**
     * Returns url of new thumbnail
     * @param int $id Just because I was unable to manage a webservice call without params
     * @return string
     */
    public static function videolist($data) {
        global $USER, $CFG, $DB, $OUTPUT, $SESSION;
        // Parameter validation.
        // REQUIRED.
        $params = self::validate_parameters(self::videolist_parameters(),
                    array('data' => $data));
        // Context validation.
        $context = context_system::instance();
        self::validate_context($context);
        // Capability checking.
        if (!has_capability('local/video_directory:video', $context)) {
            throw new moodle_exception('accessdenied');
        }
        require_once(__DIR__ . '/../locallib.php');

        $settings = get_settings();
        $dirs = get_directories();

        $videodata = json_decode($data);

        if ($videodata->search->value != "") {
            $search = $videodata->search->value;
        } else {
            $search = 0;
        }

        if ($videodata->order[0]->column != "") {
            if ($videodata->order[0]->dir == "asc") {
                $dir = " ASC ";
            } else {
                $dir = " DESC ";
            }
            switch ($videodata->order[0]->column) {
                case 2:
                    $order = ' id ' . $dir;
                    break;
                case 3:
                    $order = ' name ' . $dir;
                    break;
                case 4:
                    $order = ' orig_filename ' . $dir;
                    break;
                case 5:
                    $order = ' length ' . $dir;
                    break;
                case 6:
                    $order = ' convert_status ' . $dir;
                    break;
                case 7:
                    $order = ' private ' . $dir;
                    break;

            }
        } else {
            $order = 0;
        }

        if (isset($SESSION->video_tags) && is_array($SESSION->video_tags)) {
            $list = implode("', '", $SESSION->video_tags);
            $list = "'" . $list . "'";
            $videos = local_video_directory_get_videos_by_tags($list, 0, $videodata->start, $videodata->length, $search, $order);
        } else {
            $videos = local_video_directory_get_videos($order, $videodata->start, $videodata->length, $search);
        }
        
        $total = count($videos);

        foreach ($videos as $video) {
            // Do not show filename.
            unset($video->filename);
            if (is_numeric($video->convert_status)) {
                $video->convert_status = get_string('state_' . $video->convert_status, 'local_video_directory');
            }
            $video->tags = str_replace('/tag/index.php?tc=1', '/local/video_directory/tag.php?action=add&tag=',
                            $OUTPUT->tag_list(core_tag_tag::get_item_tags('local_video_directory',
                                                                          'local_video_directory',
                                                                          $video->id),
                            "", 'videos'));
            $versions = $DB->get_records('local_video_directory_vers', array('file_id' => $video->id));

            if (!file_exists( $dirs['converted'] . $video->id . ".mp4")) {
                $video->convert_status .= '<br>' . get_string('awaitingconversion', 'local_video_directory');
            }

            $video->thumb = local_video_get_thumbnail_url($video->thumb, $video->id);

            if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
                $video->actions = '';
            } else {
                unset($templateparams);
                $templateparams = array('id' => $video->id);
                if (!$video->subs) {
                    $templateparams['nosubs'] = 1;
                }
                if (!$versions) {
                    $templateparams['noversion'] = 1;
                }
                if ($settings->embedtype != 'none') {
                    $templateparams['embed'] = 1;
                }
                $video->actions = $OUTPUT->render_from_template('local_video_directory/list_actions', $templateparams);
            }

            if (get_streaming_server_url()) {
                $video->streaming_url = '<a target="_blank" href="' . get_streaming_server_url() . '/' . $video->id . '.mp4">'
                                        . get_streaming_server_url() . '/' . $video->id . '.mp4</a><br>';
            }
            $embedurl = $CFG->wwwroot . '/local/video_directory/embed.php?id=' . $video->uniqid;
            $video->streaming_url .= '<div style="direction:ltr">&lt;iframe src="' . $embedurl
                . '" style="width: 99vw; height: 56vw; max-width: 1280px; max-height: 720px;" frameBorder="0">&lt;/iframe></div>'
                . '<a href=https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs=300&chl=' . urlencode($embedurl)
                . ' target="_blank">QR</a>';

            if ($video->private) {
                $checked = "checked";
            } else {
                $checked = "";
            }

            // Do not allow non owner to edit privacy and title.
            if (($video->owner_id != $USER->id) && !is_siteadmin($USER)) {
                 $video->private = '';
            } else {
                $video->private = "<p style='display: none'>" . $video->private . '</p><input type="checkbox"
                                     class="checkbox ajax_edit" id="private_' . $video->id . '" ' . $checked . '>';
                $video->orig_filename = "<p style='display: none'>" . htmlspecialchars($video->orig_filename, ENT_QUOTES)
                                         . "</p><input type='text' class='hidden_input ajax_edit' id='orig_filename_" .
                $video->id . "' value='" . htmlspecialchars($video->orig_filename, ENT_QUOTES) . "'>";
            }
            $video->total = $total;
        } // end of foreach of all videos.
        return json_encode( array_values($videos),
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function videolist_returns() {
        return new external_value(PARAM_RAW, 'Return a JSON of videos');
    }

}