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


$ffmpeg = $settings->ffmpeg;
$streamingurl = $settings->streaming.'/';
$streamingdir = $converted;

$id = optional_param('id', 0, PARAM_INT);
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
        global $CFG, $DB, $seconds, $streamingdir, $OUTPUT;
        $mform = $this->_form;

        // LOOP from array seconds...
        $radioarray = array();
        $id = optional_param('id', 0, PARAM_INT);
        $length = $DB->get_field('local_video_directory', 'length', array('id' => $id));
        $length = $length ? $length : '3:00:00'; // In case present but falseish.
        $length = strtotime("1970-01-01 $length UTC");

        foreach ($seconds as $second) {
            if ($second < $length) {
                $radioarray[] = $mform->createElement('radio', 'thumb', $second, $second . ' '
                    . get_string('seconds'), $second);
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

$mform = new simplehtml_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else if ($fromform = $mform->get_data()) {
    $id = $fromform->id;
    $record = array("id" => $id, "thumb" => $id . "-" . $fromform->thumb);
    $update = $DB->update_record("local_video_directory", $record);

    // Generate the big thumb and rename the small one.
    rename($streamingdir . $id . "-" . $fromform->thumb . ".png", $streamingdir . $id . "-" . $fromform->thumb . "-mini.png");
    $timing = gmdate("H:i:s", $fromform->thumb );
    $thumb = '"' . $ffmpeg . '" -i ' . $streamingdir . $id . ".mp4 -ss " . $timing
        . " -vframes 1 " . $streamingdir . $id . "-" . $fromform->thumb . ".png -y";
    $output = exec($thumb);

    // Delete all other thumbs...
    foreach ($seconds as $second) {
        if ($second != $fromform->thumb) {
            $file = $converted . $id . "-" . $second . '.png';

            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    // Delete orig thumb.
    $file = $converted . $id . '.png';
    
    if (file_exists($file)) {
        unlink($file);
    }

    redirect($CFG->wwwroot . '/local/video_directory/list.php');
} else {
    echo $OUTPUT->header();
    echo get_string('choose_thumb', 'local_video_directory') . '<br>';
    $mform->display();
}
?>
<script>
local_video_directory_vars = {id: <?php echo $id ?>, seconds: <?php echo json_encode($seconds) ?>,
    errorcreatingthumbat: '<?php echo get_string('errorcreatingthumbat', 'local_video_directory') ?>',
    secondsstring: '<?php echo get_string('seconds') ?>'};
</script>
<?php
echo $OUTPUT->footer();