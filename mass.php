<?php

require_once('init.php');
require_once("$CFG->libdir/formslib.php");
require_once('locallib.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('mass','local_video_directory'));
$PAGE->set_title(get_string('mass','local_video_directory'));
$PAGE->set_url('/local/video_directory/mass.php');
$PAGE->navbar->add(get_string('pluginname','local_video_directory'), new moodle_url('/local/video_directory/'));
$PAGE->navbar->add(get_string('mass','local_video_directory'));

class simplehtml_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG, $DB, $USER, $massdir, $wgetdir;
			
        	$mform = $this->_form; // Don't forget the underscore! 


			$mform->addElement('html', '<table class="generaltable">');
			$mform->addElement('html', '<tr><th style="width:10%">' .get_string('choose','local_video_directory'). '</th><th>' 
									. get_string('filename','local_video_directory') . '</th><th>' . get_string('size','local_video_directory') . '</th>'
									. '<th>' . get_string('download_status','local_video_directory') . '</th></tr>');


// files in download queue
			$wgets=$DB->get_records_sql('SELECT * FROM {local_video_directory_wget} WHERE owner_id= ?',array($USER->id));
			foreach ($wgets as $wget) {
					$mform->addElement('html', '<tr><td>');
					$mform->addElement('html', get_string('wget','local_video_directory'));
					$mform->addElement('html', '</td>');
					$filename=basename($wget->url);
					$mform->addElement('html','<td>'.$wget->url.'</td><td>');
					if ($wget->success == 1) {
						$mform->addElement('html',human_filesize(filesize($wgetdir.$filename)));
					}
					$mform->addElement('html','</td><td>' . get_string('wget_'.$wget->success,'local_video_directory') . '</td></tr>');
				}
				
					
			$files = listdir($massdir);
			foreach ($files as $entry) {
				$entry = str_replace($massdir, "", $entry);
				$mform->addElement('html', '<tr><td>');
				$mform->addElement('checkbox', base64_encode($entry), "");
				$mform->setDefault(base64_encode($entry),'checked');
				$mform->addElement('html', '</td>');
				$mform->addElement('html','<td>'.$entry.'</td><td>'.human_filesize(filesize($massdir."/".$entry)).'</td></tr>');
			}
				
			$mform->addElement('html', '</table>');

			$mform->addElement('tags', 'tags', get_string('tags'), array('itemtype' => 'local_video_directory', 'component' => 'local_video_directory'));
 				      
      		$buttonarray=array();
			$buttonarray[] =& $mform->createElement('submit', 'submitbutton', get_string('savechanges'));
			$buttonarray[] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
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
//	print_r($fromform);
	$context = context_system::instance();

  	foreach($fromform as $key => $value) {
		if (($key != "submitbutton") && ($key != "tags")) {
			$filename=base64_decode($key);
			$basename = basename($filename);
			$directory = str_replace($basename, "", $filename);
			
			$tags = $fromform->tags;
			
			if ($directory != "/") {
				// remove / at start and end
				$directory = preg_replace(array("/^\//","/\/$/"), "" , $directory);
				$directory = explode("/", $directory);
				if (is_array($fromform->tags)) {
					$tags=array_merge($fromform->tags,$directory);
				} else {
					$tags=$directory;
				}
			}
			$record=array('orig_filename' => $basename, 'owner_id' => $USER->id , 'private' => 1 );
			$lastinsertid = $DB->insert_record('local_video_directory', $record);
			$copied = copy($massdir . '/' . $filename ,$uploaddir . $lastinsertid);
			if ($copied) {
				unlink($massdir . '/' . $filename);
			}

			core_tag_tag::set_item_tags('local_video_directory', 'local_video_directory', $lastinsertid, $context, $tags);
			
		}
	}
	
	RemoveEmptySubFolders($massdir);	
  	
  
  	redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.
  //Set default data (if any)
  //  $mform->set_data($toform);
  //displays the form

    $PAGE->requires->css('/local/video_directory/style.css');
  
	echo $OUTPUT->header();
   	//Menu
	include('menu.php');
  
    $mform->display();
}

echo $OUTPUT->footer();

function listdir($start_dir='.') {

  $files = array();
  if (is_dir($start_dir)) {
    $fh = opendir($start_dir);
    while (($file = readdir($fh)) !== false) {
      # loop through the files, skipping . and .., and recursing if necessary
      if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
      $filepath = $start_dir . '/' . $file;
      if ( is_dir($filepath) )
        $files = array_merge($files, listdir($filepath));
      else
        array_push($files, $filepath);
    }
    closedir($fh);
  } else {
    # false if the function was called with an invalid non-directory argument
    $files = false;
  }

  return $files;

}

function RemoveEmptySubFolders($path)
{
  global $massdir;
  $empty=true;
  foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file)
  {
     $empty &= is_dir($file) && RemoveEmptySubFolders($file);
  }
  if ($path != $massdir)
  	return $empty && rmdir($path);
  else 
  	return $empty;
}

?>
