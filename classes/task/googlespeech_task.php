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
 * Google Speech Task.
 *
 * @package    local_video_directory
 * @copyright  2018 Yedidia Klein OpenApp Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_video_directory\task;
defined('MOODLE_INTERNAL') || die();
/**
 * Class for Google speech API task.
 * @copyright  2018 Yedidia Klein OpenApp Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 // Includes the autoloader for libraries installed with composer
 require __DIR__ . '/googleSpeech/autoload.php';

 // Imports the Google Cloud client library
 use Google\Cloud\Speech\SpeechClient;
 use Google\Cloud\Storage\StorageClient;

 class googlespeech_task extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('googlespeech', 'local_video_directory');
    }

    public function execute() {
        global $CFG , $DB;

        $tospeech = $DB->get_records('local_video_directory_txtq', ['state' => 0]);
        
        if ($tospeech) {
            foreach ($tospeech as $tos) {
                $single = $tos;
                break;
            }
            // update to "in work" state
            $update = $DB->update_record('local_video_directory_txtq', ['id' => $single->id, 'state' => 1]);
        } else {
            return;
        }

        $lang = $single->lang;
        $video_id = $single->video_id;

        require_once($CFG->dirroot . '/local/video_directory/locallib.php');
        require_once($CFG->dirroot . '/local/video_directory/lib.php');

        $dirs = get_directories();

        $settings = get_settings();
        
        $ffmpeg = $settings->ffmpeg;
        $streamingdir = $dirs['converted'];

        //generate a raw file in $rawpath
        //ffmpeg -i yedidia.m4a -f s16le -acodec pcm_s16le -vn -ac 1 -ar 16k yedidia.raw
        $rawpath = $CFG->dataroot . '/temp/local_video_directory/';
        if (!file_exists($rawpath)) {
            mkdir($rawpath, 0755, true);
        }
        $output = exec($settings->ffmpeg . ' -y -i ' . $streamingdir . '/' . $video_id . '.mp4  -f s16le -acodec pcm_s16le -vn -ac 1 -ar 16k ' . $rawpath . '/' . $video_id . '.raw');

        // For some reason moodle put \r\n in textarea settings and it brake the JSON...
        $settings->googlejson = str_replace("\r\n","",$settings->googlejson);

        $key = json_decode($settings->googlejson, true);

        // Instantiates a client
        $speech = new SpeechClient([
            'keyFile' => $key,
            'languageCode' => $lang,
        ]);

        $bucketName = $settings->googlestoragebucket;
        $objectName = $video_id . '.raw';

        $uri = 'gs://' . $bucketName . '/' . $objectName;

        // Fetch the storage object
        $storage = new StorageClient([
            'keyFile' => $key,
        ]);
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload(file_get_contents($rawpath . '/' . $objectName), [
            'name' => $objectName
        ]);

        // Delete local raw file after uploading to the cloud
        $del = unlink($rawpath . '/' . $video_id . '.raw');

        $object = $storage->bucket($bucketName)->object($objectName);

        // The audio file's encoding and sample rate
        $options = [
            'encoding' => 'LINEAR16',
            'sampleRateHertz' => 16000,
            'enableWordTimeOffsets' => TRUE
        ];

        $operation = $speech->beginRecognizeOperation($object, $options);

        $isComplete = $operation->isComplete();

        while (!$isComplete) {
            sleep(1); // let's wait for a moment...
            $operation->reload();
            $isComplete = $operation->isComplete();
        }

        $result = $operation->results();

        foreach ($result as $key=>$value) {
            $secrecord['video_id'] = $video_id;
            $secrecord['orderby'] = $key;
            $secrecord['content'] = $value->topAlternative()['transcript'];
            $secrecord['start'] = '00';
            $secrecord['end'] = '00';
            $secrecord['datecreated'] = time();

            $inserted = $DB->insert_record('local_video_directory_txtsec', $secrecord);
            echo "Inserted raw $key of video $video_id to local_video_directory_txtsec table with ID : " . $inserted . "\n";

            foreach ($value->topAlternative()['words'] as $inkey => $invalue) {
                // add start and end to section
                if ($secrecord['start'] == '00') {
                    $secrecord['start'] = $invalue['startTime'];
                }
                $secrecord['end'] = $invalue['endTime'];

                $record['video_id'] = $video_id;
                $record['orderby'] = $inkey;
                $record['section_id'] = $inserted;
                $record['word'] = $invalue['word'];
                $record['start'] = $invalue['startTime'];
                $record['end'] = $invalue['endTime'];
                $record['datecreated'] = time();
    
                $insertedword = $DB->insert_record('local_video_directory_words', $record);
                echo "Inserted raw $inkey of video $video_id to local_video_directory_words table with ID : " . $insertedword . "\n";
            }
            // Update section table w/ start and end
            $updrecord['id'] = $inserted;
            $updrecord['start'] = $secrecord['start'];
            $updrecord['end'] = $secrecord['end'];
            $updated = $DB->update_record('local_video_directory_txtsec', $updrecord);
        }

        //finally delete file from bucket
        $delete = $object->delete();
        // update to "done" state
        $update = $DB->update_record('local_video_directory_txtq', ['id' => $single->id, 'state' => 2]);

    }
}
