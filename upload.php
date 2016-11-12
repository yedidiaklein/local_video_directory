<?php

require_once('init.php');
require_once("$CFG->libdir/formslib.php");

$streaming_url = $settings->streaming;

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('upload','local_video_directory'));
$PAGE->set_title(get_string('upload','local_video_directory'));
$PAGE->set_url('/local/video_directory/upload.php');
$PAGE->navbar->add(get_string('pluginname','local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('upload','local_video_directory'));
$PAGE->requires->css('/local/video_directory/style.css');

class simplehtml_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB;
        	$mform = $this->_form; // Don't forget the underscore! 
  
 			$mform->addElement('checkbox', 'private', get_string('private', 'local_video_directory'));
			$mform->setDefault('private','checked');
 
			$mform->addElement('filepicker', 'userfile', get_string('file'), null, array('maxbytes' => 1000000000, 'accepted_types' => '*'));
			
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

  	$name = $mform->get_new_filename('userfile');
	
	$record=array(	'orig_filename' => $name , 'owner_id' => $USER->id	);
		if ((isset($fromform->private)) && ($fromform->private)) {
			$record['private'] = 1;
		}
		$lastinsertid = $DB->insert_record('local_video_directory', $record);

		$success = $mform->save_file('userfile', $uploaddir.$lastinsertid);

		redirect($CFG->wwwroot . '/local/video_directory/list.php');
	
	
  
  	redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
  //Set default data (if any)
  //  $mform->set_data($toform);
  //displays the form
	echo $OUTPUT->header();
	//Menu
	include('menu.php');
  		
    $mform->display();
}


echo $OUTPUT->footer();
?>
