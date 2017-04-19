<?php

require_once('init.php');
require_once("$CFG->libdir/formslib.php");


$ffmpeg = $settings -> ffmpeg;
$streaming_url = $settings -> streaming.'/';
$streaming_dir = $converted;

$id = optional_param('id',0, PARAM_INT);
$seconds = array(3, 7, 12, 20, 60, 120);

require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('thumb', 'local_video_directory'));
$PAGE->set_title(get_string('thumb', 'local_video_directory'));
$PAGE->set_url('/local/video_directory/thumbs.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js('/local/video_directory/js/thumbs.js');
$PAGE->requires->css('/local/video_directory/style.css');
$PAGE->requires->strings_for_js(
    array_keys(
        get_string_manager()->load_component_strings('local_video_directory', current_language())
    ),
    'local_video_directory'
);

$PAGE->navbar->add(get_string('thumb', 'local_video_directory'));

class simplehtml_form extends moodleform {
    public function definition() {
        global $CFG, $DB, $seconds, $streaming_dir, $OUTPUT;

            $mform = $this->_form; // Don't forget the underscore! 
             
             // LOOP from array seconds...
              $radioarray = array();

            $id = optional_param('id', 0, PARAM_INT);
            $length = $DB->get_field('local_video_directory', 'length', array('id' => $id));
            $length = $length ? $length : '3:00:00'; // in case present but falseish
            $length = strtotime("1970-01-01 $length UTC");

            foreach ($seconds as $second) {
                if ($second < $length) {
                    $radioarray[] = $mform->createElement('radio', 'thumb', $second, $second . ' ' . get_string('seconds'), $second);
                }
            }

            $mform->addGroup($radioarray, 'radioar', '', array(' '), false);
           
            $mform->addElement('hidden', 'id', $id);
            $mform->setType('id', PARAM_INT);
          
              $buttonarray = array();
            $buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
            $buttonarray[] =& $mform->createElement('cancel', 'cancel', get_string('cancel'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            
    }
    
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
      $id = $fromform->id;
       $record = array("id" => $id, "thumb" => $id . "-" . $fromform->thumb);
    $update = $DB->update_record("local_video_directory", $record);
    
    //generate also the big thumb and rename the small one
    rename($streaming_dir . $id . "-" . $fromform->thumb . ".png", $streaming_dir . $id . "-" . $fromform->thumb . "-mini.png");
    $timing = gmdate("H:i:s", $fromform->thumb );
    $thumb = $ffmpeg . " -i ". $streaming_dir . $id . ".mp4 -ss " . $timing . " -vframes 1 " . $streaming_dir . $id . "-" . $fromform->thumb . ".png";
    exec ( $thumb );
    

    // delete all other thumbs...
    foreach ($seconds as $second) {
        if ($second != $fromform->thumb) {
            $file = $converted . $id . "-" . $second . '.png';
            
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    // delete orig thumb
    $file = $converted . $id . '.png';
    if (file_exists($file)) {
        unlink($file);
    }
  
      redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.
    // Set default data (if any)
    // $mform->set_data($toform);
    echo $OUTPUT->header();
    echo get_string('choose_thumb', 'local_video_directory') . '<br>';
          
    $mform->display();
}
?>
<script>
local_video_directory_vars = {id: <?php echo $id ?>, seconds: <?php echo json_encode($seconds) ?>, errorcreatingthumbat: '<?php echo get_string('errorcreatingthumbat', 'local_video_directory') ?>', secondsstring: '<?php echo get_string('seconds') ?>'};
</script>
<?php
echo $OUTPUT->footer();
