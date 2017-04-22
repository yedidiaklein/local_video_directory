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
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('init.php');
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");


$streaming_url = $settings->streaming;


$id = optional_param('video_id',0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('edit','local_video_directory'));
$PAGE->set_title(get_string('edit','local_video_directory'));
$PAGE->set_url('/local/video_directory/edit.php');
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(get_string('pluginname','local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('edit','local_video_directory'));

class simplehtml_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB;

        $id = optional_param('video_id', 0 , PARAM_INT);

        if ($id != 0) {
            $video = $DB->get_record('local_video_directory',array("id" => $id));
            $orig_filename = $video->orig_filename;
        } else {
            $orig_filename = "";
        }
            $mform = $this->_form; // Don't forget the underscore! 
 
            $mform->addElement('text', 'orig_filename', get_string('orig_filename','local_video_directory')); // Add elements to your form
            $mform->setType('orig_filename', PARAM_RAW);
            $mform->setDefault('orig_filename',$orig_filename );        //Default value

// For future implementation - map videos to courses.
/*           $courses = enrol_get_my_courses();
            $names = array();
            $ids = array();
            
            foreach ($courses as $course) {
                $names[] = $course->shortname;
                $ids[] = $course->id;
            }
            
            $select = $mform->addElement('select', 'courses', get_string('courses'), $names, $ids);
            $select->setMultiple(true);
*/            
            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);
            
            $mform->addElement('tags', 'tags', get_string('tags'), array('itemtype' => 'local_video_directory', 'component' => 'local_video_directory'));
            if ($id != 0) {
                $data = $DB->get_record('local_video_directory', array('id' => $id));
                $data->tags = core_tag_tag::get_item_tags_array('local_video_directory', 'local_video_directory', $id);
                $mform->setDefault('tags',$data->tags);
            }
              
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
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
  //In this case you process validated data. $mform->get_data() returns data posted in form.

    $record = array("id" => $fromform->id, "orig_filename" => $fromform->orig_filename );
    $update = $DB->update_record("local_video_directory",$record);
    $context = context_system::instance();
    core_tag_tag::set_item_tags('local_video_directory', 'local_video_directory', $fromform->id, $context, $fromform->tags);
    
  
      redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
  //Set default data (if any)
  //  $mform->set_data($toform);
  //displays the form
    echo $OUTPUT->header();

    $video = $DB->get_record('local_video_directory',array("id" => $id));
      echo '<video  width="655" height="279" controls preload="auto" poster="' . $video->thumb . '">
              <source src="play.php?video_id='. $id . '" type="video/mp4"">
            </video>';    

          
    $mform->display();
}


echo $OUTPUT->footer();
