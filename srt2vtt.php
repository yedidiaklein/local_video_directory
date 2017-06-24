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
 * Converting subtitles
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function srt2vtt($srt) {
    // Default configuration.
    $srt2vttconf = array(
        "appendCopyrightInfo" => false,
        "autoConvertEncoding" => true,
        "sourceEncodingList" => array('ISO-8859-1', 'Windows-1255', 'ISO-8859-8', 'UTF-8' ),
        "debug" => true,
    );
    if (file_exists("srt2vtt.conf.php")) {
        require_once("srt2vtt.conf.php");
    }
    try {
        if (isset($srt)) {
            // Convert to utf.
            $charset = mb_detect_encoding($srt, "Windows-1255, ISO-8859-8, UTF-8");
            $srt = iconv($charset, "UTF-8", $srt);
            // Break to lines.
            $subtitlelines = explode ("\n", $srt);

            if (empty($subtitlelines)) {
                throw new Exception(_("Subtitle file empty"));
            }
            // Prepare the result.

            $result = "WEBVTT\n\n\n";
            define('SRT_STATE_SUBNUMBER', 0);
            define('SRT_STATE_TIME', 1);
            define('SRT_STATE_TEXT', 2);
            define('SRT_STATE_BLANK', 3);

            $subs = array();
            $state = SRT_STATE_SUBNUMBER;
            $subnum = 0;
            $subtext = '';
            $subtime = '';

            foreach ($subtitlelines as $line) {
                switch($state) {
                    case SRT_STATE_SUBNUMBER:
                        $subnum = trim($line);
                        $state = SRT_STATE_TIME;
                    break;

                    case SRT_STATE_TIME:
                        $subtime = trim($line);
                        $state = SRT_STATE_TEXT;
                    break;

                    case SRT_STATE_TEXT:
                        if (trim($line) == '') {
                            $sub = new stdClass;
                            $sub->number = $subnum;
                            list($sub->startTime, $sub->stopTime) = explode(' --> ', $subtime);
                            $sub->text = $subtext;
                            $subtext = '';
                            $state = SRT_STATE_SUBNUMBER;
                            $subs[] = $sub;
                        } else {
                            $subtext .= trim($line)."\n";
                        }
                    break;
                }
            }

            foreach ($subs as $sub) {
                $result .= $sub->number."\n";
                $result .= str_replace(',', '.', $sub->startTime)." --> ".str_replace(',', '.', $sub->stopTime)."\n";
                $result .= $sub->text."\n\n";
            }

            return $result;

        } else {
            throw new Exception(_("File not found"));
        }

    } catch (Exception $e) {
        header('Content-type: text/plain; charset=utf-8');
        $result = "WEBVTT\n\n\n";
        $result .= "1\n";
        $result .= "00:00:00.000 --> 00:10:00.000\n";
        $result .= $e->getMessage()."\n\n\n";
        $subnum = 2;
        echo $result;
    } finally {
        // Append information if configured.
        if ($srt2vttconf['appendCopyrightInfo']) {
            echo (int)$subnum."\n";
            echo "00:00:00.001 --> 00:10:00.000\n";
            echo "Compiled by srt2vtt by pculka inside OpenApp Video System";
        }

    }
}