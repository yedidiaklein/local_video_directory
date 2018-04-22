<?php
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
 * External Web Service Template
 *
 * @package    local_video_directory
 * @copyright  2018 Yedidia Klein OpenApp Israel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
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
     * @return int success (1)
     */
    public static function edit($videoid, $value, $field, $status) {
        global $USER, $DB;
        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::edit_parameters(),
                array('videoid' => $videoid, 'value' => $value, 'field' => $field, 'status' => $status));
        //Context validation
        $context = context_system::instance();
        self::validate_context($context);
        //Capability checking
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
}