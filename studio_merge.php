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
 * Merge videos.
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');
require_once("$CFG->libdir/formslib.php");

$id = optional_param('video_id', 0, PARAM_INT);

// cli for merging videos
//ffmpeg  -i 95_816.mp4 -strict -2 -vf "movie=93_288.mp4[inner]; [in][inner] overlay=70:70 [out]" try.mp4
// fade out small video after finishing (if not last frame will show)
//ffmpeg -i master_video.mp4 -vf "movie=smaller_inner_video.mp4,  fade=out:300:30:alpha=1 [inner];  [in][inner] overlay=70:70 [out]" completed.mp4
// use audio of small (second) video (0:0 is video of first, 1:1 is audio of second)
// ffmpeg  -i 95_816.mp4 -i 93_288.mp4 -map 0:0 -map 1:1 -strict -2 -vf "movie=93_288.mp4[inner]; [in][inner] overlay=70:70 [out]" ../converted/try.mp4 

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('studio','local_video_directory'));
$PAGE->set_title(get_string('studio','local_video_directory'));
$PAGE->set_url('/local/video_directory/studio_merge.php?video_id=' . $id);
$PAGE->navbar->add(get_string('pluginname','local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('studio', 'local_video_directory'), new moodle_url('/local/video_directory/studio.php?video_id=' . $id));
$PAGE->navbar->add(get_string('merge','local_video_directory'));

class simplehtml_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form; // Don't forget the underscore! 
 
        $videos = $DB->get_records('local_video_directory',array());
        foreach ($videos as $video) {
            $names[$video->id] = $video->orig_filename." (".$video->id.")";
            //$ids[] = $video->id;
        }

		$id = optional_param('video_id', 0, PARAM_INT);

		$mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);

		$mform->addElement('select', 'bg_movie', get_string('bg_movie','local_video_directory'), $names);
		$mform->setDefault('bg_movie', $id);

		$mform->addElement('select', 'small_movie', get_string('small_movie','local_video_directory'), $names);
		$mform->setDefault('small_movie', $id);

		$mform->addElement('select', 'audio', get_string('audio','local_video_directory'), array(0=> get_string('bg_movie','local_video_directory'), 1=> get_string('small_movie','local_video_directory')));
		
//small video are smaller than 600
        $small_videos = $DB->get_records_sql('SELECT DISTINCT height FROM {local_video_directory} WHERE height < 600 ORDER BY height');

		foreach ($small_videos as $value) {
			$heights[$value->height]=$value->height;
		}

		$mform->addElement('select', 'height', get_string('height','local_video_directory'), $heights);
		
		$mform->addElement('select', 'border', get_string('border','local_video_directory'), array(10=>10,20=>20,30=>30,40=>40,50=>50,60=>60,70=>70,80=>80));
		
		$mform->addElement('select', 'location', get_string('location','local_video_directory'), 
					array(get_string('right','local_video_directory'),get_string('left','local_video_directory')));
		
		$mform->addElement('select', 'fade', get_string('fade_after','local_video_directory'), 
					array(get_string('fade','local_video_directory'),get_string('last_frame','local_video_directory')));
		

      		$buttonarray=array();
			$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
			$buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
			$mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
			
    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}

//Instantiate simplehtml_form 
$mform = new simplehtml_form();
 
//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($CFG->wwwroot . '/local/video_directory');
} else if ($fromform = $mform->get_data()) {
  //In this case you process validated data. $mform->get_data() returns data posted in form.
	$now = time();
	   $record = array("video_id" => $fromform->bg_movie,
					   "user_id" => $USER->id,
					   "state" => 0,
		   			//"bg_movie" => $fromform->bg_movie,
   					"video_id_small" => $fromform->small_movie,
   					"height" => $fromform->height,
   					"border" => $fromform->border,
   					"location" => $fromform->location,
   					"audio" => $fromform->audio,
					"fade" => $fromform->fade,
					"datecreated" => $now,
					"datemodified" => $now,
				 );
					
	$id = $DB->insert_record("local_video_directory_merge",$record);

	//echo $OUTPUT->header();
	//echo "Inserted id is : ".$id;	
  
  	redirect($CFG->wwwroot . '/local/video_directory/studio.php?video_id=' . $fromform->bg_movie, get_string('inqueue', 'local_video_directory'));
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
  //Set default data (if any)
  //  $mform->set_data($toform);
  //displays the form
	echo $OUTPUT->header();
  		
    $mform->display();
}


echo $OUTPUT->footer();
