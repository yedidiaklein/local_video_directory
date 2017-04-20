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
            $mform = $this->_form; // Don't forget the underscore! 
            if ($id != 0) {
                 $video = $DB -> get_record('local_video_directory',array('id' => $id)); 
                                $mform->addElement('html', $video->orig_filename);
                    $mform->addElement('hidden', 'thumb', $video->thumb);
            } else {
                $mform->addElement('hidden', 'thumb', "");
            }
                        $mform->setType('thumb', PARAM_RAW); 
            $mform->addElement('hidden', 'id', $id);
                        $mform->setType('id', PARAM_INT);
                        $buttonarray=array();
            $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('yes'));
                        $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('no'));
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
       $where = array("id" => $fromform->id);
    $deleted = $DB->delete_records('local_video_directory', $where);
    //DELETE ALSO FILES!!!
    $thumb=str_replace($streaming_url, $converted, $fromform->thumb);
    $video=$converted.$fromform->id.'.mp4';
        if (file_exists($thumb)) unlink($thumb);
        if (file_exists($video)) unlink($video);
    //DELETE TAGS...
        $where = array("itemid" => $fromform->id,"itemtype" => 'local_video_directory');
        $deleted = $DB->delete_records('tag_instance', $where);  
      redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
  //Set default data (if any)
  //  $mform->set_data($toform);
  //displays the form
    echo $OUTPUT->header();
        echo get_string("are_you_sure",'local_video_directory');        
    $mform->display();
}
echo $OUTPUT->footer();
?>
